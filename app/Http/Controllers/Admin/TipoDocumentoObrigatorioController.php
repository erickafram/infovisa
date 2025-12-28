<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoDocumentoObrigatorio;
use Illuminate\Http\Request;

class TipoDocumentoObrigatorioController extends Controller
{
    public function index(Request $request)
    {
        $query = TipoDocumentoObrigatorio::query();

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'ilike', "%{$busca}%")
                  ->orWhere('descricao', 'ilike', "%{$busca}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $tipos = $query->ordenado()->paginate(20)->withQueryString();

        return view('configuracoes.tipos-documento-obrigatorio.index', compact('tipos'));
    }

    public function create()
    {
        return view('configuracoes.tipos-documento-obrigatorio.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;

        TipoDocumentoObrigatorio::create($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-documento-obrigatorio.index')
            ->with('success', 'Tipo de documento obrigatório criado com sucesso!');
    }

    public function edit(TipoDocumentoObrigatorio $tipos_documento_obrigatorio)
    {
        return view('configuracoes.tipos-documento-obrigatorio.edit', [
            'tipo' => $tipos_documento_obrigatorio
        ]);
    }

    public function update(Request $request, TipoDocumentoObrigatorio $tipos_documento_obrigatorio)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;

        $tipos_documento_obrigatorio->update($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-documento-obrigatorio.index')
            ->with('success', 'Tipo de documento obrigatório atualizado com sucesso!');
    }

    public function destroy(TipoDocumentoObrigatorio $tipos_documento_obrigatorio)
    {
        // Verifica se está sendo usado em alguma lista
        if ($tipos_documento_obrigatorio->listasDocumento()->exists()) {
            return redirect()
                ->route('admin.configuracoes.tipos-documento-obrigatorio.index')
                ->with('error', 'Este tipo de documento está vinculado a listas e não pode ser excluído.');
        }

        $tipos_documento_obrigatorio->delete();

        return redirect()
            ->route('admin.configuracoes.tipos-documento-obrigatorio.index')
            ->with('success', 'Tipo de documento obrigatório excluído com sucesso!');
    }
}
