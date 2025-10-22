<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Responsavel;
use Illuminate\Http\Request;

class ResponsavelGlobalController extends Controller
{
    /**
     * Lista todos os responsáveis cadastrados (agrupados por CPF)
     */
    public function index(Request $request)
    {
        $query = Responsavel::query();
        
        // Busca por nome ou CPF
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'ILIKE', "%{$busca}%")
                  ->orWhere('cpf', 'LIKE', "%{$busca}%");
            });
        }
        
        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        // Buscar todos os responsáveis
        $todosResponsaveis = $query->orderBy('nome')->get();
        
        // Agrupar por CPF
        $responsaveisAgrupados = $todosResponsaveis->groupBy('cpf')->map(function ($grupo) {
            $primeiro = $grupo->first();
            $tipos = $grupo->pluck('tipo')->unique()->sort()->values();
            
            // Contar estabelecimentos únicos
            $estabelecimentosIds = $grupo->flatMap(function($resp) {
                return $resp->estabelecimentos->pluck('id');
            })->unique();
            
            return (object) [
                'id' => $primeiro->id,
                'cpf' => $primeiro->cpf,
                'cpf_formatado' => $primeiro->cpf_formatado,
                'nome' => $primeiro->nome,
                'email' => $primeiro->email,
                'telefone' => $primeiro->telefone,
                'telefone_formatado' => $primeiro->telefone_formatado,
                'tipos' => $tipos,
                'total_estabelecimentos' => $estabelecimentosIds->count(),
                'responsaveis_ids' => $grupo->pluck('id')->toArray()
            ];
        })->values();
        
        // Paginação manual
        $page = $request->get('page', 1);
        $perPage = 15;
        $total = $responsaveisAgrupados->count();
        $responsaveis = new \Illuminate\Pagination\LengthAwarePaginator(
            $responsaveisAgrupados->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return view('admin.responsaveis.index', compact('responsaveis'));
    }
    
    /**
     * Mostra detalhes do responsável e estabelecimentos vinculados (todos os tipos)
     */
    public function show($id)
    {
        // Buscar o responsável principal
        $responsavelPrincipal = Responsavel::findOrFail($id);
        
        // Buscar todos os registros com o mesmo CPF
        $todosRegistros = Responsavel::where('cpf', $responsavelPrincipal->cpf)
                                     ->with(['estabelecimentos' => function($query) {
                                         $query->withPivot('tipo_vinculo', 'ativo')
                                               ->orderBy('nome_fantasia');
                                     }])
                                     ->get();
        
        // Agrupar dados
        $responsavel = (object) [
            'id' => $responsavelPrincipal->id,
            'cpf' => $responsavelPrincipal->cpf,
            'cpf_formatado' => $responsavelPrincipal->cpf_formatado,
            'nome' => $responsavelPrincipal->nome,
            'email' => $responsavelPrincipal->email,
            'telefone' => $responsavelPrincipal->telefone,
            'telefone_formatado' => $responsavelPrincipal->telefone_formatado,
            'tipos' => $todosRegistros->pluck('tipo')->unique()->sort()->values(),
            'registros' => $todosRegistros,
            'estabelecimentos' => $todosRegistros->flatMap(function($reg) {
                return $reg->estabelecimentos->map(function($est) use ($reg) {
                    $est->tipo_responsavel = $reg->tipo;
                    return $est;
                });
            })->unique('id')->sortBy('nome_fantasia')->values()
        ];
        
        return view('admin.responsaveis.show', compact('responsavel'));
    }
}
