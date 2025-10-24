<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pactuacao;
use App\Models\Estabelecimento;
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
        ]);
        
        try {
            Pactuacao::create([
                'tipo' => $request->tipo,
                'municipio' => $request->tipo === 'municipal' ? $request->municipio : null,
                'cnae_codigo' => $request->cnae_codigo,
                'cnae_descricao' => $request->cnae_descricao,
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
}
