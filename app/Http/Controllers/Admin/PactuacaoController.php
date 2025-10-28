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
        $request->validate([
            'tipo' => 'required|in:municipal,estadual',
            'municipio' => 'required_if:tipo,municipal',
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
        
        // Busca CNAEs únicos de todos os estabelecimentos
        $cnaes = Estabelecimento::select('cnae_fiscal as codigo', 'cnae_fiscal_descricao as descricao')
            ->whereNotNull('cnae_fiscal')
            ->where(function($q) use ($termo) {
                $q->where('cnae_fiscal', 'like', "%{$termo}%")
                  ->orWhere('cnae_fiscal_descricao', 'like', "%{$termo}%");
            })
            ->distinct()
            ->limit(20)
            ->get();
        
        return response()->json($cnaes);
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
            'observacao' => 'nullable|string',
            'municipios_excecao' => 'nullable|array',
        ]);
        
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            
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
}
