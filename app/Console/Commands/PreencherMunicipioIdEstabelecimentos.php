<?php

namespace App\Console\Commands;

use App\Models\Estabelecimento;
use App\Helpers\MunicipioHelper;
use Illuminate\Console\Command;

class PreencherMunicipioIdEstabelecimentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estabelecimentos:preencher-municipio-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preenche o municipio_id dos estabelecimentos que não têm, baseado no campo cidade';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Buscando estabelecimentos sem municipio_id...');
        
        $estabelecimentos = Estabelecimento::whereNull('municipio_id')
            ->orWhere('municipio_id', 0)
            ->get();
        
        if ($estabelecimentos->isEmpty()) {
            $this->info('✅ Todos os estabelecimentos já têm municipio_id preenchido!');
            return 0;
        }
        
        $this->warn("⚠️  Encontrados {$estabelecimentos->count()} estabelecimentos sem municipio_id");
        
        $preenchidos = 0;
        $naoEncontrados = 0;
        
        foreach ($estabelecimentos as $estabelecimento) {
            $cidade = $estabelecimento->cidade;
            $estado = $estabelecimento->estado;
            
            // Tentar obter o município pelo nome
            $municipioId = MunicipioHelper::normalizarEObterIdPorNome($cidade, null);
            
            if ($municipioId) {
                $estabelecimento->municipio_id = $municipioId;
                $estabelecimento->save();
                
                $this->line("✓ Estabelecimento #{$estabelecimento->id} ({$estabelecimento->nome_fantasia}): {$cidade}/{$estado} → municipio_id = {$municipioId}");
                $preenchidos++;
            } else {
                $this->error("✗ Estabelecimento #{$estabelecimento->id}: Município '{$cidade}/{$estado}' não encontrado na base");
                $naoEncontrados++;
            }
        }
        
        $this->newLine();
        $this->info("📊 Resumo:");
        $this->info("   ✅ Preenchidos: {$preenchidos}");
        if ($naoEncontrados > 0) {
            $this->warn("   ⚠️  Não encontrados: {$naoEncontrados}");
        }
        
        return 0;
    }
}
