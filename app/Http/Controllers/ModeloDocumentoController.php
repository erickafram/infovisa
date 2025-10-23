<?php

namespace App\Http\Controllers;

use App\Models\ModeloDocumento;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModeloDocumentoController extends Controller
{
    /**
     * Lista todos os modelos de documentos
     */
    public function index()
    {
        $modelos = ModeloDocumento::with('tipoDocumento')->ordenado()->paginate(15);
        
        return view('configuracoes.modelos-documento.index', compact('modelos'));
    }

    /**
     * Exibe o formulário de criação
     */
    public function create()
    {
        $tiposDocumento = TipoDocumento::ativo()->ordenado()->get();
        
        return view('configuracoes.modelos-documento.create', compact('tiposDocumento'));
    }

    /**
     * Salva um novo modelo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'descricao' => 'nullable|string',
            'conteudo' => 'required|string',
            'variaveis' => 'nullable|array',
            'ativo' => 'boolean',
            'ordem' => 'integer|min:0',
        ]);

        // Gera código automaticamente baseado no tipo + timestamp
        $tipoDocumento = TipoDocumento::find($validated['tipo_documento_id']);
        $validated['codigo'] = $tipoDocumento->codigo . '_' . time();

        ModeloDocumento::create($validated);

        return redirect()
            ->route('admin.configuracoes.modelos-documento.index')
            ->with('success', 'Modelo de documento criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição
     */
    public function edit(ModeloDocumento $modeloDocumento)
    {
        $tiposDocumento = TipoDocumento::ativo()->ordenado()->get();
        
        return view('configuracoes.modelos-documento.edit', compact('modeloDocumento', 'tiposDocumento'));
    }

    /**
     * Atualiza um modelo existente
     */
    public function update(Request $request, ModeloDocumento $modeloDocumento)
    {
        $validated = $request->validate([
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'codigo' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'conteudo' => 'required|string',
            'variaveis' => 'nullable|array',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        // Converte checkbox ativo
        $validated['ativo'] = $request->has('ativo') ? true : false;

        $modeloDocumento->update($validated);

        return redirect()
            ->route('admin.configuracoes.modelos-documento.index')
            ->with('success', 'Modelo de documento atualizado com sucesso!');
    }

    /**
     * Remove um modelo
     */
    public function destroy(ModeloDocumento $modeloDocumento)
    {
        $modeloDocumento->delete();

        return redirect()
            ->route('admin.configuracoes.modelos-documento.index')
            ->with('success', 'Modelo de documento removido com sucesso!');
    }
}
