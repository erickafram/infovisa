<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CnpjService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CnpjController extends Controller
{
    private CnpjService $cnpjService;

    public function __construct(CnpjService $cnpjService)
    {
        $this->cnpjService = $cnpjService;
    }

    /**
     * Consulta dados de CNPJ na API Minha Receita
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function consultar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cnpj' => 'required|string|min:14|max:18'
            ]);

            $cnpj = $request->input('cnpj');
            
            // Remove formatação
            $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);
            
            // Valida CNPJ
            if (!CnpjService::validarCnpj($cnpjLimpo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CNPJ inválido'
                ], 400);
            }

            // Consulta na API
            $dados = $this->cnpjService->consultarCnpj($cnpjLimpo);

            if (!$dados) {
                return response()->json([
                    'success' => false,
                    'message' => 'CNPJ não encontrado em nenhuma base de dados da Receita Federal. Você pode preencher os dados manualmente.'
                ], 404);
            }

            // Identifica qual API retornou os dados
            $apiSource = $dados['api_source'] ?? 'desconhecida';
            $apiNames = [
                'minha_receita' => 'Minha Receita',
                'brasil_api' => 'BrasilAPI',
                'receita_ws' => 'ReceitaWS'
            ];

            return response()->json([
                'success' => true,
                'message' => 'Dados encontrados com sucesso',
                'api_source' => $apiNames[$apiSource] ?? 'Desconhecida',
                'data' => $dados
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro na consulta CNPJ', [
                'error' => $e->getMessage(),
                'cnpj' => $request->input('cnpj')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Verifica se um CNPJ já existe no sistema
     */
    public function verificarExistente($cnpj)
    {
        try {
            // Remove formatação do CNPJ
            $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

            // Verifica se existe no banco
            $existe = \App\Models\Estabelecimento::where('cnpj', $cnpjLimpo)->exists();

            return response()->json([
                'existe' => $existe,
                'cnpj' => $cnpjLimpo
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao verificar CNPJ existente', [
                'cnpj' => $cnpj,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'existe' => false,
                'erro' => 'Erro ao verificar CNPJ'
            ], 500);
        }
    }
}
