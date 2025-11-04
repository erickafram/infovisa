<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoSetor;
use App\Enums\NivelAcesso;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TipoSetorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipoSetores = TipoSetor::orderBy('nome')->paginate(20);
        
        return view('admin.configuracoes.tipo-setores.index', compact('tipoSetores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $niveisAcesso = NivelAcesso::cases();
        
        return view('admin.configuracoes.tipo-setores.create', compact('niveisAcesso'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'codigo' => 'required|string|max:50|unique:tipo_setores,codigo',
            'descricao' => 'nullable|string',
            'niveis_acesso' => 'nullable|array',
            'niveis_acesso.*' => 'string|in:' . implode(',', array_map(fn($case) => $case->value, NivelAcesso::cases())),
            'ativo' => 'boolean',
        ]);

        // Se nenhum nível foi selecionado, deixa null (disponível para todos)
        if (empty($validated['niveis_acesso'])) {
            $validated['niveis_acesso'] = null;
        }

        $validated['ativo'] = $request->has('ativo');

        TipoSetor::create($validated);

        return redirect()
            ->route('admin.configuracoes.tipo-setores.index')
            ->with('success', 'Tipo de setor criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoSetor $tipoSetor)
    {
        return view('admin.configuracoes.tipo-setores.show', compact('tipoSetor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoSetor $tipoSetor)
    {
        $niveisAcesso = NivelAcesso::cases();
        
        return view('admin.configuracoes.tipo-setores.edit', compact('tipoSetor', 'niveisAcesso'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoSetor $tipoSetor)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'codigo' => 'required|string|max:50|unique:tipo_setores,codigo,' . $tipoSetor->id,
            'descricao' => 'nullable|string',
            'niveis_acesso' => 'nullable|array',
            'niveis_acesso.*' => 'string|in:' . implode(',', array_map(fn($case) => $case->value, NivelAcesso::cases())),
            'ativo' => 'boolean',
        ]);

        // Se nenhum nível foi selecionado, deixa null (disponível para todos)
        if (empty($validated['niveis_acesso'])) {
            $validated['niveis_acesso'] = null;
        }

        $validated['ativo'] = $request->has('ativo');

        $tipoSetor->update($validated);

        return redirect()
            ->route('admin.configuracoes.tipo-setores.index')
            ->with('success', 'Tipo de setor atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoSetor $tipoSetor)
    {
        $tipoSetor->delete();

        return redirect()
            ->route('admin.configuracoes.tipo-setores.index')
            ->with('success', 'Tipo de setor excluído com sucesso!');
    }

    /**
     * Toggle status do tipo de setor
     */
    public function toggleStatus(TipoSetor $tipoSetor)
    {
        $tipoSetor->update(['ativo' => !$tipoSetor->ativo]);

        return redirect()
            ->back()
            ->with('success', 'Status atualizado com sucesso!');
    }
}
