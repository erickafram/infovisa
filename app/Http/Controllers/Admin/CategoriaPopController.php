<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaPop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoriaPopController extends Controller
{
    /**
     * Lista todas as categorias
     */
    public function index()
    {
        $categorias = CategoriaPop::withCount('documentos')
            ->ordenadas()
            ->paginate(20);

        return view('admin.categorias-pops.index', compact('categorias'));
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        return view('admin.categorias-pops.create');
    }

    /**
     * Armazena nova categoria
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias_pops,nome',
            'descricao' => 'nullable|string',
            'cor' => 'required|string|max:7',
            'ordem' => 'nullable|integer|min:0',
        ]);

        try {
            CategoriaPop::create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'cor' => $request->cor,
                'ordem' => $request->ordem ?? 0,
                'ativo' => true,
            ]);

            return redirect()
                ->route('admin.configuracoes.documentos-pops.index', ['tab' => 'categorias'])
                ->with('success', 'Categoria criada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao criar categoria POP', [
                'erro' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar categoria: ' . $e->getMessage());
        }
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(CategoriaPop $categoriaPop)
    {
        return view('admin.categorias-pops.edit', compact('categoriaPop'));
    }

    /**
     * Atualiza categoria
     */
    public function update(Request $request, CategoriaPop $categoriaPop)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias_pops,nome,' . $categoriaPop->id,
            'descricao' => 'nullable|string',
            'cor' => 'required|string|max:7',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'nullable|boolean',
        ]);

        try {
            $categoriaPop->update([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'cor' => $request->cor,
                'ordem' => $request->ordem ?? 0,
                'ativo' => $request->has('ativo'),
            ]);

            return redirect()
                ->route('admin.configuracoes.documentos-pops.index', ['tab' => 'categorias'])
                ->with('success', 'Categoria atualizada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar categoria POP', [
                'categoria_id' => $categoriaPop->id,
                'erro' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar categoria: ' . $e->getMessage());
        }
    }

    /**
     * Remove categoria
     */
    public function destroy(CategoriaPop $categoriaPop)
    {
        try {
            // Verifica se tem documentos vinculados
            if ($categoriaPop->documentos()->count() > 0) {
                return back()
                    ->with('error', 'Não é possível excluir uma categoria que possui documentos vinculados.');
            }

            $categoriaPop->delete();

            return redirect()
                ->route('admin.configuracoes.documentos-pops.index', ['tab' => 'categorias'])
                ->with('success', 'Categoria excluída com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao excluir categoria POP', [
                'categoria_id' => $categoriaPop->id,
                'erro' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Erro ao excluir categoria: ' . $e->getMessage());
        }
    }

    /**
     * API: Retorna categorias ativas para select
     */
    public function listar()
    {
        $categorias = CategoriaPop::ativas()
            ->ordenadas()
            ->get(['id', 'nome', 'cor']);

        return response()->json($categorias);
    }
}
