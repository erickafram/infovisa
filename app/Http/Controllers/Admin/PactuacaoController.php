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
        // Busca todos os municípios únicos dos estabelecimentos
        $municipios = Estabelecimento::select('municipio')
            ->whereNotNull('municipio')
            ->distinct()
            ->orderBy('municipio')
            ->pluck('municipio');
        
        // Busca todos os municípios cadastrados no sistema (para dropdown)
        $todosMunicipios = Municipio::orderBy('nome')->get();
        
        // Busca pactuações municipais agrupadas por município
        $pactuacoesMunicipais = Pactuacao::where('tipo', 'municipal')
            ->orderBy('municipio')
            ->orderBy('cnae_codigo')
            ->get()
            ->groupBy('municipio');
        
        // Busca pactuações estaduais
        $pactuacoesEstaduais = Pactuacao::where('tipo', 'estadual')
            ->orderBy('cnae_codigo')
            ->get();
        
        return view('admin.pactuacoes.index', compact(
            'municipios',
            'todosMunicipios',
            'pactuacoesMunicipais',
            'pactuacoesEstaduais'
        ));
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
