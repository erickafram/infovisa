<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMensagem;
use App\Services\WhatsappService;
use Illuminate\Http\Request;

class WhatsappPainelController extends Controller
{
    /**
     * Painel de mensagens WhatsApp
     */
    public function index(Request $request)
    {
        $query = WhatsappMensagem::with(['documentoDigital.tipoDocumento', 'estabelecimento', 'usuarioExterno'])
            ->orderBy('created_at', 'desc');

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por data
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        // Filtro por estabelecimento
        if ($request->filled('estabelecimento')) {
            $query->whereHas('estabelecimento', function ($q) use ($request) {
                $q->where('nome_fantasia', 'ilike', '%' . $request->estabelecimento . '%')
                  ->orWhere('razao_social', 'ilike', '%' . $request->estabelecimento . '%');
            });
        }

        // Filtro por destinatário
        if ($request->filled('destinatario')) {
            $query->where('nome_destinatario', 'ilike', '%' . $request->destinatario . '%');
        }

        $mensagens = $query->paginate(20)->withQueryString();

        // Estatísticas
        $estatisticas = [
            'total' => WhatsappMensagem::count(),
            'enviados' => WhatsappMensagem::where('status', 'enviado')->count(),
            'entregues' => WhatsappMensagem::where('status', 'entregue')->count(),
            'pendentes' => WhatsappMensagem::where('status', 'pendente')->count(),
            'erros' => WhatsappMensagem::where('status', 'erro')->count(),
            'lidos' => WhatsappMensagem::where('status', 'lido')->count(),
            'hoje' => WhatsappMensagem::whereDate('created_at', today())->count(),
        ];

        return view('admin.whatsapp.painel', compact('mensagens', 'estatisticas'));
    }

    /**
     * Detalhes de uma mensagem
     */
    public function detalhes($id)
    {
        $mensagem = WhatsappMensagem::with([
            'documentoDigital.tipoDocumento',
            'documentoDigital.processo',
            'estabelecimento',
            'usuarioExterno',
        ])->findOrFail($id);

        return response()->json([
            'id' => $mensagem->id,
            'destinatario' => $mensagem->nome_destinatario,
            'telefone' => $mensagem->telefone,
            'mensagem' => $mensagem->mensagem,
            'status' => $mensagem->status,
            'status_texto' => $mensagem->status_texto,
            'erro' => $mensagem->erro_mensagem,
            'tentativas' => $mensagem->tentativas,
            'documento' => $mensagem->documentoDigital ? [
                'id' => $mensagem->documentoDigital->id,
                'tipo' => $mensagem->documentoDigital->tipoDocumento->nome ?? 'N/A',
                'numero' => $mensagem->documentoDigital->numero_formatado ?? $mensagem->documentoDigital->id,
            ] : null,
            'estabelecimento' => $mensagem->estabelecimento ? [
                'id' => $mensagem->estabelecimento->id,
                'nome' => $mensagem->estabelecimento->nome_fantasia ?? $mensagem->estabelecimento->razao_social,
            ] : null,
            'enviado_em' => $mensagem->enviado_em?->format('d/m/Y H:i:s'),
            'entregue_em' => $mensagem->entregue_em?->format('d/m/Y H:i:s'),
            'lido_em' => $mensagem->lido_em?->format('d/m/Y H:i:s'),
            'criado_em' => $mensagem->created_at->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Reenviar uma mensagem com erro
     */
    public function reenviar($id)
    {
        $mensagem = WhatsappMensagem::findOrFail($id);

        if (!$mensagem->podeRetentar() && $mensagem->status !== 'erro') {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Esta mensagem não pode ser reenviada.',
            ], 422);
        }

        $service = new WhatsappService();
        $resultado = $service->enviarMensagem($mensagem->telefone, $mensagem->mensagem);

        if ($resultado['sucesso']) {
            $mensagem->marcarEnviado($resultado['message_id'] ?? null);
            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Mensagem reenviada com sucesso!',
            ]);
        }

        $mensagem->marcarErro($resultado['mensagem']);
        return response()->json([
            'sucesso' => false,
            'mensagem' => 'Falha ao reenviar: ' . $resultado['mensagem'],
        ]);
    }

    /**
     * Reenviar todas as mensagens com erro
     */
    public function reenviarTodas()
    {
        $service = new WhatsappService();
        $resultados = $service->retentarMensagensComErro();

        return response()->json([
            'sucesso' => true,
            'mensagem' => "Retentadas {$resultados['total']} mensagens: {$resultados['sucesso']} com sucesso, {$resultados['falha']} falharam.",
            'resultados' => $resultados,
        ]);
    }

    /**
     * Exportar log de mensagens (CSV)
     */
    public function exportar(Request $request)
    {
        $query = WhatsappMensagem::with(['documentoDigital.tipoDocumento', 'estabelecimento', 'usuarioExterno'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $mensagens = $query->get();

        $csv = "ID;Destinatário;Telefone;Documento;Estabelecimento;Status;Enviado Em;Erro\n";

        foreach ($mensagens as $m) {
            $csv .= implode(';', [
                $m->id,
                '"' . str_replace('"', '""', $m->nome_destinatario) . '"',
                $m->telefone,
                '"' . str_replace('"', '""', ($m->documentoDigital->tipoDocumento->nome ?? 'N/A') . ' #' . ($m->documentoDigital->numero_formatado ?? $m->documento_digital_id)) . '"',
                '"' . str_replace('"', '""', ($m->estabelecimento->nome_fantasia ?? 'N/A')) . '"',
                $m->status_texto,
                $m->enviado_em?->format('d/m/Y H:i:s') ?? '-',
                '"' . str_replace('"', '""', $m->erro_mensagem ?? '') . '"',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="whatsapp-mensagens-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
