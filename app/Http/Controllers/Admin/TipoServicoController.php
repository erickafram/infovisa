<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoServico;
use Illuminate\Http\Request;

class TipoServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = TipoServico::withCount('atividades');

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

        return view('configuracoes.tipos-servico.index', compact('tipos'));
    }

    public function create()
    {
        return view('configuracoes.tipos-servico.create');
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

        TipoServico::create($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-servico.index')
            ->with('success', 'Tipo de serviço criado com sucesso!');
    }

    public function edit(TipoServico $tipos_servico)
    {
        return view('configuracoes.tipos-servico.edit', [
            'tipo' => $tipos_servico
        ]);
    }

    public function update(Request $request, TipoServico $tipos_servico)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;

        $tipos_servico->update($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-servico.index')
            ->with('success', 'Tipo de serviço atualizado com sucesso!');
    }

    public function destroy(TipoServico $tipos_servico)
    {
        // Verifica se tem atividades vinculadas
        if ($tipos_servico->atividades()->exists()) {
            return redirect()
                ->route('admin.configuracoes.tipos-servico.index')
                ->with('error', 'Este tipo de serviço possui atividades vinculadas e não pode ser excluído.');
        }

        $tipos_servico->delete();

        return redirect()
            ->route('admin.configuracoes.tipos-servico.index')
            ->with('success', 'Tipo de serviço excluído com sucesso!');
    }
}
