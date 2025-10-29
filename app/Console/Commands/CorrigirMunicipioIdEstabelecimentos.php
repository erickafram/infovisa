<?php

namespace App\Console\Commands;

use App\Models\Estabelecimento;
use App\Models\Municipio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CorrigirMunicipioIdEstabelecimentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estabelecimentos:corrigir-municipio-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige o municipio_id dos estabelecimentos que estão com código IBGE ao invés do ID da tabela municipios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando estabelecimentos com municipio_id incorreto...');
        
        // Buscar estabelecimentos com municipio_id > 1000 (provavelmente código IBGE)
        $estabelecimentosComProblema = Estabelecimento::where('municipio_id', '>', 1000)->get();
        
        if ($estabelecimentosComProblema->isEmpty()) {
            $this->info('✅ Nenhum estabelecimento com problema encontrado!');
            return 0;
        }
        
        $this->warn("⚠️  Encontrados {$estabelecimentosComProblema->count()} estabelecimentos com municipio_id incorreto");
        
        $corrigidos = 0;
        $erros = 0;
        
        foreach ($estabelecimentosComProblema as $estabelecimento) {
            $codigoIbgeAtual = $estabelecimento->municipio_id;
            
            // Buscar município pelo código IBGE
            $municipio = Municipio::where('codigo_ibge', $codigoIbgeAtual)->first();
            
            if ($municipio) {
                $estabelecimento->municipio_id = $municipio->id;
                $estabelecimento->save();
                
                $this->line("✓ Estabelecimento #{$estabelecimento->id} ({$estabelecimento->nome_fantasia}): {$codigoIbgeAtual} → {$municipio->id} ({$municipio->nome})");
                $corrigidos++;
            } else {
                $this->error("✗ Estabelecimento #{$estabelecimento->id}: Município com código IBGE {$codigoIbgeAtual} não encontrado");
                $erros++;
            }
        }
        
        $this->newLine();
        $this->info("📊 Resumo:");
        $this->info("   ✅ Corrigidos: {$corrigidos}");
        if ($erros > 0) {
            $this->warn("   ⚠️  Erros: {$erros}");
        }
        
        // Verificar estabelecimentos sem municipio_id
        $semMunicipio = Estabelecimento::whereNull('municipio_id')->count();
        if ($semMunicipio > 0) {
            $this->newLine();
            $this->warn("⚠️  Atenção: {$semMunicipio} estabelecimentos ainda estão sem municipio_id preenchido");
            $this->info("   Execute: php artisan estabelecimentos:preencher-municipio-id");
        }
        
        return 0;
    }
}
