<?php

namespace App\Http\Controllers;

use App\Models\TipoProcesso;
use App\Models\Municipio;
use Illuminate\Http\Request;

class TipoProcessoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiposProcesso = TipoProcesso::ordenado()->get();
        return view('admin.configuracoes.tipos-processo.index', compact('tiposProcesso'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $municipios = Municipio::orderBy('nome')->get();
        return view('admin.configuracoes.tipos-processo.create', compact('municipios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|max:255|unique:tipo_processos,codigo',
            'descricao' => 'nullable|string',
            'ordem' => 'nullable|integer|min:0',
            'competencia' => 'required|in:estadual,municipal,estadual_exclusivo',
        ]);

        // Converte checkboxes para boolean (checkboxes não enviam valor quando desmarcados)
        $validated['anual'] = $request->has('anual');
        $validated['usuario_externo_pode_abrir'] = $request->has('usuario_externo_pode_abrir');
        $validated['usuario_externo_pode_visualizar'] = $request->has('usuario_externo_pode_visualizar');
        $validated['exibir_fila_publica'] = $request->has('exibir_fila_publica');
        $validated['unico_por_estabelecimento'] = $request->has('unico_por_estabelecimento');
        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;
        
        // Processa municípios descentralizados (apenas para tipos estaduais)
        if ($validated['competencia'] === 'estadual' && $request->filled('municipios_descentralizados')) {
            $validated['municipios_descentralizados'] = $request->municipios_descentralizados;
            
            // Busca IDs dos municípios
            $municipiosIds = Municipio::whereIn('nome', $request->municipios_descentralizados)
                ->pluck('id')
                ->toArray();
            $validated['municipios_descentralizados_ids'] = $municipiosIds;
        }

        TipoProcesso::create($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-processo.index')
            ->with('success', 'Tipo de processo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoProcesso $tipoProcesso)
    {
        return view('admin.configuracoes.tipos-processo.show', compact('tipoProcesso'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoProcesso $tipoProcesso)
    {
        $municipios = Municipio::orderBy('nome')->get();
        return view('admin.configuracoes.tipos-processo.edit', compact('tipoProcesso', 'municipios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoProcesso $tipoProcesso)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|max:255|unique:tipo_processos,codigo,' . $tipoProcesso->id,
            'descricao' => 'nullable|string',
            'ordem' => 'nullable|integer|min:0',
            'competencia' => 'required|in:estadual,municipal,estadual_exclusivo',
        ]);

        // Converte checkboxes para boolean (checkboxes não enviam valor quando desmarcados)
        $validated['anual'] = $request->has('anual');
        $validated['usuario_externo_pode_abrir'] = $request->has('usuario_externo_pode_abrir');
        $validated['usuario_externo_pode_visualizar'] = $request->has('usuario_externo_pode_visualizar');
        $validated['exibir_fila_publica'] = $request->has('exibir_fila_publica');
        $validated['unico_por_estabelecimento'] = $request->has('unico_por_estabelecimento');
        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;
        
        // Processa municípios descentralizados (apenas para tipos estaduais)
        if ($validated['competencia'] === 'estadual' && $request->filled('municipios_descentralizados')) {
            $validated['municipios_descentralizados'] = $request->municipios_descentralizados;
            
            // Busca IDs dos municípios
            $municipiosIds = Municipio::whereIn('nome', $request->municipios_descentralizados)
                ->pluck('id')
                ->toArray();
            $validated['municipios_descentralizados_ids'] = $municipiosIds;
        } else {
            // Se mudou para municipal ou não tem municípios, limpa a descentralização
            $validated['municipios_descentralizados'] = null;
            $validated['municipios_descentralizados_ids'] = null;
        }

        $tipoProcesso->update($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-processo.index')
            ->with('success', 'Tipo de processo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoProcesso $tipoProcesso)
    {
        $tipoProcesso->delete();

        return redirect()
            ->route('admin.configuracoes.tipos-processo.index')
            ->with('success', 'Tipo de processo removido com sucesso!');
    }
}
