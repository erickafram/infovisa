<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsuarioExterno;
use App\Models\UsuarioInterno;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\DocumentoAssinatura;
use App\Models\DocumentoDigital;
use App\Models\ProcessoDesignacao;
use App\Models\OrdemServico;
use App\Models\ProcessoDocumento;
use App\Models\DocumentoResposta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard do administrador
     */
    public function index()
    {
        $usuario = Auth::guard('interno')->user();
        
        // Conta estabelecimentos pendentes baseado no perfil do usuário
        $estabelecimentosPendentesQuery = Estabelecimento::pendentes()->with('usuarioExterno');
        $estabelecimentosPendentes = $estabelecimentosPendentesQuery->get();
        
        // Filtra por competência
        if ($usuario->isAdmin()) {
            // Admin vê todos
            $estabelecimentosPendentesCount = $estabelecimentosPendentes->count();
        } elseif ($usuario->isEstadual()) {
            // Estadual vê apenas de competência estadual
            $estabelecimentosPendentes = $estabelecimentosPendentes->filter(fn($e) => $e->isCompetenciaEstadual());
            $estabelecimentosPendentesCount = $estabelecimentosPendentes->count();
        } elseif ($usuario->isMunicipal()) {
            // Municipal vê apenas de competência municipal do seu município
            $municipioId = $usuario->municipio_id;
            $estabelecimentosPendentes = $estabelecimentosPendentes->filter(fn($e) => $e->municipio_id == $municipioId && $e->isCompetenciaMunicipal());
            $estabelecimentosPendentesCount = $estabelecimentosPendentes->count();
        } else {
            $estabelecimentosPendentesCount = 0;
        }
        
        $stats = [
            'usuarios_externos' => UsuarioExterno::count(),
            'usuarios_externos_ativos' => UsuarioExterno::where('ativo', true)->count(),
            'usuarios_externos_pendentes' => UsuarioExterno::whereNull('email_verified_at')->count(),
            'usuarios_internos' => UsuarioInterno::count(),
            'usuarios_internos_ativos' => UsuarioInterno::where('ativo', true)->count(),
            'administradores' => UsuarioInterno::administradores()->count(),
            'estabelecimentos_pendentes' => $estabelecimentosPendentesCount,
        ];

        $usuarios_externos_recentes = UsuarioExterno::latest()
            ->take(5)
            ->get();

        $usuarios_internos_recentes = UsuarioInterno::latest()
            ->take(5)
            ->get();

        // Buscar os 5 últimos estabelecimentos pendentes (já filtrados por competência)
        $estabelecimentos_pendentes = $estabelecimentosPendentes->sortByDesc('created_at')->take(5);

        // Buscar processos que o usuário está acompanhando
        $processos_acompanhados = Processo::whereHas('acompanhamentos', function($query) {
                $query->where('usuario_interno_id', Auth::guard('interno')->id());
            })
            ->with(['estabelecimento', 'usuario'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        // Buscar documentos pendentes de assinatura do usuário (excluindo rascunhos)
        $documentos_pendentes_assinatura = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->id())
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', '!=', 'rascunho');
            })
            ->with(['documentoDigital.tipoDocumento', 'documentoDigital.processo'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $stats['documentos_pendentes_assinatura'] = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->id())
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', '!=', 'rascunho');
            })
            ->count();

        // Buscar documentos em rascunho que têm o usuário como assinante
        $documentos_rascunho_pendentes = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->id())
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', 'rascunho');
            })
            ->with(['documentoDigital.tipoDocumento', 'documentoDigital.processo'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $stats['documentos_rascunho_pendentes'] = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->id())
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', 'rascunho');
            })
            ->count();

        // Buscar processos designados DIRETAMENTE para o usuário (pendentes e em andamento)
        // Exclui designações apenas por setor
        $processos_designados = ProcessoDesignacao::where('usuario_designado_id', Auth::guard('interno')->id())
            ->whereIn('status', ['pendente', 'em_andamento'])
            ->with(['processo.estabelecimento', 'usuarioDesignador'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $stats['processos_designados_pendentes'] = ProcessoDesignacao::where('usuario_designado_id', Auth::guard('interno')->id())
            ->whereIn('status', ['pendente', 'em_andamento'])
            ->count();

        // Buscar Ordens de Serviço em andamento do usuário
        // Dashboard mostra APENAS OSs onde o usuário é técnico atribuído
        // Busca OSs onde o usuário está na lista de técnicos
        $todasOS = OrdemServico::with(['estabelecimento', 'municipio'])
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->get();
        
        $ordens_servico_andamento = $todasOS
            ->filter(function($os) use ($usuario) {
                return $os->tecnicos_ids && in_array($usuario->id, $os->tecnicos_ids);
            })
            ->sortBy('data_fim')
            ->take(10);

        $stats['ordens_servico_andamento'] = $todasOS
            ->filter(function($os) use ($usuario) {
                return $os->tecnicos_ids && in_array($usuario->id, $os->tecnicos_ids);
            })
            ->count();

        // Buscar processos atribuídos ao usuário ou ao seu setor (tramitados)
        $processos_atribuidos_query = Processo::with(['estabelecimento', 'tipoProcesso', 'responsavelAtual'])
            ->whereNotIn('status', ['arquivado', 'concluido']);
        
        // Filtra por responsável direto OU setor do usuário
        $processos_atribuidos_query->where(function($q) use ($usuario) {
            $q->where('responsavel_atual_id', $usuario->id);
            if ($usuario->setor) {
                $q->orWhere('setor_atual', $usuario->setor);
            }
        });
        
        // Filtrar por competência
        if ($usuario->isEstadual()) {
            $processos_atribuidos_query->whereHas('estabelecimento', function($q) {
                $q->where('competencia_manual', 'estadual')
                  ->orWhereNull('competencia_manual');
            });
        } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
            $processos_atribuidos_query->whereHas('estabelecimento', function($q) use ($usuario) {
                $q->where('municipio_id', $usuario->municipio_id);
            });
        }
        
        $processos_atribuidos = $processos_atribuidos_query
            ->orderBy('responsavel_desde', 'desc')
            ->take(10)
            ->get();
        
        // Filtrar por competência em memória (lógica complexa)
        if ($usuario->isEstadual()) {
            $processos_atribuidos = $processos_atribuidos->filter(fn($p) => $p->estabelecimento->isCompetenciaEstadual());
        } elseif ($usuario->isMunicipal()) {
            $processos_atribuidos = $processos_atribuidos->filter(fn($p) => $p->estabelecimento->isCompetenciaMunicipal());
        }
        
        $stats['processos_atribuidos'] = Processo::whereNotIn('status', ['arquivado', 'concluido'])
            ->where(function($q) use ($usuario) {
                $q->where('responsavel_atual_id', $usuario->id);
                if ($usuario->setor) {
                    $q->orWhere('setor_atual', $usuario->setor);
                }
            })
            ->count();

        // Buscar documentos assinados pelo usuário que vencem em até 5 dias
        // Exclui documentos que já foram marcados como "respondido" (prazo finalizado)
        $documentos_vencendo = DocumentoDigital::whereHas('assinaturas', function($query) {
                $query->where('usuario_interno_id', Auth::guard('interno')->id())
                      ->where('status', 'assinado');
            })
            ->whereNotNull('data_vencimento')
            ->whereNull('prazo_finalizado_em') // Exclui documentos já respondidos
            ->where('data_vencimento', '>=', now()->startOfDay())
            ->where('data_vencimento', '<=', now()->addDays(5)->endOfDay())
            ->with(['tipoDocumento', 'processo'])
            ->orderBy('data_vencimento', 'asc')
            ->get();
            
        $stats['documentos_vencendo'] = $documentos_vencendo->count();

        // Buscar documentos pendentes de aprovação enviados por empresas
        // ProcessoDocumento: arquivos enviados diretamente no processo
        $documentos_pendentes_aprovacao_query = ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo')
            ->with(['processo.estabelecimento', 'usuarioExterno']);
        
        // DocumentoResposta: respostas a documentos com prazo
        $respostas_pendentes_aprovacao_query = DocumentoResposta::where('status', 'pendente')
            ->with(['documentoDigital.processo.estabelecimento', 'usuarioExterno']);

        // Filtrar por competência do usuário
        if ($usuario->isAdmin()) {
            // Admin vê todos
        } elseif ($usuario->isEstadual()) {
            // Estadual vê apenas de estabelecimentos de competência estadual
            // Filtramos em memória pois a lógica de competência é complexa
            $documentos_pendentes_aprovacao_query->whereHas('processo.estabelecimento', function($q) {
                $q->where('competencia_manual', 'estadual')
                  ->orWhereNull('competencia_manual');
            });
            $respostas_pendentes_aprovacao_query->whereHas('documentoDigital.processo.estabelecimento', function($q) {
                $q->where('competencia_manual', 'estadual')
                  ->orWhereNull('competencia_manual');
            });
        } elseif ($usuario->isMunicipal()) {
            // Municipal vê apenas do seu município
            $municipioId = $usuario->municipio_id;
            $documentos_pendentes_aprovacao_query->whereHas('processo.estabelecimento', function($q) use ($municipioId) {
                $q->where('municipio_id', $municipioId);
            });
            $respostas_pendentes_aprovacao_query->whereHas('documentoDigital.processo.estabelecimento', function($q) use ($municipioId) {
                $q->where('municipio_id', $municipioId);
            });
        }

        $documentos_pendentes_aprovacao = $documentos_pendentes_aprovacao_query->orderBy('created_at', 'desc')->take(10)->get();
        $respostas_pendentes_aprovacao = $respostas_pendentes_aprovacao_query->orderBy('created_at', 'desc')->take(10)->get();
        
        // Filtrar por competência em memória (lógica complexa baseada em atividades)
        if ($usuario->isEstadual()) {
            $documentos_pendentes_aprovacao = $documentos_pendentes_aprovacao->filter(fn($d) => $d->processo->estabelecimento->isCompetenciaEstadual());
            $respostas_pendentes_aprovacao = $respostas_pendentes_aprovacao->filter(fn($r) => $r->documentoDigital->processo->estabelecimento->isCompetenciaEstadual());
        } elseif ($usuario->isMunicipal()) {
            $documentos_pendentes_aprovacao = $documentos_pendentes_aprovacao->filter(fn($d) => $d->processo->estabelecimento->isCompetenciaMunicipal());
            $respostas_pendentes_aprovacao = $respostas_pendentes_aprovacao->filter(fn($r) => $r->documentoDigital->processo->estabelecimento->isCompetenciaMunicipal());
        }
        
        $stats['documentos_pendentes_aprovacao'] = $documentos_pendentes_aprovacao->count();
        $stats['respostas_pendentes_aprovacao'] = $respostas_pendentes_aprovacao->count();
        $stats['total_pendentes_aprovacao'] = $stats['documentos_pendentes_aprovacao'] + $stats['respostas_pendentes_aprovacao'];

        // Buscar atalhos rápidos do usuário
        $atalhos_rapidos = \App\Models\AtalhoRapido::where('usuario_interno_id', Auth::guard('interno')->id())
            ->orderBy('ordem')
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'usuarios_externos_recentes',
            'usuarios_internos_recentes',
            'estabelecimentos_pendentes',
            'processos_acompanhados',
            'processos_atribuidos',
            'documentos_pendentes_assinatura',
            'documentos_rascunho_pendentes',
            'processos_designados',
            'ordens_servico_andamento',
            'documentos_vencendo',
            'documentos_pendentes_aprovacao',
            'respostas_pendentes_aprovacao',
            'atalhos_rapidos'
        ));
    }
}
