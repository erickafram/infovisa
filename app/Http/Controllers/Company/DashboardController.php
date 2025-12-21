<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\ProcessoAlerta;
use App\Models\DocumentoDigital;
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
        
        // IDs dos processos
        $processoIds = $processos->pluck('id');
        
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
        
        // Alertas pendentes dos processos do usuário (não concluídos)
        $alertasPendentes = ProcessoAlerta::whereIn('processo_id', $processoIds)
            ->where('status', '!=', 'concluido')
            ->with(['processo.estabelecimento', 'usuarioCriador'])
            ->orderBy('data_alerta', 'asc')
            ->get();
        
        // Documentos digitais da vigilância que ainda NÃO foram visualizados pelo estabelecimento
        // São documentos assinados, não sigilosos, com todas as assinaturas completas
        $documentosPendentesVisualizacao = DocumentoDigital::whereIn('processo_id', $processoIds)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->whereDoesntHave('visualizacoes') // Ainda não foi visualizado
            ->with(['processo.estabelecimento', 'tipoDocumento', 'assinaturas'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($doc) {
                // Só mostra documentos com todas as assinaturas completas
                return $doc->todasAssinaturasCompletas();
            });
        
        return view('company.dashboard', compact(
            'estatisticasEstabelecimentos',
            'estatisticasProcessos',
            'ultimosEstabelecimentos',
            'ultimosProcessos',
            'alertasPendentes',
            'documentosPendentesVisualizacao'
        ));
    }
}
