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
        $usuarioId = auth('externo')->id();
        
        $query = Estabelecimento::where('usuario_externo_id', $usuarioId);
        
        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome_fantasia', 'like', "%{$search}%")
                  ->orWhere('razao_social', 'like', "%{$search}%")
                  ->orWhere('nome_completo', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }
        
        $estabelecimentos = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Estatísticas
        $estatisticas = [
            'total' => Estabelecimento::where('usuario_externo_id', $usuarioId)->count(),
            'pendentes' => Estabelecimento::where('usuario_externo_id', $usuarioId)->where('status', 'pendente')->count(),
            'aprovados' => Estabelecimento::where('usuario_externo_id', $usuarioId)->where('status', 'aprovado')->count(),
            'rejeitados' => Estabelecimento::where('usuario_externo_id', $usuarioId)->where('status', 'rejeitado')->count(),
        ];
        
        return view('company.estabelecimentos.index', compact('estabelecimentos', 'estatisticas'));
    }
    
    public function show($id)
    {
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
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
        }

        $validated = $request->validate($rules);
        
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
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
            ->where('status', 'aprovado')
            ->findOrFail($id);
        
        return view('company.estabelecimentos.edit', compact('estabelecimento'));
    }

    /**
     * Atualiza os dados do estabelecimento
     */
    public function update(Request $request, $id)
    {
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
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
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
            ->findOrFail($id);
        
        return view('company.estabelecimentos.atividades', compact('estabelecimento'));
    }

    /**
     * Atualiza as atividades exercidas
     */
    public function updateAtividades(Request $request, $id)
    {
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
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
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
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
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
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
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
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

        $responsavel = \App\Models\Responsavel::firstOrCreate(
            ['cpf' => $validated['cpf']],
            [
                'tipo' => $validated['tipo_vinculo'],
                'nome' => $validated['nome'],
                'email' => $validated['email'] ?? null,
                'telefone' => $validated['telefone'] ?? null,
                'conselho' => $validated['conselho'] ?? null,
                'numero_registro_conselho' => $validated['numero_registro'] ?? null,
                'carteirinha_conselho' => $carteirinhaPath,
                'documento_identificacao' => $documentoPath,
            ]
        );

        // Atualiza dados se o responsável já existia
        if (!$responsavel->wasRecentlyCreated) {
            $updateData = ['nome' => $validated['nome'], 'tipo' => $validated['tipo_vinculo']];
            if (isset($validated['email'])) $updateData['email'] = $validated['email'];
            if (isset($validated['telefone'])) $updateData['telefone'] = $validated['telefone'];
            if (isset($validated['conselho'])) $updateData['conselho'] = $validated['conselho'];
            if (isset($validated['numero_registro'])) $updateData['numero_registro_conselho'] = $validated['numero_registro'];
            if ($carteirinhaPath) $updateData['carteirinha_conselho'] = $carteirinhaPath;
            if ($documentoPath) $updateData['documento_identificacao'] = $documentoPath;
            $responsavel->update($updateData);
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
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
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
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
            ->where('status', 'aprovado')
            ->with('usuariosVinculados')
            ->findOrFail($id);
        
        return view('company.estabelecimentos.usuarios.index', compact('estabelecimento'));
    }

    /**
     * Vincula um usuário ao estabelecimento
     */
    public function usuariosStore(Request $request, $id)
    {
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
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
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
            ->where('status', 'aprovado')
            ->findOrFail($id);

        $estabelecimento->usuariosVinculados()->detach($usuarioId);

        return redirect()->route('company.estabelecimentos.usuarios.index', $estabelecimento->id)
            ->with('success', 'Vínculo removido com sucesso!');
    }

    /**
     * Lista de processos do estabelecimento
     */
    public function processosIndex($id)
    {
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
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
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
            ->where('status', 'aprovado')
            ->findOrFail($id);

        // Busca tipos de processo disponíveis para usuários externos
        $tiposProcesso = \App\Models\TipoProcesso::where('ativo', true)
            ->where('usuario_externo_pode_abrir', true)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();
        
        return view('company.estabelecimentos.processos.create', compact('estabelecimento', 'tiposProcesso'));
    }

    /**
     * Cria novo processo
     */
    public function processosStore(Request $request, $id)
    {
        $estabelecimento = Estabelecimento::where('usuario_externo_id', auth('externo')->id())
            ->where('status', 'aprovado')
            ->findOrFail($id);

        $validated = $request->validate([
            'tipo_processo_id' => 'required|exists:tipo_processos,id',
            'observacao' => 'nullable|string|max:1000',
        ]);

        $tipoProcesso = \App\Models\TipoProcesso::where('id', $validated['tipo_processo_id'])
            ->where('ativo', true)
            ->where('usuario_externo_pode_abrir', true)
            ->firstOrFail();

        // Gera número do processo usando o método do model
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

        return redirect()->route('company.processos.show', $processo->id)
            ->with('success', 'Processo aberto com sucesso!');
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
