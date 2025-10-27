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

        $municipios = $query->orderBy('nome')->paginate(20);

        // Estatísticas
        $stats = [
            'total' => Municipio::count(),
            'ativos' => Municipio::where('ativo', true)->count(),
            'inativos' => Municipio::where('ativo', false)->count(),
            'com_estabelecimentos' => Municipio::has('estabelecimentos')->count(),
            'com_pactuacoes' => Municipio::has('pactuacoes')->count(),
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
            'ativo' => 'boolean',
        ], [
            'nome.required' => 'O nome do município é obrigatório',
            'codigo_ibge.required' => 'O código IBGE é obrigatório',
            'codigo_ibge.size' => 'O código IBGE deve ter 7 dígitos',
            'codigo_ibge.unique' => 'Este código IBGE já está cadastrado',
            'uf.required' => 'A UF é obrigatória',
            'uf.size' => 'A UF deve ter 2 caracteres',
        ]);

        $municipio = Municipio::create([
            'nome' => mb_strtoupper(trim($request->nome)),
            'codigo_ibge' => $request->codigo_ibge,
            'uf' => mb_strtoupper($request->uf),
            'slug' => Str::slug($request->nome),
            'ativo' => $request->has('ativo'),
        ]);

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

        // Estatísticas do município
        $stats = [
            'estabelecimentos_total' => $municipio->estabelecimentos()->count(),
            'estabelecimentos_ativos' => $municipio->estabelecimentos()->where('ativo', true)->count(),
            'estabelecimentos_pendentes' => $municipio->estabelecimentos()->where('status', 'pendente')->count(),
            'pactuacoes_municipais' => $municipio->pactuacoes()->where('tipo', 'municipal')->count(),
            'pactuacoes_excecoes' => \App\Models\Pactuacao::where('tipo', 'estadual')
                ->whereJsonContains('municipios_excecao_ids', $id)
                ->count(),
        ];

        return view('admin.municipios.show', compact('municipio', 'stats'));
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

        $request->validate([
            'nome' => 'required|string|max:100',
            'codigo_ibge' => 'required|string|size:7|unique:municipios,codigo_ibge,' . $id,
            'uf' => 'required|string|size:2',
            'ativo' => 'boolean',
        ], [
            'nome.required' => 'O nome do município é obrigatório',
            'codigo_ibge.required' => 'O código IBGE é obrigatório',
            'codigo_ibge.size' => 'O código IBGE deve ter 7 dígitos',
            'codigo_ibge.unique' => 'Este código IBGE já está cadastrado',
            'uf.required' => 'A UF é obrigatória',
            'uf.size' => 'A UF deve ter 2 caracteres',
        ]);

        $municipio->update([
            'nome' => mb_strtoupper(trim($request->nome)),
            'codigo_ibge' => $request->codigo_ibge,
            'uf' => mb_strtoupper($request->uf),
            'slug' => Str::slug($request->nome),
            'ativo' => $request->has('ativo'),
        ]);

        return redirect()
            ->route('admin.configuracoes.municipios.index')
            ->with('success', 'Município atualizado com sucesso!');
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
