<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use App\Models\Processo;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $usuarioId = auth('externo')->id();
        
        // Buscar estabelecimentos do usuário
        $estabelecimentos = Estabelecimento::where('usuario_externo_id', $usuarioId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Estatísticas de estabelecimentos
        $estatisticasEstabelecimentos = [
            'total' => $estabelecimentos->count(),
            'pendentes' => $estabelecimentos->where('status', 'pendente')->count(),
            'aprovados' => $estabelecimentos->where('status', 'aprovado')->count(),
            'rejeitados' => $estabelecimentos->where('status', 'rejeitado')->count(),
        ];
        
        // IDs dos estabelecimentos do usuário
        $estabelecimentoIds = $estabelecimentos->pluck('id');
        
        // Buscar processos dos estabelecimentos do usuário
        $processos = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->with(['estabelecimento', 'tipoProcesso'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Estatísticas de processos
        $estatisticasProcessos = [
            'total' => $processos->count(),
            'em_andamento' => $processos->where('status', 'em_andamento')->count(),
            'concluidos' => $processos->where('status', 'concluido')->count(),
            'arquivados' => $processos->where('status', 'arquivado')->count(),
        ];
        
        // Últimos 5 estabelecimentos
        $ultimosEstabelecimentos = $estabelecimentos->take(5);
        
        // Últimos 5 processos
        $ultimosProcessos = $processos->take(5);
        
        return view('company.dashboard', compact(
            'estatisticasEstabelecimentos',
            'estatisticasProcessos',
            'ultimosEstabelecimentos',
            'ultimosProcessos'
        ));
    }
}
