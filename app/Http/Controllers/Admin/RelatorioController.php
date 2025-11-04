<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\OrdemServico;
use App\Models\DocumentoDigital;
use App\Models\UsuarioInterno;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    /**
     * Exibe a página de relatórios com Assistente de IA
     */
    public function index()
    {
        $usuario = auth('interno')->user();
        
        // Estatísticas gerais para exibir na tela
        $estatisticas = $this->obterEstatisticasGerais($usuario);
        
        return view('admin.relatorios.index', compact('estatisticas'));
    }
    
    /**
     * Obtém estatísticas gerais do sistema
     */
    private function obterEstatisticasGerais($usuario)
    {
        $stats = [];
        
        // Total de estabelecimentos
        $queryEstabelecimentos = Estabelecimento::query();
        if ($usuario->isMunicipal()) {
            $queryEstabelecimentos->where('municipio_id', $usuario->municipio_id);
        }
        $stats['total_estabelecimentos'] = $queryEstabelecimentos->count();
        
        // Total de processos
        $queryProcessos = Processo::query();
        if ($usuario->isMunicipal()) {
            $queryProcessos->whereHas('estabelecimento', function($q) use ($usuario) {
                $q->where('municipio_id', $usuario->municipio_id);
            });
        }
        $stats['total_processos'] = $queryProcessos->count();
        $stats['processos_abertos'] = (clone $queryProcessos)->where('status', 'aberto')->count();
        
        // Total de ordens de serviço
        $stats['total_ordens_servico'] = OrdemServico::count();
        $stats['ordens_em_andamento'] = OrdemServico::where('status', 'em_andamento')->count();
        
        // Total de documentos digitais
        $stats['total_documentos'] = DocumentoDigital::count();
        
        return $stats;
    }
}
