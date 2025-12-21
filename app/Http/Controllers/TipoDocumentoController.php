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
            'tem_prazo' => 'boolean',
            'prazo_padrao_dias' => 'nullable|integer|min:1',
            'prazo_notificacao' => 'boolean',
            'permite_resposta' => 'boolean',
        ]);

        // Se tem_prazo está desmarcado, limpa o prazo_padrao_dias e prazo_notificacao
        if (!$request->has('tem_prazo')) {
            $validated['tem_prazo'] = false;
            $validated['prazo_padrao_dias'] = null;
            $validated['prazo_notificacao'] = false;
        } else {
            // Se tem_prazo está marcado, verifica prazo_notificacao
            $validated['prazo_notificacao'] = $request->has('prazo_notificacao');
        }

        // Permite resposta do estabelecimento
        $validated['permite_resposta'] = $request->has('permite_resposta');

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
            'tem_prazo' => 'boolean',
            'prazo_padrao_dias' => 'nullable|integer|min:1',
            'prazo_notificacao' => 'boolean',
            'permite_resposta' => 'boolean',
        ]);

        // Se tem_prazo está desmarcado, limpa o prazo_padrao_dias e prazo_notificacao
        if (!$request->has('tem_prazo')) {
            $validated['tem_prazo'] = false;
            $validated['prazo_padrao_dias'] = null;
            $validated['prazo_notificacao'] = false;
        } else {
            // Se tem_prazo está marcado, verifica prazo_notificacao
            $validated['prazo_notificacao'] = $request->has('prazo_notificacao');
        }

        // Permite resposta do estabelecimento
        $validated['permite_resposta'] = $request->has('permite_resposta');

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
