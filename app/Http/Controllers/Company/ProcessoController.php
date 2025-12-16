<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use App\Models\Processo;
use Illuminate\Http\Request;

class ProcessoController extends Controller
{
    public function index(Request $request)
    {
        $usuarioId = auth('externo')->id();
        
        // IDs dos estabelecimentos do usuário
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuarioId)->pluck('id');
        
        $query = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->with(['estabelecimento', 'tipoProcesso']);
        
        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtro por estabelecimento
        if ($request->filled('estabelecimento_id')) {
            $query->where('estabelecimento_id', $request->estabelecimento_id);
        }
        
        // Busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero', 'ilike', "%{$search}%");
            });
        }
        
        $processos = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Estatísticas
        $estatisticas = [
            'total' => Processo::whereIn('estabelecimento_id', $estabelecimentoIds)->count(),
            'em_andamento' => Processo::whereIn('estabelecimento_id', $estabelecimentoIds)->where('status', 'em_andamento')->count(),
            'concluidos' => Processo::whereIn('estabelecimento_id', $estabelecimentoIds)->where('status', 'concluido')->count(),
            'arquivados' => Processo::whereIn('estabelecimento_id', $estabelecimentoIds)->where('status', 'arquivado')->count(),
        ];
        
        // Lista de estabelecimentos para filtro
        $estabelecimentos = Estabelecimento::where('usuario_externo_id', $usuarioId)
            ->orderBy('nome_fantasia')
            ->get();
        
        return view('company.processos.index', compact('processos', 'estatisticas', 'estabelecimentos'));
    }
    
    public function show($id)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuarioId)->pluck('id');
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->with(['estabelecimento', 'tipoProcesso', 'documentos'])
            ->findOrFail($id);
        
        return view('company.processos.show', compact('processo'));
    }
}
