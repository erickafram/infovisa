<?php

namespace App\Console\Commands;

use App\Models\Estabelecimento;
use App\Models\Processo;
use Illuminate\Console\Command;

class ListarEstabelecimentosElegiveis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'processos:listar-elegiveis 
                            {--ano= : Ano para verificar (padrão: ano atual)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista estabelecimentos elegíveis para abertura automática de processo de licenciamento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ano = $this->option('ano') ?? date('Y');
        $anoAnterior = $ano - 1;

        $this->info("===========================================");
        $this->info("Estabelecimentos Elegíveis para Licenciamento {$ano}");
        $this->info("===========================================\n");

        // Busca estabelecimentos elegíveis
        $estabelecimentos = Estabelecimento::where('ativo', true)
            ->whereHas('processos', function ($query) use ($anoAnterior) {
                $query->where('tipo', 'licenciamento')
                    ->where('ano', $anoAnterior);
            })
            ->with(['processos' => function ($query) use ($anoAnterior, $ano) {
                $query->where('tipo', 'licenciamento')
                    ->whereIn('ano', [$anoAnterior, $ano])
                    ->latest();
            }])
            ->orderBy('nome_fantasia')
            ->get();

        if ($estabelecimentos->isEmpty()) {
            $this->warn('Nenhum estabelecimento elegível encontrado.');
            return 0;
        }

        // Prepara dados para tabela
        $dados = [];
        $comProcesso = 0;
        $semProcesso = 0;

        foreach ($estabelecimentos as $estabelecimento) {
            $processoAnoAnterior = $estabelecimento->processos
                ->where('ano', $anoAnterior)
                ->first();

            $processoAnoAtual = $estabelecimento->processos
                ->where('ano', $ano)
                ->first();

            $status = $processoAnoAtual ? '✓ Já tem processo' : '✗ Precisa criar';
            
            if ($processoAnoAtual) {
                $comProcesso++;
            } else {
                $semProcesso++;
            }

            $dados[] = [
                'ID' => $estabelecimento->id,
                'Nome' => substr($estabelecimento->nome_fantasia ?? $estabelecimento->razao_social, 0, 40),
                'CNPJ/CPF' => $estabelecimento->cnpj ?? $estabelecimento->cpf,
                "Processo {$anoAnterior}" => $processoAnoAnterior ? $processoAnoAnterior->numero_processo : '-',
                "Status {$ano}" => $status,
            ];
        }

        // Exibe tabela
        $this->table(
            ['ID', 'Nome', 'CNPJ/CPF', "Processo {$anoAnterior}", "Status {$ano}"],
            $dados
        );

        // Resumo
        $this->newLine();
        $this->info("===========================================");
        $this->info("RESUMO");
        $this->info("===========================================");
        $this->line("Total de estabelecimentos elegíveis: {$estabelecimentos->count()}");
        $this->line("Já possuem processo em {$ano}: {$comProcesso}");
        $this->line("Precisam de processo em {$ano}: {$semProcesso}");
        $this->info("===========================================\n");

        if ($semProcesso > 0) {
            $this->warn("Execute o comando abaixo para criar os processos:");
            $this->line("php artisan processos:licenciamento-anual --ano={$ano}");
        }

        return 0;
    }
}
