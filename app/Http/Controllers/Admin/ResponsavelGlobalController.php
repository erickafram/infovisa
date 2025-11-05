<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Responsavel;
use Illuminate\Http\Request;

class ResponsavelGlobalController extends Controller
{
    /**
     * Lista todos os responsáveis cadastrados (agrupados por CPF)
     * Filtra por competência: usuários municipais veem apenas responsáveis de estabelecimentos do seu município
     * Usuários estaduais veem apenas responsáveis de estabelecimentos de competência estadual
     */
    public function index(Request $request)
    {
        $usuario = auth('interno')->user();
        
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
        
        // Buscar todos os responsáveis com seus estabelecimentos
        $todosResponsaveis = $query->with('estabelecimentos')->orderBy('nome')->get();
        
        // Filtrar responsáveis por competência (apenas se não for administrador)
        if (!$usuario->isAdmin()) {
            $todosResponsaveis = $todosResponsaveis->filter(function($responsavel) use ($usuario) {
                // Pega todos os estabelecimentos vinculados a este responsável
                $estabelecimentos = $responsavel->estabelecimentos;
                
                // Se não tem estabelecimentos vinculados, não mostra
                if ($estabelecimentos->isEmpty()) {
                    return false;
                }
                
                // Filtra estabelecimentos por competência
                $estabelecimentosPermitidos = $estabelecimentos->filter(function($estabelecimento) use ($usuario) {
                    // Usuário ESTADUAL: vê apenas estabelecimentos de competência ESTADUAL
                    if ($usuario->isEstadual()) {
                        return $estabelecimento->isCompetenciaEstadual();
                    }
                    
                    // Usuário MUNICIPAL: vê apenas estabelecimentos de competência MUNICIPAL do seu município
                    if ($usuario->isMunicipal()) {
                        // Verifica se é do município do usuário
                        $municipioEstabelecimento = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', trim($estabelecimento->cidade));
                        $municipioUsuario = $usuario->municipio; // municipio já é uma string (accessor)
                        
                        $mesmoMunicipio = $municipioEstabelecimento === $municipioUsuario;
                        
                        return $mesmoMunicipio && $estabelecimento->isCompetenciaMunicipal();
                    }
                    
                    return false;
                });
                
                // Só mostra o responsável se ele tiver pelo menos um estabelecimento permitido
                return $estabelecimentosPermitidos->isNotEmpty();
            });
        }
        
        // Agrupar por CPF
        $responsaveisAgrupados = $todosResponsaveis->groupBy('cpf')->map(function ($grupo) use ($usuario) {
            $primeiro = $grupo->first();
            $tipos = $grupo->pluck('tipo')->unique()->sort()->values();
            
            // Contar estabelecimentos únicos (filtrados por competência)
            $estabelecimentosIds = $grupo->flatMap(function($resp) use ($usuario) {
                $estabelecimentos = $resp->estabelecimentos;
                
                // Se for administrador, retorna todos
                if ($usuario->isAdmin()) {
                    return $estabelecimentos->pluck('id');
                }
                
                // Filtra por competência
                return $estabelecimentos->filter(function($estabelecimento) use ($usuario) {
                    if ($usuario->isEstadual()) {
                        return $estabelecimento->isCompetenciaEstadual();
                    }
                    
                    if ($usuario->isMunicipal()) {
                        $municipioEstabelecimento = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', trim($estabelecimento->cidade));
                        $municipioUsuario = $usuario->municipio; // municipio já é uma string (accessor)
                        $mesmoMunicipio = $municipioEstabelecimento === $municipioUsuario;
                        
                        return $mesmoMunicipio && $estabelecimento->isCompetenciaMunicipal();
                    }
                    
                    return false;
                })->pluck('id');
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
     * Filtra estabelecimentos por competência
     */
    public function show($id)
    {
        $usuario = auth('interno')->user();
        
        // Buscar o responsável principal
        $responsavelPrincipal = Responsavel::findOrFail($id);
        
        // Buscar todos os registros com o mesmo CPF
        $todosRegistros = Responsavel::where('cpf', $responsavelPrincipal->cpf)
                                     ->with(['estabelecimentos' => function($query) {
                                         $query->withPivot('tipo_vinculo', 'ativo')
                                               ->orderBy('nome_fantasia');
                                     }])
                                     ->get();
        
        // Filtrar estabelecimentos por competência
        $estabelecimentosFiltrados = $todosRegistros->flatMap(function($reg) use ($usuario) {
            return $reg->estabelecimentos->filter(function($est) use ($usuario) {
                // Administrador vê todos
                if ($usuario->isAdmin()) {
                    return true;
                }
                
                // Usuário ESTADUAL: apenas estabelecimentos de competência estadual
                if ($usuario->isEstadual()) {
                    return $est->isCompetenciaEstadual();
                }
                
                // Usuário MUNICIPAL: apenas estabelecimentos do seu município e competência municipal
                if ($usuario->isMunicipal()) {
                    $municipioEstabelecimento = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', trim($est->cidade));
                    $municipioUsuario = $usuario->municipio; // municipio já é uma string (accessor)
                    $mesmoMunicipio = $municipioEstabelecimento === $municipioUsuario;
                    
                    return $mesmoMunicipio && $est->isCompetenciaMunicipal();
                }
                
                return false;
            })->map(function($est) use ($reg) {
                $est->tipo_responsavel = $reg->tipo;
                return $est;
            });
        })->unique('id')->sortBy('nome_fantasia')->values();
        
        // Validar se o usuário tem permissão para ver este responsável
        if (!$usuario->isAdmin() && $estabelecimentosFiltrados->isEmpty()) {
            abort(403, 'Você não tem permissão para visualizar este responsável.');
        }
        
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
            'estabelecimentos' => $estabelecimentosFiltrados
        ];
        
        return view('admin.responsaveis.show', compact('responsavel'));
    }
}
