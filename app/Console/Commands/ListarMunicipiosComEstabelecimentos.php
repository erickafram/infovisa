<?php

namespace App\Console\Commands;

use App\Models\Municipio;
use Illuminate\Console\Command;

class ListarMunicipiosComEstabelecimentos extends Command
{
    protected $signature = 'municipios:listar-estabelecimentos';
    protected $description = 'Lista municípios com seus estabelecimentos';

    public function handle()
    {
        $this->info('Municípios com estabelecimentos:');
        $this->newLine();

        $municipios = Municipio::has('estabelecimentos')
            ->withCount('estabelecimentos')
            ->orderBy('estabelecimentos_count', 'desc')
            ->get();

        if ($municipios->isEmpty()) {
            $this->warn('Nenhum município com estabelecimentos encontrado.');
            return 0;
        }

        foreach ($municipios as $municipio) {
            $this->line("• {$municipio->nome}: {$municipio->estabelecimentos_count} estabelecimento(s)");
        }

        $this->newLine();
        $this->info("Total: {$municipios->count()} município(s) com estabelecimentos");

        return 0;
    }
}
