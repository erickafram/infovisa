<?php

namespace App\Http\Controllers;

use App\Models\ChatBroadcast;
use App\Models\ChatBroadcastLeitura;
use App\Models\ChatConversa;
use App\Models\ChatMensagem;
use App\Models\ChatUsuarioOnline;
use App\Models\UsuarioInterno;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatInternoController extends Controller
{
    /**
     * Retorna lista de usuários para o chat
     */
    public function usuarios(): JsonResponse
    {
        $usuarioAtual = auth('interno')->user();
        $onlineIds = ChatUsuarioOnline::getUsuariosOnlineIds();

        $usuarios = UsuarioInterno::where('id', '!=', $usuarioAtual->id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->map(function ($usuario) use ($onlineIds, $usuarioAtual) {
                // Busca conversa existente para contar não lidas
                $conversa = ChatConversa::where(function ($q) use ($usuario, $usuarioAtual) {
                    $q->where('usuario1_id', $usuarioAtual->id)->where('usuario2_id', $usuario->id);
                })->orWhere(function ($q) use ($usuario, $usuarioAtual) {
                    $q->where('usuario1_id', $usuario->id)->where('usuario2_id', $usuarioAtual->id);
                })->first();

                $naoLidas = $conversa ? $conversa->mensagensNaoLidas($usuarioAtual->id) : 0;

                return [
                    'id' => $usuario->id,
                    'nome' => $this->formatarNome($usuario->nome),
                    'nome_completo' => $usuario->nome,
                    'iniciais' => $this->getIniciais($usuario->nome),
                    'tipo' => $usuario->isEstadual() || $usuario->isAdmin() ? 'Estadual' : 'Municipal',
                    'municipio' => $usuario->municipio,
                    'online' => in_array($usuario->id, $onlineIds),
                    'nao_lidas' => $naoLidas,
                ];
            });

        // Ordena: online primeiro, depois por não lidas, depois por nome
        $usuarios = $usuarios->sortByDesc('online')
            ->sortByDesc('nao_lidas')
            ->values();

        return response()->json($usuarios);
    }

    /**
     * Retorna conversas do usuário
     */
    public function conversas(): JsonResponse
    {
        $usuarioAtual = auth('interno')->user();
        $onlineIds = ChatUsuarioOnline::getUsuariosOnlineIds();

        $conversas = ChatConversa::where('usuario1_id', $usuarioAtual->id)
            ->orWhere('usuario2_id', $usuarioAtual->id)
            ->with(['usuario1', 'usuario2', 'ultimaMensagem.remetente'])
            ->orderByDesc('ultima_mensagem_at')
            ->get()
            ->map(function ($conversa) use ($usuarioAtual, $onlineIds) {
                $outroUsuario = $conversa->getOutroUsuario($usuarioAtual->id);
                $ultimaMensagem = $conversa->ultimaMensagem;

                return [
                    'id' => $conversa->id,
                    'usuario_id' => $outroUsuario->id,
                    'nome' => $this->formatarNome($outroUsuario->nome),
                    'iniciais' => $this->getIniciais($outroUsuario->nome),
                    'tipo' => $outroUsuario->isEstadual() || $outroUsuario->isAdmin() ? 'Estadual' : 'Municipal',
                    'online' => in_array($outroUsuario->id, $onlineIds),
                    'nao_lidas' => $conversa->mensagensNaoLidas($usuarioAtual->id),
                    'ultima_mensagem' => $ultimaMensagem ? [
                        'conteudo' => $this->resumirMensagem($ultimaMensagem),
                        'data' => $ultimaMensagem->created_at->diffForHumans(short: true),
                        'minha' => $ultimaMensagem->remetente_id === $usuarioAtual->id,
                    ] : null,
                ];
            });

        return response()->json($conversas);
    }

    /**
     * Retorna mensagens de uma conversa
     */
    public function mensagens(int $usuarioId): JsonResponse
    {
        $usuarioAtual = auth('interno')->user();
        
        // Encontra ou cria a conversa
        $conversa = ChatConversa::encontrarOuCriar($usuarioAtual->id, $usuarioId);

        // Marca mensagens como lidas
        $conversa->mensagens()
            ->where('remetente_id', $usuarioId)
            ->whereNull('lida_em')
            ->update(['lida_em' => now()]);

        // Verifica se o outro usuário está online
        $outroOnline = in_array($usuarioId, ChatUsuarioOnline::getUsuariosOnlineIds());

        // Busca mensagens
        $mensagens = $conversa->mensagens()
            ->with('remetente')
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) use ($usuarioAtual, $outroOnline) {
                $minha = $msg->remetente_id === $usuarioAtual->id;
                $deletada = $msg->deletada_para_todos;
                $podeDeletar = $minha && !$deletada && $msg->created_at->diffInMinutes(now()) <= 30;
                
                return [
                    'id' => $msg->id,
                    'conteudo' => $deletada ? null : $msg->conteudo,
                    'tipo' => $deletada ? 'deletada' : $msg->tipo,
                    'arquivo_url' => $deletada ? null : $msg->arquivo_url,
                    'arquivo_nome' => $deletada ? null : $msg->arquivo_nome,
                    'arquivo_tamanho' => $deletada ? null : $msg->tamanho_formatado,
                    'minha' => $minha,
                    'data' => $msg->created_at->format('H:i'),
                    'data_completa' => $msg->created_at->format('d/m/Y H:i'),
                    'lida' => $msg->lida_em !== null,
                    'entregue' => $minha ? ($msg->lida_em !== null || $outroOnline) : false,
                    'deletada' => $deletada,
                    'pode_deletar' => $podeDeletar,
                ];
            });

        return response()->json([
            'conversa_id' => $conversa->id,
            'mensagens' => $mensagens,
        ]);
    }

    /**
     * Envia uma mensagem
     */
    public function enviar(Request $request): JsonResponse
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios_internos,id',
            'conteudo' => 'required_without:arquivo|string|max:5000',
            'arquivo' => 'required_without:conteudo|file|max:10240', // 10MB
        ]);

        $usuarioAtual = auth('interno')->user();
        $conversa = ChatConversa::encontrarOuCriar($usuarioAtual->id, $request->usuario_id);

        $dados = [
            'conversa_id' => $conversa->id,
            'remetente_id' => $usuarioAtual->id,
            'conteudo' => $request->conteudo,
            'tipo' => 'texto',
        ];

        // Se tem arquivo
        if ($request->hasFile('arquivo')) {
            $arquivo = $request->file('arquivo');
            $mime = $arquivo->getMimeType();
            
            // Determina o tipo
            if (Str::startsWith($mime, 'image/')) {
                $dados['tipo'] = 'imagem';
            } elseif (Str::startsWith($mime, 'audio/')) {
                $dados['tipo'] = 'audio';
            } else {
                $dados['tipo'] = 'arquivo';
            }

            // Salva o arquivo
            $path = $arquivo->store('chat/' . date('Y/m'), 'public');
            
            $dados['arquivo_path'] = $path;
            $dados['arquivo_nome'] = $arquivo->getClientOriginalName();
            $dados['arquivo_mime'] = $mime;
            $dados['arquivo_tamanho'] = $arquivo->getSize();
            $dados['conteudo'] = null;
        }

        $mensagem = ChatMensagem::create($dados);

        // Atualiza timestamp da conversa
        $conversa->update(['ultima_mensagem_at' => now()]);

        // Verifica se destinatário está online
        $destinatarioOnline = in_array($request->usuario_id, ChatUsuarioOnline::getUsuariosOnlineIds());

        return response()->json([
            'success' => true,
            'mensagem' => [
                'id' => $mensagem->id,
                'conteudo' => $mensagem->conteudo,
                'tipo' => $mensagem->tipo,
                'arquivo_url' => $mensagem->arquivo_url,
                'arquivo_nome' => $mensagem->arquivo_nome,
                'arquivo_tamanho' => $mensagem->tamanho_formatado,
                'minha' => true,
                'data' => $mensagem->created_at->format('H:i'),
                'data_completa' => $mensagem->created_at->format('d/m/Y H:i'),
                'lida' => false,
                'entregue' => $destinatarioOnline,
            ],
        ]);
    }

    /**
     * Atualiza status online (heartbeat)
     */
    public function heartbeat(): JsonResponse
    {
        ChatUsuarioOnline::atualizarStatus(auth('interno')->id());
        return response()->json(['success' => true]);
    }

    /**
     * Verifica novas mensagens
     */
    public function verificarNovas(Request $request): JsonResponse
    {
        $usuarioAtual = auth('interno')->user();
        $ultimaId = $request->input('ultima_id', 0);
        $usuarioId = $request->input('usuario_id');

        // Atualiza status online
        ChatUsuarioOnline::atualizarStatus($usuarioAtual->id);

        $resultado = [
            'novas_mensagens' => [],
            'total_nao_lidas' => 0,
            'mensagens_lidas' => [], // IDs das mensagens que foram lidas
            'outro_online' => false,
        ];

        // Se está em uma conversa específica, busca novas mensagens
        if ($usuarioId) {
            $conversa = ChatConversa::where(function ($q) use ($usuarioId, $usuarioAtual) {
                $q->where('usuario1_id', $usuarioAtual->id)->where('usuario2_id', $usuarioId);
            })->orWhere(function ($q) use ($usuarioId, $usuarioAtual) {
                $q->where('usuario1_id', $usuarioId)->where('usuario2_id', $usuarioAtual->id);
            })->first();

            if ($conversa) {
                // Verifica se outro usuário está online
                $resultado['outro_online'] = in_array($usuarioId, ChatUsuarioOnline::getUsuariosOnlineIds());

                // Busca novas mensagens recebidas
                $novas = $conversa->mensagens()
                    ->where('id', '>', $ultimaId)
                    ->where('remetente_id', '!=', $usuarioAtual->id)
                    ->with('remetente')
                    ->get();

                // Marca como lidas
                $conversa->mensagens()
                    ->where('remetente_id', $usuarioId)
                    ->whereNull('lida_em')
                    ->update(['lida_em' => now()]);

                $resultado['novas_mensagens'] = $novas->map(function ($msg) {
                    return [
                        'id' => $msg->id,
                        'conteudo' => $msg->conteudo,
                        'tipo' => $msg->tipo,
                        'arquivo_url' => $msg->arquivo_url,
                        'arquivo_nome' => $msg->arquivo_nome,
                        'arquivo_tamanho' => $msg->tamanho_formatado,
                        'minha' => false,
                        'data' => $msg->created_at->format('H:i'),
                        'data_completa' => $msg->created_at->format('d/m/Y H:i'),
                        'lida' => false,
                        'entregue' => false,
                    ];
                });

                // Busca IDs das mensagens que eu enviei e foram lidas
                $resultado['mensagens_lidas'] = $conversa->mensagens()
                    ->where('remetente_id', $usuarioAtual->id)
                    ->whereNotNull('lida_em')
                    ->pluck('id')
                    ->toArray();
            }
        }

        // Conta total de não lidas
        $conversas = ChatConversa::where('usuario1_id', $usuarioAtual->id)
            ->orWhere('usuario2_id', $usuarioAtual->id)
            ->get();

        foreach ($conversas as $conv) {
            $resultado['total_nao_lidas'] += $conv->mensagensNaoLidas($usuarioAtual->id);
        }

        return response()->json($resultado);
    }

    /**
     * Formata o nome (primeiro e segundo nome, ou diferenciador se duplicado)
     */
    private function formatarNome(string $nomeCompleto): string
    {
        $partes = explode(' ', trim($nomeCompleto));
        
        if (count($partes) === 1) {
            return $partes[0];
        }

        // Retorna primeiro e segundo nome
        return $partes[0] . ' ' . $partes[1];
    }

    /**
     * Retorna as iniciais do nome
     */
    private function getIniciais(string $nome): string
    {
        $partes = explode(' ', trim($nome));
        $iniciais = strtoupper(substr($partes[0], 0, 1));
        
        if (count($partes) > 1) {
            $iniciais .= strtoupper(substr(end($partes), 0, 1));
        }

        return $iniciais;
    }

    /**
     * Resume a mensagem para preview
     */
    private function resumirMensagem(ChatMensagem $msg): string
    {
        if ($msg->deletada_para_todos) {
            return '🚫 Mensagem apagada';
        }
        if ($msg->tipo === 'imagem') {
            return '📷 Imagem';
        }
        if ($msg->tipo === 'audio') {
            return '🎵 Áudio';
        }
        if ($msg->tipo === 'arquivo') {
            return '📎 ' . ($msg->arquivo_nome ?? 'Arquivo');
        }

        $texto = $msg->conteudo ?? '';
        return Str::limit($texto, 30);
    }

    /**
     * Apaga uma mensagem (até 30 minutos após envio)
     */
    public function apagarMensagem(Request $request, int $mensagemId): JsonResponse
    {
        $usuarioAtual = auth('interno')->user();
        
        $mensagem = ChatMensagem::find($mensagemId);
        
        if (!$mensagem) {
            return response()->json(['success' => false, 'error' => 'Mensagem não encontrada'], 404);
        }

        // Verifica se é o remetente
        if ($mensagem->remetente_id !== $usuarioAtual->id) {
            return response()->json(['success' => false, 'error' => 'Você só pode apagar suas próprias mensagens'], 403);
        }

        // Verifica se está dentro do prazo de 30 minutos
        if ($mensagem->created_at->diffInMinutes(now()) > 30) {
            return response()->json(['success' => false, 'error' => 'O prazo de 30 minutos para apagar a mensagem expirou'], 400);
        }

        // Apaga para todos
        $mensagem->update([
            'deletada_em' => now(),
            'deletada_para_todos' => true,
            'conteudo' => null,
            'arquivo_path' => null,
            'arquivo_nome' => null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Busca usuários por nome
     */
    public function buscarUsuarios(Request $request): JsonResponse
    {
        $termo = $request->input('q', '');
        $usuarioAtual = auth('interno')->user();
        $onlineIds = ChatUsuarioOnline::getUsuariosOnlineIds();

        $query = UsuarioInterno::where('id', '!=', $usuarioAtual->id)
            ->where('ativo', true);

        if ($termo) {
            $query->where('nome', 'like', "%{$termo}%");
        }

        $usuarios = $query->orderBy('nome')
            ->limit(20)
            ->get()
            ->map(function ($usuario) use ($onlineIds, $usuarioAtual) {
                $conversa = ChatConversa::where(function ($q) use ($usuario, $usuarioAtual) {
                    $q->where('usuario1_id', $usuarioAtual->id)->where('usuario2_id', $usuario->id);
                })->orWhere(function ($q) use ($usuario, $usuarioAtual) {
                    $q->where('usuario1_id', $usuario->id)->where('usuario2_id', $usuarioAtual->id);
                })->first();

                return [
                    'id' => $usuario->id,
                    'nome' => $this->formatarNome($usuario->nome),
                    'nome_completo' => $usuario->nome,
                    'iniciais' => $this->getIniciais($usuario->nome),
                    'tipo' => $usuario->isEstadual() || $usuario->isAdmin() ? 'Estadual' : 'Municipal',
                    'municipio' => $usuario->municipio,
                    'online' => in_array($usuario->id, $onlineIds),
                    'nao_lidas' => $conversa ? $conversa->mensagensNaoLidas($usuarioAtual->id) : 0,
                ];
            });

        return response()->json($usuarios);
    }

    /**
     * Retorna mensagens do Suporte InfoVISA (broadcasts)
     */
    public function suporteMensagens(): JsonResponse
    {
        $usuarioAtual = auth('interno')->user();
        
        $broadcasts = ChatBroadcast::orderBy('created_at')
            ->get()
            ->filter(fn($b) => $b->podeVer($usuarioAtual))
            ->map(function ($broadcast) use ($usuarioAtual) {
                $leitura = ChatBroadcastLeitura::where('broadcast_id', $broadcast->id)
                    ->where('usuario_id', $usuarioAtual->id)
                    ->first();

                // Marca como lido se não existir registro
                if (!$leitura) {
                    $leitura = ChatBroadcastLeitura::create([
                        'broadcast_id' => $broadcast->id,
                        'usuario_id' => $usuarioAtual->id,
                        'lida_em' => now(),
                    ]);
                } elseif (!$leitura->lida_em) {
                    $leitura->update(['lida_em' => now()]);
                }

                return [
                    'id' => $broadcast->id,
                    'conteudo' => $broadcast->conteudo,
                    'tipo' => $broadcast->tipo,
                    'arquivo_url' => $broadcast->arquivo_path ? Storage::url($broadcast->arquivo_path) : null,
                    'arquivo_nome' => $broadcast->arquivo_nome,
                    'data' => $broadcast->created_at->format('H:i'),
                    'data_completa' => $broadcast->created_at->format('d/m/Y H:i'),
                    'minha' => false,
                ];
            })->values();

        return response()->json([
            'mensagens' => $broadcasts,
        ]);
    }

    /**
     * Conta broadcasts não lidos
     */
    public function suporteNaoLidos(): JsonResponse
    {
        $usuarioAtual = auth('interno')->user();
        
        $broadcasts = ChatBroadcast::all()->filter(fn($b) => $b->podeVer($usuarioAtual));
        
        $naoLidos = 0;
        foreach ($broadcasts as $broadcast) {
            $leitura = ChatBroadcastLeitura::where('broadcast_id', $broadcast->id)
                ->where('usuario_id', $usuarioAtual->id)
                ->whereNotNull('lida_em')
                ->first();
            
            if (!$leitura) {
                $naoLidos++;
            }
        }

        return response()->json(['nao_lidos' => $naoLidos]);
    }
}
