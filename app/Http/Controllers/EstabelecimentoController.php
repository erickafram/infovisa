<?php

namespace App\Http\Controllers;

use App\Models\Estabelecimento;
use App\Models\EstabelecimentoHistorico;
use App\Models\UsuarioExterno;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EstabelecimentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Buscar estabelecimentos do usuário logado (externo ou interno)
        $query = Estabelecimento::query();

        // Se usuário externo estiver logado, mostrar apenas seus estabelecimentos
        if (auth('externo')->check()) {
            $query->doUsuario(auth('externo')->id());
        }

        // Se usuário interno estiver logado, aplicar filtros baseados no perfil
        if (auth('interno')->check()) {
            $usuario = auth('interno')->user();
            
            // Aplica filtro baseado no perfil do usuário
            $query->paraUsuario($usuario);
            
            // Mostrar apenas estabelecimentos aprovados
            $query->aprovados();
        }

        // Filtro de município
        if ($request->filled('municipio')) {
            $query->porMunicipio($request->municipio);
        }

        // Filtro de busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome_fantasia', 'like', "%{$search}%")
                  ->orWhere('razao_social', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('cidade', 'like', "%{$search}%");
            });
        }

        $estabelecimentos = $query->with(['usuarioExterno', 'aprovadoPor', 'municipio'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Filtra estabelecimentos por competência baseado no perfil do usuário
        if (auth('interno')->check()) {
            $usuario = auth('interno')->user();
            
            // Usuários ESTADUAIS veem APENAS estabelecimentos de competência ESTADUAL
            if ($usuario->isEstadual()) {
                $estabelecimentosFiltrados = $estabelecimentos->getCollection()->filter(function ($estabelecimento) {
                    return $estabelecimento->isCompetenciaEstadual();
                });
                $estabelecimentos->setCollection($estabelecimentosFiltrados);
            }
            
            // Usuários MUNICIPAIS veem APENAS estabelecimentos de competência MUNICIPAL
            if ($usuario->isMunicipal()) {
                $estabelecimentosFiltrados = $estabelecimentos->getCollection()->filter(function ($estabelecimento) {
                    return $estabelecimento->isCompetenciaMunicipal();
                });
                $estabelecimentos->setCollection($estabelecimentosFiltrados);
            }
        }

        // Estatísticas para o dashboard
        $estatisticasQuery = Estabelecimento::query();
        if (auth('interno')->check()) {
            $estatisticasQuery->paraUsuario(auth('interno')->user());
        }
        
        $estatisticas = [
            'total' => $estatisticasQuery->aprovados()->count(),
            'pendentes' => $estatisticasQuery->pendentes()->count(),
            'aprovados' => $estatisticasQuery->aprovados()->where('ativo', true)->count(),
            'rejeitados' => $estatisticasQuery->rejeitados()->count(),
            'desativados' => $estatisticasQuery->where('ativo', false)->count(),
        ];

        return view('estabelecimentos.index', compact('estabelecimentos', 'estatisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Redireciona para escolha do tipo
        return redirect()->route('admin.estabelecimentos.create.juridica');
    }

    /**
     * Show the form for creating a new Pessoa Jurídica.
     */
    public function createJuridica()
    {
        return view('estabelecimentos.create-juridica');
    }

    /**
     * Show the form for creating a new Pessoa Física.
     */
    public function createFisica()
    {
        return view('estabelecimentos.create-fisica');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug temporário
        \Log::info('Dados recebidos:', $request->all());
        
        $rules = [
            'tipo_pessoa' => 'required|in:juridica,fisica',
            'tipo_setor' => 'required|in:publico,privado',
            'nome_fantasia' => 'required|string|max:255',
            'endereco' => 'required|string|max:255', // Este é o logradouro
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'required|string|max:100',
            'cidade' => 'required|string|max:100',
            'estado' => 'required|string|size:2',
            'cep' => 'required|string|size:8',
            'telefone' => 'required|string|max:15', // Agora é obrigatório
            'email' => 'required|email|max:255', // Agora é obrigatório
            // Campos da API
            'natureza_juridica' => 'nullable|string',
            'porte' => 'nullable|string',
            'situacao_cadastral' => 'nullable|string',
            'descricao_situacao_cadastral' => 'nullable|string',
            'data_situacao_cadastral' => 'nullable|date',
            'data_inicio_atividade' => 'nullable|date',
            'cnae_fiscal' => 'nullable|string',
            'cnae_fiscal_descricao' => 'nullable|string',
            'capital_social' => 'nullable|numeric',
            'logradouro' => 'nullable|string',
            'codigo_municipio_ibge' => 'nullable|string',
            'motivo_situacao_cadastral' => 'nullable|string',
            'descricao_motivo_situacao_cadastral' => 'nullable|string',
            'atividades_exercidas' => 'nullable|string', // JSON das atividades selecionadas
            'respostas_questionario' => 'nullable|string', // JSON das respostas dos questionários
        ];

        // Validações específicas por tipo de pessoa
        if ($request->tipo_pessoa === 'juridica') {
            $rules['cnpj'] = 'required|string|size:14';
            $rules['razao_social'] = 'required|string|max:255';
            
            // Para estabelecimentos privados, CNPJ deve ser único
            // Para estabelecimentos públicos, CNPJ pode ser repetido
            if ($request->tipo_setor === 'privado') {
                $rules['cnpj'] .= '|unique:estabelecimentos,cnpj';
            }
        } else {
            $rules['cpf'] = 'required|string|size:11|unique:estabelecimentos,cpf';
            $rules['nome_completo'] = 'required|string|max:255';
            $rules['rg'] = 'required|string|max:20';
            $rules['orgao_emissor'] = 'required|string|max:20';
            $rules['data_inicio_funcionamento'] = 'required|date';
        }

        $validated = $request->validate($rules);

        // Processa campos JSON se existirem
        if ($request->filled('cnaes_secundarios')) {
            $validated['cnaes_secundarios'] = json_decode($request->cnaes_secundarios, true);
        }
        
        if ($request->filled('qsa')) {
            $validated['qsa'] = json_decode($request->qsa, true);
        }

        // Processa atividades exercidas selecionadas pelo usuário
        if ($request->filled('atividades_exercidas')) {
            $atividadesExercidas = json_decode($request->atividades_exercidas, true);
            \Log::info('Atividades recebidas:', ['atividades' => $atividadesExercidas]);
            $validated['atividades_exercidas'] = $atividadesExercidas;
        } else {
            \Log::warning('Nenhuma atividade recebida no request');
        }

        // Processa respostas dos questionários
        if ($request->filled('respostas_questionario')) {
            $respostasQuestionario = json_decode($request->respostas_questionario, true);
            \Log::info('Respostas questionário recebidas:', ['respostas' => $respostasQuestionario]);
            $validated['respostas_questionario'] = $respostasQuestionario;
        }

        // Define o usuário responsável e status inicial
        if (auth('interno')->check()) {
            // Para usuários internos (admin), o estabelecimento não precisa estar vinculado a um usuário externo
            $validated['usuario_externo_id'] = null;
            // Admin pode criar já aprovado
            $validated['status'] = $request->input('status', 'aprovado');
            if ($validated['status'] === 'aprovado') {
                $validated['aprovado_por'] = auth('interno')->id();
                $validated['aprovado_em'] = now();
            }
        } else {
            // Para usuários externos, vincula ao usuário logado e cria como pendente
            $validated['usuario_externo_id'] = auth('externo')->id();
            $validated['status'] = 'pendente';
        }

        // Define o município baseado na cidade
        $validated['municipio'] = $validated['cidade'];
        
        // Normaliza o município e obtém o ID
        $nomeMunicipio = $validated['cidade'];
        $codigoIbge = $validated['codigo_municipio_ibge'] ?? null;
        
        if ($nomeMunicipio) {
            // Remove " - TO" ou "/TO" do nome se existir
            $nomeMunicipio = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', $nomeMunicipio);
            
            $municipioId = \App\Helpers\MunicipioHelper::normalizarEObterIdPorNome($nomeMunicipio, $codigoIbge);
            if ($municipioId) {
                $validated['municipio_id'] = $municipioId;
                $validated['municipio'] = $nomeMunicipio;
            }
        }

        // VALIDAÇÃO: Usuários municipais só podem cadastrar estabelecimentos do seu município
        if (auth('interno')->check()) {
            $usuario = auth('interno')->user();
            
            if ($usuario->isMunicipal()) {
                // Verifica se o usuário tem município vinculado
                if (!$usuario->municipio_id) {
                    return back()->withErrors([
                        'cidade' => 'Seu usuário não possui município vinculado. Entre em contato com o administrador.'
                    ])->withInput();
                }
                
                // Verifica se o município do estabelecimento é o mesmo do usuário
                if (isset($validated['municipio_id']) && $validated['municipio_id'] != $usuario->municipio_id) {
                    $municipioUsuario = $usuario->municipioRelacionado->nome ?? 'seu município';
                    return back()->withErrors([
                        'cidade' => "Você só pode cadastrar estabelecimentos do município de {$municipioUsuario}. O estabelecimento informado pertence a {$nomeMunicipio}."
                    ])->withInput();
                }
                
                // Se não conseguiu identificar o município do estabelecimento
                if (!isset($validated['municipio_id'])) {
                    return back()->withErrors([
                        'cidade' => 'Não foi possível identificar o município do estabelecimento. Verifique se a cidade está correta.'
                    ])->withInput();
                }
            }
        }

        // Remove formatação de campos numéricos
        if (isset($validated['cnpj'])) {
            $validated['cnpj'] = preg_replace('/[^0-9]/', '', $validated['cnpj']);
        }
        
        if (isset($validated['cpf'])) {
            $validated['cpf'] = preg_replace('/[^0-9]/', '', $validated['cpf']);
        }
        
        if (isset($validated['cep'])) {
            $validated['cep'] = preg_replace('/[^0-9]/', '', $validated['cep']);
        }
        
        // Mapeia data_inicio_funcionamento para data_inicio_atividade (pessoa física)
        if (isset($validated['data_inicio_funcionamento'])) {
            $validated['data_inicio_atividade'] = $validated['data_inicio_funcionamento'];
            unset($validated['data_inicio_funcionamento']);
        }

        $estabelecimento = Estabelecimento::create($validated);

        // Registra no histórico
        EstabelecimentoHistorico::registrar(
            $estabelecimento->id,
            'criado',
            null,
            $estabelecimento->status,
            'Estabelecimento cadastrado'
        );

        return redirect()
            ->route('admin.estabelecimentos.index')
            ->with('success', 'Estabelecimento cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        
        // Verifica se o usuário tem permissão para acessar este estabelecimento
        if (auth('interno')->check()) {
            $usuario = auth('interno')->user();
            
            // Usuários MUNICIPAIS só podem acessar estabelecimentos MUNICIPAIS
            if ($usuario->isMunicipal() && $estabelecimento->isCompetenciaEstadual()) {
                abort(403, 'Acesso negado. Este estabelecimento é de competência estadual e você não tem permissão para acessá-lo.');
            }
            
            // Usuários ESTADUAIS só podem acessar estabelecimentos ESTADUAIS
            if ($usuario->isEstadual() && $estabelecimento->isCompetenciaMunicipal()) {
                abort(403, 'Acesso negado. Este estabelecimento é de competência municipal e você não tem permissão para acessá-lo.');
            }
        }
        
        return view('estabelecimentos.show', compact('estabelecimento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        
        // Verifica se o usuário tem permissão para editar este estabelecimento
        if (auth('interno')->check()) {
            $usuario = auth('interno')->user();
            
            // Usuários MUNICIPAIS só podem editar estabelecimentos MUNICIPAIS
            if ($usuario->isMunicipal() && $estabelecimento->isCompetenciaEstadual()) {
                abort(403, 'Acesso negado. Este estabelecimento é de competência estadual e você não tem permissão para editá-lo.');
            }
            
            // Usuários ESTADUAIS só podem editar estabelecimentos ESTADUAIS
            if ($usuario->isEstadual() && $estabelecimento->isCompetenciaMunicipal()) {
                abort(403, 'Acesso negado. Este estabelecimento é de competência municipal e você não tem permissão para editá-lo.');
            }
        }
        
        // Redireciona para view específica baseado no tipo de pessoa
        if ($estabelecimento->tipo_pessoa === 'fisica') {
            return view('estabelecimentos.edit-fisica', compact('estabelecimento'));
        }
        
        return view('estabelecimentos.edit', compact('estabelecimento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        
        // Verifica se o usuário tem permissão para atualizar este estabelecimento
        if (auth('interno')->check()) {
            $usuario = auth('interno')->user();
            
            // Usuários MUNICIPAIS só podem atualizar estabelecimentos MUNICIPAIS
            if ($usuario->isMunicipal() && $estabelecimento->isCompetenciaEstadual()) {
                abort(403, 'Acesso negado. Este estabelecimento é de competência estadual e você não tem permissão para atualizá-lo.');
            }
            
            // Usuários ESTADUAIS só podem atualizar estabelecimentos ESTADUAIS
            if ($usuario->isEstadual() && $estabelecimento->isCompetenciaMunicipal()) {
                abort(403, 'Acesso negado. Este estabelecimento é de competência municipal e você não tem permissão para atualizá-lo.');
            }
        }
        
        $rules = [
            'tipo_setor' => 'required|in:publico,privado',
            'nome_fantasia' => 'required|string|max:255',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'required|string|max:100',
            'cidade' => 'required|string|max:100',
            'estado' => 'required|string|size:2',
            'cep' => 'required|string',
            'telefone' => 'nullable|string|max:20',
            'telefone2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ];

        // Adicionar regras específicas baseado no tipo de pessoa
        if ($estabelecimento->tipo_pessoa === 'juridica') {
            $rules['razao_social'] = 'required|string|max:255';
            $rules['cnpj'] = 'required|string';
        } else {
            $rules['nome_completo'] = 'required|string|max:255';
            $rules['cpf'] = 'required|string';
        }

        $validated = $request->validate($rules);

        // Limpar formatação de documentos e CEP
        if (isset($validated['cnpj'])) {
            $validated['cnpj'] = preg_replace('/[^0-9]/', '', $validated['cnpj']);
        }
        
        if (isset($validated['cpf'])) {
            $validated['cpf'] = preg_replace('/[^0-9]/', '', $validated['cpf']);
        }
        
        if (isset($validated['cep'])) {
            $validated['cep'] = preg_replace('/[^0-9]/', '', $validated['cep']);
        }

        $estabelecimento->update($validated);

        // Garante que o timestamp de atualização reflita a última edição
        $estabelecimento->touch();

        return redirect()
            ->route('admin.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Estabelecimento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $nome = '';

            DB::transaction(function () use ($id, &$nome) {
                $estabelecimento = Estabelecimento::with('responsaveis')->findOrFail($id);

                $nome = $estabelecimento->nome_fantasia
                    ?? $estabelecimento->razao_social
                    ?? $estabelecimento->nome_completo
                    ?? 'Estabelecimento';

                // Remove vínculos em tabelas auxiliares
                $estabelecimento->responsaveis()->detach();

                // Remove definitivamente o registro
                $estabelecimento->forceDelete();
            });

            return redirect()
                ->route('admin.estabelecimentos.index')
                ->with('success', "Estabelecimento '{$nome}' excluído com sucesso!");

        } catch (\Exception $e) {
            \Log::error('Erro ao excluir estabelecimento', [
                'id' => $id,
                'erro' => $e->getMessage()
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir estabelecimento. Tente novamente.');
        }
    }

    /**
     * Altera manualmente a competência do estabelecimento (decisão administrativa/judicial)
     */
    public function alterarCompetencia(Request $request, string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        
        $request->validate([
            'competencia_manual' => 'required|in:estadual,municipal,automatica',
            'motivo_alteracao_competencia' => 'required|string|min:10|max:1000',
        ], [
            'competencia_manual.required' => 'Selecione a nova competência',
            'motivo_alteracao_competencia.required' => 'O motivo da alteração é obrigatório',
            'motivo_alteracao_competencia.min' => 'O motivo deve ter no mínimo 10 caracteres',
            'motivo_alteracao_competencia.max' => 'O motivo deve ter no máximo 1000 caracteres',
        ]);
        
        // Se escolheu "automatica", remove o override manual
        if ($request->competencia_manual === 'automatica') {
            $estabelecimento->update([
                'competencia_manual' => null,
                'motivo_alteracao_competencia' => $request->motivo_alteracao_competencia,
                'alterado_por' => auth('interno')->id(),
                'alterado_em' => now(),
            ]);
            
            $competenciaFinal = $estabelecimento->isCompetenciaEstadual() ? 'ESTADUAL' : 'MUNICIPAL';
            
            return redirect()
                ->route('admin.estabelecimentos.show', $estabelecimento->id)
                ->with('success', "Competência voltou a seguir as regras de pactuação automática! O estabelecimento agora é de competência {$competenciaFinal}.");
        }
        
        // Caso contrário, define o override manual
        $estabelecimento->update([
            'competencia_manual' => $request->competencia_manual,
            'motivo_alteracao_competencia' => $request->motivo_alteracao_competencia,
            'alterado_por' => auth('interno')->id(),
            'alterado_em' => now(),
        ]);
        
        return redirect()
            ->route('admin.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Competência alterada com sucesso! O estabelecimento agora é de competência ' . strtoupper($request->competencia_manual) . '.');
    }

    /**
     * Show the form for editing activities.
     */
    public function editAtividades(string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        
        // Verifica se o usuário tem permissão para editar atividades deste estabelecimento
        if (auth('interno')->check()) {
            $usuario = auth('interno')->user();
            
            // Usuários MUNICIPAIS só podem editar atividades de estabelecimentos MUNICIPAIS
            if ($usuario->isMunicipal() && $estabelecimento->isCompetenciaEstadual()) {
                abort(403, 'Acesso negado. Este estabelecimento é de competência estadual e você não tem permissão para editar suas atividades.');
            }
            
            // Usuários ESTADUAIS só podem editar atividades de estabelecimentos ESTADUAIS
            if ($usuario->isEstadual() && $estabelecimento->isCompetenciaMunicipal()) {
                abort(403, 'Acesso negado. Este estabelecimento é de competência municipal e você não tem permissão para editar suas atividades.');
            }
        }
        
        // Para pessoa física, usa view específica com API IBGE
        if ($estabelecimento->tipo_pessoa === 'fisica') {
            return view('estabelecimentos.atividades-fisica', compact('estabelecimento'));
        }
        
        // Buscar atividades da API ReceitaWS se for pessoa jurídica
        $atividadesApi = [];
        if ($estabelecimento->tipo_pessoa === 'juridica' && $estabelecimento->cnpj) {
            try {
                $cnpj = preg_replace('/[^0-9]/', '', $estabelecimento->cnpj);
                $response = Http::timeout(10)->get("https://receitaws.com.br/v1/cnpj/{$cnpj}");
                
                if ($response->successful()) {
                    $dados = $response->json();
                    
                    // Atividade principal
                    if (!empty($dados['atividade_principal'])) {
                        foreach ($dados['atividade_principal'] as $atividade) {
                            $atividadesApi[] = [
                                'codigo' => $atividade['code'] ?? '',
                                'descricao' => $atividade['text'] ?? '',
                                'tipo' => 'principal'
                            ];
                        }
                    }
                    
                    // Atividades secundárias
                    if (!empty($dados['atividades_secundarias'])) {
                        foreach ($dados['atividades_secundarias'] as $atividade) {
                            $atividadesApi[] = [
                                'codigo' => $atividade['code'] ?? '',
                                'descricao' => $atividade['text'] ?? '',
                                'tipo' => 'secundaria'
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao buscar atividades da API: ' . $e->getMessage());
            }
        }
        
        // Se não conseguiu buscar da API, usa as atividades salvas no banco
        if (empty($atividadesApi) && $estabelecimento->atividades_exercidas) {
            foreach ($estabelecimento->atividades_exercidas as $atividade) {
                $atividadesApi[] = [
                    'codigo' => $atividade['codigo'] ?? '',
                    'descricao' => $atividade['descricao'] ?? '',
                    'tipo' => ($atividade['principal'] ?? false) ? 'principal' : 'secundaria'
                ];
            }
        }
        
        // Se ainda não tem atividades, adiciona a atividade principal do CNAE fiscal
        if (empty($atividadesApi) && $estabelecimento->cnae_fiscal) {
            $atividadesApi[] = [
                'codigo' => $estabelecimento->cnae_fiscal,
                'descricao' => $estabelecimento->cnae_fiscal_descricao ?? '',
                'tipo' => 'principal'
            ];
        }
        
        return view('estabelecimentos.atividades', compact('estabelecimento', 'atividadesApi'));
    }

    /**
     * Update the activities.
     */
    public function updateAtividades(Request $request, string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        
        // Verifica se o usuário tem permissão para atualizar atividades deste estabelecimento
        if (auth('interno')->check()) {
            $usuario = auth('interno')->user();
            
            // Usuários MUNICIPAIS só podem atualizar atividades de estabelecimentos MUNICIPAIS
            if ($usuario->isMunicipal() && $estabelecimento->isCompetenciaEstadual()) {
                abort(403, 'Acesso negado. Este estabelecimento é de competência estadual e você não tem permissão para atualizar suas atividades.');
            }
            
            // Usuários ESTADUAIS só podem atualizar atividades de estabelecimentos ESTADUAIS
            if ($usuario->isEstadual() && $estabelecimento->isCompetenciaMunicipal()) {
                abort(403, 'Acesso negado. Este estabelecimento é de competência municipal e você não tem permissão para atualizar suas atividades.');
            }
        }
        
        $validated = $request->validate([
            'atividades_exercidas' => 'nullable|string',
        ]);
        
        // Decodifica o JSON das atividades
        $atividades = [];
        if (!empty($validated['atividades_exercidas'])) {
            $atividades = json_decode($validated['atividades_exercidas'], true);
        }
        
        $estabelecimento->update([
            'atividades_exercidas' => $atividades
        ]);
        
        $estabelecimento->touch();
        
        return redirect()
            ->route('admin.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Atividades atualizadas com sucesso!');
    }

    /**
     * Lista estabelecimentos pendentes de aprovação
     */
    public function pendentes(Request $request)
    {
        $query = Estabelecimento::pendentes()
            ->doMunicipioUsuario()
            ->with(['usuarioExterno']);

        // Filtro de busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome_fantasia', 'like', "%{$search}%")
                  ->orWhere('razao_social', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        $estabelecimentos = $query->orderBy('created_at', 'asc')->paginate(15);

        return view('estabelecimentos.pendentes', compact('estabelecimentos'));
    }

    /**
     * Lista estabelecimentos rejeitados
     */
    public function rejeitados(Request $request)
    {
        $query = Estabelecimento::rejeitados()
            ->doMunicipioUsuario()
            ->with(['usuarioExterno', 'aprovadoPor']);

        // Filtro de busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome_fantasia', 'like', "%{$search}%")
                  ->orWhere('razao_social', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        $estabelecimentos = $query->orderBy('aprovado_em', 'desc')->paginate(15);

        return view('estabelecimentos.rejeitados', compact('estabelecimentos'));
    }

    /**
     * Lista estabelecimentos desativados
     */
    public function desativados(Request $request)
    {
        $query = Estabelecimento::where('ativo', false)
            ->doMunicipioUsuario()
            ->with(['usuarioExterno', 'aprovadoPor']);

        // Filtro de busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome_fantasia', 'like', "%{$search}%")
                  ->orWhere('razao_social', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        $estabelecimentos = $query->orderBy('updated_at', 'desc')->paginate(15);

        return view('estabelecimentos.desativados', compact('estabelecimentos'));
    }


    /**
     * Aprova um estabelecimento
     */
    public function aprovar(Request $request, string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);

        // Verifica permissão
        if (!auth('interno')->check()) {
            return redirect()->back()->with('error', 'Você não tem permissão para aprovar estabelecimentos.');
        }

        $validated = $request->validate([
            'observacao' => 'nullable|string|max:500',
        ]);

        $estabelecimento->aprovar($validated['observacao'] ?? null);

        return redirect()
            ->route('admin.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Estabelecimento aprovado com sucesso!');
    }

    /**
     * Rejeita um estabelecimento
     */
    public function rejeitar(Request $request, string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);

        // Verifica permissão
        if (!auth('interno')->check()) {
            return redirect()->back()->with('error', 'Você não tem permissão para rejeitar estabelecimentos.');
        }

        $validated = $request->validate([
            'motivo_rejeicao' => 'required|string|max:1000',
            'observacao' => 'nullable|string|max:500',
        ]);

        $estabelecimento->rejeitar(
            $validated['motivo_rejeicao'],
            $validated['observacao'] ?? null
        );

        return redirect()
            ->route('admin.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Estabelecimento rejeitado.');
    }

    /**
     * Reinicia um estabelecimento (volta para pendente)
     */
    public function reiniciar(Request $request, string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);

        // Verifica permissão
        if (!auth('interno')->check()) {
            return redirect()->back()->with('error', 'Você não tem permissão para reiniciar estabelecimentos.');
        }

        $validated = $request->validate([
            'observacao' => 'nullable|string|max:500',
        ]);

        $estabelecimento->reiniciar($validated['observacao'] ?? null);

        return redirect()
            ->route('admin.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Estabelecimento reiniciado. Status voltou para pendente.');
    }


    /**
     * Exibe o histórico de um estabelecimento
     */
    public function historico(string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        $historicos = $estabelecimento->historicos()
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('estabelecimentos.historico', compact('estabelecimento', 'historicos'));
    }

    /**
     * Volta estabelecimento aprovado para pendente (apenas admin sem processos)
     */
    public function voltarPendente(Request $request, string $id)
    {
        // Verifica se é administrador
        if (!auth('interno')->user()->nivel_acesso->isAdmin()) {
            return redirect()->back()->with('error', 'Apenas administradores podem realizar esta ação.');
        }

        $estabelecimento = Estabelecimento::findOrFail($id);

        // Verifica se está aprovado
        if ($estabelecimento->status !== 'aprovado') {
            return redirect()->back()->with('error', 'Apenas estabelecimentos aprovados podem voltar para pendente.');
        }

        // Verifica se tem processos
        if ($estabelecimento->processos()->count() > 0) {
            return redirect()->back()->with('error', 'Não é possível voltar para pendente. Este estabelecimento possui processos vinculados.');
        }

        $validated = $request->validate([
            'observacao' => 'required|string|max:1000',
        ]);

        // Atualiza status
        $statusAnterior = $estabelecimento->status;
        $estabelecimento->status = 'pendente';
        $estabelecimento->aprovado_por = null;
        $estabelecimento->aprovado_em = null;
        $estabelecimento->save();

        // Registra no histórico
        EstabelecimentoHistorico::registrar(
            $estabelecimento->id,
            'reiniciado',
            $statusAnterior,
            'pendente',
            'Voltou para pendente: ' . $validated['observacao']
        );

        return redirect()
            ->route('admin.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Estabelecimento voltou para status pendente.');
    }

    /**
     * Desativa um estabelecimento (apenas admin)
     */
    public function desativar(Request $request, string $id)
    {
        // Verifica se é administrador
        if (!auth('interno')->user()->nivel_acesso->isAdmin()) {
            return redirect()->back()->with('error', 'Apenas administradores podem desativar estabelecimentos.');
        }

        $validated = $request->validate([
            'motivo' => 'required|string|max:1000',
        ]);

        $estabelecimento = Estabelecimento::findOrFail($id);
        $estabelecimento->ativo = false;
        $estabelecimento->motivo_desativacao = $validated['motivo'];
        $estabelecimento->save();

        // Registra no histórico
        EstabelecimentoHistorico::registrar(
            $estabelecimento->id,
            'atualizado',
            'ativo',
            'inativo',
            'Estabelecimento desativado: ' . $validated['motivo']
        );

        return redirect()
            ->route('admin.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Estabelecimento desativado com sucesso.');
    }

    /**
     * Ativa um estabelecimento (apenas admin)
     */
    public function ativar(string $id)
    {
        // Verifica se é administrador
        if (!auth('interno')->user()->nivel_acesso->isAdmin()) {
            return redirect()->back()->with('error', 'Apenas administradores podem ativar estabelecimentos.');
        }

        $estabelecimento = Estabelecimento::findOrFail($id);
        $estabelecimento->ativo = true;
        $estabelecimento->motivo_desativacao = null; // Limpa o motivo ao reativar
        $estabelecimento->save();

        // Registra no histórico
        EstabelecimentoHistorico::registrar(
            $estabelecimento->id,
            'atualizado',
            'inativo',
            'ativo',
            'Estabelecimento reativado'
        );

        return redirect()
            ->route('admin.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Estabelecimento reativado com sucesso.');
    }

    /**
     * Lista usuários vinculados ao estabelecimento
     */
    public function usuariosIndex(string $id)
    {
        $estabelecimento = Estabelecimento::with(['usuariosVinculados' => function($query) {
            $query->orderBy('estabelecimento_usuario_externo.created_at', 'desc');
        }])->findOrFail($id);

        // Buscar todos os usuários externos para vincular
        $usuariosDisponiveis = UsuarioExterno::where('ativo', true)
            ->whereNotIn('id', $estabelecimento->usuariosVinculados->pluck('id'))
            ->orderBy('nome')
            ->get();

        return view('estabelecimentos.usuarios.index', compact('estabelecimento', 'usuariosDisponiveis'));
    }

    /**
     * Vincula um usuário externo ao estabelecimento
     */
    public function vincularUsuario(Request $request, string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);

        $validated = $request->validate([
            'usuario_externo_id' => 'required|exists:usuarios_externos,id',
            'tipo_vinculo' => 'required|in:proprietario,responsavel_legal,responsavel_tecnico,contador,procurador,outro',
            'observacao' => 'nullable|string|max:500',
        ]);

        // Verifica se já está vinculado
        if ($estabelecimento->usuariosVinculados()->where('usuario_externo_id', $validated['usuario_externo_id'])->exists()) {
            return redirect()->back()->with('error', 'Este usuário já está vinculado ao estabelecimento.');
        }

        // Vincula
        $estabelecimento->usuariosVinculados()->attach($validated['usuario_externo_id'], [
            'tipo_vinculo' => $validated['tipo_vinculo'],
            'observacao' => $validated['observacao'] ?? null,
            'vinculado_por' => auth('interno')->id(),
        ]);

        // Registra no histórico
        $usuario = UsuarioExterno::find($validated['usuario_externo_id']);
        EstabelecimentoHistorico::registrar(
            $estabelecimento->id,
            'atualizado',
            null,
            null,
            "Usuário {$usuario->nome} vinculado como " . ucfirst(str_replace('_', ' ', $validated['tipo_vinculo']))
        );

        return redirect()
            ->route('admin.estabelecimentos.usuarios.index', $estabelecimento->id)
            ->with('success', 'Usuário vinculado com sucesso.');
    }

    /**
     * Desvincula um usuário externo do estabelecimento
     */
    public function desvincularUsuario(string $id, string $usuario_id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        $usuario = UsuarioExterno::findOrFail($usuario_id);

        $estabelecimento->usuariosVinculados()->detach($usuario_id);

        // Registra no histórico
        EstabelecimentoHistorico::registrar(
            $estabelecimento->id,
            'atualizado',
            null,
            null,
            "Usuário {$usuario->nome} desvinculado"
        );

        return redirect()
            ->route('admin.estabelecimentos.usuarios.index', $estabelecimento->id)
            ->with('success', 'Usuário desvinculado com sucesso.');
    }

    /**
     * Atualiza o vínculo de um usuário externo
     */
    public function atualizarVinculo(Request $request, string $id, string $usuario_id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);

        $validated = $request->validate([
            'tipo_vinculo' => 'required|in:proprietario,responsavel_legal,responsavel_tecnico,contador,procurador,outro',
            'observacao' => 'nullable|string|max:500',
        ]);

        $estabelecimento->usuariosVinculados()->updateExistingPivot($usuario_id, [
            'tipo_vinculo' => $validated['tipo_vinculo'],
            'observacao' => $validated['observacao'] ?? null,
        ]);

        $usuario = UsuarioExterno::find($usuario_id);
        EstabelecimentoHistorico::registrar(
            $estabelecimento->id,
            'atualizado',
            null,
            null,
            "Vínculo do usuário {$usuario->nome} atualizado"
        );

        return redirect()
            ->route('admin.estabelecimentos.usuarios.index', $estabelecimento->id)
            ->with('success', 'Vínculo atualizado com sucesso.');
    }

    /**
     * Buscar estabelecimento por CPF
     */
    public function buscarPorCpf($cpf)
    {
        // Remove máscara do CPF
        $cpf = preg_replace('/\D/', '', $cpf);
        
        // Busca estabelecimento por CPF
        $estabelecimento = Estabelecimento::where('cpf', $cpf)->first();
        
        if ($estabelecimento) {
            return response()->json([
                'existe' => true,
                'nome' => $estabelecimento->nome_completo,
                'rg' => $estabelecimento->rg,
                'orgao_emissor' => $estabelecimento->orgao_emissor,
                'nome_fantasia' => $estabelecimento->nome_fantasia,
                'email' => $estabelecimento->email,
                'telefone' => $estabelecimento->telefone,
            ]);
        }
        
        return response()->json(['existe' => false]);
    }

    /**
     * Buscar usuários externos por nome ou CPF
     */
    public function buscarUsuarios(Request $request)
    {
        $query = $request->input('q');
        $estabelecimentoId = $request->input('estabelecimento_id');
        
        if (strlen($query) < 3) {
            return response()->json([]);
        }
        
        // Busca usuários ativos que não estão vinculados ao estabelecimento
        $usuarios = UsuarioExterno::where('ativo', true)
            ->where(function($q) use ($query) {
                $q->where('nome', 'ILIKE', "%{$query}%")
                  ->orWhere('cpf', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'ILIKE', "%{$query}%");
            })
            ->whereNotIn('id', function($subquery) use ($estabelecimentoId) {
                $subquery->select('usuario_externo_id')
                    ->from('estabelecimento_usuario_externo')
                    ->where('estabelecimento_id', $estabelecimentoId);
            })
            ->limit(10)
            ->get(['id', 'nome', 'cpf', 'email']);
        
        // Formata CPF para exibição
        $usuarios->transform(function($usuario) {
            $usuario->cpf = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $usuario->cpf);
            return $usuario;
        });
        
        return response()->json($usuarios);
    }
}
