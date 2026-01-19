<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use App\Models\Pactuacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EstabelecimentoController extends Controller
{
    /**
     * Retorna query base para estabelecimentos do usuário (próprios e vinculados)
     */
    private function estabelecimentosDoUsuario()
    {
        $usuarioId = auth('externo')->id();
        
        return Estabelecimento::where(function($q) use ($usuarioId) {
            $q->where('usuario_externo_id', $usuarioId)
              ->orWhereHas('usuariosVinculados', function($q2) use ($usuarioId) {
                  $q2->where('usuario_externo_id', $usuarioId);
              });
        });
    }

    /**
     * Busca questionários para uma lista de CNAEs
     */
    public function buscarQuestionarios(Request $request)
    {
        $cnaes = $request->input('cnaes', []);
        
        if (empty($cnaes)) {
            return response()->json([]);
        }

        // Normaliza os CNAEs (remove formatação)
        $cnaesNormalizados = array_map(function($cnae) {
            return preg_replace('/[^0-9]/', '', $cnae);
        }, $cnaes);

        // Busca pactuações que requerem questionário
        $questionarios = Pactuacao::whereIn('cnae_codigo', $cnaesNormalizados)
            ->where('requer_questionario', true)
            ->where('ativo', true)
            ->get()
            ->map(function($pactuacao) {
                return [
                    'cnae' => $pactuacao->cnae_codigo,
                    'cnae_formatado' => $pactuacao->cnae_codigo,
                    'descricao' => $pactuacao->cnae_descricao,
                    'pergunta' => $pactuacao->pergunta,
                    'tabela' => $pactuacao->tabela,
                    'municipios_excecao' => $pactuacao->municipios_excecao ?? [],
                ];
            });

        return response()->json($questionarios);
    }

    public function index(Request $request)
    {
        // Busca todos os estabelecimentos do usuário (próprios e vinculados) para estatísticas
        $todosEstabelecimentos = $this->estabelecimentosDoUsuario()->get();
        
        // Query para listagem com filtros
        $query = $this->estabelecimentosDoUsuario();
        
        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome_fantasia', 'ilike', "%{$search}%")
                  ->orWhere('razao_social', 'ilike', "%{$search}%")
                  ->orWhere('nome_completo', 'ilike', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }
        
        $estabelecimentos = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Estatísticas baseadas na collection já carregada
        $estatisticas = [
            'total' => $todosEstabelecimentos->count(),
            'pendentes' => $todosEstabelecimentos->where('status', 'pendente')->count(),
            'aprovados' => $todosEstabelecimentos->where('status', 'aprovado')->count(),
            'rejeitados' => $todosEstabelecimentos->where('status', 'rejeitado')->count(),
        ];
        
        return view('company.estabelecimentos.index', compact('estabelecimentos', 'estatisticas'));
    }
    
    public function show($id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->with(['processos.tipoProcesso'])
            ->findOrFail($id);
        
        return view('company.estabelecimentos.show', compact('estabelecimento'));
    }
    
    public function create()
    {
        return view('company.estabelecimentos.create');
    }
    
    public function createJuridica()
    {
        return view('company.estabelecimentos.create-juridica');
    }
    
    public function createFisica()
    {
        return view('company.estabelecimentos.create-fisica');
    }
    
    public function store(Request $request)
    {
        Log::info('Dados recebidos no store:', $request->all());
        
        $rules = [
            'tipo_pessoa' => 'required|in:juridica,fisica',
            'tipo_setor' => 'required|in:publico,privado',
            'nome_fantasia' => 'required|string|max:255',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'required|string|max:100',
            'cidade' => 'required|string|max:100',
            'estado' => 'required|string|size:2',
            'cep' => 'required|string',
            'telefone' => 'required|string',
            'email' => 'required|email|max:255',
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
            'atividades_exercidas' => 'nullable|string',
            'respostas_questionario' => 'nullable|string',
        ];

        if ($request->tipo_pessoa === 'juridica') {
            $rules['cnpj'] = 'required|string';
            $rules['razao_social'] = 'required|string|max:255';
        } else {
            $rules['cpf'] = 'required|string';
            $rules['nome_completo'] = 'required|string|max:255';
            $rules['rg'] = 'required|string|max:20';
            $rules['orgao_emissor'] = 'required|string|max:20';
            // Para pessoa física, atividades_exercidas é obrigatório
            $rules['atividades_exercidas'] = 'required|string';
        }

        $validated = $request->validate($rules, [
            'atividades_exercidas.required' => 'Você deve adicionar pelo menos uma Atividade Econômica (CNAE).',
        ]);
        
        // Valida se há pelo menos uma atividade para pessoa física
        if ($request->tipo_pessoa === 'fisica') {
            $atividades = json_decode($request->atividades_exercidas, true);
            if (empty($atividades) || !is_array($atividades) || count($atividades) === 0) {
                return back()->withErrors([
                    'atividades_exercidas' => 'Você deve adicionar pelo menos uma Atividade Econômica (CNAE).'
                ])->withInput();
            }
        }
        
        // Limpa formatação do CNPJ/CPF antes de verificar unicidade
        if ($request->tipo_pessoa === 'juridica') {
            $cnpjLimpo = preg_replace('/\D/', '', $validated['cnpj']);
            if ($request->tipo_setor === 'privado') {
                $existe = Estabelecimento::where('cnpj', $cnpjLimpo)->exists();
                if ($existe) {
                    return back()->withErrors(['cnpj' => 'Este CNPJ já está cadastrado no sistema.'])->withInput();
                }
            }
            $validated['cnpj'] = $cnpjLimpo;
        } else {
            $cpfLimpo = preg_replace('/\D/', '', $validated['cpf']);
            $existe = Estabelecimento::where('cpf', $cpfLimpo)->exists();
            if ($existe) {
                return back()->withErrors(['cpf' => 'Este CPF já está cadastrado no sistema.'])->withInput();
            }
            $validated['cpf'] = $cpfLimpo;
        }

        // Processa campos JSON
        if ($request->filled('cnaes_secundarios')) {
            $validated['cnaes_secundarios'] = json_decode($request->cnaes_secundarios, true);
        }
        if ($request->filled('qsa')) {
            $validated['qsa'] = json_decode($request->qsa, true);
        }
        if ($request->filled('atividades_exercidas')) {
            $validated['atividades_exercidas'] = json_decode($request->atividades_exercidas, true);
        }
        if ($request->filled('respostas_questionario')) {
            $validated['respostas_questionario'] = json_decode($request->respostas_questionario, true);
        }

        // Usuário externo - sempre pendente
        $validated['usuario_externo_id'] = auth('externo')->id();
        $validated['status'] = 'pendente';
        $validated['ativo'] = true;

        // Define o município
        $validated['municipio'] = $validated['cidade'];
        $nomeMunicipio = $validated['cidade'];
        $codigoIbge = $validated['codigo_municipio_ibge'] ?? null;
        
        if ($nomeMunicipio) {
            $nomeMunicipio = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', $nomeMunicipio);
            $municipioId = \App\Helpers\MunicipioHelper::normalizarEObterIdPorNome($nomeMunicipio, $codigoIbge);
            if ($municipioId) {
                $validated['municipio_id'] = $municipioId;
                $validated['municipio'] = $nomeMunicipio;
            }
        }

        // ========================================
        // VALIDAÇÃO: Município usa InfoVISA?
        // ========================================
        // Cria um estabelecimento temporário (não salvo) para verificar competência
        $estabelecimentoTemp = new Estabelecimento($validated);
        
        // Se for de competência MUNICIPAL, verifica se o município usa o InfoVISA
        if ($estabelecimentoTemp->isCompetenciaMunicipal()) {
            $municipio = null;
            if (isset($validated['municipio_id'])) {
                $municipio = \App\Models\Municipio::find($validated['municipio_id']);
            }
            
            if (!$municipio || !$municipio->usa_infovisa) {
                $nomeMunicipioMsg = $municipio ? $municipio->nome : ($validated['municipio'] ?? 'seu município');
                return back()->withErrors([
                    'cidade' => "O município de {$nomeMunicipioMsg} ainda não utiliza o InfoVISA. " .
                               "Estabelecimentos de competência municipal deste município não podem se cadastrar no momento. " .
                               "Entre em contato com a Vigilância Sanitária do seu município para mais informações."
                ])->withInput();
            }
        }
        // ========================================

        // Remove formatação (CEP e telefone - CNPJ/CPF já foram limpos acima)
        if (isset($validated['cep'])) {
            $validated['cep'] = preg_replace('/\D/', '', $validated['cep']);
        }
        if (isset($validated['telefone'])) {
            $validated['telefone'] = preg_replace('/\D/', '', $validated['telefone']);
        }

        try {
            $estabelecimento = Estabelecimento::create($validated);

            return redirect()->route('company.estabelecimentos.show', $estabelecimento->id)
                ->with('success', 'Estabelecimento cadastrado com sucesso! Aguarde a aprovação da Vigilância Sanitária.');
        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar estabelecimento: ' . $e->getMessage(), [
                'dados' => $validated,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Erro ao cadastrar estabelecimento: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Formulário de edição do estabelecimento
     */
    public function edit($id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->findOrFail($id);
        
        return view('company.estabelecimentos.edit', compact('estabelecimento'));
    }

    /**
     * Atualiza os dados do estabelecimento
     */
    public function update(Request $request, $id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->findOrFail($id);

        $rules = [
            'nome_fantasia' => 'required|string|max:255',
            'telefone' => 'required|string',
            'email' => 'required|email|max:255',
            'cep' => 'nullable|string',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
        ];

        // Campos específicos por tipo de pessoa
        if ($estabelecimento->tipo_pessoa === 'juridica') {
            $rules['razao_social'] = 'nullable|string|max:255';
        } else {
            $rules['nome_completo'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        // Remove formatação do telefone e CEP
        if (isset($validated['telefone'])) {
            $validated['telefone'] = preg_replace('/\D/', '', $validated['telefone']);
        }
        if (isset($validated['cep'])) {
            $validated['cep'] = preg_replace('/\D/', '', $validated['cep']);
        }

        $estabelecimento->update($validated);

        return redirect()->route('company.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Dados atualizados com sucesso!');
    }

    /**
     * Formulário de edição de atividades
     */
    public function editAtividades($id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->findOrFail($id);
        
        return view('company.estabelecimentos.atividades', compact('estabelecimento'));
    }

    /**
     * Atualiza as atividades exercidas
     */
    public function updateAtividades(Request $request, $id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->findOrFail($id);

        // Bloqueia edição se estabelecimento já foi aprovado
        if ($estabelecimento->status === 'aprovado') {
            return redirect()->route('company.estabelecimentos.atividades.edit', $estabelecimento->id)
                ->with('error', 'Não é possível alterar atividades de um estabelecimento já aprovado. Entre em contato com a Vigilância Sanitária.');
        }

        $atividades = $request->input('atividades_exercidas', []);
        
        if (is_string($atividades)) {
            $atividades = json_decode($atividades, true) ?? [];
        }

        $estabelecimento->update(['atividades_exercidas' => $atividades]);

        return redirect()->route('company.estabelecimentos.show', $estabelecimento->id)
            ->with('success', 'Atividades atualizadas com sucesso!');
    }

    /**
     * Lista de responsáveis do estabelecimento
     */
    public function responsaveisIndex($id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->with(['responsaveisLegais', 'responsaveisTecnicos'])
            ->findOrFail($id);
        
        return view('company.estabelecimentos.responsaveis.index', compact('estabelecimento'));
    }

    /**
     * Formulário para adicionar responsável
     */
    public function responsaveisCreate($id, $tipo = 'legal')
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->findOrFail($id);
        
        // Valida o tipo
        if (!in_array($tipo, ['legal', 'tecnico'])) {
            $tipo = 'legal';
        }
        
        return view('company.estabelecimentos.responsaveis.create', compact('estabelecimento', 'tipo'));
    }

    /**
     * Salva novo responsável
     */
    public function responsaveisStore(Request $request, $id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->findOrFail($id);

        $rules = [
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string',
            'tipo_vinculo' => 'required|in:legal,tecnico',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
        ];

        // Campos específicos para responsável técnico
        if ($request->tipo_vinculo === 'tecnico') {
            $rules['conselho'] = 'required|string|max:100';
            $rules['numero_registro'] = 'required|string|max:50';
            $rules['carteirinha_conselho'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        } else {
            $rules['conselho'] = 'nullable|string|max:100';
            $rules['numero_registro'] = 'nullable|string|max:50';
            $rules['documento_identificacao'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        $validated = $request->validate($rules);

        // Limpa formatação
        $validated['cpf'] = preg_replace('/\D/', '', $validated['cpf']);
        if (isset($validated['telefone'])) {
            $validated['telefone'] = preg_replace('/\D/', '', $validated['telefone']);
        }

        // Upload de arquivos
        $carteirinhaPath = null;
        $documentoPath = null;
        
        if ($request->hasFile('carteirinha_conselho')) {
            $carteirinhaPath = $request->file('carteirinha_conselho')->store('responsaveis/carteirinhas', 'public');
        }
        
        if ($request->hasFile('documento_identificacao')) {
            $documentoPath = $request->file('documento_identificacao')->store('responsaveis/documentos', 'public');
        }

        // Busca ou cria o responsável
        $responsavel = \App\Models\Responsavel::where('cpf', $validated['cpf'])->first();
        
        if ($responsavel) {
            // Atualiza dados se o responsável já existia
            $updateData = [
                'nome' => $validated['nome'],
                'tipo' => $validated['tipo_vinculo'],
                'email' => $validated['email'] ?? null,
                'telefone' => $validated['telefone'] ?? null,
            ];
            
            if (isset($validated['conselho'])) {
                $updateData['conselho'] = $validated['conselho'];
            }
            if (isset($validated['numero_registro'])) {
                $updateData['numero_registro_conselho'] = $validated['numero_registro'];
            }
            if ($carteirinhaPath) {
                $updateData['carteirinha_conselho'] = $carteirinhaPath;
            }
            if ($documentoPath) {
                $updateData['documento_identificacao'] = $documentoPath;
            }
            
            $responsavel->update($updateData);
        } else {
            // Cria novo responsável
            $responsavel = \App\Models\Responsavel::create([
                'cpf' => $validated['cpf'],
                'tipo' => $validated['tipo_vinculo'],
                'nome' => $validated['nome'],
                'email' => $validated['email'] ?? null,
                'telefone' => $validated['telefone'] ?? null,
                'conselho' => $validated['conselho'] ?? null,
                'numero_registro_conselho' => $validated['numero_registro'] ?? null,
                'carteirinha_conselho' => $carteirinhaPath,
                'documento_identificacao' => $documentoPath,
            ]);
        }

        // Verifica se já existe vínculo com este tipo
        $vinculoExistente = $estabelecimento->responsaveis()
            ->where('responsavel_id', $responsavel->id)
            ->wherePivot('tipo_vinculo', $validated['tipo_vinculo'])
            ->exists();

        if ($vinculoExistente) {
            return redirect()->route('company.estabelecimentos.responsaveis.index', $estabelecimento->id)
                ->with('warning', 'Este responsável já está vinculado como ' . ($validated['tipo_vinculo'] === 'legal' ? 'Responsável Legal' : 'Responsável Técnico') . '.');
        }

        // Usa attach para permitir múltiplos vínculos (legal e técnico) para a mesma pessoa
        $estabelecimento->responsaveis()->attach($responsavel->id, [
            'tipo_vinculo' => $validated['tipo_vinculo'],
            'ativo' => true
        ]);

        return redirect()->route('company.estabelecimentos.responsaveis.index', $estabelecimento->id)
            ->with('success', 'Responsável adicionado com sucesso!');
    }

    /**
     * Remove responsável
     */
    public function responsaveisDestroy($id, $responsavelId)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->findOrFail($id);

        $estabelecimento->responsaveis()->detach($responsavelId);

        return redirect()->route('company.estabelecimentos.responsaveis.index', $estabelecimento->id)
            ->with('success', 'Responsável removido com sucesso!');
    }

    /**
     * Lista de usuários vinculados ao estabelecimento
     */
    public function usuariosIndex($id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->with(['usuariosVinculados', 'usuarioExterno'])
            ->findOrFail($id);
        
        // Inclui o usuário criador na lista (se não estiver já vinculado)
        $criador = $estabelecimento->usuarioExterno;
        $criadorVinculado = $estabelecimento->usuariosVinculados->contains('id', $criador?->id);
        
        return view('company.estabelecimentos.usuarios.index', compact('estabelecimento', 'criador', 'criadorVinculado'));
    }

    /**
     * Vincula um usuário ao estabelecimento
     */
    public function usuariosStore(Request $request, $id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email',
            'tipo_vinculo' => 'required|string|max:50',
            'observacao' => 'nullable|string|max:255',
        ]);

        $usuario = \App\Models\UsuarioExterno::where('email', $validated['email'])->first();

        if (!$usuario) {
            return back()->withErrors(['email' => 'Usuário não encontrado com este e-mail.'])->withInput();
        }

        if ($usuario->id === auth('externo')->id()) {
            return back()->withErrors(['email' => 'Você não pode vincular a si mesmo.'])->withInput();
        }

        $estabelecimento->usuariosVinculados()->syncWithoutDetaching([
            $usuario->id => [
                'tipo_vinculo' => $validated['tipo_vinculo'],
                'observacao' => $validated['observacao'] ?? null,
                'vinculado_por' => auth('externo')->id(),
            ]
        ]);

        return redirect()->route('company.estabelecimentos.usuarios.index', $estabelecimento->id)
            ->with('success', 'Usuário vinculado com sucesso!');
    }

    /**
     * Remove vínculo de usuário
     */
    public function usuariosDestroy($id, $usuarioId)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->findOrFail($id);

        // Bloqueia exclusão do usuário criador do estabelecimento
        if ($estabelecimento->usuario_externo_id == $usuarioId) {
            return redirect()->route('company.estabelecimentos.usuarios.index', $estabelecimento->id)
                ->with('error', 'Não é possível desvincular o usuário que cadastrou o estabelecimento. Apenas um administrador pode realizar esta ação.');
        }

        $estabelecimento->usuariosVinculados()->detach($usuarioId);

        return redirect()->route('company.estabelecimentos.usuarios.index', $estabelecimento->id)
            ->with('success', 'Vínculo removido com sucesso!');
    }

    /**
     * Lista de processos do estabelecimento
     */
    public function processosIndex($id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->with(['processos.tipoProcesso'])
            ->findOrFail($id);
        
        return view('company.estabelecimentos.processos.index', compact('estabelecimento'));
    }

    /**
     * Formulário para abrir novo processo
     */
    public function processosCreate($id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->with(['responsaveisLegais'])
            ->findOrFail($id);

        // Verifica se tem responsável legal cadastrado
        if ($estabelecimento->responsaveisLegais->isEmpty()) {
            return redirect()->route('company.estabelecimentos.responsaveis.index', $estabelecimento->id)
                ->with('error', 'Para abrir um processo, é obrigatório ter pelo menos um Responsável Legal cadastrado. Por favor, cadastre o responsável legal primeiro.');
        }

        // Busca tipos de processo disponíveis para usuários externos
        $tiposProcessoBase = \App\Models\TipoProcesso::where('ativo', true)
            ->where('usuario_externo_pode_abrir', true)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        // Filtra tipos de processo baseado nas regras de anual/único
        $anoAtual = date('Y');
        $tiposProcesso = $tiposProcessoBase->filter(function($tipo) use ($estabelecimento, $anoAtual) {
            // Se é único por estabelecimento, verifica se já existe algum processo deste tipo
            if ($tipo->unico_por_estabelecimento) {
                $existeProcesso = \App\Models\Processo::where('estabelecimento_id', $estabelecimento->id)
                    ->where('tipo', $tipo->codigo)
                    ->exists();
                
                if ($existeProcesso) {
                    return false; // Não pode abrir, já existe
                }
            }
            // Se é anual, verifica se já existe processo deste tipo no ano atual
            elseif ($tipo->anual) {
                $existeNoAno = \App\Models\Processo::where('estabelecimento_id', $estabelecimento->id)
                    ->where('tipo', $tipo->codigo)
                    ->where('ano', $anoAtual)
                    ->exists();
                
                if ($existeNoAno) {
                    return false; // Não pode abrir, já existe neste ano
                }
            }
            
            // Pode abrir
            return true;
        });

        // Busca documentos obrigatórios baseados nas atividades exercidas
        $documentosObrigatorios = $this->buscarDocumentosObrigatorios($estabelecimento, $tiposProcesso);
        
        // Busca tipos bloqueados para mostrar mensagem informativa
        $tiposBloqueados = $tiposProcessoBase->filter(function($tipo) use ($estabelecimento, $anoAtual) {
            if ($tipo->unico_por_estabelecimento) {
                return \App\Models\Processo::where('estabelecimento_id', $estabelecimento->id)
                    ->where('tipo', $tipo->codigo)
                    ->exists();
            }
            if ($tipo->anual) {
                return \App\Models\Processo::where('estabelecimento_id', $estabelecimento->id)
                    ->where('tipo', $tipo->codigo)
                    ->where('ano', $anoAtual)
                    ->exists();
            }
            return false;
        });
        
        return view('company.estabelecimentos.processos.create', compact('estabelecimento', 'tiposProcesso', 'documentosObrigatorios', 'tiposBloqueados'));
    }

    /**
     * Busca documentos obrigatórios para o estabelecimento baseado nas atividades exercidas
     */
    private function buscarDocumentosObrigatorios($estabelecimento, $tiposProcesso)
    {
        $documentosPorTipoProcesso = [];
        
        // Pega as atividades exercidas do estabelecimento (apenas as marcadas)
        $atividadesExercidas = $estabelecimento->atividades_exercidas ?? [];
        
        if (empty($atividadesExercidas)) {
            return $documentosPorTipoProcesso;
        }

        // Extrai os códigos CNAE das atividades exercidas
        $codigosCnae = collect($atividadesExercidas)->map(function($atividade) {
            $codigo = is_array($atividade) ? ($atividade['codigo'] ?? null) : $atividade;
            return $codigo ? preg_replace('/[^0-9]/', '', $codigo) : null;
        })->filter()->values()->toArray();

        if (empty($codigosCnae)) {
            return $documentosPorTipoProcesso;
        }

        // Busca as atividades cadastradas que correspondem aos CNAEs exercidos
        $atividadeIds = \App\Models\Atividade::where('ativo', true)
            ->where(function($query) use ($codigosCnae) {
                foreach ($codigosCnae as $codigo) {
                    $query->orWhere('codigo_cnae', $codigo);
                }
            })
            ->pluck('id');

        if ($atividadeIds->isEmpty()) {
            return $documentosPorTipoProcesso;
        }

        // Para cada tipo de processo, busca as listas de documentos aplicáveis
        foreach ($tiposProcesso as $tipoProcesso) {
            $query = \App\Models\ListaDocumento::where('ativo', true)
                ->where('tipo_processo_id', $tipoProcesso->id)
                ->whereHas('atividades', function($q) use ($atividadeIds) {
                    $q->whereIn('atividades.id', $atividadeIds);
                })
                ->with(['tiposDocumentoObrigatorio' => function($q) {
                    $q->orderBy('lista_documento_tipo.ordem');
                }]);

            // Filtra por escopo (estadual ou do município do estabelecimento)
            $query->where(function($q) use ($estabelecimento) {
                $q->where('escopo', 'estadual');
                if ($estabelecimento->municipio_id) {
                    $q->orWhere(function($q2) use ($estabelecimento) {
                        $q2->where('escopo', 'municipal')
                           ->where('municipio_id', $estabelecimento->municipio_id);
                    });
                }
            });

            $listas = $query->get();

            // Consolida os documentos de todas as listas aplicáveis
            $documentos = collect();
            foreach ($listas as $lista) {
                foreach ($lista->tiposDocumentoObrigatorio as $doc) {
                    // Evita duplicatas pelo ID do tipo de documento
                    if (!$documentos->contains('id', $doc->id)) {
                        $documentos->push([
                            'id' => $doc->id,
                            'nome' => $doc->nome,
                            'descricao' => $doc->descricao,
                            'obrigatorio' => $doc->pivot->obrigatorio,
                            'observacao' => $doc->pivot->observacao,
                            'lista_nome' => $lista->nome,
                        ]);
                    } else {
                        // Se já existe, verifica se deve ser obrigatório (se qualquer lista marcar como obrigatório)
                        $documentos = $documentos->map(function($item) use ($doc) {
                            if ($item['id'] === $doc->id && $doc->pivot->obrigatorio) {
                                $item['obrigatorio'] = true;
                            }
                            return $item;
                        });
                    }
                }
            }

            // Ordena: obrigatórios primeiro, depois por nome
            $documentos = $documentos->sortBy([
                ['obrigatorio', 'desc'],
                ['nome', 'asc'],
            ])->values();

            if ($documentos->isNotEmpty()) {
                $documentosPorTipoProcesso[$tipoProcesso->id] = $documentos;
            }
        }

        return $documentosPorTipoProcesso;
    }

    /**
     * Cria novo processo
     */
    public function processosStore(Request $request, $id)
    {
        $estabelecimento = $this->estabelecimentosDoUsuario()
            ->where('status', 'aprovado')
            ->findOrFail($id);

        $validated = $request->validate([
            'tipo_processo_id' => 'required|exists:tipo_processos,id',
            'observacao' => 'nullable|string|max:1000',
            'documentos' => 'nullable|array',
            'documentos.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ], [
            'documentos.*.max' => 'Cada arquivo não pode ter mais de 10MB.',
            'documentos.*.mimes' => 'Apenas arquivos PDF, JPG e PNG são permitidos.',
        ]);

        $tipoProcesso = \App\Models\TipoProcesso::where('id', $validated['tipo_processo_id'])
            ->where('ativo', true)
            ->where('usuario_externo_pode_abrir', true)
            ->firstOrFail();

        // Validação de processo único por estabelecimento
        if ($tipoProcesso->unico_por_estabelecimento) {
            $existeProcesso = \App\Models\Processo::where('estabelecimento_id', $estabelecimento->id)
                ->where('tipo', $tipoProcesso->codigo)
                ->exists();
            
            if ($existeProcesso) {
                return back()->withErrors(['tipo_processo_id' => 'Este estabelecimento já possui um processo de ' . $tipoProcesso->nome . '. Este tipo de processo só pode ser aberto uma vez.']);
            }
        }
        // Validação de processo anual
        elseif ($tipoProcesso->anual) {
            $anoAtual = date('Y');
            $existeNoAno = \App\Models\Processo::where('estabelecimento_id', $estabelecimento->id)
                ->where('tipo', $tipoProcesso->codigo)
                ->where('ano', $anoAtual)
                ->exists();
            
            if ($existeNoAno) {
                return back()->withErrors(['tipo_processo_id' => 'Este estabelecimento já possui um processo de ' . $tipoProcesso->nome . ' aberto em ' . $anoAtual . '. Processos anuais só podem ser abertos uma vez por ano.']);
            }
        }

        try {
            $processo = \DB::transaction(function () use ($estabelecimento, $tipoProcesso, $validated, $request) {
                // Gera número do processo usando o método do model (dentro da transaction)
                $ano = date('Y');
                $dadosNumero = \App\Models\Processo::gerarNumeroProcesso($ano);

                $processo = \App\Models\Processo::create([
                    'estabelecimento_id' => $estabelecimento->id,
                    'usuario_externo_id' => auth('externo')->id(),
                    'aberto_por_externo' => true,
                    'tipo' => $tipoProcesso->codigo,
                    'ano' => $dadosNumero['ano'],
                    'numero_sequencial' => $dadosNumero['numero_sequencial'],
                    'numero_processo' => $dadosNumero['numero_processo'],
                    'status' => 'aberto',
                    'observacoes' => $validated['observacao'] ?? null,
                ]);

                // Salva os documentos enviados
                if ($request->hasFile('documentos')) {
                    foreach ($request->file('documentos') as $tipoDocumentoId => $arquivo) {
                        if ($arquivo && $arquivo->isValid()) {
                            $tipoDocumento = \App\Models\TipoDocumentoObrigatorio::find($tipoDocumentoId);
                            
                            $nomeOriginal = $arquivo->getClientOriginalName();
                            $extensao = $arquivo->getClientOriginalExtension();
                            $tamanho = $arquivo->getSize();
                            $nomeArquivo = time() . '_' . uniqid() . '.' . $extensao;
                            
                            // Salva o arquivo
                            $caminho = $arquivo->storeAs(
                                'processos/' . $processo->id . '/documentos',
                                $nomeArquivo,
                                'public'
                            );

                            // Cria o registro do documento
                            \App\Models\ProcessoDocumento::create([
                                'processo_id' => $processo->id,
                                'usuario_externo_id' => auth('externo')->id(),
                                'tipo_usuario' => 'externo',
                                'nome_arquivo' => $nomeArquivo,
                                'nome_original' => $nomeOriginal,
                                'caminho' => $caminho,
                                'extensao' => strtolower($extensao),
                                'tamanho' => $tamanho,
                                'tipo_documento' => 'documento_obrigatorio',
                                'tipo_documento_obrigatorio_id' => $tipoDocumentoId,
                                'observacoes' => $tipoDocumento ? $tipoDocumento->nome : null,
                                'status_aprovacao' => 'pendente',
                            ]);
                        }
                    }
                }

                return $processo;
            });

            return redirect()->route('company.processos.show', $processo->id)
                ->with('success', 'Processo aberto com sucesso!');
        } catch (\Exception $e) {
            \Log::error('Erro ao criar processo (company)', [
                'erro' => $e->getMessage(),
                'estabelecimento_id' => $estabelecimento->id,
            ]);
            
            return back()->withErrors(['erro' => 'Erro ao criar processo. Tente novamente.'])->withInput();
        }
    }

    /**
     * Busca usuários externos para vincular ao estabelecimento
     */
    public function buscarUsuariosExternos(Request $request)
    {
        $query = $request->input('q', '');
        $estabelecimentoId = $request->input('estabelecimento_id');
        
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        // Busca usuários externos que não sejam o próprio usuário logado
        $usuarios = \App\Models\UsuarioExterno::where('id', '!=', auth('externo')->id())
            ->where(function($q) use ($query) {
                $q->where('nome', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('cpf', 'like', "%{$query}%");
            });

        // Exclui usuários já vinculados ao estabelecimento
        if ($estabelecimentoId) {
            $estabelecimento = Estabelecimento::find($estabelecimentoId);
            if ($estabelecimento) {
                $usuariosVinculados = $estabelecimento->usuariosVinculados->pluck('id')->toArray();
                $usuarios->whereNotIn('id', $usuariosVinculados);
            }
        }

        $usuarios = $usuarios->limit(10)->get(['id', 'nome', 'email', 'cpf']);

        return response()->json($usuarios->map(function($usuario) {
            return [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'cpf' => $usuario->cpf_formatado ?? $usuario->cpf,
            ];
        }));
    }

    /**
     * Busca responsável por CPF para preenchimento automático
     */
    public function buscarResponsavelPorCpf(Request $request)
    {
        $cpf = preg_replace('/\D/', '', $request->input('cpf', ''));
        
        if (strlen($cpf) !== 11) {
            return response()->json(['encontrado' => false]);
        }

        $responsavel = \App\Models\Responsavel::where('cpf', $cpf)->first();

        if (!$responsavel) {
            return response()->json(['encontrado' => false]);
        }

        return response()->json([
            'encontrado' => true,
            'dados' => [
                'nome' => $responsavel->nome,
                'email' => $responsavel->email,
                'telefone' => $responsavel->telefone,
                'conselho' => $responsavel->conselho,
                'numero_registro' => $responsavel->numero_registro_conselho,
                // Indica se já tem documento (não envia o documento em si por segurança)
                'tem_documento_identificacao' => !empty($responsavel->documento_identificacao),
                'tem_carteirinha_conselho' => !empty($responsavel->carteirinha_conselho),
            ]
        ]);
    }
}
