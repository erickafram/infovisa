<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Estabelecimento;
use App\Models\TipoAcao;
use App\Models\UsuarioInterno;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdemServicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuario = Auth::guard('interno')->user();
        
        // Query base
        $query = OrdemServico::with(['estabelecimento', 'municipio'])
            ->orderBy('created_at', 'desc');
        
        // Filtro por competência baseado no nível de acesso
        if ($usuario->isAdmin()) {
            // Administrador vê tudo (sem filtro)
        } elseif ($usuario->isEstadual()) {
            // Gestor/Técnico Estadual vê apenas OSs estaduais
            $query->where('competencia', 'estadual');
        } elseif ($usuario->isMunicipal()) {
            // Gestor/Técnico Municipal vê apenas OSs municipais do seu município
            $query->where('competencia', 'municipal')
                  ->where('municipio_id', $usuario->municipio_id);
        } else {
            // Outros usuários não veem nada (segurança)
            $query->whereRaw('1 = 0');
        }
        
        $ordensServico = $query->paginate(20);
        
        return view('ordens-servico.index', compact('ordensServico'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $usuario = Auth::guard('interno')->user();
        
        // Busca estabelecimentos conforme competência
        $estabelecimentos = $this->getEstabelecimentosPorCompetencia($usuario);
        
        // Busca tipos de ação ativos
        $tiposAcao = TipoAcao::ativo()->orderBy('descricao')->get();
        
        // Busca técnicos conforme competência
        $tecnicos = $this->getTecnicosPorCompetencia($usuario);
        
        // Busca municípios se for municipal
        $municipios = null;
        if ($usuario->isMunicipal()) {
            $municipios = Municipio::where('id', $usuario->municipio_id)->get();
        } elseif ($usuario->isAdmin()) {
            $municipios = Municipio::orderBy('nome')->get();
        }
        
        return view('ordens-servico.create', compact('estabelecimentos', 'tiposAcao', 'tecnicos', 'municipios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $usuario = Auth::guard('interno')->user();
        
        $validated = $request->validate([
            'estabelecimento_id' => 'nullable|exists:estabelecimentos,id',
            'processo_id' => 'nullable|exists:processos,id',
            'tipos_acao_ids' => 'required|array|min:1',
            'tipos_acao_ids.*' => 'exists:tipo_acoes,id',
            'tecnicos_ids' => 'required|array|min:1',
            'tecnicos_ids.*' => 'exists:usuarios_internos,id',
            'observacoes' => 'nullable|string',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);
        
        // Determina competência e município
        if (!empty($validated['estabelecimento_id'])) {
            // Tem estabelecimento vinculado
            $estabelecimento = Estabelecimento::findOrFail($validated['estabelecimento_id']);
            
            // Se não foi especificado processo_id, tenta vincular ao processo ativo do estabelecimento
            if (empty($validated['processo_id'])) {
                $processoAtivo = \App\Models\Processo::where('estabelecimento_id', $estabelecimento->id)
                    ->whereIn('status', ['aberto', 'em_andamento'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($processoAtivo) {
                    $validated['processo_id'] = $processoAtivo->id;
                }
            }
            
            if ($usuario->isEstadual()) {
                $validated['competencia'] = 'estadual';
                $validated['municipio_id'] = null;
            } elseif ($usuario->isMunicipal()) {
                $validated['competencia'] = 'municipal';
                $validated['municipio_id'] = $usuario->municipio_id;
                
                // Valida se o estabelecimento pertence ao município do usuário
                if ($estabelecimento->municipio_id != $usuario->municipio_id) {
                    return back()->withErrors(['estabelecimento_id' => 'Você não tem permissão para criar OS para este estabelecimento.'])->withInput();
                }
            } elseif ($usuario->isAdmin()) {
                // Admin pode escolher competência baseado no estabelecimento
                $validated['competencia'] = $estabelecimento->competencia_manual ?? 'estadual';
                $validated['municipio_id'] = $estabelecimento->municipio_id;
            }
        } else {
            // Sem estabelecimento - define competência baseada no usuário
            if ($usuario->isEstadual()) {
                $validated['competencia'] = 'estadual';
                $validated['municipio_id'] = null;
            } elseif ($usuario->isMunicipal()) {
                $validated['competencia'] = 'municipal';
                $validated['municipio_id'] = $usuario->municipio_id;
            } elseif ($usuario->isAdmin()) {
                // Admin sem estabelecimento - define como estadual por padrão
                $validated['competencia'] = 'estadual';
                $validated['municipio_id'] = null;
            }
        }
        
        // Gera número da OS e define data de abertura automática
        $validated['numero'] = OrdemServico::gerarNumero();
        $validated['data_abertura'] = now()->format('Y-m-d');
        $validated['status'] = 'aberta';
        
        $ordemServico = OrdemServico::create($validated);
        
        return redirect()->route('admin.ordens-servico.index')
            ->with('success', "Ordem de Serviço {$ordemServico->numero} criada com sucesso!");
    }

    /**
     * Display the specified resource.
     */
    public function show(OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Verifica permissão
        if (!$this->podeVisualizarOS($usuario, $ordemServico)) {
            abort(403, 'Você não tem permissão para visualizar esta ordem de serviço.');
        }
        
        $ordemServico->load(['estabelecimento.municipio', 'municipio', 'processo']);
        
        return view('ordens-servico.show', compact('ordemServico'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Bloqueia edição se OS estiver finalizada
        if ($ordemServico->status === 'finalizada') {
            return redirect()->route('admin.ordens-servico.show', $ordemServico)
                ->with('error', 'Não é possível editar uma Ordem de Serviço finalizada. Use a opção "Reiniciar OS" se necessário.');
        }
        
        // Verifica permissão
        if (!$this->podeEditarOS($usuario, $ordemServico)) {
            abort(403, 'Você não tem permissão para editar esta ordem de serviço.');
        }
        
        // Busca estabelecimentos conforme competência
        $estabelecimentos = $this->getEstabelecimentosPorCompetencia($usuario);
        
        // Busca tipos de ação ativos
        $tiposAcao = TipoAcao::ativo()->orderBy('descricao')->get();
        
        // Busca técnicos conforme competência
        $tecnicos = $this->getTecnicosPorCompetencia($usuario);
        
        // Busca municípios se for municipal
        $municipios = null;
        if ($usuario->isMunicipal()) {
            $municipios = Municipio::where('id', $usuario->municipio_id)->get();
        } elseif ($usuario->isAdmin()) {
            $municipios = Municipio::orderBy('nome')->get();
        }
        
        return view('ordens-servico.edit', compact('ordemServico', 'estabelecimentos', 'tiposAcao', 'tecnicos', 'municipios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Bloqueia edição se OS estiver finalizada
        if ($ordemServico->status === 'finalizada') {
            return redirect()->route('admin.ordens-servico.show', $ordemServico)
                ->with('error', 'Não é possível editar uma Ordem de Serviço finalizada.');
        }
        
        // Verifica permissão
        if (!$this->podeEditarOS($usuario, $ordemServico)) {
            abort(403, 'Você não tem permissão para editar esta ordem de serviço.');
        }
        
        $validated = $request->validate([
            'estabelecimento_id' => 'nullable|exists:estabelecimentos,id',
            'processo_id' => 'nullable|exists:processos,id',
            'tipos_acao_ids' => 'required|array|min:1',
            'tipos_acao_ids.*' => 'exists:tipo_acoes,id',
            'tecnicos_ids' => 'required|array|min:1',
            'tecnicos_ids.*' => 'exists:usuarios_internos,id',
            'observacoes' => 'nullable|string',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);
        
        // Valida se o estabelecimento pertence ao município do usuário (se municipal)
        if (!empty($validated['estabelecimento_id']) && $usuario->isMunicipal()) {
            $estabelecimento = Estabelecimento::findOrFail($validated['estabelecimento_id']);
            if ($estabelecimento->municipio_id != $usuario->municipio_id) {
                return back()->withErrors(['estabelecimento_id' => 'Você não tem permissão para atribuir OS para este estabelecimento.'])->withInput();
            }
        }
        
        // Se estabelecimento foi alterado, busca processo ativo para vincular
        if (!empty($validated['estabelecimento_id']) && $validated['estabelecimento_id'] != $ordemServico->estabelecimento_id) {
            $estabelecimento = Estabelecimento::findOrFail($validated['estabelecimento_id']);
            
            // Busca processo ativo do estabelecimento
            $processo = \App\Models\Processo::where('estabelecimento_id', $estabelecimento->id)
                ->whereIn('status', ['aberto', 'em_analise', 'pendente'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($processo) {
                $validated['processo_id'] = $processo->id;
            } else {
                $validated['processo_id'] = null;
            }
            
            // Atualiza competência e município baseado no estabelecimento
            if ($usuario->isEstadual()) {
                $validated['competencia'] = 'estadual';
                $validated['municipio_id'] = null;
            } elseif ($usuario->isMunicipal()) {
                $validated['competencia'] = 'municipal';
                $validated['municipio_id'] = $usuario->municipio_id;
            } elseif ($usuario->isAdmin()) {
                $validated['competencia'] = $estabelecimento->competencia_manual ?? 'estadual';
                $validated['municipio_id'] = $estabelecimento->municipio_id;
            }
        }
        
        $ordemServico->update($validated);
        
        return redirect()->route('admin.ordens-servico.index')
            ->with('success', "Ordem de Serviço {$ordemServico->numero} atualizada com sucesso!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Verifica permissão
        if (!$this->podeExcluirOS($usuario, $ordemServico)) {
            abort(403, 'Você não tem permissão para excluir esta ordem de serviço.');
        }
        
        $numero = $ordemServico->numero;
        $ordemServico->delete();
        
        return redirect()->route('admin.ordens-servico.index')
            ->with('success', "Ordem de Serviço {$numero} excluída com sucesso!");
    }

    /**
     * Retorna estabelecimentos conforme competência do usuário
     */
    private function getEstabelecimentosPorCompetencia($usuario)
    {
        $query = Estabelecimento::orderBy('nome_fantasia');
        
        if ($usuario->isMunicipal()) {
            // Gestor municipal vê apenas estabelecimentos do seu município
            $query->where('municipio_id', $usuario->municipio_id);
        }
        // Administrador e Estadual veem todos inicialmente
        
        $estabelecimentos = $query->get();
        
        // Filtra por competência usando o método do modelo
        if ($usuario->isEstadual()) {
            // Gestor estadual vê apenas estabelecimentos de competência estadual
            $estabelecimentos = $estabelecimentos->filter(function($estabelecimento) {
                return $estabelecimento->isCompetenciaEstadual();
            });
        } elseif ($usuario->isMunicipal()) {
            // Gestor municipal vê apenas estabelecimentos de competência municipal
            $estabelecimentos = $estabelecimentos->filter(function($estabelecimento) {
                return !$estabelecimento->isCompetenciaEstadual();
            });
        }
        
        return $estabelecimentos;
    }

    /**
     * Retorna técnicos conforme competência do usuário
     */
    private function getTecnicosPorCompetencia($usuario)
    {
        $query = UsuarioInterno::where('ativo', true)->orderBy('nome');
        
        if ($usuario->isEstadual()) {
            // Gestor estadual vê apenas técnicos estaduais
            $query->where('nivel_acesso', 'estadual');
        } elseif ($usuario->isMunicipal()) {
            // Gestor municipal vê apenas técnicos municipais do seu município
            $query->where('nivel_acesso', 'municipal')
                  ->where('municipio_id', $usuario->municipio_id);
        }
        // Administrador vê todos
        
        return $query->get();
    }

    /**
     * Verifica se usuário pode visualizar a OS
     */
    private function podeVisualizarOS($usuario, $ordemServico)
    {
        // Admin sempre pode
        if ($usuario->isAdmin()) {
            return true;
        }
        
        // Se é técnico atribuído, sempre pode visualizar (independente da competência)
        if ($ordemServico->tecnicos_ids && in_array($usuario->id, $ordemServico->tecnicos_ids)) {
            return true;
        }
        
        // Gestor estadual pode ver OSs estaduais
        if ($usuario->isEstadual() && $ordemServico->competencia === 'estadual') {
            return true;
        }
        
        // Gestor municipal pode ver OSs municipais do seu município
        if ($usuario->isMunicipal() && $ordemServico->competencia === 'municipal' && $ordemServico->municipio_id == $usuario->municipio_id) {
            return true;
        }
        
        return false;
    }

    /**
     * Verifica se usuário pode editar a OS
     */
    private function podeEditarOS($usuario, $ordemServico)
    {
        return $this->podeVisualizarOS($usuario, $ordemServico);
    }

    /**
     * Verifica se usuário pode excluir a OS
     */
    private function podeExcluirOS($usuario, $ordemServico)
    {
        return $this->podeVisualizarOS($usuario, $ordemServico);
    }

    /**
     * API: Retorna processos de um estabelecimento
     */
    public function getProcessosPorEstabelecimento($estabelecimentoId)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Valida se o estabelecimento existe
        $estabelecimento = Estabelecimento::find($estabelecimentoId);
        
        if (!$estabelecimento) {
            return response()->json(['error' => 'Estabelecimento não encontrado'], 404);
        }
        
        // Verifica permissão de acesso ao estabelecimento
        if ($usuario->isMunicipal() && $estabelecimento->municipio_id != $usuario->municipio_id) {
            return response()->json(['error' => 'Sem permissão para acessar este estabelecimento'], 403);
        }
        
        // Busca processos do estabelecimento (exceto arquivados)
        $processos = \App\Models\Processo::where('estabelecimento_id', $estabelecimentoId)
            ->where('status', '!=', 'arquivado')
            ->orderBy('numero_processo', 'desc')
            ->get(['id', 'numero_processo', 'tipo', 'status'])
            ->map(function($processo) {
                return [
                    'id' => $processo->id,
                    'numero_processo' => $processo->numero_processo,
                    'tipo' => $processo->tipo,
                    'tipo_label' => \App\Models\Processo::tipos()[$processo->tipo] ?? $processo->tipo,
                    'status' => $processo->status,
                ];
            });
        
        return response()->json([
            'success' => true,
            'processos' => $processos,
            'total' => $processos->count()
        ]);
    }

    /**
     * API: Busca tipos de ação com autocomplete
     */
    public function searchTiposAcao(Request $request)
    {
        $usuario = Auth::guard('interno')->user();
        $search = $request->get('q', '');
        
        // Define competências permitidas
        $competenciaFiltro = ['ambos'];
        if ($usuario->isEstadual()) {
            $competenciaFiltro[] = 'estadual';
        } elseif ($usuario->isMunicipal()) {
            $competenciaFiltro[] = 'municipal';
        } else {
            // Admin vê todos
            $competenciaFiltro = ['estadual', 'municipal', 'ambos'];
        }
        
        // Busca tipos de ação
        $tiposAcao = TipoAcao::ativo()
            ->whereIn('competencia', $competenciaFiltro)
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('descricao', 'ILIKE', "%{$search}%")
                      ->orWhere('codigo_procedimento', 'ILIKE', "%{$search}%");
                });
            })
            ->orderBy('descricao')
            ->limit(50)
            ->get(['id', 'descricao', 'codigo_procedimento']);
        
        // Formata para Select2
        $results = $tiposAcao->map(function($tipo) {
            return [
                'id' => $tipo->id,
                'text' => $tipo->descricao,
                'codigo' => $tipo->codigo_procedimento
            ];
        });
        
        return response()->json([
            'results' => $results,
            'pagination' => ['more' => false]
        ]);
    }

    /**
     * API: Busca técnicos com autocomplete
     */
    public function searchTecnicos(Request $request)
    {
        $usuario = Auth::guard('interno')->user();
        $search = $request->get('q', '');
        
        // Busca técnicos conforme competência
        $query = UsuarioInterno::where('ativo', true);
        
        if ($usuario->isEstadual()) {
            $query->where('nivel_acesso', 'estadual');
        } elseif ($usuario->isMunicipal()) {
            $query->where('nivel_acesso', 'municipal')
                  ->where('municipio_id', $usuario->municipio_id);
        }
        // Admin vê todos
        
        // Aplica filtro de busca
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nome', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }
        
        $tecnicos = $query->orderBy('nome')
            ->limit(50)
            ->get(['id', 'nome', 'email', 'nivel_acesso']);
        
        // Formata para Select2
        $results = $tecnicos->map(function($tecnico) {
            return [
                'id' => $tecnico->id,
                'text' => $tecnico->nome,
                'email' => $tecnico->email,
                'nivel' => $tecnico->nivel_acesso
            ];
        });
        
        return response()->json([
            'results' => $results,
            'pagination' => ['more' => false]
        ]);
    }

    /**
     * Finalizar ordem de serviço
     */
    public function finalizar(Request $request, OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Verifica se o usuário é técnico atribuído à OS
        $isTecnico = $ordemServico->tecnicos_ids && in_array($usuario->id, $ordemServico->tecnicos_ids);
        
        if (!$isTecnico) {
            return response()->json([
                'message' => 'Você não tem permissão para finalizar esta ordem de serviço.'
            ], 403);
        }
        
        // Valida os dados
        $validated = $request->validate([
            'atividades_realizadas' => 'required|in:sim,parcial,nao',
            'observacoes_finalizacao' => 'required|string|min:20',
            'estabelecimento_id' => 'nullable|exists:estabelecimentos,id',
        ], [
            'atividades_realizadas.required' => 'Informe se as atividades foram realizadas.',
            'observacoes_finalizacao.required' => 'As observações são obrigatórias.',
            'observacoes_finalizacao.min' => 'As observações devem ter no mínimo 20 caracteres.',
        ]);
        
        // Se estabelecimento foi informado, vincula e busca processo
        $dadosAtualizacao = [
            'status' => 'finalizada',
            'data_conclusao' => now(),
            'atividades_realizadas' => $validated['atividades_realizadas'],
            'observacoes_finalizacao' => $validated['observacoes_finalizacao'],
            'finalizada_por' => $usuario->id,
            'finalizada_em' => now(),
        ];
        
        if (!empty($validated['estabelecimento_id'])) {
            $estabelecimento = Estabelecimento::findOrFail($validated['estabelecimento_id']);
            
            // Busca processo ativo do estabelecimento
            $processo = \App\Models\Processo::where('estabelecimento_id', $estabelecimento->id)
                ->whereIn('status', ['aberto', 'em_analise', 'pendente'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            $dadosAtualizacao['estabelecimento_id'] = $estabelecimento->id;
            $dadosAtualizacao['processo_id'] = $processo ? $processo->id : null;
            
            // Atualiza competência e município baseado no estabelecimento
            if ($usuario->isEstadual()) {
                $dadosAtualizacao['competencia'] = 'estadual';
                $dadosAtualizacao['municipio_id'] = null;
            } elseif ($usuario->isMunicipal()) {
                $dadosAtualizacao['competencia'] = 'municipal';
                $dadosAtualizacao['municipio_id'] = $usuario->municipio_id;
            } elseif ($usuario->isAdmin()) {
                $dadosAtualizacao['competencia'] = $estabelecimento->competencia_manual ?? 'estadual';
                $dadosAtualizacao['municipio_id'] = $estabelecimento->municipio_id;
            }
        }
        
        // Atualiza a OS
        $ordemServico->update($dadosAtualizacao);
        
        // Cria notificação para gestores
        $this->criarNotificacaoFinalizacao($ordemServico, $usuario);
        
        return response()->json([
            'message' => 'Ordem de serviço finalizada com sucesso!',
            'ordem_servico' => $ordemServico
        ]);
    }

    /**
     * Criar notificação de finalização para gestores
     */
    private function criarNotificacaoFinalizacao(OrdemServico $ordemServico, $tecnico)
    {
        // Busca gestores que devem receber notificação
        $gestores = UsuarioInterno::where('ativo', true)
            ->where(function($query) use ($ordemServico) {
                if ($ordemServico->competencia === 'estadual') {
                    $query->where('nivel_acesso', 'estadual')
                          ->orWhere('nivel_acesso', 'administrador');
                } elseif ($ordemServico->competencia === 'municipal') {
                    $query->where(function($q) use ($ordemServico) {
                        $q->where('nivel_acesso', 'municipal')
                          ->where('municipio_id', $ordemServico->municipio_id);
                    })->orWhere('nivel_acesso', 'administrador');
                }
            })
            ->get();
        
        foreach ($gestores as $gestor) {
            // Monta mensagem com ou sem estabelecimento
            $estabelecimentoInfo = $ordemServico->estabelecimento 
                ? ' do estabelecimento ' . $ordemServico->estabelecimento->nome_fantasia 
                : ' (sem estabelecimento vinculado)';
            
            \App\Models\Notificacao::create([
                'usuario_interno_id' => $gestor->id,
                'tipo' => 'ordem_servico_finalizada',
                'titulo' => 'OS #' . $ordemServico->numero . ' Finalizada',
                'mensagem' => 'O técnico ' . $tecnico->nome . ' finalizou a OS #' . $ordemServico->numero . $estabelecimentoInfo,
                'link' => route('admin.ordens-servico.show', $ordemServico),
                'ordem_servico_id' => $ordemServico->id,
                'prioridade' => 'normal',
            ]);
        }
    }

    /**
     * Reiniciar ordem de serviço finalizada
     */
    public function reiniciar(OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Apenas gestores podem reiniciar
        if (!$usuario->isAdmin() && !$usuario->isEstadual() && !$usuario->isMunicipal()) {
            return redirect()->route('admin.ordens-servico.show', $ordemServico)
                ->with('error', 'Você não tem permissão para reiniciar esta ordem de serviço.');
        }
        
        // Verifica se está finalizada
        if ($ordemServico->status !== 'finalizada') {
            return redirect()->route('admin.ordens-servico.show', $ordemServico)
                ->with('error', 'Apenas ordens de serviço finalizadas podem ser reiniciadas.');
        }
        
        // Reinicia a OS
        $ordemServico->update([
            'status' => 'em_andamento',
            'atividades_realizadas' => null,
            'observacoes_finalizacao' => null,
            'finalizada_por' => null,
            'finalizada_em' => null,
            'data_conclusao' => null,
        ]);
        
        // Cria notificação para os técnicos
        foreach ($ordemServico->tecnicos_ids ?? [] as $tecnicoId) {
            \App\Models\Notificacao::create([
                'usuario_interno_id' => $tecnicoId,
                'tipo' => 'ordem_servico_reiniciada',
                'titulo' => 'OS #' . $ordemServico->numero . ' Reiniciada',
                'mensagem' => 'A OS #' . $ordemServico->numero . ' foi reiniciada por ' . $usuario->nome . ' e voltou ao status "Em Andamento".',
                'link' => route('admin.ordens-servico.show', $ordemServico),
                'ordem_servico_id' => $ordemServico->id,
                'prioridade' => 'alta',
            ]);
        }
        
        return redirect()->route('admin.ordens-servico.show', $ordemServico)
            ->with('success', 'Ordem de Serviço reiniciada com sucesso! Status alterado para "Em Andamento".');
    }

    /**
     * Buscar processos ativos de um estabelecimento
     */
    public function getProcessosEstabelecimento($estabelecimentoId)
    {
        try {
            $statusLabels = [
                'aberto' => 'Aberto',
                'em_analise' => 'Em Análise',
                'pendente' => 'Pendente',
                'deferido' => 'Deferido',
                'indeferido' => 'Indeferido',
                'arquivado' => 'Arquivado',
            ];

            $processos = \App\Models\Processo::where('estabelecimento_id', $estabelecimentoId)
                ->whereIn('status', ['aberto', 'em_analise', 'pendente'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($processo) use ($statusLabels) {
                    return [
                        'id' => $processo->id,
                        'numero' => $processo->numero_processo ?? "Processo #{$processo->id}",
                        'status' => $processo->status,
                        'status_label' => $statusLabels[$processo->status] ?? ucfirst($processo->status),
                        'data_abertura' => $processo->created_at ? $processo->created_at->format('d/m/Y') : '-',
                    ];
                });

            return response()->json([
                'processos' => $processos
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar processos do estabelecimento: ' . $e->getMessage());
            return response()->json([
                'processos' => [],
                'error' => 'Erro ao buscar processos'
            ], 500);
        }
    }
}
