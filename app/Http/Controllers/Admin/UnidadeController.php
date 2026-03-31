<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unidade;
use Illuminate\Http\Request;

class UnidadeController extends Controller
{
    public function index()
    {
        $unidades = Unidade::ordenadas()->get();
        return view('admin.configuracoes.unidades.index', compact('unidades'));
    }

    public function create()
    {
        return view('admin.configuracoes.unidades.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated['ativo'] = true;
        $validated['ordem'] = $validated['ordem'] ?? 0;

        Unidade::create($validated);

        return redirect()->route('admin.configuracoes.unidades.index')
            ->with('success', 'Unidade cadastrada com sucesso.');
    }

    public function edit(Unidade $unidade)
    {
        return view('admin.configuracoes.unidades.edit', compact('unidade'));
    }

    public function update(Request $request, Unidade $unidade)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $unidade->update($validated);

        return redirect()->route('admin.configuracoes.unidades.index')
            ->with('success', 'Unidade atualizada com sucesso.');
    }

    public function toggleStatus(Unidade $unidade)
    {
        $unidade->update(['ativo' => !$unidade->ativo]);

        return redirect()->route('admin.configuracoes.unidades.index')
            ->with('success', 'Status da unidade atualizado.');
    }

    public function destroy(Unidade $unidade)
    {
        if ($unidade->tiposProcesso()->count() > 0) {
            return redirect()->route('admin.configuracoes.unidades.index')
                ->with('error', 'Não é possível excluir. Esta unidade está vinculada a tipos de processo.');
        }

        $unidade->delete();

        return redirect()->route('admin.configuracoes.unidades.index')
            ->with('success', 'Unidade excluída com sucesso.');
    }
}
