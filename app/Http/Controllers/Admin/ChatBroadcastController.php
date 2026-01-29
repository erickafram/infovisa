<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatBroadcast;
use App\Models\ChatBroadcastLeitura;
use App\Models\UsuarioInterno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatBroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = ChatBroadcast::with('remetente')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.configuracoes.chat-broadcast.index', compact('broadcasts'));
    }

    public function create()
    {
        return view('admin.configuracoes.chat-broadcast.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'conteudo' => 'required|string|max:5000',
            'niveis_acesso' => 'required|array|min:1',
            'niveis_acesso.*' => 'string',
            'arquivo' => 'nullable|file|max:10240',
        ], [
            'conteudo.required' => 'A mensagem é obrigatória.',
            'niveis_acesso.required' => 'Selecione pelo menos um nível de acesso.',
        ]);

        $dados = [
            'enviado_por' => auth('interno')->id(),
            'conteudo' => $request->conteudo,
            'niveis_acesso' => $request->niveis_acesso,
            'tipo' => 'texto',
        ];

        if ($request->hasFile('arquivo')) {
            $arquivo = $request->file('arquivo');
            $mime = $arquivo->getMimeType();
            
            if (Str::startsWith($mime, 'image/')) {
                $dados['tipo'] = 'imagem';
            } else {
                $dados['tipo'] = 'arquivo';
            }

            $path = $arquivo->store('chat-broadcast/' . date('Y/m'), 'public');
            $dados['arquivo_path'] = $path;
            $dados['arquivo_nome'] = $arquivo->getClientOriginalName();
        }

        ChatBroadcast::create($dados);

        return redirect()->route('admin.configuracoes.chat-broadcast.index')
            ->with('success', 'Mensagem enviada com sucesso para os usuários selecionados!');
    }

    public function destroy(ChatBroadcast $chatBroadcast)
    {
        // Remove arquivo se existir
        if ($chatBroadcast->arquivo_path) {
            Storage::disk('public')->delete($chatBroadcast->arquivo_path);
        }

        $chatBroadcast->delete();

        return redirect()->route('admin.configuracoes.chat-broadcast.index')
            ->with('success', 'Mensagem removida com sucesso!');
    }

    /**
     * Retorna estatísticas de leitura
     */
    public function estatisticas(ChatBroadcast $chatBroadcast)
    {
        $niveis = $chatBroadcast->niveis_acesso;
        
        // Busca usuários que deveriam receber
        $query = UsuarioInterno::where('ativo', true);
        
        if (!in_array('todos', $niveis)) {
            $query->whereIn('nivel_acesso', $niveis);
        }

        $totalUsuarios = $query->count();
        $leituras = ChatBroadcastLeitura::where('broadcast_id', $chatBroadcast->id)
            ->whereNotNull('lida_em')
            ->count();

        return response()->json([
            'total' => $totalUsuarios,
            'lidos' => $leituras,
            'percentual' => $totalUsuarios > 0 ? round(($leituras / $totalUsuarios) * 100) : 0,
        ]);
    }
}
