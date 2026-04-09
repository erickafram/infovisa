<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumentoResposta;
use Illuminate\Http\Request;

class TipoDocumentoRespostaController extends Controller
{
    public function index()
    {
        $tipos = TipoDocumentoResposta::ordenado()->get();
        return view('configuracoes.tipos-documento-resposta.index', compact('tipos'));
    }

    public function create()
    {
        return view('configuracoes.tipos-documento-resposta.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'tipo_setor' => 'required|string|in:todos,publico,privado',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;

        TipoDocumentoResposta::create($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-documento-resposta.index')
            ->with('success', 'Tipo de documento resposta criado com sucesso!');
    }

    public function edit(TipoDocumentoResposta $tipos_documento_respostum)
    {
        return view('configuracoes.tipos-documento-resposta.edit', ['tipo' => $tipos_documento_respostum]);
    }

    public function update(Request $request, TipoDocumentoResposta $tipos_documento_respostum)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'tipo_setor' => 'required|string|in:todos,publico,privado',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;

        $tipos_documento_respostum->update($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-documento-resposta.index')
            ->with('success', 'Tipo de documento resposta atualizado!');
    }

    public function destroy(TipoDocumentoResposta $tipos_documento_respostum)
    {
        $tipos_documento_respostum->delete();

        return redirect()
            ->route('admin.configuracoes.tipos-documento-resposta.index')
            ->with('success', 'Tipo de documento resposta excluído!');
    }
}
