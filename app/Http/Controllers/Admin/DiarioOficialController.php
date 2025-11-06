<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuscarDiarioRequest;
use App\Models\DiarioBuscaSalva;
use App\Services\DiarioOficialService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiarioOficialController extends Controller
{
    public function __construct(
        private DiarioOficialService $diarioService
    ) {}
    
    /**
     * Exibe página principal de busca
     */
    public function index(): View
    {
        return view('admin.diario-oficial.index');
    }
    
    /**
     * Realiza busca no Diário Oficial
     */
    public function buscar(BuscarDiarioRequest $request): JsonResponse
    {
        try {
            $results = $this->diarioService->buscar(
                $request->texto,
                $request->data_inicial,
                $request->data_final
            );
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'totalResults' => count($results),
                'message' => count($results) > 0 
                    ? 'Busca realizada com sucesso!' 
                    : 'Nenhum resultado encontrado para os critérios informados.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar busca: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lista buscas salvas do usuário
     */
    public function listarBuscas(): JsonResponse
    {
        try {
            $usuario = auth('interno')->user();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            $buscas = DiarioBuscaSalva::where('usuario_interno_id', $usuario->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($busca) {
                    return [
                        'id' => $busca->id,
                        'nome' => $busca->nome,
                        'texto' => $busca->texto,
                        'data_inicial' => $busca->data_inicial->format('Y-m-d'),
                        'data_final' => $busca->data_final->format('Y-m-d'),
                        'created_at' => $busca->created_at->format('d/m/Y H:i')
                    ];
                });
            
            return response()->json([
                'success' => true,
                'buscas' => $buscas
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar buscas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Salva uma nova busca
     */
    public function salvarBusca(Request $request): JsonResponse
    {
        try {
            $usuario = auth('interno')->user();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            $request->validate([
                'nome' => 'required|string|max:255',
                'texto' => 'required|string',
                'data_inicial' => 'required|date',
                'data_final' => 'required|date|after_or_equal:data_inicial'
            ]);
            
            $busca = DiarioBuscaSalva::create([
                'usuario_interno_id' => $usuario->id,
                'nome' => $request->nome,
                'texto' => $request->texto,
                'data_inicial' => $request->data_inicial,
                'data_final' => $request->data_final
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Busca salva com sucesso!',
                'busca' => [
                    'id' => $busca->id,
                    'nome' => $busca->nome,
                    'texto' => $busca->texto,
                    'data_inicial' => $busca->data_inicial->format('Y-m-d'),
                    'data_final' => $busca->data_final->format('Y-m-d')
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar busca: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Exclui uma busca salva
     */
    public function excluirBusca($id): JsonResponse
    {
        try {
            $usuario = auth('interno')->user();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            $busca = DiarioBuscaSalva::where('usuario_interno_id', $usuario->id)
                ->findOrFail($id);
            
            $busca->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Busca excluída com sucesso!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir busca: ' . $e->getMessage()
            ], 404);
        }
    }
}
