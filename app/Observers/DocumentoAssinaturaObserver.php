<?php

namespace App\Observers;

use App\Models\DocumentoAssinatura;
use App\Models\DocumentoDigital;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Log;

class DocumentoAssinaturaObserver
{
    /**
     * Quando uma assinatura é atualizada (assinada),
     * verifica se todas as assinaturas obrigatórias estão completas
     * e envia notificação via WhatsApp
     */
    public function updated(DocumentoAssinatura $assinatura): void
    {
        // Só processa se o status mudou para 'assinado'
        if (!$assinatura->wasChanged('status') || $assinatura->status !== 'assinado') {
            return;
        }

        $documento = $assinatura->documentoDigital;
        if (!$documento) {
            return;
        }

        // Verifica se TODAS as assinaturas obrigatórias estão completas
        if (!$documento->todasAssinaturasCompletas()) {
            return;
        }

        // Todas assinadas! Enviar WhatsApp
        try {
            $service = new WhatsappService();

            if (!$service->estaOperacional()) {
                Log::info('WhatsApp Observer: Serviço não operacional, pulando notificação', [
                    'documento_id' => $documento->id,
                ]);
                return;
            }

            $resultados = $service->notificarDocumentoAssinado($documento);

            Log::info('WhatsApp Observer: Notificação processada', [
                'documento_id' => $documento->id,
                'total' => $resultados['total'],
                'enviados' => $resultados['enviados'],
                'erros' => $resultados['erros'],
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp Observer: Erro ao enviar notificação', [
                'documento_id' => $documento->id,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}
