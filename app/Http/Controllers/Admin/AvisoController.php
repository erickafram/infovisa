<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aviso;
use App\Enums\NivelAcesso;
use Illuminate\Http\Request;

class AvisoController extends Controller
{
    public function index()
    {
        $avisos = Aviso::with('criador')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('configuracoes.avisos.index', compact('avisos'));
    }

    public function create()
    {
        $niveisAcesso = NivelAcesso::options();
        return view('configuracoes.avisos.create', compact('niveisAcesso'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'mensagem' => 'required|string|max:1000',
            'link' => 'nullable|string|max:500',
            'tipo' => 'required|in:info,aviso,urgente',
            'niveis_acesso' => 'required|array|min:1',
            'niveis_acesso.*' => 'string',
            'data_expiracao' => 'nullable|date|after_or_equal:today',
            'ativo' => 'boolean',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['criado_por'] = auth('interno')->id();

        Aviso::create($validated);

        return redirect()
            ->route('admin.configuracoes.avisos.index')
            ->with('success', 'Aviso criado com sucesso!');
    }

    public function edit(Aviso $aviso)
    {
        $niveisAcesso = NivelAcesso::options();
        return view('configuracoes.avisos.edit', compact('aviso', 'niveisAcesso'));
    }

    public function update(Request $request, Aviso $aviso)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'mensagem' => 'required|string|max:1000',
            'link' => 'nullable|string|max:500',
            'tipo' => 'required|in:info,aviso,urgente',
            'niveis_acesso' => 'required|array|min:1',
            'niveis_acesso.*' => 'string',
            'data_expiracao' => 'nullable|date',
            'ativo' => 'boolean',
        ]);

        $validated['ativo'] = $request->has('ativo');

        $aviso->update($validated);

        return redirect()
            ->route('admin.configuracoes.avisos.index')
            ->with('success', 'Aviso atualizado com sucesso!');
    }

    public function destroy(Aviso $aviso)
    {
        $aviso->delete();

        return redirect()
            ->route('admin.configuracoes.avisos.index')
            ->with('success', 'Aviso excluído com sucesso!');
    }

    public function toggleAtivo(Aviso $aviso)
    {
        $aviso->update(['ativo' => !$aviso->ativo]);

        return back()->with('success', 'Status do aviso alterado!');
    }
}
