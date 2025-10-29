<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoAcao;
use Illuminate\Http\Request;

class TipoAcaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoAcao::query();

        // Filtro por competência
        if ($request->filled('competencia')) {
            $query->where('competencia', $request->competencia);
        }

        // Filtro por atividade SIA
        if ($request->filled('atividade_sia')) {
            $query->where('atividade_sia', $request->atividade_sia === '1');
        }

        // Filtro por status
        if ($request->filled('ativo')) {
            $query->where('ativo', $request->ativo === '1');
        }

        // Busca por descrição ou código
        if ($request->filled('busca')) {
            $query->where(function($q) use ($request) {
                $q->where('descricao', 'ilike', '%' . $request->busca . '%')
                  ->orWhere('codigo_procedimento', 'ilike', '%' . $request->busca . '%');
            });
        }

        $tipoAcoes = $query->orderBy('descricao')->paginate(15);

        return view('admin.tipo-acoes.index', compact('tipoAcoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipo-acoes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'codigo_procedimento' => 'required|string|max:255|unique:tipo_acoes,codigo_procedimento',
            'atividade_sia' => 'boolean',
            'competencia' => 'required|in:estadual,municipal,ambos',
            'ativo' => 'boolean',
        ], [
            'descricao.required' => 'A descrição é obrigatória.',
            'codigo_procedimento.required' => 'O código do procedimento é obrigatório.',
            'codigo_procedimento.unique' => 'Este código de procedimento já está cadastrado.',
            'competencia.required' => 'A competência é obrigatória.',
            'competencia.in' => 'A competência deve ser estadual, municipal ou ambos.',
        ]);

        // Garante valores booleanos
        $validated['atividade_sia'] = $request->has('atividade_sia');
        $validated['ativo'] = $request->has('ativo') ? true : true; // Padrão ativo

        TipoAcao::create($validated);

        return redirect()
            ->route('admin.configuracoes.tipo-acoes.index')
            ->with('success', 'Tipo de Ação cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoAcao $tipoAcao)
    {
        return view('admin.tipo-acoes.edit', compact('tipoAcao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoAcao $tipoAcao)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'codigo_procedimento' => 'required|string|max:255|unique:tipo_acoes,codigo_procedimento,' . $tipoAcao->id,
            'atividade_sia' => 'boolean',
            'competencia' => 'required|in:estadual,municipal,ambos',
            'ativo' => 'boolean',
        ], [
            'descricao.required' => 'A descrição é obrigatória.',
            'codigo_procedimento.required' => 'O código do procedimento é obrigatório.',
            'codigo_procedimento.unique' => 'Este código de procedimento já está cadastrado.',
            'competencia.required' => 'A competência é obrigatória.',
            'competencia.in' => 'A competência deve ser estadual, municipal ou ambos.',
        ]);

        // Garante valores booleanos
        $validated['atividade_sia'] = $request->has('atividade_sia');
        $validated['ativo'] = $request->has('ativo');

        $tipoAcao->update($validated);

        return redirect()
            ->route('admin.configuracoes.tipo-acoes.index')
            ->with('success', 'Tipo de Ação atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoAcao $tipoAcao)
    {
        try {
            $tipoAcao->delete();
            
            return redirect()
                ->route('admin.configuracoes.tipo-acoes.index')
                ->with('success', 'Tipo de Ação excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.configuracoes.tipo-acoes.index')
                ->with('error', 'Erro ao excluir Tipo de Ação. Pode estar vinculado a outros registros.');
        }
    }
}
