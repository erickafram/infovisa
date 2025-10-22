<?php

namespace App\Http\Controllers;

use App\Models\ProcessoPasta;
use App\Models\Processo;
use Illuminate\Http\Request;

class ProcessoPastaController extends Controller
{
    /**
     * Listar pastas de um processo
     */
    public function index($estabelecimentoId, $processoId)
    {
        $processo = Processo::findOrFail($processoId);
        $pastas = $processo->pastas()->orderBy('ordem')->get();

        return response()->json($pastas);
    }

    /**
     * Criar nova pasta
     */
    public function store(Request $request, $estabelecimentoId, $processoId)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
        ]);

        $processo = Processo::findOrFail($processoId);

        // Pega a última ordem
        $ultimaOrdem = $processo->pastas()->max('ordem') ?? 0;

        $pasta = ProcessoPasta::create([
            'processo_id' => $processoId,
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
            'cor' => $validated['cor'] ?? '#3B82F6',
            'ordem' => $ultimaOrdem + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasta criada com sucesso!',
            'pasta' => $pasta,
        ]);
    }

    /**
     * Atualizar pasta
     */
    public function update(Request $request, $estabelecimentoId, $processoId, $pastaId)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
        ]);

        $pasta = ProcessoPasta::where('processo_id', $processoId)
            ->findOrFail($pastaId);

        $pasta->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pasta atualizada com sucesso!',
            'pasta' => $pasta,
        ]);
    }

    /**
     * Excluir pasta
     */
    public function destroy($estabelecimentoId, $processoId, $pastaId)
    {
        $pasta = ProcessoPasta::where('processo_id', $processoId)
            ->findOrFail($pastaId);

        // Move todos os documentos e arquivos para "Todos" (pasta_id = null)
        $pasta->documentos()->update(['pasta_id' => null]);
        $pasta->documentosDigitais()->update(['pasta_id' => null]);

        $pasta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pasta excluída com sucesso! Os documentos foram movidos para "Todos".',
        ]);
    }

    /**
     * Mover documento/arquivo para pasta
     */
    public function moverItem(Request $request, $estabelecimentoId, $processoId)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:documento,arquivo',
            'item_id' => 'required|integer',
            'pasta_id' => 'nullable|integer',
        ]);

        if ($validated['tipo'] === 'documento') {
            $item = \App\Models\DocumentoDigital::where('processo_id', $processoId)
                ->findOrFail($validated['item_id']);
        } else {
            $item = \App\Models\ProcessoDocumento::where('processo_id', $processoId)
                ->findOrFail($validated['item_id']);
        }

        $item->update(['pasta_id' => $validated['pasta_id']]);

        return response()->json([
            'success' => true,
            'message' => 'Item movido com sucesso!',
        ]);
    }
}
