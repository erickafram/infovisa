<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalvarBuscaRequest;
use App\Models\BuscaSalva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuscaSalvaController extends Controller
{
    /**
     * Lista buscas salvas do usuário
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth('interno')->id();
        
        $buscas = BuscaSalva::where('usuario_interno_id', $userId)
                           ->orderBy('created_at', 'desc')
                           ->get();
        
        return response()->json([
            'success' => true,
            'buscas' => $buscas
        ]);
    }
    
    /**
     * Salva uma nova busca
     */
    public function store(SalvarBuscaRequest $request): JsonResponse
    {
        $busca = BuscaSalva::create([
            'nome' => $request->nome,
            'texto' => $request->texto,
            'data_inicial' => $request->data_inicial,
            'data_final' => $request->data_final,
            'usuario_interno_id' => auth('interno')->id(),
            'usuario_ip' => $request->ip()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Busca salva com sucesso!',
            'busca' => $busca
        ]);
    }
    
    /**
     * Exclui uma busca salva
     */
    public function destroy(BuscaSalva $buscaSalva): JsonResponse
    {
        // Verificar se pertence ao usuário atual
        if ($buscaSalva->usuario_interno_id !== auth('interno')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir esta busca.'
            ], 403);
        }
        
        $buscaSalva->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Busca excluída com sucesso!'
        ]);
    }
}
