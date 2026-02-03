<?php

namespace App\Console\Commands;

use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\TipoProcesso;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbrirProcessosLicenciamentoAnual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'processos:licenciamento-anual 
                            {--ano= : Ano para criar os processos (padrão: ano atual)}
                            {--dry-run : Simular sem criar processos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Abre automaticamente processos de licenciamento sanitário para estabelecimentos ativos no início do ano';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ano = $this->option('ano') ?? date('Y');
        $dryRun = $this->option('dry-run');

        $this->info("===========================================");
        $this->info("Abertura Automática de Processos de Licenciamento");
        $this->info("Ano: {$ano}");
        $this->info("Modo: " . ($dryRun ? 'SIMULAÇÃO' : 'PRODUÇÃO'));
        $this->info("===========================================\n");

        // Verifica se o tipo de processo existe
        $tipoLicenciamento = TipoProcesso::where('codigo', 'licenciamento')
            ->where('ativo', true)
            ->first();

        if (!$tipoLicenciamento) {
            $this->error('Tipo de processo "licenciamento" não encontrado ou inativo!');
            return 1;
        }

        // Busca estabelecimentos elegíveis
        $estabelecimentos = $this->buscarEstabelecimentosElegiveis($ano);

        if ($estabelecimentos->isEmpty()) {
            $this->warn('Nenhum estabelecimento elegível encontrado.');
            return 0;
        }

        $this->info("Estabelecimentos elegíveis: {$estabelecimentos->count()}\n");

        $sucessos = 0;
        $erros = 0;
        $jaExistentes = 0;

        $progressBar = $this->output->createProgressBar($estabelecimentos->count());
        $progressBar->start();

        foreach ($estabelecimentos as $estabelecimento) {
            try {
                // Verifica se já existe processo de licenciamento para este ano
                $processoExistente = Processo::where('estabelecimento_id', $estabelecimento->id)
                    ->where('tipo', 'licenciamento')
                    ->where('ano', $ano)
                    ->first();

                if ($processoExistente) {
                    $jaExistentes++;
                    $progressBar->advance();
                    continue;
                }

                if (!$dryRun) {
                    // Cria o processo dentro de uma transação
                    DB::transaction(function () use ($estabelecimento, $ano, &$sucessos) {
                        $numeroProcesso = Processo::gerarNumeroProcesso($ano);
                        
                        // Busca o tipo de processo de licenciamento para pegar o setor configurado
                        $tipoProcesso = \App\Models\TipoProcesso::where('codigo', 'licenciamento')->first();
                        
                        // Prepara dados do processo
                        $dadosProcesso = [
                            'estabelecimento_id' => $estabelecimento->id,
                            'usuario_id' => null, // Sistema automático
                            'tipo' => 'licenciamento',
                            'ano' => $numeroProcesso['ano'],
                            'numero_sequencial' => $numeroProcesso['numero_sequencial'],
                            'numero_processo' => $numeroProcesso['numero_processo'],
                            'status' => 'aberto',
                            'observacoes' => 'Processo aberto automaticamente pelo sistema para renovação anual de licenciamento sanitário.',
                        ];
                        
                        // Se o tipo de processo tem setor configurado, atribui automaticamente
                        if ($tipoProcesso && $tipoProcesso->tipo_setor_id && $tipoProcesso->tipoSetor) {
                            $dadosProcesso['setor_atual'] = $tipoProcesso->tipoSetor->codigo;
                        }

                        Processo::create($dadosProcesso);

                        $sucessos++;
                    });

                    // Log da criação
                    Log::info("Processo de licenciamento criado automaticamente", [
                        'estabelecimento_id' => $estabelecimento->id,
                        'estabelecimento_nome' => $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social,
                        'ano' => $ano,
                    ]);
                } else {
                    $sucessos++;
                }

            } catch (\Exception $e) {
                $erros++;
                Log::error("Erro ao criar processo automático de licenciamento", [
                    'estabelecimento_id' => $estabelecimento->id,
                    'erro' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Relatório final
        $this->info("===========================================");
        $this->info("RELATÓRIO FINAL");
        $this->info("===========================================");
        $this->line("Total de estabelecimentos elegíveis: {$estabelecimentos->count()}");
        $this->line("Processos criados com sucesso: " . ($dryRun ? "{$sucessos} (simulação)" : $sucessos));
        $this->line("Processos já existentes: {$jaExistentes}");
        
        if ($erros > 0) {
            $this->error("Erros: {$erros}");
        } else {
            $this->line("Erros: {$erros}");
        }
        
        $this->info("===========================================\n");

        if ($dryRun) {
            $this->warn('MODO SIMULAÇÃO: Nenhum processo foi criado de fato.');
        }

        return 0;
    }

    /**
     * Busca estabelecimentos elegíveis para abertura automática de processo
     * 
     * Critérios:
     * 1. Estabelecimento ativo
     * 2. Teve processo de licenciamento no ano anterior
     * 3. Ainda não tem processo de licenciamento no ano atual
     */
    private function buscarEstabelecimentosElegiveis(int $ano)
    {
        $anoAnterior = $ano - 1;

        return Estabelecimento::where('ativo', true)
            ->whereHas('processos', function ($query) use ($anoAnterior) {
                $query->where('tipo', 'licenciamento')
                    ->where('ano', $anoAnterior);
            })
            ->whereDoesntHave('processos', function ($query) use ($ano) {
                $query->where('tipo', 'licenciamento')
                    ->where('ano', $ano);
            })
            ->with(['processos' => function ($query) use ($anoAnterior) {
                $query->where('tipo', 'licenciamento')
                    ->where('ano', $anoAnterior)
                    ->latest();
            }])
            ->orderBy('nome_fantasia')
            ->get();
    }
}
