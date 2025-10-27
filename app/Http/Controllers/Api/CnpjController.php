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

    /**
     * Verifica se as atividades são de competência estadual ou municipal
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verificarCompetencia(Request $request): JsonResponse
    {
        try {
            $atividades = $request->input('atividades', []);
            $municipio = $request->input('municipio', null);
            
            \Log::info('=== VERIFICAÇÃO DE COMPETÊNCIA INICIADA ===', [
                'atividades_recebidas' => $atividades,
                'municipio_recebido' => $municipio
            ]);
            
            // Valida se tem atividades
            if (empty($atividades) || !is_array($atividades)) {
                return response()->json([
                    'competencia' => 'municipal',
                    'atividades_verificadas' => 0,
                    'erro' => 'Nenhuma atividade fornecida'
                ]);
            }
            
            // Remove " - TO" ou "/TO" do nome do município
            if ($municipio) {
                $municipio = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', $municipio);
                $municipio = trim($municipio);
            }

            // Verifica se pelo menos uma atividade é estadual
            $temAtividadeEstadual = false;
            $atividadesVerificadas = [];
            
            foreach ($atividades as $cnae) {
                try {
                    // Remove formatação do CNAE
                    $cnaeOriginal = $cnae;
                    $cnae = preg_replace('/[^0-9]/', '', $cnae);
                    
                    \Log::info('Verificando CNAE', [
                        'cnae_original' => $cnaeOriginal,
                        'cnae_limpo' => $cnae,
                        'municipio' => $municipio
                    ]);
                    
                    // Verifica se é atividade estadual
                    $isEstadual = \App\Models\Pactuacao::isAtividadeEstadual($cnae, $municipio);
                    
                    \Log::info('Resultado verificação', [
                        'cnae' => $cnae,
                        'is_estadual' => $isEstadual ? 'SIM' : 'NÃO'
                    ]);
                    
                    $atividadesVerificadas[] = [
                        'cnae' => $cnae,
                        'estadual' => $isEstadual
                    ];
                    
                    if ($isEstadual) {
                        $temAtividadeEstadual = true;
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro ao verificar CNAE individual', [
                        'cnae' => $cnae,
                        'erro' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            $resultado = [
                'competencia' => $temAtividadeEstadual ? 'estadual' : 'municipal',
                'atividades_verificadas' => count($atividades),
                'detalhes' => $atividadesVerificadas,
                'municipio' => $municipio
            ];
            
            \Log::info('=== RESULTADO FINAL ===', $resultado);

            return response()->json($resultado);

        } catch (\Exception $e) {
            \Log::error('ERRO GERAL ao verificar competência', [
                'request_all' => $request->all(),
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'competencia' => 'municipal',
                'erro' => $e->getMessage(),
                'atividades_verificadas' => 0
            ], 200); // Retorna 200 para não quebrar o frontend
        }
    }
}
