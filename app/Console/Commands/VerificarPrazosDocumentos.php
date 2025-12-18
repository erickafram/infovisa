<?php

namespace App\Console\Commands;

use App\Models\DocumentoDigital;
use Illuminate\Console\Command;

class VerificarPrazosDocumentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documentos:verificar-prazos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica documentos de notificação e inicia prazo automaticamente após 5 dias úteis de disponibilidade (§1º)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando prazos de documentos de notificação...');

        // Busca documentos de notificação assinados que ainda não tiveram o prazo iniciado
        $documentos = DocumentoDigital::where('prazo_notificacao', true)
            ->where('status', 'assinado')
            ->whereNull('prazo_iniciado_em')
            ->whereNotNull('prazo_dias')
            ->get();

        $iniciados = 0;

        foreach ($documentos as $documento) {
            // Verifica se todas as assinaturas estão completas
            if (!$documento->todasAssinaturasCompletas()) {
                continue;
            }

            // Verifica se já passou 5 dias úteis
            if ($documento->verificarInicioAutomaticoPrazo()) {
                $iniciados++;
                $this->line("  ✓ Prazo iniciado para documento #{$documento->numero_documento}");
            }
        }

        $this->info("Verificação concluída. {$iniciados} documento(s) tiveram o prazo iniciado automaticamente.");

        return Command::SUCCESS;
    }
}
