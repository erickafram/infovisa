<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TipoDocumentoController extends Controller
{
    /**
     * Lista todos os tipos de documentos
     */
    public function index()
    {
        $tipos = TipoDocumento::ordenado()->paginate(15);
        
        return view('configuracoes.tipos-documento.index', compact('tipos'));
    }

    /**
     * Exibe o formulário de criação
     */
    public function create()
    {
        return view('configuracoes.tipos-documento.create');
    }

    /**
     * Salva um novo tipo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:255|unique:tipo_documentos,codigo',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'integer|min:0',
        ]);

        // Gera código automaticamente se não fornecido
        if (empty($validated['codigo'])) {
            $validated['codigo'] = Str::slug($validated['nome']);
        }

        TipoDocumento::create($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-documento.index')
            ->with('success', 'Tipo de documento criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição
     */
    public function edit(TipoDocumento $tipoDocumento)
    {
        return view('configuracoes.tipos-documento.edit', compact('tipoDocumento'));
    }

    /**
     * Atualiza um tipo existente
     */
    public function update(Request $request, TipoDocumento $tipoDocumento)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:255|unique:tipo_documentos,codigo,' . $tipoDocumento->id,
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'integer|min:0',
        ]);

        // Gera código automaticamente se não fornecido
        if (empty($validated['codigo'])) {
            $validated['codigo'] = Str::slug($validated['nome']);
        }

        $tipoDocumento->update($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-documento.index')
            ->with('success', 'Tipo de documento atualizado com sucesso!');
    }

    /**
     * Remove um tipo
     */
    public function destroy(TipoDocumento $tipoDocumento)
    {
        $tipoDocumento->delete();

        return redirect()
            ->route('admin.configuracoes.tipos-documento.index')
            ->with('success', 'Tipo de documento removido com sucesso!');
    }
}
