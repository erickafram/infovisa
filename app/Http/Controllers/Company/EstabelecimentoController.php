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
                $q->where('nome_fantasia', 'ilike', "%{$search}%")
                  ->orWhere('razao_social', 'ilike', "%{$search}%")
                  ->orWhere('nome_completo', 'ilike', "%{$search}%")
                  ->orWhere('cnpj', 'ilike', "%{$search}%")
                  ->orWhere('cpf', 'ilike', "%{$search}%");
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
}
