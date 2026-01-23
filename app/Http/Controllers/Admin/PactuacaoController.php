<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pactuacao;
use App\Models\Estabelecimento;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PactuacaoController extends Controller
{
    /**
     * Lista todas as pactuações (municipais e estaduais)
     */
    public function index()
    {
        // Busca todos os municípios cadastrados no sistema (para dropdown de exceções)
        $todosMunicipios = Municipio::orderBy('nome')->get();
        
        // Busca pactuações por tabela
        $tabelaI = Pactuacao::where('tabela', 'I')->orderBy('cnae_codigo')->get();
        $tabelaII = Pactuacao::where('tabela', 'II')->orderBy('cnae_codigo')->get();
        $tabelaIII = Pactuacao::where('tabela', 'III')->orderBy('cnae_codigo')->get();
        $tabelaIV = Pactuacao::where('tabela', 'IV')->orderBy('cnae_codigo')->get();
        $tabelaV = Pactuacao::where('tabela', 'V')->orderBy('cnae_codigo')->get();
        
        // Busca pactuações estaduais (todas exceto Tabela I)
        $pactuacoesEstaduais = Pactuacao::where('tipo', 'estadual')
            ->orderBy('tabela')
            ->orderBy('cnae_codigo')
            ->get();
        
        return view('admin.pactuacoes.index', compact(
            'todosMunicipios',
            'tabelaI',
            'tabelaII',
            'tabelaIII',
            'tabelaIV',
            'tabelaV',
            'pactuacoesEstaduais'
        ));
    }

    /**
     * Retorna dados de uma pactuação específica
     */
    public function show($id)
    {
        $pactuacao = Pactuacao::findOrFail($id);
        return response()->json($pactuacao);
    }

    /**
     * Busca questionários para uma lista de CNAEs
     */
    public function buscarQuestionarios(Request $request)
    {
        $cnaes = $request->input('cnaes', []);
        
        if (empty($cnaes)) {
            return response()->json([]);
        }

        // Normaliza os CNAEs (remove formatação)
        $cnaesNormalizados = array_map(function($cnae) {
            return preg_replace('/[^0-9]/', '', $cnae);
        }, $cnaes);

        // Busca pactuações que requerem questionário
        $questionarios = Pactuacao::whereIn('cnae_codigo', $cnaesNormalizados)
            ->where('requer_questionario', true)
            ->where('ativo', true)
            ->get()
            ->map(function($pactuacao) {
                return [
                    'cnae' => $pactuacao->cnae_codigo,
                    'cnae_formatado' => $pactuacao->cnae_codigo,
                    'descricao' => $pactuacao->cnae_descricao,
                    'pergunta' => $pactuacao->pergunta,
                    'tabela' => $pactuacao->tabela,
                    'municipios_excecao' => $pactuacao->municipios_excecao ?? [],
                ];
            });

        return response()->json($questionarios);
    }

    /**
     * Adiciona uma atividade à pactuação
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:municipal,estadual',
            'municipio' => 'required_if:tipo,municipal',
            'cnae_codigo' => 'required|string',
            'cnae_descricao' => 'required|string',
            'municipios_excecao' => 'nullable|array',
            'observacao' => 'nullable|string',
        ]);
        
        try {
            Pactuacao::create([
                'tipo' => $request->tipo,
                'municipio' => $request->tipo === 'municipal' ? $request->municipio : null,
                'cnae_codigo' => $request->cnae_codigo,
                'cnae_descricao' => $request->cnae_descricao,
                'municipios_excecao' => $request->tipo === 'estadual' ? $request->municipios_excecao : null,
                'observacao' => $request->observacao,
                'ativo' => true,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Atividade adicionada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar atividade: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Adiciona múltiplas atividades de uma vez
     */
    public function storeMultiple(Request $request)
    {
        // Debug: log dos dados recebidos
        \Log::info('storeMultiple chamado', [
            'dados' => $request->all()
        ]);
        
        $request->validate([
            'tipo' => 'required|in:municipal,estadual',
            'municipio' => 'nullable|string',
            'tabela' => 'required|in:I,II,III,IV,V',
            'classificacao_risco' => 'required|in:baixo,medio,alto',
            'pergunta' => 'nullable|string',
            'atividades' => 'required|array',
            'atividades.*.codigo' => 'required|string',
            'atividades.*.descricao' => 'required|string',
            'municipios_excecao' => 'nullable|array',
            'observacao' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            foreach ($request->atividades as $atividade) {
                Pactuacao::updateOrCreate(
                    [
                        'tipo' => $request->tipo,
                        'municipio' => $request->tipo === 'municipal' ? $request->municipio : null,
                        'cnae_codigo' => $atividade['codigo'],
                    ],
                    [
                        'cnae_descricao' => $atividade['descricao'],
                        'tabela' => $request->tabela,
                        'classificacao_risco' => $request->classificacao_risco,
                        'pergunta' => $request->pergunta,
                        'municipios_excecao' => $request->tipo === 'estadual' ? $request->municipios_excecao : null,
                        'observacao' => $request->observacao,
                        'ativo' => true,
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => count($request->atividades) . ' atividade(s) adicionada(s) com sucesso!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar atividades: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Ativa/Desativa uma pactuação
     */
    public function toggleStatus($id)
    {
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            $pactuacao->ativo = !$pactuacao->ativo;
            $pactuacao->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso!',
                'ativo' => $pactuacao->ativo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove uma pactuação
     */
    public function destroy($id)
    {
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            $pactuacao->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Atividade removida com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover atividade: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Busca CNAEs disponíveis (para autocomplete)
     */
    public function buscarCnaes(Request $request)
    {
        $termo = $request->get('termo', '');
        
        if (empty($termo)) {
            return response()->json([]);
        }
        
        // Remove caracteres não numéricos
        $termoLimpo = preg_replace('/[^0-9]/', '', $termo);
        
        \Log::info('Buscando CNAE', ['termo' => $termo, 'termo_limpo' => $termoLimpo]);
        
        try {
            // Busca na API do IBGE usando cURL
            $url = "https://servicodados.ibge.gov.br/api/v2/cnae/classes/{$termoLimpo}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            \Log::info('Resposta API IBGE', [
                'url' => $url,
                'http_code' => $httpCode,
                'curl_error' => $curlError,
                'response_length' => strlen($response)
            ]);
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                
                if (isset($data[0])) {
                    $cnae = $data[0];
                    \Log::info('CNAE encontrado na API IBGE', ['cnae' => $cnae]);
                    return response()->json([
                        [
                            'codigo' => $cnae['id'] ?? $termoLimpo,
                            'descricao' => $cnae['descricao'] ?? 'Descrição não encontrada'
                        ]
                    ]);
                }
            }
            
            // Se não encontrou na API, busca nos estabelecimentos cadastrados
            $cnaes = Estabelecimento::select('cnae_fiscal as codigo', 'cnae_fiscal_descricao as descricao')
                ->whereNotNull('cnae_fiscal')
                ->where(function($q) use ($termo) {
                    $q->where('cnae_fiscal', 'like', "%{$termo}%")
                      ->orWhere('cnae_fiscal_descricao', 'like', "%{$termo}%");
                })
                ->distinct()
                ->limit(20)
                ->get();
            
            \Log::info('CNAEs encontrados nos estabelecimentos', ['count' => $cnaes->count()]);
            
            if ($cnaes->isNotEmpty()) {
                return response()->json($cnaes);
            }
            
            // Se não encontrou em lugar nenhum, retorna o código com descrição genérica
            \Log::info('CNAE não encontrado, retornando descrição genérica');
            return response()->json([
                [
                    'codigo' => $termoLimpo,
                    'descricao' => "CNAE {$termoLimpo} - Descrição não disponível (adicione manualmente)"
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar CNAE: ' . $e->getMessage());
            
            // Em caso de erro, retorna o código com descrição genérica
            return response()->json([
                [
                    'codigo' => $termoLimpo,
                    'descricao' => "CNAE {$termoLimpo} - Descrição não disponível (adicione manualmente)"
                ]
            ]);
        }
    }
    
    /**
     * Adiciona um município à lista de exceções de uma pactuação estadual
     */
    public function adicionarExcecao(Request $request, $id)
    {
        $request->validate([
            'municipio' => 'required|string',
        ]);
        
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            
            if ($pactuacao->tipo !== 'estadual') {
                return response()->json([
                    'success' => false,
                    'message' => 'Exceções só podem ser adicionadas a pactuações estaduais'
                ], 422);
            }
            
            $pactuacao->adicionarMunicipioExcecao($request->municipio);
            
            return response()->json([
                'success' => true,
                'message' => 'Município adicionado às exceções com sucesso!',
                'municipios_excecao' => $pactuacao->municipios_excecao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar exceção: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Remove um município da lista de exceções de uma pactuação estadual
     */
    public function removerExcecao(Request $request, $id)
    {
        $request->validate([
            'municipio' => 'required|string',
        ]);
        
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            
            if ($pactuacao->tipo !== 'estadual') {
                return response()->json([
                    'success' => false,
                    'message' => 'Exceções só podem ser removidas de pactuações estaduais'
                ], 422);
            }
            
            $pactuacao->removerMunicipioExcecao($request->municipio);
            
            return response()->json([
                'success' => true,
                'message' => 'Município removido das exceções com sucesso!',
                'municipios_excecao' => $pactuacao->municipios_excecao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover exceção: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Atualiza observação e exceções de uma pactuação
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tabela' => 'nullable|in:I,II,III,IV,V',
            'classificacao_risco' => 'nullable|in:baixo,medio,alto',
            'pergunta' => 'nullable|string',
            'observacao' => 'nullable|string',
            'municipios_excecao' => 'nullable|array',
        ]);
        
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            
            // Atualiza todos os campos se fornecidos
            if ($request->has('tabela')) {
                $pactuacao->tabela = $request->tabela;
            }
            
            if ($request->has('classificacao_risco')) {
                $pactuacao->classificacao_risco = $request->classificacao_risco;
            }
            
            if ($request->has('pergunta')) {
                $pactuacao->pergunta = $request->pergunta;
            }
            
            if ($request->has('observacao')) {
                $pactuacao->observacao = $request->observacao;
            }
            
            if ($request->has('municipios_excecao') && $pactuacao->tipo === 'estadual') {
                $pactuacao->municipios_excecao = $request->municipios_excecao;
            }
            
            $pactuacao->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Pactuação atualizada com sucesso!',
                'pactuacao' => $pactuacao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar pactuação: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Pesquisa atividades por código CNAE ou descrição
     */
    public function pesquisar(Request $request)
    {
        $termo = $request->input('termo', '');
        
        if (strlen($termo) < 2) {
            return response()->json([]);
        }
        
        // Busca por código CNAE ou descrição
        $resultados = Pactuacao::where(function($query) use ($termo) {
                $query->where('cnae_codigo', 'LIKE', '%' . $termo . '%')
                      ->orWhere('cnae_descricao', 'ILIKE', '%' . $termo . '%');
            })
            ->where('ativo', true)
            ->orderBy('tabela')
            ->orderBy('cnae_codigo')
            ->limit(50)
            ->get()
            ->map(function($pactuacao) {
                return [
                    'id' => $pactuacao->id,
                    'cnae_codigo' => $pactuacao->cnae_codigo,
                    'cnae_descricao' => $pactuacao->cnae_descricao,
                    'tabela' => $pactuacao->tabela,
                    'tipo' => $pactuacao->tipo,
                    'observacao' => $pactuacao->observacao,
                    'classificacao_risco' => $pactuacao->classificacao_risco,
                    'requer_questionario' => $pactuacao->requer_questionario
                ];
            });
        
        return response()->json($resultados);
    }
}
