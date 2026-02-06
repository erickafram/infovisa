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
     * Retorna total esperado (incluindo não enviados), aprovados e pendentes
     */
    private function calcularInfoDocumentos($processo)
    {
        try {
            // Busca o total de documentos obrigatórios esperados para este processo
            $documentosObrigatorios = $this->buscarDocumentosObrigatoriosParaProcesso($processo);
            
            // Filtra apenas os obrigatórios
            $apenasObrigatorios = $documentosObrigatorios->where('obrigatorio', true);
            
            $total = $apenasObrigatorios->count();
            $aprovados = $apenasObrigatorios->where('status', 'aprovado')->count();
            $pendentes = $apenasObrigatorios->where('status', 'pendente')->count();

            return [
                'total' => $total,
                'enviados' => $aprovados,
                'pendentes_aprovacao' => $pendentes
            ];
        } catch (\Exception $e) {
            // Em caso de erro, retorna valores padrão
            return ['total' => 0, 'enviados' => 0, 'pendentes_aprovacao' => 0];
        }
    }
    
    /**
     * Busca os documentos obrigatórios esperados para um processo
     * Baseado nas atividades do estabelecimento e tipo de processo
     */
    private function buscarDocumentosObrigatoriosParaProcesso($processo)
    {
        $estabelecimento = $processo->estabelecimento;
        $tipoProcesso = $processo->tipoProcesso;
        $tipoProcessoId = $tipoProcesso->id ?? null;
        
        if (!$tipoProcessoId || !$estabelecimento) {
            return collect();
        }

        // Verifica se é um processo especial (Projeto Arquitetônico ou Análise de Rotulagem)
        $isProcessoEspecial = $tipoProcesso && in_array($tipoProcesso->codigo, ['projeto_arquitetonico', 'analise_rotulagem']);

        // Pega as atividades exercidas do estabelecimento
        $atividadesExercidas = $estabelecimento->atividades_exercidas ?? [];
        
        if (!$isProcessoEspecial && empty($atividadesExercidas)) {
            return collect();
        }

        $atividadeIds = collect();
        
        if (!$isProcessoEspecial && !empty($atividadesExercidas)) {
            $codigosCnae = collect($atividadesExercidas)->map(function($atividade) {
                $codigo = is_array($atividade) ? ($atividade['codigo'] ?? null) : $atividade;
                return $codigo ? preg_replace('/[^0-9]/', '', $codigo) : null;
            })->filter()->values()->toArray();

            if (!empty($codigosCnae)) {
                $atividadeIds = \App\Models\Atividade::where('ativo', true)
                    ->where(function($query) use ($codigosCnae) {
                        foreach ($codigosCnae as $codigo) {
                            $query->orWhere('codigo_cnae', $codigo);
                        }
                    })
                    ->pluck('id');
            }
        }

        // Busca as listas de documentos aplicáveis
        $query = \App\Models\ListaDocumento::where('ativo', true)
            ->where('tipo_processo_id', $tipoProcessoId)
            ->with(['tiposDocumentoObrigatorio' => function($q) {
                $q->orderBy('lista_documento_tipo.ordem');
            }]);

        if ($isProcessoEspecial) {
            $query->whereDoesntHave('atividades');
        } else {
            if ($atividadeIds->isEmpty()) {
                return collect();
            }
            $query->whereHas('atividades', function($q) use ($atividadeIds) {
                $q->whereIn('atividades.id', $atividadeIds);
            });
        }

        $query->where(function($q) use ($estabelecimento) {
            $q->where('escopo', 'estadual');
            if ($estabelecimento->municipio_id) {
                $q->orWhere(function($q2) use ($estabelecimento) {
                    $q2->where('escopo', 'municipal')
                       ->where('municipio_id', $estabelecimento->municipio_id);
                });
            }
        });

        $listas = $query->get();

        $documentos = collect();
        
        // Busca documentos já enviados
        $documentosEnviadosInfo = $processo->documentos
            ->whereNotNull('tipo_documento_obrigatorio_id')
            ->groupBy('tipo_documento_obrigatorio_id')
            ->map(function($docs) {
                $docRecente = $docs->sortByDesc('created_at')->first();
                return [
                    'status' => $docRecente->status_aprovacao,
                    'documento' => $docRecente,
                ];
            });
        
        $escopoCompetencia = $estabelecimento->getEscopoCompetencia();
        $tipoSetorEnum = $estabelecimento->tipo_setor;
        $tipoSetor = $tipoSetorEnum instanceof \App\Enums\TipoSetor ? $tipoSetorEnum->value : ($tipoSetorEnum ?? 'privado');
        
        // Documentos comuns
        $documentosComuns = \App\Models\TipoDocumentoObrigatorio::where('ativo', true)
            ->where('documento_comum', true)
            ->where(function($q) use ($tipoProcessoId) {
                $q->whereNull('tipo_processo_id')
                  ->orWhere('tipo_processo_id', $tipoProcessoId);
            })
            ->where(function($q) use ($escopoCompetencia) {
                $q->where('escopo_competencia', 'todos')
                  ->orWhere('escopo_competencia', $escopoCompetencia);
            })
            ->where(function($q) use ($tipoSetor) {
                $q->where('tipo_setor', 'todos')
                  ->orWhere('tipo_setor', $tipoSetor);
            })
            ->ordenado()
            ->get();
        
        foreach ($documentosComuns as $doc) {
            $infoEnviado = $documentosEnviadosInfo->get($doc->id);
            $documentos->push([
                'id' => $doc->id,
                'nome' => $doc->nome,
                'obrigatorio' => true,
                'status' => $infoEnviado['status'] ?? null,
            ]);
        }
        
        // Documentos das listas
        foreach ($listas as $lista) {
            foreach ($lista->tiposDocumentoObrigatorio as $doc) {
                $aplicaEscopo = $doc->escopo_competencia === 'todos' || $doc->escopo_competencia === $escopoCompetencia;
                $aplicaTipoSetor = $doc->tipo_setor === 'todos' || $doc->tipo_setor === $tipoSetor;
                
                if (!$aplicaEscopo || !$aplicaTipoSetor) {
                    continue;
                }
                
                if (!$documentos->contains('id', $doc->id)) {
                    $infoEnviado = $documentosEnviadosInfo->get($doc->id);
                    $documentos->push([
                        'id' => $doc->id,
                        'nome' => $doc->nome,
                        'obrigatorio' => $doc->pivot->obrigatorio,
                        'status' => $infoEnviado['status'] ?? null,
                    ]);
                } else {
                    $documentos = $documentos->map(function($item) use ($doc) {
                        if ($item['id'] === $doc->id && $doc->pivot->obrigatorio) {
                            $item['obrigatorio'] = true;
                        }
                        return $item;
                    });
                }
            }
        }
        
        return $documentos;
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
            $estabelecimentosPendentes = $estabelecimentosPendentes->filter(function($e) {
                try { return $e->isCompetenciaEstadual(); } catch (\Exception $ex) { return false; }
            });
            $estabelecimentosPendentesCount = $estabelecimentosPendentes->count();
        } elseif ($usuario->isMunicipal()) {
            // Municipal vê apenas de competência municipal do seu município
            $municipioId = $usuario->municipio_id;
            $estabelecimentosPendentes = $estabelecimentosPendentes->filter(function($e) use ($municipioId) {
                try { return $e->municipio_id == $municipioId && $e->isCompetenciaMunicipal(); } catch (\Exception $ex) { return false; }
            });
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
        // REGRA: Processos diretamente atribuídos (responsavel_atual_id) SEMPRE aparecem,
        // filtro de competência se aplica SOMENTE aos processos do setor.
        $processos_atribuidos_query = Processo::with(['estabelecimento', 'tipoProcesso', 'responsavelAtual'])
            ->whereNotIn('status', ['arquivado', 'concluido']);
        
        // Processos do usuário direto OU do setor (com filtro de competência apenas para setor)
        $processos_atribuidos_query->where(function($q) use ($usuario) {
            // Processos diretamente atribuídos - SEM filtro de competência
            $q->where('responsavel_atual_id', $usuario->id);
            
            // Processos do setor - COM filtro de competência
            if ($usuario->setor) {
                $q->orWhere(function($subQ) use ($usuario) {
                    $subQ->where('setor_atual', $usuario->setor);
                    
                    if ($usuario->isEstadual()) {
                        $subQ->whereHas('estabelecimento', function($estQ) {
                            $estQ->where('competencia_manual', 'estadual')
                                  ->orWhereNull('competencia_manual');
                        });
                    } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
                        $subQ->whereHas('estabelecimento', function($estQ) use ($usuario) {
                            $estQ->where('municipio_id', $usuario->municipio_id);
                        });
                    }
                });
            }
        });
        
        $processos_atribuidos = $processos_atribuidos_query
            ->orderBy('responsavel_desde', 'desc')
            ->take(10)
            ->get();
        
        // Filtrar por competência em memória - APENAS para processos do setor
        if ($usuario->isEstadual()) {
            $processos_atribuidos = $processos_atribuidos->filter(function($p) use ($usuario) {
                if ($p->responsavel_atual_id == $usuario->id) return true;
                try { return $p->estabelecimento->isCompetenciaEstadual(); } catch (\Exception $e) { return false; }
            });
        } elseif ($usuario->isMunicipal()) {
            $processos_atribuidos = $processos_atribuidos->filter(function($p) use ($usuario) {
                if ($p->responsavel_atual_id == $usuario->id) return true;
                try { return $p->estabelecimento->isCompetenciaMunicipal(); } catch (\Exception $e) { return false; }
            });
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
        // REGRA: Só mostra documentos de processos que estão na área do usuário
        // (setor_atual = setor do usuário OU responsavel_atual_id = usuário)
        // ProcessoDocumento: arquivos enviados diretamente no processo
        $documentos_pendentes_aprovacao_query = ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo')
            ->with(['processo.estabelecimento', 'usuarioExterno']);
        
        // DocumentoResposta: respostas a documentos com prazo
        $respostas_pendentes_aprovacao_query = DocumentoResposta::where('status', 'pendente')
            ->with(['documentoDigital.processo.estabelecimento', 'usuarioExterno']);

        // Filtrar por setor/responsável do processo + competência do usuário
        if ($usuario->isAdmin()) {
            // Admin vê todos
        } else {
            // Filtrar por processos que estão no setor do usuário ou atribuídos diretamente
            $documentos_pendentes_aprovacao_query->whereHas('processo', function($q) use ($usuario) {
                $q->where(function($sub) use ($usuario) {
                    $sub->where('responsavel_atual_id', $usuario->id);
                    if ($usuario->setor) {
                        $sub->orWhere('setor_atual', $usuario->setor);
                    }
                });
            });
            $respostas_pendentes_aprovacao_query->whereHas('documentoDigital.processo', function($q) use ($usuario) {
                $q->where(function($sub) use ($usuario) {
                    $sub->where('responsavel_atual_id', $usuario->id);
                    if ($usuario->setor) {
                        $sub->orWhere('setor_atual', $usuario->setor);
                    }
                });
            });

            // Filtrar também por competência
            if ($usuario->isEstadual()) {
                $documentos_pendentes_aprovacao_query->whereHas('processo.estabelecimento', function($q) {
                    $q->where('competencia_manual', 'estadual')
                      ->orWhereNull('competencia_manual');
                });
                $respostas_pendentes_aprovacao_query->whereHas('documentoDigital.processo.estabelecimento', function($q) {
                    $q->where('competencia_manual', 'estadual')
                      ->orWhereNull('competencia_manual');
                });
            } elseif ($usuario->isMunicipal()) {
                $municipioId = $usuario->municipio_id;
                $documentos_pendentes_aprovacao_query->whereHas('processo.estabelecimento', function($q) use ($municipioId) {
                    $q->where('municipio_id', $municipioId);
                });
                $respostas_pendentes_aprovacao_query->whereHas('documentoDigital.processo.estabelecimento', function($q) use ($municipioId) {
                    $q->where('municipio_id', $municipioId);
                });
            }
        }

        $documentos_pendentes_aprovacao = $documentos_pendentes_aprovacao_query->orderBy('created_at', 'desc')->take(10)->get();
        $respostas_pendentes_aprovacao = $respostas_pendentes_aprovacao_query->orderBy('created_at', 'desc')->take(10)->get();
        
        // Filtrar por competência em memória (lógica complexa baseada em atividades)
        if ($usuario->isEstadual()) {
            $documentos_pendentes_aprovacao = $documentos_pendentes_aprovacao->filter(function($d) {
                try { return $d->processo->estabelecimento->isCompetenciaEstadual(); } catch (\Exception $e) { return false; }
            });
            $respostas_pendentes_aprovacao = $respostas_pendentes_aprovacao->filter(function($r) {
                try { return $r->documentoDigital->processo->estabelecimento->isCompetenciaEstadual(); } catch (\Exception $e) { return false; }
            });
        } elseif ($usuario->isMunicipal()) {
            $documentos_pendentes_aprovacao = $documentos_pendentes_aprovacao->filter(function($d) {
                try { return $d->processo->estabelecimento->isCompetenciaMunicipal(); } catch (\Exception $e) { return false; }
            });
            $respostas_pendentes_aprovacao = $respostas_pendentes_aprovacao->filter(function($r) {
                try { return $r->documentoDigital->processo->estabelecimento->isCompetenciaMunicipal(); } catch (\Exception $e) { return false; }
            });
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

        // Contadores separados: "Para Mim" vs "Meu Setor"
        // "Para Mim" = apenas ações pessoais diretas (OS + Assinaturas)
        $stats['para_mim_total'] = ($stats['documentos_pendentes_assinatura'] ?? 0) 
            + ($stats['ordens_servico_andamento'] ?? 0);
        
        $stats['processos_do_setor'] = 0;
        if ($usuario->setor) {
            $stats['processos_do_setor'] = Processo::whereNotIn('status', ['arquivado', 'concluido'])
                ->where('setor_atual', $usuario->setor)
                ->count();
        }
        
        // "Meu Setor" = aprovações pendentes + processos no setor
        $stats['setor_total'] = ($stats['total_pendentes_aprovacao'] ?? 0) 
            + ($stats['processos_do_setor'] ?? 0);

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
        // REGRA: Só mostra documentos de processos que estão no setor do usuário ou atribuídos a ele
        $documentos_pendentes_query = ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo')
            ->with(['processo.estabelecimento']);

        $respostas_pendentes_query = DocumentoResposta::where('status', 'pendente')
            ->with(['documentoDigital.processo.estabelecimento', 'documentoDigital.tipoDocumento']);

        // Filtrar por setor/responsável do processo + competência
        if (!$usuario->isAdmin()) {
            // Só processos do meu setor ou atribuídos a mim
            $documentos_pendentes_query->whereHas('processo', function($q) use ($usuario) {
                $q->where(function($sub) use ($usuario) {
                    $sub->where('responsavel_atual_id', $usuario->id);
                    if ($usuario->setor) {
                        $sub->orWhere('setor_atual', $usuario->setor);
                    }
                });
            });
            $respostas_pendentes_query->whereHas('documentoDigital.processo', function($q) use ($usuario) {
                $q->where(function($sub) use ($usuario) {
                    $sub->where('responsavel_atual_id', $usuario->id);
                    if ($usuario->setor) {
                        $sub->orWhere('setor_atual', $usuario->setor);
                    }
                });
            });

            // Filtrar também por competência
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
        }

        $documentos_pendentes = $documentos_pendentes_query->orderBy('created_at', 'desc')->get();
        $respostas_pendentes = $respostas_pendentes_query->orderBy('created_at', 'desc')->get();

        // Filtrar por competência em memória (lógica complexa de atividades)
        if ($usuario->isEstadual()) {
            $documentos_pendentes = $documentos_pendentes->filter(function($d) {
                try { return $d->processo->estabelecimento->isCompetenciaEstadual(); } catch (\Exception $e) { return false; }
            });
            $respostas_pendentes = $respostas_pendentes->filter(function($r) {
                try { return $r->documentoDigital->processo->estabelecimento->isCompetenciaEstadual(); } catch (\Exception $e) { return false; }
            });
        } elseif ($usuario->isMunicipal()) {
            $documentos_pendentes = $documentos_pendentes->filter(function($d) {
                try { return $d->processo->estabelecimento->isCompetenciaMunicipal(); } catch (\Exception $e) { return false; }
            });
            $respostas_pendentes = $respostas_pendentes->filter(function($r) {
                try { return $r->documentoDigital->processo->estabelecimento->isCompetenciaMunicipal(); } catch (\Exception $e) { return false; }
            });
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

        // 1º PRIORIDADE: Ordens de Serviço em aberto (aparecem primeiro no topo)
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
                'ordem' => 0, // PRIORIDADE MÁXIMA
            ]);
        }

        // 2º PRIORIDADE: Documentos pendentes de assinatura
        foreach($assinaturas as $ass) {
            $todasTarefas->push([
                'tipo' => 'assinatura',
                'id' => $ass->documentoDigital->id,
                'titulo' => $ass->documentoDigital->tipoDocumento->nome ?? 'Documento',
                'subtitulo' => 'Assinatura • ' . $ass->created_at->diffForHumans(),
                'url' => route('admin.assinatura.assinar', $ass->documentoDigital->id),
                'badge' => null,
                'atrasado' => false,
                'ordem' => 1, // SEGUNDA PRIORIDADE - Assinaturas
            ]);
        }

        // 3º PRIORIDADE: Aprovações e respostas agrupadas por processo
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
                    'ordem' => 2, // Respostas têm prioridade maior que aprovações normais
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
                    'ordem' => 3, // Aprovações de documentos por último
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

        // REGRA: Processos diretamente atribuídos ao usuário (responsavel_atual_id) SEMPRE aparecem,
        // independentemente da competência do estabelecimento. Se alguém tramitou para o usuário, ele deve ver.
        // O filtro de competência se aplica SOMENTE aos processos do setor (setor_atual).
        
        $query = Processo::with(['estabelecimento', 'tipoProcesso', 'responsavelAtual'])
            ->whereNotIn('status', ['arquivado', 'concluido']);

        // Processos do usuário direto OU do setor (com filtro de competência apenas para setor)
        $query->where(function($q) use ($usuario) {
            // Processos diretamente atribuídos ao usuário - SEM filtro de competência
            $q->where('responsavel_atual_id', $usuario->id);
            
            // Processos do setor - COM filtro de competência
            if ($usuario->setor) {
                $q->orWhere(function($subQ) use ($usuario) {
                    $subQ->where('setor_atual', $usuario->setor);
                    
                    // Aplica filtro de competência APENAS para processos do setor
                    if ($usuario->isEstadual()) {
                        $subQ->whereHas('estabelecimento', fn($estQ) => 
                            $estQ->where('competencia_manual', 'estadual')->orWhereNull('competencia_manual'));
                    } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
                        $subQ->whereHas('estabelecimento', fn($estQ) => 
                            $estQ->where('municipio_id', $usuario->municipio_id));
                    }
                });
            }
        });

        $processos = $query->orderBy('responsavel_desde', 'desc')->get();

        // Filtrar por competência em memória - APENAS para processos do setor, não os diretamente atribuídos
        if ($usuario->isEstadual()) {
            $processos = $processos->filter(function($p) use ($usuario) {
                if ($p->responsavel_atual_id == $usuario->id) return true;
                try { return $p->estabelecimento->isCompetenciaEstadual(); } catch (\Exception $e) { return false; }
            });
        } elseif ($usuario->isMunicipal()) {
            $processos = $processos->filter(function($p) use ($usuario) {
                if ($p->responsavel_atual_id == $usuario->id) return true;
                try { return $p->estabelecimento->isCompetenciaMunicipal(); } catch (\Exception $e) { return false; }
            });
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
            $isMeuDireto = $proc->responsavel_atual_id == $usuario->id;
            $isDoSetor = $usuario->setor && $proc->setor_atual === $usuario->setor;
            
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
        $filtro = $request->get('filtro', 'todos'); // todos, para_mim, aprovacao, resposta, assinatura, os

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
        // REGRA: Só mostra documentos de processos que estão no setor do usuário ou atribuídos a ele
        $documentos_pendentes_query = ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo')
            ->with(['processo.estabelecimento']);

        $respostas_pendentes_query = DocumentoResposta::where('status', 'pendente')
            ->with(['documentoDigital.processo.estabelecimento', 'documentoDigital.tipoDocumento']);

        // Filtrar por setor/responsável do processo + competência
        if (!$usuario->isAdmin()) {
            // Só processos do meu setor ou atribuídos a mim
            $documentos_pendentes_query->whereHas('processo', function($q) use ($usuario) {
                $q->where(function($sub) use ($usuario) {
                    $sub->where('responsavel_atual_id', $usuario->id);
                    if ($usuario->setor) {
                        $sub->orWhere('setor_atual', $usuario->setor);
                    }
                });
            });
            $respostas_pendentes_query->whereHas('documentoDigital.processo', function($q) use ($usuario) {
                $q->where(function($sub) use ($usuario) {
                    $sub->where('responsavel_atual_id', $usuario->id);
                    if ($usuario->setor) {
                        $sub->orWhere('setor_atual', $usuario->setor);
                    }
                });
            });

            // Filtrar também por competência
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
        }

        $documentos_pendentes = $documentos_pendentes_query->orderBy('created_at', 'desc')->get();
        $respostas_pendentes = $respostas_pendentes_query->orderBy('created_at', 'desc')->get();

        // Filtrar por competência em memória (lógica complexa de atividades)
        if ($usuario->isEstadual()) {
            $documentos_pendentes = $documentos_pendentes->filter(function($d) {
                try { return $d->processo->estabelecimento->isCompetenciaEstadual(); } catch (\Exception $e) { return false; }
            });
            $respostas_pendentes = $respostas_pendentes->filter(function($r) {
                try { return $r->documentoDigital->processo->estabelecimento->isCompetenciaEstadual(); } catch (\Exception $e) { return false; }
            });
        } elseif ($usuario->isMunicipal()) {
            $documentos_pendentes = $documentos_pendentes->filter(function($d) {
                try { return $d->processo->estabelecimento->isCompetenciaMunicipal(); } catch (\Exception $e) { return false; }
            });
            $respostas_pendentes = $respostas_pendentes->filter(function($r) {
                try { return $r->documentoDigital->processo->estabelecimento->isCompetenciaMunicipal(); } catch (\Exception $e) { return false; }
            });
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

        // Combinar TODAS as tarefas (sem filtro, para calcular contadores corretos)
        $todasTarefasCompleta = collect();

        // 1º PRIORIDADE: Ordens de Serviço em aberto
        foreach($ordensServico as $os) {
            $diasRestantes = $os->data_fim ? now()->startOfDay()->diffInDays($os->data_fim->startOfDay(), false) : null;
            $isVencido = $diasRestantes !== null && $diasRestantes < 0;
            $tiposAcao = $os->tiposAcao();
            
            $todasTarefasCompleta->push([
                'tipo' => 'os',
                'id' => $os->id,
                'numero' => $os->numero,
                'titulo' => 'OS #' . $os->numero,
                'subtitulo' => $os->estabelecimento->nome_fantasia ?? 'Sem estabelecimento',
                'tipo_acao' => $tiposAcao && $tiposAcao->count() > 0 ? $tiposAcao->first()->descricao : null,
                'url' => route('admin.ordens-servico.show', $os),
                'dias_restantes' => $diasRestantes,
                'atrasado' => $isVencido,
                'ordem' => 0,
                'data' => $os->created_at->format('d/m/Y H:i'),
                'created_at' => $os->created_at,
                'grupo' => 'para_mim',
            ]);
        }

        // 2º PRIORIDADE: Documentos pendentes de assinatura
        foreach($assinaturas as $ass) {
            $todasTarefasCompleta->push([
                'tipo' => 'assinatura',
                'id' => $ass->documentoDigital->id,
                'titulo' => $ass->documentoDigital->tipoDocumento->nome ?? 'Documento',
                'subtitulo' => $ass->documentoDigital->processo->estabelecimento->nome_fantasia ?? 
                               $ass->documentoDigital->processo->estabelecimento->razao_social ?? 'Estabelecimento',
                'numero_processo' => $ass->documentoDigital->processo->numero_processo ?? null,
                'url' => route('admin.assinatura.assinar', $ass->documentoDigital->id),
                'badge' => null,
                'atrasado' => false,
                'ordem' => 1,
                'data' => $ass->created_at->format('d/m/Y H:i'),
                'created_at' => $ass->created_at,
                'grupo' => 'para_mim',
            ]);
        }

        // 3º PRIORIDADE: Aprovações e respostas agrupadas por processo
        $tarefasOrdenadas = collect($tarefasArray)->sortByDesc('dias_pendente');
        foreach($tarefasOrdenadas as $tarefa) {
            $diasRestantes = $tarefa['is_licenciamento'] ? (5 - $tarefa['dias_pendente']) : null;
            
            if ($tarefa['tipo'] === 'resposta') {
                $todasTarefasCompleta->push([
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
                    'ordem' => 2,
                    'data' => $tarefa['created_at']->format('d/m/Y H:i'),
                    'created_at' => $tarefa['created_at'],
                    'grupo' => 'setor',
                ]);
            } else {
                $todasTarefasCompleta->push([
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
                    'ordem' => 3,
                    'data' => $tarefa['created_at']->format('d/m/Y H:i'),
                    'created_at' => $tarefa['created_at'],
                    'grupo' => 'setor',
                ]);
            }
        }

        // Contadores GLOBAIS (sempre completos, independente do filtro ativo)
        $osCount = $todasTarefasCompleta->where('tipo', 'os')->count();
        $assinaturaCount = $todasTarefasCompleta->where('tipo', 'assinatura')->count();
        $aprovacaoCount = $todasTarefasCompleta->where('tipo', 'aprovacao')->count();
        $respostaCount = $todasTarefasCompleta->where('tipo', 'resposta')->count();
        $contadores = [
            'total' => $todasTarefasCompleta->count(),
            'aprovacao' => $aprovacaoCount,
            'resposta' => $respostaCount,
            'assinatura' => $assinaturaCount,
            'os' => $osCount,
            'para_mim' => $osCount + $assinaturaCount,
            'setor' => $aprovacaoCount + $respostaCount,
        ];

        // Aplicar filtro
        $todasTarefas = match($filtro) {
            'para_mim' => $todasTarefasCompleta->whereIn('tipo', ['os', 'assinatura']),
            'setor' => $todasTarefasCompleta->whereIn('tipo', ['aprovacao', 'resposta']),
            'os' => $todasTarefasCompleta->where('tipo', 'os'),
            'assinatura' => $todasTarefasCompleta->where('tipo', 'assinatura'),
            'aprovacao' => $todasTarefasCompleta->where('tipo', 'aprovacao'),
            'resposta' => $todasTarefasCompleta->where('tipo', 'resposta'),
            default => $todasTarefasCompleta,
        };

        // Ordenar: atrasados primeiro, depois por ordem
        $todasTarefas = $todasTarefas->sortBy([
            ['atrasado', 'desc'],
            ['ordem', 'asc'],
            ['created_at', 'desc'],
        ]);

        $total = $todasTarefas->count();
        $lastPage = max(1, ceil($total / $perPage));
        $tarefasPaginadas = $todasTarefas->forPage($page, $perPage)->values();

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
