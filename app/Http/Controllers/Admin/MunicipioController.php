<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MunicipioController extends Controller
{
    /**
     * Lista todos os municípios
     */
    public function index(Request $request)
    {
        $query = Municipio::query();

        // Filtro de busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo_ibge', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filtro de status
        if ($request->filled('ativo')) {
            $query->where('ativo', $request->ativo === '1');
        }

        // Filtro de UF
        if ($request->filled('uf')) {
            $query->where('uf', $request->uf);
        }

        $municipios = $query->withCount('usuariosInternos')->orderBy('nome')->paginate(20);

        // Estatísticas
        $stats = [
            'total' => Municipio::count(),
            'ativos' => Municipio::where('ativo', true)->count(),
            'inativos' => Municipio::where('ativo', false)->count(),
            'com_estabelecimentos' => Municipio::has('estabelecimentos')->count(),
            'com_pactuacoes' => Municipio::has('pactuacoes')->count(),
            'com_usuarios' => Municipio::has('usuariosInternos')->count(),
        ];

        return view('admin.municipios.index', compact('municipios', 'stats'));
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        return view('admin.municipios.create');
    }

    /**
     * Salva novo município
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:100',
            'codigo_ibge' => 'required|string|size:7|unique:municipios,codigo_ibge',
            'uf' => 'required|string|size:2',
            'logomarca' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            // ativo não precisa de validação pois usamos $request->has('ativo')
        ], [
            'nome.required' => 'O nome do município é obrigatório',
            'codigo_ibge.required' => 'O código IBGE é obrigatório',
            'codigo_ibge.size' => 'O código IBGE deve ter 7 dígitos',
            'codigo_ibge.unique' => 'Este código IBGE já está cadastrado',
            'uf.required' => 'A UF é obrigatória',
            'uf.size' => 'A UF deve ter 2 caracteres',
            'logomarca.image' => 'O arquivo deve ser uma imagem',
            'logomarca.mimes' => 'A logomarca deve ser um arquivo: jpeg, png, jpg ou svg',
            'logomarca.max' => 'A logomarca não pode ser maior que 2MB',
        ]);

        $dados = [
            'nome' => mb_strtoupper(trim($request->nome)),
            'codigo_ibge' => $request->codigo_ibge,
            'uf' => mb_strtoupper($request->uf),
            'slug' => Str::slug($request->nome),
            'ativo' => $request->has('ativo'),
        ];

        // Upload da logomarca
        if ($request->hasFile('logomarca')) {
            // Garante que o diretório existe
            if (!\Storage::disk('public')->exists('municipios/logomarcas')) {
                \Storage::disk('public')->makeDirectory('municipios/logomarcas');
            }
            
            $arquivo = $request->file('logomarca');
            $nomeArquivo = 'logomarca_' . Str::slug($request->nome) . '_' . time() . '.' . $arquivo->getClientOriginalExtension();
            $caminho = $arquivo->storeAs('municipios/logomarcas', $nomeArquivo, 'public');
            $dados['logomarca'] = 'storage/' . $caminho;
        }

        $municipio = Municipio::create($dados);

        return redirect()
            ->route('admin.configuracoes.municipios.index')
            ->with('success', 'Município cadastrado com sucesso!');
    }

    /**
     * Exibe detalhes do município
     */
    public function show($id)
    {
        $municipio = Municipio::with(['estabelecimentos', 'pactuacoes'])->findOrFail($id);

        // Busca pactuações municipais (Tabela I - atividades de competência de TODOS os municípios)
        // Essas pactuações não têm municipio_id porque são para todos os 139 municípios
        $pactuacoesMunicipais = \App\Models\Pactuacao::where('tipo', 'municipal')
            ->where('tabela', 'I')
            ->where('ativo', true)
            ->orderBy('cnae_codigo')
            ->get();
        
        // Busca descentralizações (Tabela III - atividades estaduais delegadas ao município)
        $descentralizacoes = $municipio->descentralizacoes();

        // Estatísticas do município
        $stats = [
            'estabelecimentos_total' => $municipio->estabelecimentos()->count(),
            'estabelecimentos_ativos' => $municipio->estabelecimentos()->where('ativo', true)->count(),
            'estabelecimentos_pendentes' => $municipio->estabelecimentos()->where('status', 'pendente')->count(),
            'pactuacoes_municipais' => $pactuacoesMunicipais->count(),
            'pactuacoes_excecoes' => $descentralizacoes->count(),
        ];

        return view('admin.municipios.show', compact('municipio', 'stats', 'pactuacoesMunicipais', 'descentralizacoes'));
    }

    /**
     * Exibe formulário de edição
     */
    public function edit($id)
    {
        $municipio = Municipio::findOrFail($id);
        return view('admin.municipios.edit', compact('municipio'));
    }

    /**
     * Atualiza município
     */
    public function update(Request $request, $id)
    {
        $municipio = Municipio::findOrFail($id);

        // Debug: Log todos os dados recebidos
        \Log::info('=== ATUALIZAÇÃO DE MUNICÍPIO ===', [
            'municipio_id' => $id,
            'has_file_logomarca' => $request->hasFile('logomarca'),
            'all_files' => array_keys($request->allFiles()),
            'all_input' => array_keys($request->all()),
            'content_type' => $request->header('Content-Type'),
        ]);

        // Validação com log de erros
        $validator = \Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'codigo_ibge' => 'required|string|size:7|unique:municipios,codigo_ibge,' . $id,
            'uf' => 'required|string|size:2',
            'logomarca' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'remover_logomarca' => 'nullable|in:1,on',
            // ativo não precisa de validação pois usamos $request->has('ativo')
        ], [
            'nome.required' => 'O nome do município é obrigatório',
            'codigo_ibge.required' => 'O código IBGE é obrigatório',
            'codigo_ibge.size' => 'O código IBGE deve ter 7 dígitos',
            'codigo_ibge.unique' => 'Este código IBGE já está cadastrado',
            'uf.required' => 'A UF é obrigatória',
            'uf.size' => 'A UF deve ter 2 caracteres',
            'logomarca.image' => 'O arquivo deve ser uma imagem',
            'logomarca.mimes' => 'A logomarca deve ser um arquivo: jpeg, png, jpg ou svg',
            'logomarca.max' => 'A logomarca não pode ser maior que 2MB',
        ]);

        if ($validator->fails()) {
            \Log::error('❌ Validação falhou', [
                'errors' => $validator->errors()->toArray(),
            ]);
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        \Log::info('✅ Validação passou!');

        $dados = [
            'nome' => mb_strtoupper(trim($request->nome)),
            'codigo_ibge' => $request->codigo_ibge,
            'uf' => mb_strtoupper($request->uf),
            'slug' => Str::slug($request->nome),
            'ativo' => $request->has('ativo'),
            'usa_infovisa' => $request->has('usa_infovisa'),
            'data_adesao_infovisa' => $request->has('usa_infovisa') ? $request->data_adesao_infovisa : null,
        ];

        // Upload da nova logomarca (tem prioridade sobre remoção)
        if ($request->hasFile('logomarca')) {
            $arquivo = $request->file('logomarca');
            
            \Log::info('Upload de logomarca iniciado', [
                'municipio_id' => $id,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'arquivo_valido' => $arquivo->isValid(),
                'arquivo_erro' => $arquivo->getError(),
            ]);
            
            // Verifica se o arquivo é válido
            if (!$arquivo->isValid()) {
                \Log::error('Arquivo inválido', [
                    'erro_codigo' => $arquivo->getError(),
                    'erro_mensagem' => $arquivo->getErrorMessage(),
                ]);
                return redirect()
                    ->back()
                    ->with('error', 'Erro no upload do arquivo: ' . $arquivo->getErrorMessage());
            }
            
            // Garante que o diretório existe
            if (!\Storage::disk('public')->exists('municipios/logomarcas')) {
                \Storage::disk('public')->makeDirectory('municipios/logomarcas');
                \Log::info('Diretório criado: municipios/logomarcas no disco public');
            }
            
            // Remove logomarca antiga se existir
            if ($municipio->logomarca) {
                $caminhoAntigo = str_replace('storage/', '', $municipio->logomarca);
                \Storage::disk('public')->delete($caminhoAntigo);
                \Log::info('Logomarca antiga removida', ['caminho' => $caminhoAntigo]);
            }
            
            $nomeArquivo = 'logomarca_' . Str::slug($request->nome) . '_' . time() . '.' . $arquivo->getClientOriginalExtension();
            
            try {
                // Salva no disco 'public' explicitamente
                $caminho = $arquivo->storeAs('municipios/logomarcas', $nomeArquivo, 'public');
                $dados['logomarca'] = 'storage/' . $caminho;
                
                \Log::info('Logomarca salva com sucesso!', [
                    'caminho_storage' => $caminho,
                    'caminho_public' => $dados['logomarca'],
                    'nome_arquivo' => $nomeArquivo,
                    'caminho_completo' => \Storage::disk('public')->path($caminho),
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao salvar logomarca', [
                    'erro' => $e->getMessage(),
                    'arquivo' => $nomeArquivo,
                ]);
                return redirect()
                    ->back()
                    ->with('error', 'Erro ao salvar logomarca: ' . $e->getMessage());
            }
        } elseif ($request->has('remover_logomarca') && $municipio->logomarca) {
            // Remove logomarca apenas se não houver upload de nova imagem
            $caminhoRemover = str_replace('storage/', '', $municipio->logomarca);
            \Storage::disk('public')->delete($caminhoRemover);
            $dados['logomarca'] = null;
            \Log::info('Logomarca removida', ['caminho' => $caminhoRemover]);
        } else {
            \Log::warning('Nenhum arquivo de logomarca recebido', [
                'municipio_id' => $id,
                'has_file' => $request->hasFile('logomarca'),
                'all_files' => $request->allFiles(),
            ]);
        }

        $municipio->update($dados);
        
        $mensagem = 'Município atualizado com sucesso!';
        if (isset($dados['logomarca'])) {
            $mensagem .= ' Logomarca salva em: ' . $dados['logomarca'];
        }

        return redirect()
            ->route('admin.configuracoes.municipios.index')
            ->with('success', $mensagem);
    }

    /**
     * Ativa/Desativa município
     */
    public function toggleStatus($id)
    {
        try {
            $municipio = Municipio::findOrFail($id);
            $municipio->ativo = !$municipio->ativo;
            $municipio->save();

            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso!',
                'ativo' => $municipio->ativo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove município
     */
    public function destroy($id)
    {
        try {
            $municipio = Municipio::findOrFail($id);

            // Verifica se há estabelecimentos vinculados
            if ($municipio->estabelecimentos()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível excluir este município pois existem estabelecimentos vinculados.');
            }

            // Verifica se há pactuações vinculadas
            if ($municipio->pactuacoes()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível excluir este município pois existem pactuações vinculadas.');
            }

            $municipio->delete();

            return redirect()
                ->route('admin.configuracoes.municipios.index')
                ->with('success', 'Município excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir município: ' . $e->getMessage());
        }
    }

    /**
     * Busca municípios (para autocomplete)
     */
    public function buscar(Request $request)
    {
        $termo = $request->get('termo', '');

        $municipios = Municipio::where('ativo', true)
            ->where(function($q) use ($termo) {
                $q->where('nome', 'like', "%{$termo}%")
                  ->orWhere('codigo_ibge', 'like', "%{$termo}%");
            })
            ->orderBy('nome')
            ->limit(20)
            ->get(['id', 'nome', 'codigo_ibge', 'uf']);

        return response()->json($municipios);
    }
}
