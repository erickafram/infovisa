<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atividade;
use App\Models\TipoServico;
use Illuminate\Http\Request;

class AtividadeController extends Controller
{
    public function index(Request $request)
    {
        $query = Atividade::with('tipoServico');

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'ilike', "%{$busca}%")
                  ->orWhere('codigo_cnae', 'ilike', "%{$busca}%")
                  ->orWhere('descricao', 'ilike', "%{$busca}%");
            });
        }

        if ($request->filled('tipo_servico_id')) {
            $query->where('tipo_servico_id', $request->tipo_servico_id);
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $atividades = $query->ordenado()->paginate(20)->withQueryString();
        $tiposServico = TipoServico::ativos()->ordenado()->get();

        return view('configuracoes.atividades.index', compact('atividades', 'tiposServico'));
    }

    public function create()
    {
        $tiposServico = TipoServico::ativos()->ordenado()->get();
        return view('configuracoes.atividades.create', compact('tiposServico'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_servico_id' => 'required|exists:tipos_servico,id',
            'nome' => 'required|string|max:255',
            'codigo_cnae' => 'nullable|string|max:20',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;

        Atividade::create($validated);

        return redirect()
            ->route('admin.configuracoes.atividades.index')
            ->with('success', 'Atividade criada com sucesso!');
    }

    /**
     * Salva múltiplas atividades de uma vez
     */
    public function storeMultiple(Request $request)
    {
        $validated = $request->validate([
            'tipo_servico_id' => 'required|exists:tipos_servico,id',
            'atividades' => 'required|array|min:1',
            'atividades.*.nome' => 'required|string|max:255',
            'atividades.*.codigo_cnae' => 'nullable|string|max:20',
            'atividades.*.descricao' => 'nullable|string',
        ]);

        $tipoServicoId = $validated['tipo_servico_id'];
        $count = 0;

        foreach ($validated['atividades'] as $index => $atividadeData) {
            if (!empty($atividadeData['nome'])) {
                Atividade::create([
                    'tipo_servico_id' => $tipoServicoId,
                    'nome' => $atividadeData['nome'],
                    'codigo_cnae' => $atividadeData['codigo_cnae'] ?? null,
                    'descricao' => $atividadeData['descricao'] ?? null,
                    'ativo' => true,
                    'ordem' => $index,
                ]);
                $count++;
            }
        }

        $redirect = $request->header('referer') && str_contains($request->header('referer'), 'listas-documento')
            ? redirect()->route('admin.configuracoes.listas-documento.index', ['tab' => 'atividades'])
            : redirect()->route('admin.configuracoes.atividades.index');

        return $redirect->with('success', "{$count} atividade(s) criada(s) com sucesso!");
    }

    public function edit(Atividade $atividade)
    {
        $tiposServico = TipoServico::ativos()->ordenado()->get();
        return view('configuracoes.atividades.edit', compact('atividade', 'tiposServico'));
    }

    public function update(Request $request, Atividade $atividade)
    {
        $validated = $request->validate([
            'tipo_servico_id' => 'required|exists:tipos_servico,id',
            'nome' => 'required|string|max:255',
            'codigo_cnae' => 'nullable|string|max:20',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;

        $atividade->update($validated);

        return redirect()
            ->route('admin.configuracoes.atividades.index')
            ->with('success', 'Atividade atualizada com sucesso!');
    }

    public function destroy(Atividade $atividade)
    {
        // Verifica se está vinculada a alguma lista
        if ($atividade->listasDocumento()->exists()) {
            return redirect()
                ->route('admin.configuracoes.atividades.index')
                ->with('error', 'Esta atividade está vinculada a listas de documentos e não pode ser excluída.');
        }

        $atividade->delete();

        return redirect()
            ->route('admin.configuracoes.atividades.index')
            ->with('success', 'Atividade excluída com sucesso!');
    }
}
