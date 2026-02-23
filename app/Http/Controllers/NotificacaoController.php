<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificacao;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    /**
     * Retorna notificações do usuário logado
     */
    public function index()
    {
        $usuario = Auth::guard('interno')->user();
        
        $notificacoes = Notificacao::doUsuario($usuario->id)
            ->recentes()
            ->with('ordemServico')
            ->paginate(20);
        
        return view('notificacoes.index', compact('notificacoes'));
    }

    /**
     * API: Retorna notificações não lidas (para o sino)
     */
    public function naoLidas()
    {
        $usuario = Auth::guard('interno')->user();
        
        $notificacoes = Notificacao::doUsuario($usuario->id)
            ->naoLidas()
            ->recentes()
            ->limit(10)
            ->get();
        
        $total = Notificacao::doUsuario($usuario->id)
            ->naoLidas()
            ->count();
        
        return response()->json([
            'notificacoes' => $notificacoes,
            'total' => $total
        ]);
    }

    /**
     * Marcar notificação como lida
     */
    public function marcarComoLida($id)
    {
        $usuario = Auth::guard('interno')->user();
        
        $notificacao = Notificacao::doUsuario($usuario->id)
            ->findOrFail($id);
        
        $notificacao->marcarComoLida();

        // Se for notificação de OS, marca também duplicadas relacionadas
        // (mesmo tipo + mesma OS por id ou por link), incluindo casos legados sem ordem_servico_id.
        if (str_starts_with((string) $notificacao->tipo, 'ordem_servico') || $notificacao->tipo === 'atividade_reiniciada') {
            Notificacao::doUsuario($usuario->id)
                ->naoLidas()
                ->where('tipo', $notificacao->tipo)
                ->where(function ($query) use ($notificacao) {
                    if (!empty($notificacao->ordem_servico_id)) {
                        $query->where('ordem_servico_id', $notificacao->ordem_servico_id);
                    }

                    if (!empty($notificacao->link)) {
                        $query->orWhere('link', $notificacao->link);
                    }
                })
                ->update([
                    'lida' => true,
                    'lida_em' => now(),
                ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Notificação marcada como lida'
        ]);
    }

    /**
     * Marcar todas como lidas
     */
    public function marcarTodasComoLidas()
    {
        $usuario = Auth::guard('interno')->user();
        
        Notificacao::doUsuario($usuario->id)
            ->naoLidas()
            ->update([
                'lida' => true,
                'lida_em' => now()
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Todas as notificações foram marcadas como lidas'
        ]);
    }
}
