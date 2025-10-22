<?php

namespace App\Http\Controllers;

use App\Models\Estabelecimento;
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

        // Se usuário interno estiver logado, mostrar estabelecimentos do usuário selecionado ou todos
        if (auth('interno')->check()) {
            // Por enquanto mostra todos, depois podemos filtrar por município/estado
            // $query->where('cidade', auth('interno')->user()->municipio ?? 'todos');
        }

        // Filtro de busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome_fantasia', 'like', "%{$search}%")
                  ->orWhere('razao_social', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('cidade', 'like', "%{$search}%");
            });
        }

        $estabelecimentos = $query->with('usuarioExterno')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('estabelecimentos.index', compact('estabelecimentos'));
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

        // Define o usuário responsável
        if (auth('interno')->check()) {
            // Para usuários internos (admin), o estabelecimento não precisa estar vinculado a um usuário externo
            $validated['usuario_externo_id'] = null;
        } else {
            // Para usuários externos, vincula ao usuário logado
            $validated['usuario_externo_id'] = auth('externo')->id();
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

        $estabelecimento = Estabelecimento::create($validated);

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
        
        return view('estabelecimentos.show', compact('estabelecimento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        
        return view('estabelecimentos.edit', compact('estabelecimento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        
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
     * Show the form for editing activities.
     */
    public function editAtividades(string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        
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
        
        return view('estabelecimentos.atividades', compact('estabelecimento', 'atividadesApi'));
    }

    /**
     * Update the activities.
     */
    public function updateAtividades(Request $request, string $id)
    {
        $estabelecimento = Estabelecimento::findOrFail($id);
        
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
}
