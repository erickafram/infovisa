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
use App\Models\Aviso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Calcula informações de documentos obrigatórios para um processo
     */
    private function calcularInfoDocumentos($processo)
    {
        try {
            // Conta documentos obrigatórios que têm tipo_documento_obrigatorio_id preenchido e foram aprovados
            $documentosAprovados = $processo->documentos()
                ->whereNotNull('tipo_documento_obrigatorio_id')
                ->where('status_aprovacao', 'aprovado')
                ->count();
            
            // Conta total de tipos de documentos obrigatórios únicos esperados para este processo
            // baseado nos documentos já enviados com tipo_documento_obrigatorio_id
            $tiposUnicos = $processo->documentos()
                ->whereNotNull('tipo_documento_obrigatorio_id')
                ->distinct('tipo_documento_obrigatorio_id')
                ->count('tipo_documento_obrigatorio_id');
            
            // Conta quantos estão pendentes de aprovação
            $documentosPendentes = $processo->documentos()
                ->where('tipo_usuario', 'externo')
                ->where('status_aprovacao', 'pendente')
                ->count();

            return [
                'total' => $tiposUnicos ?: 0,
                'enviados' => $documentosAprovados,
                'pendentes_aprovacao' => $documentosPendentes
            ];
        } catch (\Exception $e) {
            // Em caso de erro, retorna valores padrão
            return ['total' => 0, 'enviados' => 0, 'pendentes_aprovacao' => 0];
        }
    }

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
                $query->where('usuario_interno_id', Auth::guard('interno')->user()->id);
            })
            ->with(['estabelecimento', 'usuario'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        // Buscar documentos pendentes de assinatura do usuário (excluindo rascunhos)
        $documentos_pendentes_assinatura = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->user()->id)
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', '!=', 'rascunho');
            })
            ->with(['documentoDigital.tipoDocumento', 'documentoDigital.processo'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $stats['documentos_pendentes_assinatura'] = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->user()->id)
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', '!=', 'rascunho');
            })
            ->count();

        // Buscar documentos em rascunho que têm o usuário como assinante
        $documentos_rascunho_pendentes = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->user()->id)
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', 'rascunho');
            })
            ->with(['documentoDigital.tipoDocumento', 'documentoDigital.processo'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $stats['documentos_rascunho_pendentes'] = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->user()->id)
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', 'rascunho');
            })
            ->count();

        // Buscar processos designados DIRETAMENTE para o usuário (pendentes e em andamento)
        // Exclui designações apenas por setor
        $processos_designados = ProcessoDesignacao::where('usuario_designado_id', Auth::guard('interno')->user()->id)
            ->whereIn('status', ['pendente', 'em_andamento'])
            ->with(['processo.estabelecimento', 'usuarioDesignador'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $stats['processos_designados_pendentes'] = ProcessoDesignacao::where('usuario_designado_id', Auth::guard('interno')->user()->id)
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
                $query->where('usuario_interno_id', Auth::guard('interno')->user()->id)
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
        $atalhos_rapidos = \App\Models\AtalhoRapido::where('usuario_interno_id', Auth::guard('interno')->user()->id)
            ->orderBy('ordem')
            ->get();

        // Buscar avisos ativos para o nível de acesso do usuário
        $avisos_sistema = Aviso::ativos()
            ->paraNivel($usuario->nivel_acesso->value)
            ->orderBy('tipo', 'desc') // urgente primeiro
            ->orderBy('created_at', 'desc')
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
            'atalhos_rapidos',
            'avisos_sistema'
        ));
    }

    /**
     * Retorna tarefas paginadas via AJAX
     */
    public function tarefasPaginadas(Request $request)
    {
        $usuario = Auth::guard('interno')->user();
        $page = $request->get('page', 1);
        $perPage = 8;

        // Buscar documentos pendentes de assinatura
        $assinaturas = DocumentoAssinatura::where('usuario_interno_id', $usuario->id)
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', fn($q) => $q->where('status', '!=', 'rascunho'))
            ->with(['documentoDigital.tipoDocumento'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Buscar OSs em andamento do usuário
        $ordensServico = OrdemServico::with(['estabelecimento'])
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->get()
            ->filter(fn($os) => $os->tecnicos_ids && in_array($usuario->id, $os->tecnicos_ids))
            ->sortBy('data_fim');

        // Buscar documentos pendentes de aprovação
        $documentos_pendentes_query = ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo')
            ->with(['processo.estabelecimento']);

        $respostas_pendentes_query = DocumentoResposta::where('status', 'pendente')
            ->with(['documentoDigital.processo.estabelecimento', 'documentoDigital.tipoDocumento']);

        // Filtrar por competência
        if ($usuario->isEstadual()) {
            $documentos_pendentes_query->whereHas('processo.estabelecimento', fn($q) => 
                $q->where('competencia_manual', 'estadual')->orWhereNull('competencia_manual'));
            $respostas_pendentes_query->whereHas('documentoDigital.processo.estabelecimento', fn($q) => 
                $q->where('competencia_manual', 'estadual')->orWhereNull('competencia_manual'));
        } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
            $documentos_pendentes_query->whereHas('processo.estabelecimento', fn($q) => 
                $q->where('municipio_id', $usuario->municipio_id));
            $respostas_pendentes_query->whereHas('documentoDigital.processo.estabelecimento', fn($q) => 
                $q->where('municipio_id', $usuario->municipio_id));
        }

        $documentos_pendentes = $documentos_pendentes_query->orderBy('created_at', 'desc')->get();
        $respostas_pendentes = $respostas_pendentes_query->orderBy('created_at', 'desc')->get();

        // Filtrar por competência em memória
        if ($usuario->isEstadual()) {
            $documentos_pendentes = $documentos_pendentes->filter(fn($d) => $d->processo->estabelecimento->isCompetenciaEstadual());
            $respostas_pendentes = $respostas_pendentes->filter(fn($r) => $r->documentoDigital->processo->estabelecimento->isCompetenciaEstadual());
        } elseif ($usuario->isMunicipal()) {
            $documentos_pendentes = $documentos_pendentes->filter(fn($d) => $d->processo->estabelecimento->isCompetenciaMunicipal());
            $respostas_pendentes = $respostas_pendentes->filter(fn($r) => $r->documentoDigital->processo->estabelecimento->isCompetenciaMunicipal());
        }

        // Agrupar documentos por processo
        $tarefasArray = [];
        foreach($documentos_pendentes as $doc) {
            $key = 'processo_' . $doc->processo_id;
            $tipoProcesso = $doc->processo->tipo ?? null;
            $tipoProcessoNome = $doc->processo->tipo_nome ?? ucfirst($tipoProcesso ?? 'Processo');
            // Prazo de 5 dias aplica-se APENAS a processos de licenciamento
            $isLicenciamento = $tipoProcesso === 'licenciamento';
            
            if (!isset($tarefasArray[$key])) {
                $diasPendente = (int) $doc->created_at->diffInDays(now());
                $tarefasArray[$key] = [
                    'tipo' => 'aprovacao',
                    'processo_id' => $doc->processo_id,
                    'estabelecimento_id' => $doc->processo->estabelecimento_id,
                    'estabelecimento' => $doc->processo->estabelecimento->nome_fantasia ?? $doc->processo->estabelecimento->razao_social ?? 'Estabelecimento',
                    'numero_processo' => $doc->processo->numero_processo,
                    'tipo_processo' => $tipoProcessoNome,
                    'is_licenciamento' => $isLicenciamento,
                    'primeiro_arquivo' => $doc->nome_original,
                    'total' => 1,
                    'dias_pendente' => $diasPendente,
                    'atrasado' => $isLicenciamento && $diasPendente > 5, // Só atrasado se for licenciamento
                    'created_at' => $doc->created_at,
                ];
            } else {
                $tarefasArray[$key]['total']++;
                // Usar o documento mais RECENTE para calcular o prazo (cada novo documento reinicia o prazo)
                if ($doc->created_at > $tarefasArray[$key]['created_at']) {
                    $tarefasArray[$key]['created_at'] = $doc->created_at;
                    $tarefasArray[$key]['primeiro_arquivo'] = $doc->nome_original;
                    $diasPendente = (int) $doc->created_at->diffInDays(now());
                    $tarefasArray[$key]['dias_pendente'] = $diasPendente;
                    $tarefasArray[$key]['atrasado'] = $isLicenciamento && $diasPendente > 5;
                }
            }
        }

        // Respostas são tratadas separadamente para mostrar o tipo de documento original
        foreach($respostas_pendentes as $resposta) {
            $key = 'resposta_' . $resposta->documentoDigital->processo_id;
            $tipoDocumento = $resposta->documentoDigital->tipoDocumento->nome ?? 'Documento';
            $tipoProcesso = $resposta->documentoDigital->processo->tipo ?? null;
            $tipoProcessoNome = $resposta->documentoDigital->processo->tipo_nome ?? ucfirst($tipoProcesso ?? 'Processo');
            // Prazo de 5 dias aplica-se APENAS a processos de licenciamento
            $isLicenciamento = $tipoProcesso === 'licenciamento';
            
            if (!isset($tarefasArray[$key])) {
                $diasPendente = (int) $resposta->created_at->diffInDays(now());
                $tarefasArray[$key] = [
                    'tipo' => 'resposta',
                    'processo_id' => $resposta->documentoDigital->processo_id,
                    'estabelecimento_id' => $resposta->documentoDigital->processo->estabelecimento_id,
                    'estabelecimento' => $resposta->documentoDigital->processo->estabelecimento->nome_fantasia ?? 'Estabelecimento',
                    'numero_processo' => $resposta->documentoDigital->processo->numero_processo,
                    'tipo_processo' => $tipoProcessoNome,
                    'is_licenciamento' => $isLicenciamento,
                    'tipo_documento' => $tipoDocumento,
                    'primeiro_arquivo' => $resposta->nome_original,
                    'total' => 1,
                    'dias_pendente' => $diasPendente,
                    'atrasado' => $isLicenciamento && $diasPendente > 5, // Só atrasado se for licenciamento
                    'created_at' => $resposta->created_at,
                ];
            } else {
                $tarefasArray[$key]['total']++;
                // Usar a resposta mais RECENTE para calcular o prazo (cada nova resposta reinicia o prazo)
                if ($resposta->created_at > $tarefasArray[$key]['created_at']) {
                    $tarefasArray[$key]['created_at'] = $resposta->created_at;
                    $tarefasArray[$key]['primeiro_arquivo'] = $resposta->nome_original;
                    $diasPendente = (int) $resposta->created_at->diffInDays(now());
                    $tarefasArray[$key]['dias_pendente'] = $diasPendente;
                    $tarefasArray[$key]['atrasado'] = $isLicenciamento && $diasPendente > 5;
                }
            }
        }

        // Combinar todas as tarefas
        $todasTarefas = collect();

        // Adicionar assinaturas
        foreach($assinaturas as $ass) {
            $todasTarefas->push([
                'tipo' => 'assinatura',
                'id' => $ass->documentoDigital->id,
                'titulo' => $ass->documentoDigital->tipoDocumento->nome ?? 'Documento',
                'subtitulo' => 'Assinatura • ' . $ass->created_at->diffForHumans(),
                'url' => route('admin.assinatura.assinar', $ass->documentoDigital->id),
                'badge' => null,
                'atrasado' => false,
                'ordem' => 0,
            ]);
        }

        // Adicionar OSs
        foreach($ordensServico as $os) {
            $diasRestantes = $os->data_fim ? now()->startOfDay()->diffInDays($os->data_fim->startOfDay(), false) : null;
            $isVencido = $diasRestantes !== null && $diasRestantes < 0;
            $tiposAcao = $os->tiposAcao();
            
            $todasTarefas->push([
                'tipo' => 'os',
                'id' => $os->id,
                'numero' => $os->numero,
                'titulo' => 'OS #' . $os->numero,
                'subtitulo' => ($os->estabelecimento->nome_fantasia ?? 'Sem estabelecimento') . 
                    ($tiposAcao && $tiposAcao->count() > 0 ? ' • ' . $tiposAcao->first()->descricao : ''),
                'url' => route('admin.ordens-servico.show', $os),
                'dias_restantes' => $diasRestantes,
                'atrasado' => $isVencido,
                'ordem' => 1,
            ]);
        }

        // Adicionar aprovações e respostas agrupadas
        $tarefasOrdenadas = collect($tarefasArray)->sortByDesc('dias_pendente');
        foreach($tarefasOrdenadas as $tarefa) {
            // Prazo só existe para licenciamento
            $diasRestantes = $tarefa['is_licenciamento'] ? (5 - $tarefa['dias_pendente']) : null;
            
            // Diferencia respostas de aprovações normais
            if ($tarefa['tipo'] === 'resposta') {
                $todasTarefas->push([
                    'tipo' => 'resposta',
                    'processo_id' => $tarefa['processo_id'],
                    'estabelecimento_id' => $tarefa['estabelecimento_id'],
                    'titulo' => 'Resposta - ' . ($tarefa['tipo_documento'] ?? 'Documento'),
                    'subtitulo' => $tarefa['estabelecimento'] . ' • ' . $tarefa['numero_processo'],
                    'url' => route('admin.estabelecimentos.processos.show', [$tarefa['estabelecimento_id'], $tarefa['processo_id']]),
                    'total' => $tarefa['total'],
                    'dias_restantes' => $diasRestantes,
                    'atrasado' => $tarefa['atrasado'],
                    'dias_pendente' => $tarefa['dias_pendente'],
                    'is_licenciamento' => $tarefa['is_licenciamento'],
                    'tipo_processo' => $tarefa['tipo_processo'],
                    'ordem' => 1, // Respostas têm prioridade maior que aprovações normais
                ]);
            } else {
                $todasTarefas->push([
                    'tipo' => 'aprovacao',
                    'processo_id' => $tarefa['processo_id'],
                    'estabelecimento_id' => $tarefa['estabelecimento_id'],
                    'titulo' => \Str::limit($tarefa['primeiro_arquivo'], 30),
                    'subtitulo' => $tarefa['estabelecimento'] . ' • ' . $tarefa['numero_processo'],
                    'url' => route('admin.estabelecimentos.processos.show', [$tarefa['estabelecimento_id'], $tarefa['processo_id']]),
                    'total' => $tarefa['total'],
                    'dias_restantes' => $diasRestantes,
                    'atrasado' => $tarefa['atrasado'],
                    'dias_pendente' => $tarefa['dias_pendente'],
                    'is_licenciamento' => $tarefa['is_licenciamento'],
                    'tipo_processo' => $tarefa['tipo_processo'],
                    'ordem' => 2,
                ]);
            }
        }

        // Ordenar: atrasados primeiro, depois por ordem
        $todasTarefas = $todasTarefas->sortBy([
            ['atrasado', 'desc'],
            ['ordem', 'asc'],
        ]);

        $total = $todasTarefas->count();
        $lastPage = ceil($total / $perPage);
        $tarefasPaginadas = $todasTarefas->forPage($page, $perPage)->values();

        return response()->json([
            'data' => $tarefasPaginadas,
            'current_page' => (int) $page,
            'last_page' => $lastPage,
            'total' => $total,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Retorna processos atribuídos/tramitados para o usuário paginados via AJAX
     */
    public function processosAtribuidosPaginados(Request $request)
    {
        $usuario = Auth::guard('interno')->user();
        $page = $request->get('page', 1);
        $perPage = 8;

        $query = Processo::with(['estabelecimento', 'tipoProcesso', 'responsavelAtual'])
            ->whereNotIn('status', ['arquivado', 'concluido']);

        // Mostra processos onde o usuário é responsável direto OU que estão no setor do usuário
        $query->where(function($q) use ($usuario) {
            $q->where('responsavel_atual_id', $usuario->id);
            
            // Inclui também processos do setor do usuário (sem responsável direto ou com qualquer responsável)
            if ($usuario->setor) {
                $q->orWhere('setor_atual', $usuario->setor);
            }
        });

        // Filtrar por competência
        if ($usuario->isEstadual()) {
            $query->whereHas('estabelecimento', fn($q) => 
                $q->where('competencia_manual', 'estadual')->orWhereNull('competencia_manual'));
        } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
            $query->whereHas('estabelecimento', fn($q) => 
                $q->where('municipio_id', $usuario->municipio_id));
        }

        $processos = $query->orderBy('responsavel_desde', 'desc')->get();

        // Filtrar por competência em memória
        if ($usuario->isEstadual()) {
            $processos = $processos->filter(fn($p) => $p->estabelecimento->isCompetenciaEstadual());
        } elseif ($usuario->isMunicipal()) {
            $processos = $processos->filter(fn($p) => $p->estabelecimento->isCompetenciaMunicipal());
        }

        $total = $processos->count();
        $lastPage = ceil($total / $perPage) ?: 1;
        $processosPaginados = $processos->forPage($page, $perPage)->values();

        $data = $processosPaginados->map(function($proc) use ($usuario) {
            // Calcula status do prazo
            $prazoInfo = null;
            if ($proc->prazo_atribuicao) {
                $prazo = \Carbon\Carbon::parse($proc->prazo_atribuicao);
                $hoje = \Carbon\Carbon::today();
                $diasRestantes = $hoje->diffInDays($prazo, false);
                
                $prazoInfo = [
                    'data' => $prazo->format('d/m/Y'),
                    'vencido' => $diasRestantes < 0,
                    'proximo' => $diasRestantes >= 0 && $diasRestantes <= 3,
                    'dias_restantes' => $diasRestantes,
                ];
            }
            
            // Verifica se é processo direto do usuário ou apenas do setor
            $isMeuDireto = $proc->responsavel_atual_id === $usuario->id;
            $isDoSetor = !$isMeuDireto && $usuario->setor && $proc->setor_atual === $usuario->setor;
            
            // Calcula informações de documentos
            $infoDocumentos = $this->calcularInfoDocumentos($proc);
            
            return [
                'id' => $proc->id,
                'numero_processo' => $proc->numero_processo,
                'estabelecimento_id' => $proc->estabelecimento_id,
                'estabelecimento' => $proc->estabelecimento->nome_fantasia ?? $proc->estabelecimento->razao_social ?? '-',
                'status' => $proc->status,
                'status_nome' => $proc->status_nome,
                'is_meu_direto' => $isMeuDireto,
                'is_do_setor' => $isDoSetor,
                'setor_atual' => $proc->setor_atual,
                'responsavel_desde' => $proc->responsavel_desde ? $proc->responsavel_desde->diffForHumans() : null,
                'prazo' => $prazoInfo,
                'docs_total' => $infoDocumentos['total'],
                'docs_enviados' => $infoDocumentos['enviados'],
                'docs_pendentes' => $infoDocumentos['pendentes_aprovacao'],
                'url' => route('admin.estabelecimentos.processos.show', [$proc->estabelecimento_id, $proc->id]),
            ];
        });

        return response()->json([
            'data' => $data,
            'current_page' => (int) $page,
            'last_page' => $lastPage,
            'total' => $total,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Exibe página com todas as tarefas
     */
    public function todasTarefas()
    {
        return view('admin.dashboard.tarefas');
    }

    /**
     * Retorna todas as tarefas paginadas via AJAX (para página completa)
     */
    public function todasTarefasPaginadas(Request $request)
    {
        $usuario = Auth::guard('interno')->user();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);
        $filtro = $request->get('filtro', 'todos'); // todos, aprovacao, resposta, assinatura, os

        // Buscar documentos pendentes de assinatura
        $assinaturas = DocumentoAssinatura::where('usuario_interno_id', $usuario->id)
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', fn($q) => $q->where('status', '!=', 'rascunho'))
            ->with(['documentoDigital.tipoDocumento', 'documentoDigital.processo.estabelecimento'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Buscar OSs em andamento do usuário
        $ordensServico = OrdemServico::with(['estabelecimento'])
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->get()
            ->filter(fn($os) => $os->tecnicos_ids && in_array($usuario->id, $os->tecnicos_ids))
            ->sortBy('data_fim');

        // Buscar documentos pendentes de aprovação
        $documentos_pendentes_query = ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo')
            ->with(['processo.estabelecimento']);

        $respostas_pendentes_query = DocumentoResposta::where('status', 'pendente')
            ->with(['documentoDigital.processo.estabelecimento', 'documentoDigital.tipoDocumento']);

        // Filtrar por competência
        if ($usuario->isEstadual()) {
            $documentos_pendentes_query->whereHas('processo.estabelecimento', fn($q) => 
                $q->where('competencia_manual', 'estadual')->orWhereNull('competencia_manual'));
            $respostas_pendentes_query->whereHas('documentoDigital.processo.estabelecimento', fn($q) => 
                $q->where('competencia_manual', 'estadual')->orWhereNull('competencia_manual'));
        } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
            $documentos_pendentes_query->whereHas('processo.estabelecimento', fn($q) => 
                $q->where('municipio_id', $usuario->municipio_id));
            $respostas_pendentes_query->whereHas('documentoDigital.processo.estabelecimento', fn($q) => 
                $q->where('municipio_id', $usuario->municipio_id));
        }

        $documentos_pendentes = $documentos_pendentes_query->orderBy('created_at', 'desc')->get();
        $respostas_pendentes = $respostas_pendentes_query->orderBy('created_at', 'desc')->get();

        // Filtrar por competência em memória
        if ($usuario->isEstadual()) {
            $documentos_pendentes = $documentos_pendentes->filter(fn($d) => $d->processo->estabelecimento->isCompetenciaEstadual());
            $respostas_pendentes = $respostas_pendentes->filter(fn($r) => $r->documentoDigital->processo->estabelecimento->isCompetenciaEstadual());
        } elseif ($usuario->isMunicipal()) {
            $documentos_pendentes = $documentos_pendentes->filter(fn($d) => $d->processo->estabelecimento->isCompetenciaMunicipal());
            $respostas_pendentes = $respostas_pendentes->filter(fn($r) => $r->documentoDigital->processo->estabelecimento->isCompetenciaMunicipal());
        }

        // Agrupar documentos por processo
        $tarefasArray = [];
        foreach($documentos_pendentes as $doc) {
            $key = 'processo_' . $doc->processo_id;
            $tipoProcesso = $doc->processo->tipo ?? null;
            $tipoProcessoNome = $doc->processo->tipo_nome ?? ucfirst($tipoProcesso ?? 'Processo');
            $isLicenciamento = $tipoProcesso === 'licenciamento';
            
            if (!isset($tarefasArray[$key])) {
                $diasPendente = (int) $doc->created_at->diffInDays(now());
                $tarefasArray[$key] = [
                    'tipo' => 'aprovacao',
                    'processo_id' => $doc->processo_id,
                    'estabelecimento_id' => $doc->processo->estabelecimento_id,
                    'estabelecimento' => $doc->processo->estabelecimento->nome_fantasia ?? $doc->processo->estabelecimento->razao_social ?? 'Estabelecimento',
                    'numero_processo' => $doc->processo->numero_processo,
                    'tipo_processo' => $tipoProcessoNome,
                    'is_licenciamento' => $isLicenciamento,
                    'primeiro_arquivo' => $doc->nome_original,
                    'total' => 1,
                    'dias_pendente' => $diasPendente,
                    'atrasado' => $isLicenciamento && $diasPendente > 5,
                    'created_at' => $doc->created_at,
                ];
            } else {
                $tarefasArray[$key]['total']++;
                // Usar o documento mais RECENTE para calcular o prazo (cada novo documento reinicia o prazo)
                if ($doc->created_at > $tarefasArray[$key]['created_at']) {
                    $tarefasArray[$key]['created_at'] = $doc->created_at;
                    $tarefasArray[$key]['primeiro_arquivo'] = $doc->nome_original;
                    $diasPendente = (int) $doc->created_at->diffInDays(now());
                    $tarefasArray[$key]['dias_pendente'] = $diasPendente;
                    $tarefasArray[$key]['atrasado'] = $isLicenciamento && $diasPendente > 5;
                }
            }
        }

        // Respostas são tratadas separadamente
        foreach($respostas_pendentes as $resposta) {
            $key = 'resposta_' . $resposta->documentoDigital->processo_id;
            $tipoDocumento = $resposta->documentoDigital->tipoDocumento->nome ?? 'Documento';
            $tipoProcesso = $resposta->documentoDigital->processo->tipo ?? null;
            $tipoProcessoNome = $resposta->documentoDigital->processo->tipo_nome ?? ucfirst($tipoProcesso ?? 'Processo');
            $isLicenciamento = $tipoProcesso === 'licenciamento';
            
            if (!isset($tarefasArray[$key])) {
                $diasPendente = (int) $resposta->created_at->diffInDays(now());
                $tarefasArray[$key] = [
                    'tipo' => 'resposta',
                    'processo_id' => $resposta->documentoDigital->processo_id,
                    'estabelecimento_id' => $resposta->documentoDigital->processo->estabelecimento_id,
                    'estabelecimento' => $resposta->documentoDigital->processo->estabelecimento->nome_fantasia ?? 'Estabelecimento',
                    'numero_processo' => $resposta->documentoDigital->processo->numero_processo,
                    'tipo_processo' => $tipoProcessoNome,
                    'is_licenciamento' => $isLicenciamento,
                    'tipo_documento' => $tipoDocumento,
                    'primeiro_arquivo' => $resposta->nome_original,
                    'total' => 1,
                    'dias_pendente' => $diasPendente,
                    'atrasado' => $isLicenciamento && $diasPendente > 5,
                    'created_at' => $resposta->created_at,
                ];
            } else {
                $tarefasArray[$key]['total']++;
                // Usar a resposta mais RECENTE para calcular o prazo (cada nova resposta reinicia o prazo)
                if ($resposta->created_at > $tarefasArray[$key]['created_at']) {
                    $tarefasArray[$key]['created_at'] = $resposta->created_at;
                    $tarefasArray[$key]['primeiro_arquivo'] = $resposta->nome_original;
                    $diasPendente = (int) $resposta->created_at->diffInDays(now());
                    $tarefasArray[$key]['dias_pendente'] = $diasPendente;
                    $tarefasArray[$key]['atrasado'] = $isLicenciamento && $diasPendente > 5;
                }
            }
        }

        // Combinar todas as tarefas
        $todasTarefas = collect();

        // Adicionar assinaturas
        if ($filtro === 'todos' || $filtro === 'assinatura') {
            foreach($assinaturas as $ass) {
                $todasTarefas->push([
                    'tipo' => 'assinatura',
                    'id' => $ass->documentoDigital->id,
                    'titulo' => $ass->documentoDigital->tipoDocumento->nome ?? 'Documento',
                    'subtitulo' => $ass->documentoDigital->processo->estabelecimento->nome_fantasia ?? 
                                   $ass->documentoDigital->processo->estabelecimento->razao_social ?? 'Estabelecimento',
                    'numero_processo' => $ass->documentoDigital->processo->numero_processo ?? null,
                    'url' => route('admin.assinatura.assinar', $ass->documentoDigital->id),
                    'badge' => null,
                    'atrasado' => false,
                    'ordem' => 0,
                    'data' => $ass->created_at->format('d/m/Y H:i'),
                    'created_at' => $ass->created_at,
                ]);
            }
        }

        // Adicionar OSs
        if ($filtro === 'todos' || $filtro === 'os') {
            foreach($ordensServico as $os) {
                $diasRestantes = $os->data_fim ? now()->startOfDay()->diffInDays($os->data_fim->startOfDay(), false) : null;
                $isVencido = $diasRestantes !== null && $diasRestantes < 0;
                $tiposAcao = $os->tiposAcao();
                
                $todasTarefas->push([
                    'tipo' => 'os',
                    'id' => $os->id,
                    'numero' => $os->numero,
                    'titulo' => 'OS #' . $os->numero,
                    'subtitulo' => $os->estabelecimento->nome_fantasia ?? 'Sem estabelecimento',
                    'tipo_acao' => $tiposAcao && $tiposAcao->count() > 0 ? $tiposAcao->first()->descricao : null,
                    'url' => route('admin.ordens-servico.show', $os),
                    'dias_restantes' => $diasRestantes,
                    'atrasado' => $isVencido,
                    'ordem' => 1,
                    'data' => $os->created_at->format('d/m/Y H:i'),
                    'created_at' => $os->created_at,
                ]);
            }
        }

        // Adicionar aprovações e respostas agrupadas
        $tarefasOrdenadas = collect($tarefasArray)->sortByDesc('dias_pendente');
        foreach($tarefasOrdenadas as $tarefa) {
            if ($filtro !== 'todos' && $filtro !== $tarefa['tipo']) continue;
            
            $diasRestantes = $tarefa['is_licenciamento'] ? (5 - $tarefa['dias_pendente']) : null;
            
            if ($tarefa['tipo'] === 'resposta') {
                $todasTarefas->push([
                    'tipo' => 'resposta',
                    'processo_id' => $tarefa['processo_id'],
                    'estabelecimento_id' => $tarefa['estabelecimento_id'],
                    'titulo' => 'Resposta - ' . ($tarefa['tipo_documento'] ?? 'Documento'),
                    'subtitulo' => $tarefa['estabelecimento'],
                    'numero_processo' => $tarefa['numero_processo'],
                    'url' => route('admin.estabelecimentos.processos.show', [$tarefa['estabelecimento_id'], $tarefa['processo_id']]),
                    'total' => $tarefa['total'],
                    'dias_restantes' => $diasRestantes,
                    'atrasado' => $tarefa['atrasado'],
                    'dias_pendente' => $tarefa['dias_pendente'],
                    'is_licenciamento' => $tarefa['is_licenciamento'],
                    'tipo_processo' => $tarefa['tipo_processo'],
                    'ordem' => 1,
                    'data' => $tarefa['created_at']->format('d/m/Y H:i'),
                    'created_at' => $tarefa['created_at'],
                ]);
            } else {
                $todasTarefas->push([
                    'tipo' => 'aprovacao',
                    'processo_id' => $tarefa['processo_id'],
                    'estabelecimento_id' => $tarefa['estabelecimento_id'],
                    'titulo' => \Str::limit($tarefa['primeiro_arquivo'], 50),
                    'subtitulo' => $tarefa['estabelecimento'],
                    'numero_processo' => $tarefa['numero_processo'],
                    'url' => route('admin.estabelecimentos.processos.show', [$tarefa['estabelecimento_id'], $tarefa['processo_id']]),
                    'total' => $tarefa['total'],
                    'dias_restantes' => $diasRestantes,
                    'atrasado' => $tarefa['atrasado'],
                    'dias_pendente' => $tarefa['dias_pendente'],
                    'is_licenciamento' => $tarefa['is_licenciamento'],
                    'tipo_processo' => $tarefa['tipo_processo'],
                    'ordem' => 2,
                    'data' => $tarefa['created_at']->format('d/m/Y H:i'),
                    'created_at' => $tarefa['created_at'],
                ]);
            }
        }

        // Ordenar: atrasados primeiro, depois por ordem
        $todasTarefas = $todasTarefas->sortBy([
            ['atrasado', 'desc'],
            ['ordem', 'asc'],
            ['created_at', 'desc'],
        ]);

        $total = $todasTarefas->count();
        $lastPage = max(1, ceil($total / $perPage));
        $tarefasPaginadas = $todasTarefas->forPage($page, $perPage)->values();

        // Contadores por tipo
        $contadores = [
            'total' => $total,
            'aprovacao' => $todasTarefas->where('tipo', 'aprovacao')->count(),
            'resposta' => $todasTarefas->where('tipo', 'resposta')->count(),
            'assinatura' => $todasTarefas->where('tipo', 'assinatura')->count(),
            'os' => $todasTarefas->where('tipo', 'os')->count(),
        ];

        return response()->json([
            'data' => $tarefasPaginadas,
            'current_page' => (int) $page,
            'last_page' => $lastPage,
            'total' => $total,
            'per_page' => $perPage,
            'contadores' => $contadores,
        ]);
    }
}
