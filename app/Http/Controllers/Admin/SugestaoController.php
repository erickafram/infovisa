<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sugestao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SugestaoController extends Controller
{
    /**
     * Lista todas as sugestões (com filtros opcionais)
     */
    public function index(Request $request)
    {
        $query = Sugestao::with(['usuario', 'adminResponsavel'])
            ->orderBy('created_at', 'desc');

        // Filtro por página/URL
        if ($request->filled('pagina_url')) {
            $query->where('pagina_url', $request->pagina_url);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por minhas sugestões
        if ($request->boolean('minhas')) {
            $query->where('usuario_interno_id', Auth::guard('interno')->id());
        }

        $sugestoes = $query->paginate(20);

        // Adiciona informações de permissão para cada sugestão
        $usuario = Auth::guard('interno')->user();
        $sugestoes->getCollection()->transform(function ($sugestao) use ($usuario) {
            $sugestao->pode_editar = $sugestao->podeEditar($usuario);
            $sugestao->pode_excluir = $sugestao->podeExcluir($usuario);
            $sugestao->pode_gerenciar = $sugestao->podeGerenciar($usuario);
            return $sugestao;
        });

        return response()->json([
            'success' => true,
            'data' => $sugestoes,
            'tipos' => Sugestao::TIPOS,
            'status_list' => Sugestao::STATUS,
        ]);
    }

    /**
     * Cria uma nova sugestão
     */
    public function store(Request $request)
    {
        $request->validate([
            'pagina_url' => 'required|string|max:255',
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'tipo' => 'required|in:funcionalidade,melhoria,modulo,correcao,outro',
        ]);

        $sugestao = Sugestao::create([
            'usuario_interno_id' => Auth::guard('interno')->id(),
            'pagina_url' => $request->pagina_url,
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'tipo' => $request->tipo,
            'status' => 'pendente',
        ]);

        $sugestao->load(['usuario', 'adminResponsavel']);

        return response()->json([
            'success' => true,
            'message' => 'Sugestão enviada com sucesso!',
            'data' => $sugestao,
        ]);
    }

    /**
     * Exibe uma sugestão específica
     */
    public function show(Sugestao $sugestao)
    {
        $sugestao->load(['usuario', 'adminResponsavel']);
        
        $usuario = Auth::guard('interno')->user();
        $sugestao->pode_editar = $sugestao->podeEditar($usuario);
        $sugestao->pode_excluir = $sugestao->podeExcluir($usuario);
        $sugestao->pode_gerenciar = $sugestao->podeGerenciar($usuario);

        return response()->json([
            'success' => true,
            'data' => $sugestao,
        ]);
    }

    /**
     * Atualiza uma sugestão (criador ou admin)
     */
    public function update(Request $request, Sugestao $sugestao)
    {
        $usuario = Auth::guard('interno')->user();

        if (!$sugestao->podeEditar($usuario)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar esta sugestão.',
            ], 403);
        }

        $rules = [
            'titulo' => 'sometimes|required|string|max:255',
            'descricao' => 'sometimes|required|string',
            'tipo' => 'sometimes|required|in:funcionalidade,melhoria,modulo,correcao,outro',
        ];

        // Admin pode atualizar campos adicionais
        if ($usuario->isAdmin()) {
            $rules['status'] = 'sometimes|required|in:pendente,em_analise,em_desenvolvimento,concluido,cancelado';
            $rules['resposta_admin'] = 'nullable|string';
            $rules['checklist'] = 'nullable|array';
            $rules['checklist.*.texto'] = 'required|string';
            $rules['checklist.*.concluido'] = 'boolean';
        }

        $request->validate($rules);

        $data = $request->only(['titulo', 'descricao', 'tipo']);

        if ($usuario->isAdmin()) {
            if ($request->has('status')) {
                $data['status'] = $request->status;
                
                // Se marcou como concluído, registra a data
                if ($request->status === 'concluido' && $sugestao->status !== 'concluido') {
                    $data['concluido_em'] = now();
                    $data['admin_responsavel_id'] = $usuario->id;
                }
            }
            
            if ($request->has('resposta_admin')) {
                $data['resposta_admin'] = $request->resposta_admin;
            }
            
            if ($request->has('checklist')) {
                $data['checklist'] = $request->checklist;
            }

            // Se não tem admin responsável ainda, atribui o atual
            if (!$sugestao->admin_responsavel_id && $request->hasAny(['status', 'resposta_admin', 'checklist'])) {
                $data['admin_responsavel_id'] = $usuario->id;
            }
        }

        $sugestao->update($data);
        $sugestao->load(['usuario', 'adminResponsavel']);

        return response()->json([
            'success' => true,
            'message' => 'Sugestão atualizada com sucesso!',
            'data' => $sugestao,
        ]);
    }

    /**
     * Exclui uma sugestão
     */
    public function destroy(Sugestao $sugestao)
    {
        $usuario = Auth::guard('interno')->user();

        if (!$sugestao->podeExcluir($usuario)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir esta sugestão.',
            ], 403);
        }

        $sugestao->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sugestão excluída com sucesso!',
        ]);
    }

    /**
     * Atualiza item do checklist (apenas admin)
     */
    public function toggleChecklistItem(Request $request, Sugestao $sugestao)
    {
        $usuario = Auth::guard('interno')->user();

        if (!$usuario->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem atualizar o checklist.',
            ], 403);
        }

        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $checklist = $sugestao->checklist ?? [];
        $index = $request->index;

        if (!isset($checklist[$index])) {
            return response()->json([
                'success' => false,
                'message' => 'Item não encontrado no checklist.',
            ], 404);
        }

        $checklist[$index]['concluido'] = !($checklist[$index]['concluido'] ?? false);
        $sugestao->update(['checklist' => $checklist]);

        return response()->json([
            'success' => true,
            'message' => 'Item atualizado!',
            'data' => $sugestao,
        ]);
    }

    /**
     * Adiciona item ao checklist (apenas admin)
     */
    public function addChecklistItem(Request $request, Sugestao $sugestao)
    {
        $usuario = Auth::guard('interno')->user();

        if (!$usuario->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem adicionar itens ao checklist.',
            ], 403);
        }

        $request->validate([
            'texto' => 'required|string|max:255',
        ]);

        $checklist = $sugestao->checklist ?? [];
        $checklist[] = [
            'texto' => $request->texto,
            'concluido' => false,
        ];

        $sugestao->update(['checklist' => $checklist]);

        return response()->json([
            'success' => true,
            'message' => 'Item adicionado ao checklist!',
            'data' => $sugestao,
        ]);
    }

    /**
     * Remove item do checklist (apenas admin)
     */
    public function removeChecklistItem(Request $request, Sugestao $sugestao)
    {
        $usuario = Auth::guard('interno')->user();

        if (!$usuario->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem remover itens do checklist.',
            ], 403);
        }

        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $checklist = $sugestao->checklist ?? [];
        $index = $request->index;

        if (!isset($checklist[$index])) {
            return response()->json([
                'success' => false,
                'message' => 'Item não encontrado no checklist.',
            ], 404);
        }

        array_splice($checklist, $index, 1);
        $sugestao->update(['checklist' => array_values($checklist)]);

        return response()->json([
            'success' => true,
            'message' => 'Item removido do checklist!',
            'data' => $sugestao,
        ]);
    }

    /**
     * Estatísticas das sugestões (para admin)
     */
    public function estatisticas()
    {
        $stats = [
            'total' => Sugestao::count(),
            'pendentes' => Sugestao::where('status', 'pendente')->count(),
            'em_analise' => Sugestao::where('status', 'em_analise')->count(),
            'em_desenvolvimento' => Sugestao::where('status', 'em_desenvolvimento')->count(),
            'concluidas' => Sugestao::where('status', 'concluido')->count(),
            'canceladas' => Sugestao::where('status', 'cancelado')->count(),
            'por_tipo' => Sugestao::selectRaw('tipo, count(*) as total')
                ->groupBy('tipo')
                ->pluck('total', 'tipo'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
