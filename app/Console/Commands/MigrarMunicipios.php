<?php

namespace App\Console\Commands;

use App\Helpers\MunicipioHelper;
use App\Models\Estabelecimento;
use App\Models\Municipio;
use App\Models\Pactuacao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarMunicipios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'municipios:migrar {--force : Força a migração mesmo que já existam dados}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra dados de municípios (string) para relacionamento com tabela municipios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migração de municípios...');

        DB::beginTransaction();

        try {
            // 1. Migrar estabelecimentos
            $this->info('Migrando estabelecimentos...');
            $this->migrarEstabelecimentos();

            // 2. Migrar pactuações
            $this->info('Migrando pactuações...');
            $this->migrarPactuacoes();

            DB::commit();
            $this->info('Migração concluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erro na migração: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Migra municípios dos estabelecimentos
     */
    protected function migrarEstabelecimentos()
    {
        // Busca estabelecimentos sem municipio_id e que tenham cidade ou municipio
        $estabelecimentos = Estabelecimento::whereNull('municipio_id')
            ->where(function($query) {
                $query->whereNotNull('municipio')
                      ->orWhereNotNull('cidade');
            })
            ->get();

        $bar = $this->output->createProgressBar($estabelecimentos->count());
        $bar->start();

        $migrados = 0;
        $erros = 0;
        $semMunicipio = 0;

        foreach ($estabelecimentos as $estabelecimento) {
            try {
                // Tenta usar código IBGE se disponível
                $codigoIbge = $estabelecimento->codigo_municipio_ibge;
                
                // Usa municipio se preenchido, senão usa cidade
                $nomeMunicipio = $estabelecimento->municipio ?: $estabelecimento->cidade;

                if (empty($nomeMunicipio)) {
                    $semMunicipio++;
                    $bar->advance();
                    continue;
                }

                // Remove " - TO" ou "/TO" do nome se existir
                $nomeMunicipio = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', $nomeMunicipio);

                // Normaliza e obtém o ID do município
                $municipioId = MunicipioHelper::normalizarEObterIdPorNome($nomeMunicipio, $codigoIbge);

                if ($municipioId) {
                    $estabelecimento->municipio_id = $municipioId;
                    
                    // Atualiza também o campo municipio se estiver vazio
                    if (empty($estabelecimento->municipio)) {
                        $estabelecimento->municipio = $nomeMunicipio;
                    }
                    
                    $estabelecimento->save();
                    $migrados++;
                } else {
                    $erros++;
                    $this->warn("\nNão foi possível migrar: {$nomeMunicipio} (Código IBGE: {$codigoIbge})");
                }
            } catch (\Exception $e) {
                $erros++;
                $this->error("\nErro ao migrar estabelecimento {$estabelecimento->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Estabelecimentos migrados: {$migrados}");
        if ($semMunicipio > 0) {
            $this->warn("Sem município/cidade: {$semMunicipio}");
        }
        if ($erros > 0) {
            $this->warn("Erros: {$erros}");
        }
    }

    /**
     * Migra municípios das pactuações
     */
    protected function migrarPactuacoes()
    {
        // Migrar pactuações municipais
        $pactuacoesMunicipais = Pactuacao::where('tipo', 'municipal')
            ->whereNull('municipio_id')
            ->whereNotNull('municipio')
            ->get();

        $this->info("Migrando {$pactuacoesMunicipais->count()} pactuações municipais...");

        foreach ($pactuacoesMunicipais as $pactuacao) {
            try {
                $municipioId = MunicipioHelper::normalizarEObterIdPorNome($pactuacao->municipio);
                if ($municipioId) {
                    $pactuacao->municipio_id = $municipioId;
                    $pactuacao->save();
                }
            } catch (\Exception $e) {
                $this->error("Erro ao migrar pactuação {$pactuacao->id}: " . $e->getMessage());
            }
        }

        // Migrar exceções de pactuações estaduais
        $pactuacoesEstaduais = Pactuacao::where('tipo', 'estadual')
            ->whereNotNull('municipios_excecao')
            ->get();

        $this->info("Migrando exceções de {$pactuacoesEstaduais->count()} pactuações estaduais...");

        foreach ($pactuacoesEstaduais as $pactuacao) {
            try {
                if (!is_array($pactuacao->municipios_excecao) || empty($pactuacao->municipios_excecao)) {
                    continue;
                }

                $municipiosIds = [];
                foreach ($pactuacao->municipios_excecao as $nomeMunicipio) {
                    $municipioId = MunicipioHelper::normalizarEObterIdPorNome($nomeMunicipio);
                    if ($municipioId) {
                        $municipiosIds[] = $municipioId;
                    }
                }

                if (!empty($municipiosIds)) {
                    $pactuacao->municipios_excecao_ids = $municipiosIds;
                    $pactuacao->save();
                }
            } catch (\Exception $e) {
                $this->error("Erro ao migrar exceções da pactuação {$pactuacao->id}: " . $e->getMessage());
            }
        }

        $this->info('Pactuações migradas com sucesso!');
    }
}
