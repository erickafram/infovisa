<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\TipoAcao;
use App\Models\UsuarioInterno;
use App\Models\Municipio;
use App\Models\ChatConversa;
use App\Models\ChatMensagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class OrdemServicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
        
        // Filtros personalizados
        if ($request->filled('estabelecimento')) {
            $term = trim($request->input('estabelecimento'));
            $numericTerm = preg_replace('/\D+/', '', $term);

            $query->whereHas('estabelecimento', function ($subQuery) use ($term, $numericTerm) {
                $subQuery->where(function ($inner) use ($term, $numericTerm) {
                    // Busca case-insensitive usando ILIKE (PostgreSQL)
                    $inner->whereRaw("nome_fantasia ILIKE ?", ["%{$term}%"])
                        ->orWhereRaw("razao_social ILIKE ?", ["%{$term}%"])
                        ->orWhere('cnpj', 'like', "%{$term}%")
                        ->orWhere('cpf', 'like', "%{$term}%");

                    if (!empty($numericTerm)) {
                        $inner->orWhere('cnpj', 'like', "%{$numericTerm}%")
                              ->orWhere('cpf', 'like', "%{$numericTerm}%");
                    }
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_inicio', '>=', $request->input('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_fim', '<=', $request->input('data_fim'));
        }

        $ordensServico = $query->paginate(10)->withQueryString();

        $statusOptions = [
            'em_andamento' => 'Em Andamento',
            'finalizada' => 'Finalizada',
            'cancelada' => 'Cancelada',
        ];

        $filters = $request->only(['estabelecimento', 'status', 'data_inicio', 'data_fim']);
        
        return view('ordens-servico.index', compact('ordensServico', 'statusOptions', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     * APENAS Administrador, Gestor Estadual e Gestor Municipal podem criar OS
     */
    public function create(Request $request)
    {
        $usuario = Auth::guard('interno')->user();
        $permiteDatasRetroativas = $usuario->isAdmin();
        
        // Verifica permissão: apenas Admin e Gestores podem criar OS
        if (!$usuario->isAdmin() && !$usuario->isGestor()) {
            return redirect()
                ->back()
                ->with('error', 'Apenas Administradores e Gestores podem criar Ordens de Serviço.');
        }
        
        // Busca estabelecimentos conforme competência
        $estabelecimentos = $this->getEstabelecimentosPorCompetencia($usuario);
        
        // Busca tipos de ação ativos com subações
        $tiposAcao = TipoAcao::ativo()->with('subAcoesAtivas')->orderBy('descricao')->get();
        
        // Busca técnicos conforme competência
        $tecnicos = $this->getTecnicosPorCompetencia($usuario);
        
        // Busca municípios se for municipal
        $municipios = null;
        if ($usuario->isMunicipal()) {
            $municipios = Municipio::where('id', $usuario->municipio_id)->get();
        } elseif ($usuario->isAdmin()) {
            $municipios = Municipio::orderBy('nome')->get();
        }
        
        // Pré-seleciona estabelecimento e processo se passados via query string
        $estabelecimentoPreSelecionado = null;
        $processoPreSelecionado = null;
        
        if ($request->filled('estabelecimento_id')) {
            $estabelecimentoPreSelecionado = Estabelecimento::find($request->estabelecimento_id);
        }
        
        if ($request->filled('processo_id')) {
            $processoPreSelecionado = Processo::find($request->processo_id);
        }
        
        return view('ordens-servico.create', compact('estabelecimentos', 'tiposAcao', 'tecnicos', 'municipios', 'estabelecimentoPreSelecionado', 'processoPreSelecionado', 'permiteDatasRetroativas'));
    }

    /**
     * Store a newly created resource in storage.
     * APENAS Administrador, Gestor Estadual e Gestor Municipal podem criar OS
     */
    public function store(Request $request)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Verifica permissão: apenas Admin e Gestores podem criar OS
        if (!$usuario->isAdmin() && !$usuario->isGestor()) {
            return redirect()
                ->back()
                ->with('error', 'Apenas Administradores e Gestores podem criar Ordens de Serviço.');
        }
        
        // Validação condicional: processo é obrigatório se há estabelecimento
        $rules = [
            'tipo_vinculacao' => 'required|in:com_estabelecimento,sem_estabelecimento',
            'estabelecimento_id' => 'nullable|exists:estabelecimentos,id',
            'tipos_acao_ids' => 'required|array|min:1',
            'tipos_acao_ids.*' => 'exists:tipo_acoes,id',
            'atividades_tecnicos' => 'required|json',
            'observacoes' => 'nullable|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'documento_anexo' => 'nullable|file|mimes:pdf|max:10240',
        ];

        if (!$usuario->isAdmin()) {
            $rules['data_inicio'] = 'required|date|after_or_equal:today';
            $rules['data_fim'] = 'required|date|after_or_equal:data_inicio|after_or_equal:today';
        }
        
        // Se tem estabelecimento, processo é obrigatório
        if (!empty($request->estabelecimento_id)) {
            $rules['processo_id'] = 'required|exists:processos,id';
        } else {
            $rules['processo_id'] = 'nullable|exists:processos,id';
        }
        
        $messages = [
            'processo_id.required' => 'Selecione um processo vinculado ao estabelecimento.',
            'atividades_tecnicos.required' => 'Atribua técnicos para todas as atividades selecionadas.',
            'atividades_tecnicos.json' => 'Estrutura de técnicos por atividade inválida.',
            'data_inicio.required' => 'Informe a data de início da ordem de serviço.',
            'data_fim.required' => 'Informe a data de término da ordem de serviço.',
            'data_fim.after_or_equal' => 'A data de término não pode ser anterior à data de início.',
            'data_inicio.after_or_equal' => 'Não é permitido criar ordem de serviço com data de início retroativa.',
        ];

        $validated = $request->validate($rules, $messages);
        
        // Processa e valida a estrutura de atividades com técnicos
        $atividadesTecnicos = json_decode($validated['atividades_tecnicos'], true);
        
        if (!is_array($atividadesTecnicos) || empty($atividadesTecnicos)) {
            return back()->withErrors(['atividades_tecnicos' => 'Atribua técnicos para todas as atividades selecionadas.'])->withInput();
        }
        
        // Valida se todos os técnicos existem e têm permissão
        $tecnicosIds = [];
        foreach ($atividadesTecnicos as $atividade) {
            if (!isset($atividade['tecnicos']) || !is_array($atividade['tecnicos'])) {
                return back()->withErrors(['atividades_tecnicos' => 'Estrutura de técnicos inválida.'])->withInput();
            }
            $tecnicosIds = array_merge($tecnicosIds, $atividade['tecnicos']);
        }
        
        $tecnicosIds = array_unique($tecnicosIds);
        $tecnicosValidos = $this->getTecnicosPorCompetencia($usuario)->pluck('id')->toArray();
        
        foreach ($tecnicosIds as $tecnicoId) {
            if (!in_array($tecnicoId, $tecnicosValidos)) {
                return back()->withErrors(['atividades_tecnicos' => 'Um ou mais técnicos selecionados não são válidos para sua competência.'])->withInput();
            }
        }
        
        // Mantém compatibilidade com campo antigo tecnicos_ids
        $validated['tecnicos_ids'] = $tecnicosIds;
        $validated['atividades_tecnicos'] = $atividadesTecnicos;
        
        // Upload do documento se fornecido
        if ($request->hasFile('documento_anexo')) {
            $arquivo = $request->file('documento_anexo');
            $nomeArquivo = time() . '_' . $arquivo->getClientOriginalName();
            $caminhoArquivo = $arquivo->storeAs('ordens-servico/documentos', $nomeArquivo, 'public');
            $validated['documento_anexo_path'] = $caminhoArquivo;
            $validated['documento_anexo_nome'] = $arquivo->getClientOriginalName();
        }
        
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
        // Usa transação para garantir atomicidade na geração do número
        $ordemServico = \DB::transaction(function () use ($validated) {
            $validated['numero'] = OrdemServico::gerarNumero();
            $validated['data_abertura'] = now()->format('Y-m-d');
            $validated['status'] = 'em_andamento';
            
            return OrdemServico::create($validated);
        });
        
        // Envia notificação no chat para os técnicos atribuídos
        $this->enviarNotificacaoTecnicos($ordemServico, $usuario);
        
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
        
        // Busca tipos de ação ativos com subações
        $tiposAcao = TipoAcao::ativo()->with('subAcoesAtivas')->orderBy('descricao')->get();
        
        // Busca técnicos conforme competência
        $tecnicos = $this->getTecnicosPorCompetencia($usuario);
        
        // Busca municípios se for municipal
        $municipios = null;
        if ($usuario->isMunicipal()) {
            $municipios = Municipio::where('id', $usuario->municipio_id)->get();
        } elseif ($usuario->isAdmin()) {
            $municipios = Municipio::orderBy('nome')->get();
        }
        
        return view('ordens-servico.edit', compact('ordemServico', 'tiposAcao', 'tecnicos', 'municipios'));
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
        
        // Validação condicional: processo é obrigatório se há estabelecimento
        $rules = [
            'estabelecimento_id' => 'nullable|exists:estabelecimentos,id',
            'tipos_acao_ids' => 'required|array|min:1',
            'tipos_acao_ids.*' => 'exists:tipo_acoes,id',
            'atividades_tecnicos' => 'required|json',
            'observacoes' => 'nullable|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ];
        
        // Se tem estabelecimento, processo é obrigatório
        if (!empty($request->estabelecimento_id)) {
            $rules['processo_id'] = 'required|exists:processos,id';
        } else {
            $rules['processo_id'] = 'nullable|exists:processos,id';
        }
        
        $validated = $request->validate($rules);
        
        // Processa e valida a estrutura de atividades com técnicos
        $atividadesTecnicos = json_decode($validated['atividades_tecnicos'], true);
        
        if (!is_array($atividadesTecnicos) || empty($atividadesTecnicos)) {
            return back()->withErrors(['atividades_tecnicos' => 'Atribua técnicos para todas as atividades selecionadas.'])->withInput();
        }
        
        // Valida se todos os técnicos existem e têm permissão
        $tecnicosIds = [];
        foreach ($atividadesTecnicos as $atividade) {
            if (!isset($atividade['tecnicos']) || !is_array($atividade['tecnicos'])) {
                return back()->withErrors(['atividades_tecnicos' => 'Estrutura de técnicos inválida.'])->withInput();
            }
            $tecnicosIds = array_merge($tecnicosIds, $atividade['tecnicos']);
        }
        
        $tecnicosIds = array_unique($tecnicosIds);
        $tecnicosValidos = $this->getTecnicosPorCompetencia($usuario)->pluck('id')->toArray();
        
        foreach ($tecnicosIds as $tecnicoId) {
            if (!in_array($tecnicoId, $tecnicosValidos)) {
                return back()->withErrors(['atividades_tecnicos' => 'Um ou mais técnicos selecionados não são válidos para sua competência.'])->withInput();
            }
        }
        
        // Mantém compatibilidade com campo antigo tecnicos_ids
        $validated['tecnicos_ids'] = $tecnicosIds;
        $validated['atividades_tecnicos'] = $atividadesTecnicos;
        
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
    public function destroy(Request $request, OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Verifica permissão
        if (!$this->podeExcluirOS($usuario, $ordemServico)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para excluir esta ordem de serviço.'
                ], 403);
            }
            abort(403, 'Você não tem permissão para excluir esta ordem de serviço.');
        }
        
        // Valida senha de assinatura digital
        $senhaAssinatura = $request->input('senha_assinatura');
        
        if (!$senhaAssinatura) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A senha de assinatura digital é obrigatória.'
                ], 422);
            }
            return back()->withErrors(['senha_assinatura' => 'A senha de assinatura digital é obrigatória.']);
        }
        
        // Verifica se o usuário tem senha de assinatura configurada
        if (!$usuario->senha_assinatura_digital) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não possui senha de assinatura digital configurada. Configure em "Configurar Senha de Assinatura".'
                ], 422);
            }
            return back()->withErrors(['senha_assinatura' => 'Você não possui senha de assinatura digital configurada.']);
        }
        
        // Valida a senha
        if (!Hash::check($senhaAssinatura, $usuario->senha_assinatura_digital)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Senha de assinatura digital incorreta.'
                ], 422);
            }
            return back()->withErrors(['senha_assinatura' => 'Senha de assinatura digital incorreta.']);
        }
        
        $numero = $ordemServico->numero;
        $ordemServico->delete();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Ordem de Serviço {$numero} excluída com sucesso!"
            ]);
        }
        
        return redirect()->route('admin.ordens-servico.index')
            ->with('success', "Ordem de Serviço {$numero} excluída com sucesso!");
    }

    /**
     * Cancela uma ordem de serviço
     */
    public function cancelar(Request $request, OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Verifica permissão
        if (!$this->podeEditarOS($usuario, $ordemServico)) {
            abort(403, 'Você não tem permissão para cancelar esta ordem de serviço.');
        }
        
        // Não permite cancelar OS finalizada
        if ($ordemServico->status === 'finalizada') {
            return redirect()->route('admin.ordens-servico.show', $ordemServico)
                ->with('error', 'Não é possível cancelar uma Ordem de Serviço finalizada.');
        }
        
        // Não permite cancelar OS já cancelada
        if ($ordemServico->status === 'cancelada') {
            return redirect()->route('admin.ordens-servico.show', $ordemServico)
                ->with('error', 'Esta Ordem de Serviço já está cancelada.');
        }
        
        // Valida motivo do cancelamento
        $validated = $request->validate([
            'motivo_cancelamento' => 'required|string|min:20',
        ], [
            'motivo_cancelamento.required' => 'Informe o motivo do cancelamento.',
            'motivo_cancelamento.min' => 'O motivo deve ter no mínimo 20 caracteres.',
        ]);
        
        $numero = $ordemServico->numero;
        $ordemServico->update([
            'status' => 'cancelada',
            'motivo_cancelamento' => $validated['motivo_cancelamento'],
            'cancelada_em' => now(),
            'cancelada_por' => $usuario->id,
        ]);
        
        return redirect()->route('admin.ordens-servico.index')
            ->with('success', "Ordem de Serviço {$numero} cancelada com sucesso!");
    }

    /**
     * Reinicia uma ordem de serviço cancelada
     */
    public function reativar(OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Apenas gestores podem reativar
        if (!$usuario->isAdmin() && !$usuario->isEstadual() && !$usuario->isMunicipal()) {
            abort(403, 'Apenas gestores podem reativar ordens de serviço canceladas.');
        }
        
        // Verifica se está cancelada
        if ($ordemServico->status !== 'cancelada') {
            return redirect()->route('admin.ordens-servico.show', $ordemServico)
                ->with('error', 'Apenas ordens de serviço canceladas podem ser reativadas.');
        }
        
        $numero = $ordemServico->numero;
        $ordemServico->update([
            'status' => 'em_andamento',
            'motivo_cancelamento' => null,
            'cancelada_em' => null,
            'cancelada_por' => null,
        ]);
        
        return redirect()->route('admin.ordens-servico.show', $ordemServico)
            ->with('success', "Ordem de Serviço {$numero} reativada com sucesso!");
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
        
        if ($usuario->isAdmin() || $usuario->isEstadual()) {
            // Administrador e usuários estaduais veem técnicos e gestores estaduais (não admin)
            $query->whereIn('nivel_acesso', ['gestor_estadual', 'tecnico_estadual']);
        } elseif ($usuario->isMunicipal()) {
            // Gestor/Técnico municipal vê apenas usuários municipais do seu município
            $query->whereIn('nivel_acesso', ['gestor_municipal', 'tecnico_municipal'])
                  ->where('municipio_id', $usuario->municipio_id);
        }
        
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
     * Técnicos não podem editar
     */
    private function podeEditarOS($usuario, $ordemServico)
    {
        // Técnicos não podem editar OS
        if ($usuario->nivel_acesso === \App\Enums\NivelAcesso::TecnicoEstadual ||
            $usuario->nivel_acesso === \App\Enums\NivelAcesso::TecnicoMunicipal) {
            return false;
        }
        
        return $this->podeVisualizarOS($usuario, $ordemServico);
    }

    /**
     * Verifica se usuário pode excluir a OS
     * Apenas Administrador e Gestores podem excluir
     */
    private function podeExcluirOS($usuario, $ordemServico)
    {
        // Técnicos não podem excluir OS
        if ($usuario->nivel_acesso === \App\Enums\NivelAcesso::TecnicoEstadual ||
            $usuario->nivel_acesso === \App\Enums\NivelAcesso::TecnicoMunicipal) {
            return false;
        }
        
        // Admin pode excluir qualquer OS
        if ($usuario->nivel_acesso === \App\Enums\NivelAcesso::Administrador) {
            return true;
        }
        
        // Gestores podem excluir se tiverem acesso à OS
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
            'acoes_executadas_ids' => 'nullable|array',
            'acoes_executadas_ids.*' => 'exists:tipo_acoes,id',
        ], [
            'atividades_realizadas.required' => 'Informe se as atividades foram realizadas.',
            'observacoes_finalizacao.required' => 'As observações são obrigatórias.',
            'observacoes_finalizacao.min' => 'As observações devem ter no mínimo 20 caracteres.',
        ]);
        
        // Processa ações executadas conforme status
        $acoesExecutadasIds = [];
        
        if ($validated['atividades_realizadas'] === 'sim') {
            // Concluído com sucesso: todas as ações foram executadas
            $acoesExecutadasIds = $ordemServico->tipos_acao_ids;
        } elseif ($validated['atividades_realizadas'] === 'parcial') {
            // Concluído parcialmente: apenas as ações selecionadas
            $acoesExecutadasIds = $request->input('acoes_executadas_ids', []);
        } elseif ($validated['atividades_realizadas'] === 'nao') {
            // Não concluído: nenhuma ação foi executada
            $acoesExecutadasIds = [];
        }
        
        // Se estabelecimento foi informado, vincula e busca processo
        $dadosAtualizacao = [
            'status' => 'finalizada',
            'data_conclusao' => now(),
            'atividades_realizadas' => $validated['atividades_realizadas'],
            'observacoes_finalizacao' => $validated['observacoes_finalizacao'],
            'acoes_executadas_ids' => $acoesExecutadasIds,
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
        
        // Reseta o status de todas as atividades para pendente
        $atividades = $ordemServico->atividades_tecnicos ?? [];
        foreach ($atividades as $index => $atividade) {
            $atividades[$index]['status'] = 'pendente';
            $atividades[$index]['status_execucao'] = null;
            $atividades[$index]['finalizada_por'] = null;
            $atividades[$index]['finalizada_em'] = null;
            $atividades[$index]['observacoes_finalizacao'] = null;
        }
        
        // Reinicia a OS
        $ordemServico->update([
            'status' => 'em_andamento',
            'atividades_realizadas' => null,
            'observacoes_finalizacao' => null,
            'acoes_executadas_ids' => [],
            'finalizada_por' => null,
            'finalizada_em' => null,
            'data_conclusao' => null,
            'atividades_tecnicos' => $atividades, // Reseta as atividades
        ]);
        
        // Cria notificação para os técnicos
        foreach ($ordemServico->tecnicos_ids ?? [] as $tecnicoId) {
            \App\Models\Notificacao::create([
                'usuario_interno_id' => $tecnicoId,
                'tipo' => 'ordem_servico_reiniciada',
                'titulo' => 'OS #' . $ordemServico->numero . ' Reiniciada',
                'mensagem' => 'A OS #' . $ordemServico->numero . ' foi reiniciada por ' . $usuario->nome . '. Todas as atividades voltaram ao status "Pendente".',
                'link' => route('admin.ordens-servico.show', $ordemServico),
                'ordem_servico_id' => $ordemServico->id,
                'prioridade' => 'alta',
            ]);
        }
        
        return redirect()->route('admin.ordens-servico.show', $ordemServico)
            ->with('success', 'Ordem de Serviço reiniciada com sucesso! Todas as atividades voltaram ao status "Pendente".');
    }

    /**
     * Reiniciar uma atividade individual (apenas gestores)
     */
    public function reiniciarAtividade(Request $request, OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Apenas gestores podem reiniciar atividades
        if (!$usuario->isAdmin() && !$usuario->isGestor()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para reiniciar atividades.'
            ], 403);
        }
        
        $validated = $request->validate([
            'atividade_index' => 'required|integer|min:0',
        ]);
        
        $atividadeIndex = $validated['atividade_index'];
        $atividades = $ordemServico->atividades_tecnicos ?? [];
        
        // Verifica se o índice é válido
        if (!isset($atividades[$atividadeIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Atividade não encontrada.'
            ], 404);
        }
        
        $atividade = $atividades[$atividadeIndex];
        
        // Verifica se a atividade está finalizada
        if (($atividade['status'] ?? 'pendente') !== 'finalizada') {
            return response()->json([
                'success' => false,
                'message' => 'Esta atividade já está pendente.'
            ], 400);
        }
        
        // Reseta a atividade para pendente
        $atividades[$atividadeIndex]['status'] = 'pendente';
        $atividades[$atividadeIndex]['status_execucao'] = null;
        $atividades[$atividadeIndex]['finalizada_por'] = null;
        $atividades[$atividadeIndex]['finalizada_em'] = null;
        $atividades[$atividadeIndex]['observacoes_finalizacao'] = null;
        
        // Se a OS estava finalizada, volta para em_andamento
        $dadosOS = ['atividades_tecnicos' => $atividades];
        if ($ordemServico->status === 'finalizada') {
            $dadosOS['status'] = 'em_andamento';
            $dadosOS['atividades_realizadas'] = null;
            $dadosOS['observacoes_finalizacao'] = null;
            $dadosOS['acoes_executadas_ids'] = [];
            $dadosOS['finalizada_por'] = null;
            $dadosOS['finalizada_em'] = null;
            $dadosOS['data_conclusao'] = null;
        }
        
        $ordemServico->update($dadosOS);
        
        $nomeAtividade = $atividade['nome_atividade'] ?? 'Atividade';
        
        // Notifica os técnicos da atividade
        $tecnicosAtividade = $atividade['tecnicos'] ?? [];
        foreach ($tecnicosAtividade as $tecnicoId) {
            \App\Models\Notificacao::create([
                'usuario_interno_id' => $tecnicoId,
                'tipo' => 'atividade_reiniciada',
                'titulo' => 'Atividade Reiniciada - OS #' . $ordemServico->numero,
                'mensagem' => 'A atividade "' . $nomeAtividade . '" da OS #' . $ordemServico->numero . ' foi reiniciada por ' . $usuario->nome . '. A atividade voltou ao status "Pendente".',
                'link' => route('admin.ordens-servico.show', $ordemServico),
                'ordem_servico_id' => $ordemServico->id,
                'prioridade' => 'alta',
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Atividade "' . $nomeAtividade . '" reiniciada com sucesso!',
            'os_reaberta' => $ordemServico->status === 'em_andamento' && $ordemServico->getOriginal('status') === 'finalizada',
        ]);
    }

    /**
     * Buscar estabelecimentos com autocomplete (AJAX)
     */
    public function buscarEstabelecimentos(Request $request)
    {
        $usuario = Auth::guard('interno')->user();
        $termo = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 20;
        
        // Query base
        $query = Estabelecimento::query();
        
        // Filtro por município para usuário municipal
        if ($usuario->isMunicipal() && $usuario->municipio_id) {
            $query->where('municipio_id', $usuario->municipio_id);
        }
        // Admin e Estadual veem todos inicialmente (filtro de competência será aplicado depois)
        
        // Busca por CNPJ/CPF, Nome Fantasia ou Razão Social
        if (!empty($termo)) {
            // Remove formatação do termo (pontos, hífen, barra) para buscar apenas números
            $termoNumeros = preg_replace('/[^0-9]/', '', $termo);
            
            $query->where(function($q) use ($termo, $termoNumeros) {
                // Busca por CNPJ/CPF (com ou sem formatação)
                if (!empty($termoNumeros)) {
                    $q->where('cnpj', 'ILIKE', "%{$termoNumeros}%")
                      ->orWhere('cpf', 'ILIKE', "%{$termoNumeros}%");
                }
                // Busca por nome
                $q->orWhere('nome_fantasia', 'ILIKE', "%{$termo}%")
                  ->orWhere('razao_social', 'ILIKE', "%{$termo}%")
                  ->orWhere('nome_completo', 'ILIKE', "%{$termo}%");
            });
        }
        
        // Busca todos os resultados para filtrar por competência
        $estabelecimentosQuery = $query->orderBy('nome_fantasia')->get();
        
        // Filtra por competência baseado no tipo de usuário
        if ($usuario->isEstadual()) {
            // Estadual vê apenas estabelecimentos de competência estadual
            $estabelecimentosQuery = $estabelecimentosQuery->filter(function($est) {
                return $est->isCompetenciaEstadual();
            });
        } elseif ($usuario->isMunicipal()) {
            // Municipal vê apenas estabelecimentos de competência municipal
            $estabelecimentosQuery = $estabelecimentosQuery->filter(function($est) {
                return $est->isCompetenciaMunicipal();
            });
        }
        // Admin vê todos
        
        // Paginação manual após filtro
        $total = $estabelecimentosQuery->count();
        $estabelecimentos = $estabelecimentosQuery->slice(($page - 1) * $perPage, $perPage)->values();
        
        // Formata resultados para Select2
        $results = $estabelecimentos->map(function($estabelecimento) {
            // Pessoa Jurídica (CNPJ)
            if (!empty($estabelecimento->cnpj)) {
                $documento = $estabelecimento->cnpj;
                $nome = $estabelecimento->nome_fantasia . ' - ' . $estabelecimento->razao_social;
            } 
            // Pessoa Física (CPF)
            else {
                $documento = $estabelecimento->cpf ?? 'Sem documento';
                $nome = $estabelecimento->nome_completo ?? $estabelecimento->razao_social;
            }
            
            return [
                'id' => $estabelecimento->id,
                'text' => $documento . ' - ' . $nome
            ];
        });
        
        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Buscar processos ativos de um estabelecimento
     */
    public function getProcessosEstabelecimento($estabelecimentoId, Request $request = null)
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

            // Pega o processo atual da OS (se estiver editando)
            $processoAtualId = request()->query('processo_atual_id');

            $processos = \App\Models\Processo::where('estabelecimento_id', $estabelecimentoId)
                ->where(function($query) use ($processoAtualId) {
                    // Busca processos ativos OU o processo atual da OS
                    $query->whereIn('status', ['aberto', 'em_analise', 'pendente']);
                    
                    if ($processoAtualId) {
                        $query->orWhere('id', $processoAtualId);
                    }
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($processo) use ($statusLabels) {
                    // Formata número do processo (ex: 2025/00004)
                    $numeroProcesso = $processo->numero_processo ?? "Processo #{$processo->id}";
                    
                    // Tipo de processo
                    $tipoProcesso = $processo->tipo ?? 'Não informado';
                    
                    return [
                        'id' => $processo->id,
                        'numero' => $numeroProcesso,
                        'tipo' => $tipoProcesso,
                        'texto_completo' => $numeroProcesso . ' - ' . $tipoProcesso,
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

    /**
     * Finalizar uma atividade específica do técnico
     * A OS só será finalizada quando TODAS as atividades forem concluídas
     */
    public function finalizarAtividade(Request $request, OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Valida os dados
        $validated = $request->validate([
            'atividade_index' => 'required|integer|min:0',
            'status_execucao' => 'required|in:concluido,parcial,nao_concluido',
            'observacoes' => 'required|string|min:10|max:1000',
            'estabelecimento_id' => 'nullable|exists:estabelecimentos,id',
        ], [
            'atividade_index.required' => 'Índice da atividade é obrigatório.',
            'status_execucao.required' => 'Selecione o status da execução.',
            'status_execucao.in' => 'Status de execução inválido.',
            'observacoes.required' => 'Informe as observações da atividade.',
            'observacoes.min' => 'As observações devem ter no mínimo 10 caracteres.',
        ]);
        
        $atividadeIndex = $validated['atividade_index'];
        $atividades = $ordemServico->atividades_tecnicos ?? [];
        
        // Verifica se o índice é válido
        if (!isset($atividades[$atividadeIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Atividade não encontrada.'
            ], 404);
        }
        
        $atividade = $atividades[$atividadeIndex];
        
        // Verifica se o técnico está atribuído a esta atividade
        $tecnicosAtividade = $atividade['tecnicos'] ?? [];
        if (!in_array($usuario->id, $tecnicosAtividade)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não está atribuído a esta atividade.'
            ], 403);
        }

        // Se houver mais de um técnico e existir responsável, só o responsável pode finalizar
        $responsavelId = $atividade['responsavel_id'] ?? null;
        if (count($tecnicosAtividade) > 1 && $responsavelId && $usuario->id !== $responsavelId) {
            return response()->json([
                'success' => false,
                'message' => 'Somente o técnico responsável pode finalizar esta atividade.'
            ], 403);
        }
        
        // Verifica se a atividade já foi finalizada
        if (($atividade['status'] ?? 'pendente') === 'finalizada') {
            return response()->json([
                'success' => false,
                'message' => 'Esta atividade já foi finalizada.'
            ], 400);
        }
        
        // Atualiza o status da atividade
        $atividades[$atividadeIndex]['status'] = 'finalizada';
        $atividades[$atividadeIndex]['status_execucao'] = $validated['status_execucao'];
        $atividades[$atividadeIndex]['finalizada_por'] = $usuario->id;
        $atividades[$atividadeIndex]['finalizada_em'] = now()->toISOString();
        $atividades[$atividadeIndex]['observacoes_finalizacao'] = $validated['observacoes'];
        
        // Dados para atualizar na OS
        $dadosOS = ['atividades_tecnicos' => $atividades];
        
        // Se foi informado um estabelecimento e a OS não tem, vincula
        if (!empty($validated['estabelecimento_id']) && !$ordemServico->estabelecimento_id) {
            $dadosOS['estabelecimento_id'] = $validated['estabelecimento_id'];
        }
        
        // Salva as atividades atualizadas
        $ordemServico->update($dadosOS);
        
        // Verifica se TODAS as atividades foram finalizadas
        $todasFinalizadas = true;
        foreach ($atividades as $atv) {
            if (($atv['status'] ?? 'pendente') !== 'finalizada') {
                $todasFinalizadas = false;
                break;
            }
        }
        
        // Se todas as atividades foram finalizadas, finaliza a OS automaticamente
        if ($todasFinalizadas) {
            // Determina o status geral baseado nas atividades
            $statusGeral = 'sim';
            $acoesExecutadasIds = [];
            foreach ($atividades as $atv) {
                $tipoAcaoId = $atv['tipo_acao_id'] ?? null;
                $statusExecucaoAtividade = $atv['status_execucao'] ?? 'concluido';

                if ($tipoAcaoId && $statusExecucaoAtividade !== 'nao_concluido') {
                    $acoesExecutadasIds[] = (int) $tipoAcaoId;
                }

                if (($atv['status_execucao'] ?? 'concluido') === 'nao_concluido') {
                    $statusGeral = 'nao';
                    break;
                } elseif (($atv['status_execucao'] ?? 'concluido') === 'parcial') {
                    $statusGeral = 'parcial';
                }
            }

            $acoesExecutadasIds = array_values(array_unique($acoesExecutadasIds));
            
            $ordemServico->update([
                'status' => 'finalizada',
                'data_conclusao' => now(),
                'finalizada_em' => now(),
                'atividades_realizadas' => $statusGeral,
                'acoes_executadas_ids' => $acoesExecutadasIds,
                'observacoes_finalizacao' => 'Ordem de Serviço finalizada automaticamente após conclusão de todas as atividades.',
            ]);
            
            // Cria notificação para gestores
            $this->criarNotificacaoFinalizacao($ordemServico, $usuario);
            
            return response()->json([
                'success' => true,
                'message' => 'Atividade finalizada! A Ordem de Serviço foi encerrada automaticamente pois todas as atividades foram concluídas.',
                'os_finalizada' => true,
                'atividade_nome' => $atividade['nome_atividade'] ?? 'Atividade'
            ]);
        }
        
        // Conta quantas atividades ainda estão pendentes
        $pendentes = 0;
        foreach ($atividades as $atv) {
            if (($atv['status'] ?? 'pendente') !== 'finalizada') {
                $pendentes++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Atividade finalizada com sucesso!',
            'os_finalizada' => false,
            'atividade_nome' => $atividade['nome_atividade'] ?? 'Atividade',
            'atividades_pendentes' => $pendentes
        ]);
    }

    /**
     * Retorna as atividades do técnico logado para uma OS
     */
    public function getMinhasAtividades(OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        $atividades = $ordemServico->atividades_tecnicos ?? [];
        
        $minhasAtividades = [];
        
        foreach ($atividades as $index => $atividade) {
            $tecnicosAtividade = $atividade['tecnicos'] ?? [];
            
            // Verifica se o técnico está atribuído a esta atividade
            if (in_array($usuario->id, $tecnicosAtividade)) {
                $responsavelId = $atividade['responsavel_id'] ?? null;
                $responsavel = $responsavelId ? UsuarioInterno::find($responsavelId) : null;
                
                $minhasAtividades[] = [
                    'index' => $index,
                    'tipo_acao_id' => $atividade['tipo_acao_id'] ?? null,
                    'sub_acao_id' => $atividade['sub_acao_id'] ?? null,
                    'nome_atividade' => $atividade['nome_atividade'] ?? 'Atividade',
                    'status' => $atividade['status'] ?? 'pendente',
                    'responsavel_id' => $responsavelId,
                    'responsavel_nome' => $responsavel ? $responsavel->nome : null,
                    'sou_responsavel' => $responsavelId == $usuario->id,
                    'finalizada_em' => $atividade['finalizada_em'] ?? null,
                    'observacoes_finalizacao' => $atividade['observacoes_finalizacao'] ?? null,
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'atividades' => $minhasAtividades,
            'total' => count($minhasAtividades),
            'pendentes' => count(array_filter($minhasAtividades, fn($a) => $a['status'] !== 'finalizada')),
            'finalizadas' => count(array_filter($minhasAtividades, fn($a) => $a['status'] === 'finalizada')),
        ]);
    }

    /**
     * Gerar PDF da Ordem de Serviço
     */
    public function gerarPdf(OrdemServico $ordemServico)
    {
        $usuario = Auth::guard('interno')->user();
        
        // Verifica permissão
        if (!$this->podeVisualizarOS($usuario, $ordemServico)) {
            abort(403, 'Você não tem permissão para gerar PDF desta ordem de serviço.');
        }

        $ordemServico->load(['estabelecimento.municipio', 'municipio', 'processo']);

        // Renderiza a view para PDF
        $html = view('ordens-servico.pdf', compact('ordemServico'))->render();

        // Gera PDF usando DomPDF
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        return $pdf->download('OS-' . str_pad($ordemServico->numero, 5, '0', STR_PAD_LEFT) . '.pdf');
    }
    
    /**
     * Envia notificação no chat interno para os técnicos atribuídos
     */
    private function enviarNotificacaoTecnicos(OrdemServico $ordemServico, $remetente)
    {
        try {
            \Log::info('OS Notificação: Iniciando envio de notificações', [
                'os_id' => $ordemServico->id,
                'remetente_id' => $remetente->id,
                'remetente_nome' => $remetente->nome,
            ]);
            
            // Verifica se o chat interno está ativo
            $chatAtivo = \App\Models\ConfiguracaoSistema::where('chave', 'chat_interno_ativo')->first();
            if (!$chatAtivo || $chatAtivo->valor !== 'true') {
                \Log::info('OS Notificação: Chat interno está DESATIVADO');
                return;
            }
            
            \Log::info('OS Notificação: Chat interno está ATIVO');
            
            // Carrega dados completos da OS
            $ordemServico->load(['estabelecimento.municipio', 'municipio', 'processo']);
            
            // Extrai técnicos únicos de todas as atividades
            $tecnicosNotificados = [];
            $atividadesTecnicos = $ordemServico->atividades_tecnicos ?? [];
            
            \Log::info('OS Notificação: Atividades encontradas', [
                'total' => count($atividadesTecnicos),
                'atividades' => $atividadesTecnicos,
            ]);
            
            // Agrupa atividades por técnico
            $atividadesPorTecnico = [];
            foreach ($atividadesTecnicos as $atividade) {
                $nomeAtividade = $atividade['nome_atividade'] ?? 'Atividade';
                $tecnicosIds = $atividade['tecnicos'] ?? [];
                $responsavelId = $atividade['responsavel_id'] ?? null;
                
                foreach ($tecnicosIds as $tecnicoId) {
                    if (!isset($atividadesPorTecnico[$tecnicoId])) {
                        $atividadesPorTecnico[$tecnicoId] = [
                            'atividades' => [],
                            'eh_responsavel' => false,
                        ];
                    }
                    $atividadesPorTecnico[$tecnicoId]['atividades'][] = $nomeAtividade;
                    if ($tecnicoId == $responsavelId) {
                        $atividadesPorTecnico[$tecnicoId]['eh_responsavel'] = true;
                    }
                }
            }
            
            // Dados do estabelecimento
            $estabelecimentoNome = $ordemServico->estabelecimento 
                ? ($ordemServico->estabelecimento->nome_fantasia ?? $ordemServico->estabelecimento->razao_social)
                : 'Não vinculado';
            
            $estabelecimentoEndereco = '';
            if ($ordemServico->estabelecimento) {
                $est = $ordemServico->estabelecimento;
                $estabelecimentoEndereco = $est->logradouro;
                if ($est->numero) $estabelecimentoEndereco .= ', ' . $est->numero;
                if ($est->bairro) $estabelecimentoEndereco .= ' - ' . $est->bairro;
                if ($est->municipio) $estabelecimentoEndereco .= ' - ' . $est->municipio->nome . '/' . $est->municipio->uf;
            }
            
            $estabelecimentoCnpj = '';
            if ($ordemServico->estabelecimento) {
                $estabelecimentoCnpj = $ordemServico->estabelecimento->cnpj_formatado ?? $ordemServico->estabelecimento->cpf_formatado ?? '';
            }
            
            // URL do PDF (rota correta)
            $pdfUrl = route('admin.ordens-servico.pdf', $ordemServico->id);
            $osUrl = route('admin.ordens-servico.show', $ordemServico->id);
            
            foreach ($atividadesPorTecnico as $tecnicoId => $dados) {
                \Log::info('OS Notificação: Verificando técnico', [
                    'tecnico_id' => $tecnicoId,
                    'remetente_id' => $remetente->id,
                ]);
                
                // Busca o técnico
                $tecnico = UsuarioInterno::find($tecnicoId);
                if (!$tecnico) {
                    \Log::warning('OS Notificação: Técnico não encontrado', ['tecnico_id' => $tecnicoId]);
                    continue;
                }
                
                \Log::info('OS Notificação: Enviando mensagem para técnico', [
                    'tecnico_id' => $tecnicoId,
                    'tecnico_nome' => $tecnico->nome,
                ]);
                
                // Tipo de atribuição
                $tipoTecnico = $dados['eh_responsavel'] ? 'Técnico Responsável' : 'Técnico';
                
                // Lista de atividades
                $listaAtividades = implode("\n• ", $dados['atividades']);
                
                // Monta a mensagem
                $mensagemTexto = "📋 *NOVA ORDEM DE SERVIÇO*\n\n";
                $mensagemTexto .= "Olá {$tecnico->nome}!\n\n";
                $mensagemTexto .= "Você foi atribuído como *{$tipoTecnico}* em uma nova Ordem de Serviço.\n\n";
                $mensagemTexto .= "═══════════════════════════\n";
                $mensagemTexto .= "📌 *OS Nº:* " . str_pad($ordemServico->numero, 5, '0', STR_PAD_LEFT) . "\n";
                $mensagemTexto .= "🏢 *Estabelecimento:* {$estabelecimentoNome}\n";
                if ($estabelecimentoCnpj) {
                    $mensagemTexto .= "📄 *CNPJ/CPF:* {$estabelecimentoCnpj}\n";
                }
                if ($estabelecimentoEndereco) {
                    $mensagemTexto .= "📍 *Endereço:* {$estabelecimentoEndereco}\n";
                }
                $mensagemTexto .= "📅 *Período:* " . \Carbon\Carbon::parse($ordemServico->data_inicio)->format('d/m/Y') . " a " . \Carbon\Carbon::parse($ordemServico->data_fim)->format('d/m/Y') . "\n";
                $mensagemTexto .= "═══════════════════════════\n\n";
                $mensagemTexto .= "📝 *Ações a executar:*\n• {$listaAtividades}\n\n";
                $mensagemTexto .= "🔗 *Acessar OS:* {$osUrl}\n";
                $mensagemTexto .= "📎 *Baixar PDF:* {$pdfUrl}\n\n";
                $mensagemTexto .= "— Suporte INFOVISA";
                
                // Encontra ou cria conversa entre remetente e técnico
                $conversa = ChatConversa::encontrarOuCriar($remetente->id, $tecnicoId);
                
                \Log::info('OS Notificação: Conversa encontrada/criada', [
                    'conversa_id' => $conversa->id,
                ]);
                
                // Cria a mensagem de TEXTO (não arquivo)
                $mensagem = ChatMensagem::create([
                    'conversa_id' => $conversa->id,
                    'remetente_id' => $remetente->id,
                    'conteudo' => $mensagemTexto,
                    'tipo' => 'texto',
                ]);
                
                \Log::info('OS Notificação: Mensagem criada com sucesso', [
                    'mensagem_id' => $mensagem->id,
                ]);
                
                // Atualiza timestamp da conversa
                $conversa->update(['ultima_mensagem_at' => now()]);
            }
            
            \Log::info('OS Notificação: Processo finalizado', [
                'total_notificados' => count($atividadesPorTecnico),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('OS Notificação: ERRO ao enviar notificações', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

