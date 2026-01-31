<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtividadeEquipamentoRadiacao;
use App\Models\TipoProcesso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtividadeEquipamentoRadiacaoController extends Controller
{
    /**
     * Lista todas as atividades cadastradas
     */
    public function index(Request $request)
    {
        $query = AtividadeEquipamentoRadiacao::with('tiposProcesso');

        // Filtro por busca
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('codigo_atividade', 'ilike', "%{$busca}%")
                    ->orWhere('descricao_atividade', 'ilike', "%{$busca}%");
            });
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $atividades = $query->orderBy('codigo_atividade')
            ->paginate(20)
            ->withQueryString();

        return view('admin.configuracoes.equipamentos-radiacao.index', compact('atividades'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        // Busca tipos de processo estaduais ativos
        $tiposProcesso = TipoProcesso::where('ativo', true)
            ->where('competencia', 'estadual')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        return view('admin.configuracoes.equipamentos-radiacao.create', compact('tiposProcesso'));
    }

    /**
     * Salva uma nova atividade
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_atividade' => 'required|string|max:20|unique:atividades_equipamento_radiacao,codigo_atividade',
            'descricao_atividade' => 'required|string|max:500',
            'observacoes' => 'nullable|string',
            'ativo' => 'boolean',
            'obrigatorio_processo' => 'boolean',
            'tipos_processo' => 'nullable|array',
            'tipos_processo.*' => 'exists:tipo_processos,id',
        ], [
            'codigo_atividade.required' => 'O código da atividade é obrigatório.',
            'codigo_atividade.unique' => 'Este código de atividade já está cadastrado.',
            'descricao_atividade.required' => 'A descrição da atividade é obrigatória.',
        ]);

        $validated['criado_por'] = Auth::guard('interno')->id();
        $validated['ativo'] = $request->has('ativo');
        $validated['obrigatorio_processo'] = $request->has('obrigatorio_processo');

        $atividade = AtividadeEquipamentoRadiacao::create($validated);

        // Sincroniza os tipos de processo se obrigatório estiver marcado
        if ($validated['obrigatorio_processo'] && !empty($request->tipos_processo)) {
            $atividade->tiposProcesso()->sync($request->tipos_processo);
        } else {
            $atividade->tiposProcesso()->detach();
        }

        return redirect()
            ->route('admin.configuracoes.equipamentos-radiacao.index')
            ->with('success', 'Atividade cadastrada com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit(AtividadeEquipamentoRadiacao $equipamentos_radiacao)
    {
        // Busca tipos de processo estaduais ativos
        $tiposProcesso = TipoProcesso::where('ativo', true)
            ->where('competencia', 'estadual')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        // IDs dos tipos de processo selecionados
        $tiposProcessoSelecionados = $equipamentos_radiacao->tiposProcesso->pluck('id')->toArray();

        return view('admin.configuracoes.equipamentos-radiacao.edit', [
            'atividade' => $equipamentos_radiacao,
            'tiposProcesso' => $tiposProcesso,
            'tiposProcessoSelecionados' => $tiposProcessoSelecionados,
        ]);
    }

    /**
     * Atualiza uma atividade
     */
    public function update(Request $request, AtividadeEquipamentoRadiacao $equipamentos_radiacao)
    {
        $validated = $request->validate([
            'codigo_atividade' => 'required|string|max:20|unique:atividades_equipamento_radiacao,codigo_atividade,' . $equipamentos_radiacao->id,
            'descricao_atividade' => 'required|string|max:500',
            'observacoes' => 'nullable|string',
            'ativo' => 'boolean',
            'obrigatorio_processo' => 'boolean',
            'tipos_processo' => 'nullable|array',
            'tipos_processo.*' => 'exists:tipo_processos,id',
        ], [
            'codigo_atividade.required' => 'O código da atividade é obrigatório.',
            'codigo_atividade.unique' => 'Este código de atividade já está cadastrado.',
            'descricao_atividade.required' => 'A descrição da atividade é obrigatória.',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['obrigatorio_processo'] = $request->has('obrigatorio_processo');

        $equipamentos_radiacao->update($validated);

        // Sincroniza os tipos de processo se obrigatório estiver marcado
        if ($validated['obrigatorio_processo'] && !empty($request->tipos_processo)) {
            $equipamentos_radiacao->tiposProcesso()->sync($request->tipos_processo);
        } else {
            $equipamentos_radiacao->tiposProcesso()->detach();
        }

        return redirect()
            ->route('admin.configuracoes.equipamentos-radiacao.index')
            ->with('success', 'Atividade atualizada com sucesso!');
    }

    /**
     * Remove uma atividade
     */
    public function destroy(AtividadeEquipamentoRadiacao $equipamentos_radiacao)
    {
        $equipamentos_radiacao->tiposProcesso()->detach();
        $equipamentos_radiacao->delete();

        return redirect()
            ->route('admin.configuracoes.equipamentos-radiacao.index')
            ->with('success', 'Atividade removida com sucesso!');
    }

    /**
     * Alterna o status ativo/inativo
     */
    public function toggleStatus(AtividadeEquipamentoRadiacao $equipamentos_radiacao)
    {
        $equipamentos_radiacao->update([
            'ativo' => !$equipamentos_radiacao->ativo
        ]);

        $status = $equipamentos_radiacao->ativo ? 'ativada' : 'desativada';

        return redirect()
            ->back()
            ->with('success', "Atividade {$status} com sucesso!");
    }
}
