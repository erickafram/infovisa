<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtividadeResponsavelTecnico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtividadeResponsavelTecnicoController extends Controller
{
    public function index(Request $request)
    {
        $query = AtividadeResponsavelTecnico::query();

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('codigo_atividade', 'ilike', "%{$busca}%")
                    ->orWhere('descricao_atividade', 'ilike', "%{$busca}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $atividades = $query
            ->orderBy('codigo_atividade')
            ->paginate(20)
            ->withQueryString();

        return view('admin.configuracoes.responsaveis-tecnicos.index', compact('atividades'));
    }

    public function create()
    {
        return view('admin.configuracoes.responsaveis-tecnicos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_atividade' => 'required|string|max:20|unique:atividades_responsavel_tecnico,codigo_atividade',
            'descricao_atividade' => 'required|string|max:500',
            'observacoes' => 'nullable|string',
            'ativo' => 'boolean',
        ], [
            'codigo_atividade.required' => 'O código da atividade é obrigatório.',
            'codigo_atividade.unique' => 'Este código de atividade já está cadastrado.',
            'descricao_atividade.required' => 'A descrição da atividade é obrigatória.',
        ]);

        $validated['criado_por'] = Auth::guard('interno')->id();
        $validated['ativo'] = $request->has('ativo');

        AtividadeResponsavelTecnico::create($validated);

        return redirect()
            ->route('admin.configuracoes.responsaveis-tecnicos.index')
            ->with('success', 'Atividade cadastrada com sucesso!');
    }

    public function edit(AtividadeResponsavelTecnico $responsavel_tecnico)
    {
        return view('admin.configuracoes.responsaveis-tecnicos.edit', [
            'atividade' => $responsavel_tecnico,
        ]);
    }

    public function update(Request $request, AtividadeResponsavelTecnico $responsavel_tecnico)
    {
        $validated = $request->validate([
            'codigo_atividade' => 'required|string|max:20|unique:atividades_responsavel_tecnico,codigo_atividade,' . $responsavel_tecnico->id,
            'descricao_atividade' => 'required|string|max:500',
            'observacoes' => 'nullable|string',
            'ativo' => 'boolean',
        ], [
            'codigo_atividade.required' => 'O código da atividade é obrigatório.',
            'codigo_atividade.unique' => 'Este código de atividade já está cadastrado.',
            'descricao_atividade.required' => 'A descrição da atividade é obrigatória.',
        ]);

        $validated['ativo'] = $request->has('ativo');

        $responsavel_tecnico->update($validated);

        return redirect()
            ->route('admin.configuracoes.responsaveis-tecnicos.index')
            ->with('success', 'Atividade atualizada com sucesso!');
    }

    public function destroy(AtividadeResponsavelTecnico $responsavel_tecnico)
    {
        $responsavel_tecnico->delete();

        return redirect()
            ->route('admin.configuracoes.responsaveis-tecnicos.index')
            ->with('success', 'Atividade removida com sucesso!');
    }

    public function toggleStatus(AtividadeResponsavelTecnico $responsavel_tecnico)
    {
        $responsavel_tecnico->update([
            'ativo' => !$responsavel_tecnico->ativo,
        ]);

        $status = $responsavel_tecnico->ativo ? 'ativada' : 'desativada';

        return redirect()
            ->back()
            ->with('success', "Atividade {$status} com sucesso!");
    }
}
