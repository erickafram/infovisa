<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AtalhoRapido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtalhoRapidoController extends Controller
{
    public function index()
    {
        $atalhos = AtalhoRapido::where('usuario_interno_id', Auth::guard('interno')->user()->id)
            ->orderBy('ordem')
            ->get();

        return response()->json($atalhos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:100',
            'url' => 'required|string|max:500',
            'icone' => 'nullable|string|max:50',
            'cor' => 'nullable|string|max:20',
        ]);

        $userId = Auth::guard('interno')->user()->id;
        
        // Pegar a maior ordem atual
        $maxOrdem = AtalhoRapido::where('usuario_interno_id', $userId)->max('ordem') ?? 0;

        $atalho = AtalhoRapido::create([
            'usuario_interno_id' => $userId,
            'titulo' => $request->titulo,
            'url' => $request->url,
            'icone' => $request->icone ?? 'link',
            'cor' => $request->cor ?? 'blue',
            'ordem' => $maxOrdem + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Atalho criado com sucesso!',
            'atalho' => $atalho,
        ]);
    }

    public function update(Request $request, AtalhoRapido $atalho)
    {
        // Verificar se pertence ao usuário
        if ($atalho->usuario_interno_id !== Auth::guard('interno')->user()->id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $request->validate([
            'titulo' => 'required|string|max:100',
            'url' => 'required|string|max:500',
            'icone' => 'nullable|string|max:50',
            'cor' => 'nullable|string|max:20',
        ]);

        $atalho->update([
            'titulo' => $request->titulo,
            'url' => $request->url,
            'icone' => $request->icone ?? $atalho->icone,
            'cor' => $request->cor ?? $atalho->cor,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Atalho atualizado com sucesso!',
            'atalho' => $atalho,
        ]);
    }

    public function destroy(AtalhoRapido $atalho)
    {
        // Verificar se pertence ao usuário
        if ($atalho->usuario_interno_id !== Auth::guard('interno')->user()->id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $atalho->delete();

        return response()->json([
            'success' => true,
            'message' => 'Atalho removido com sucesso!',
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'atalhos' => 'required|array',
            'atalhos.*' => 'integer|exists:atalhos_rapidos,id',
        ]);

        $userId = Auth::guard('interno')->user()->id;

        foreach ($request->atalhos as $ordem => $id) {
            AtalhoRapido::where('id', $id)
                ->where('usuario_interno_id', $userId)
                ->update(['ordem' => $ordem]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordem atualizada!',
        ]);
    }
}
