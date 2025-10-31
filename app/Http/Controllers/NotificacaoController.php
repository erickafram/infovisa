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
