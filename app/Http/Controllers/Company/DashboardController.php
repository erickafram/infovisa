<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\ProcessoAlerta;
use App\Models\ProcessoDocumento;
use App\Models\DocumentoDigital;
use App\Models\ListaDocumento;
use App\Models\TipoDocumentoObrigatorio;
use App\Models\Atividade;

class DashboardController extends Controller
{
    public function index()
    {
        $usuarioId = auth('externo')->id();
        
        // Buscar estabelecimentos do usuário (próprios e vinculados)
        $estabelecimentos = Estabelecimento::where('usuario_externo_id', $usuarioId)
            ->orWhereHas('usuariosVinculados', function($q) use ($usuarioId) {
                $q->where('usuario_externo_id', $usuarioId);
            })
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
            ->with(['estabelecimento', 'tipoProcesso', 'documentos'])
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
        
        // Processos em andamento com documentos obrigatórios pendentes
        $processosComDocsPendentes = $this->calcularProcessosComDocumentosPendentes($processos);
        
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
            ->filter(fn ($doc) => $doc->todasAssinaturasCompletas());
        
        // Documentos rejeitados que precisam de correção pelo usuário externo
        $documentosRejeitados = ProcessoDocumento::whereIn('processo_id', $processoIds)
            ->where('status_aprovacao', 'rejeitado')
            ->with(['processo.estabelecimento', 'tipoDocumentoObrigatorio'])
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Documentos com prazo pendente (notificações que precisam de resposta)
        $documentosComPrazo = DocumentoDigital::whereIn('processo_id', $processoIds)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->where('prazo_notificacao', true)
            ->whereNotNull('prazo_iniciado_em')
            ->whereNull('prazo_finalizado_em')
            ->with(['processo.estabelecimento', 'tipoDocumento'])
            ->orderBy('data_vencimento', 'asc')
            ->get()
            ->filter(fn ($doc) => $doc->todasAssinaturasCompletas());
        
        return view('company.dashboard', compact(
            'estatisticasEstabelecimentos',
            'estatisticasProcessos',
            'ultimosEstabelecimentos',
            'ultimosProcessos',
            'alertasPendentes',
            'documentosPendentesVisualizacao',
            'documentosRejeitados',
            'documentosComPrazo',
            'processosComDocsPendentes'
        ));
    }

    /**
     * Calcula o status de documentos obrigatórios para processos em andamento
     */
    private function calcularProcessosComDocumentosPendentes($processos)
    {
        $resultado = collect();
        
        // Filtra apenas processos em andamento
        $processosEmAndamento = $processos->where('status', 'em_andamento');
        
        foreach ($processosEmAndamento as $processo) {
            $documentosObrigatorios = $this->buscarDocumentosObrigatoriosParaProcesso($processo);
            
            $totalObrigatorios = $documentosObrigatorios->where('obrigatorio', true)->count();
            $enviadosOuAprovados = $documentosObrigatorios->where('obrigatorio', true)
                ->whereIn('status_envio', ['pendente', 'aprovado'])->count();
            $aprovados = $documentosObrigatorios->where('obrigatorio', true)
                ->where('status_envio', 'aprovado')->count();
            
            $percentual = $totalObrigatorios > 0 ? round(($enviadosOuAprovados / $totalObrigatorios) * 100) : 0;
            $faltam = $totalObrigatorios - $enviadosOuAprovados;
            $todosAprovados = ($aprovados == $totalObrigatorios && $totalObrigatorios > 0);
            
            // Adiciona ao resultado apenas se tem documentos obrigatórios e não estão todos aprovados
            if ($totalObrigatorios > 0 && !$todosAprovados) {
                $resultado->push([
                    'processo' => $processo,
                    'total' => $totalObrigatorios,
                    'enviados' => $enviadosOuAprovados,
                    'aprovados' => $aprovados,
                    'faltam' => $faltam,
                    'percentual' => $percentual,
                    'todos_aprovados' => $todosAprovados,
                ]);
            }
        }
        
        // Ordena por menor percentual (mais urgentes primeiro)
        return $resultado->sortBy('percentual')->values();
    }

    /**
     * Busca documentos obrigatórios para um processo
     */
    private function buscarDocumentosObrigatoriosParaProcesso($processo)
    {
        $estabelecimento = $processo->estabelecimento;
        $tipoProcesso = $processo->tipoProcesso;
        $tipoProcessoId = $tipoProcesso->id ?? null;
        
        if (!$tipoProcessoId) {
            return collect();
        }

        $isProcessoEspecial = $tipoProcesso && in_array($tipoProcesso->codigo, ['projeto_arquitetonico', 'analise_rotulagem']);
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
                $atividadeIds = Atividade::where('ativo', true)
                    ->where(function($query) use ($codigosCnae) {
                        foreach ($codigosCnae as $codigo) {
                            $query->orWhere('codigo_cnae', $codigo);
                        }
                    })
                    ->pluck('id');
            }
        }

        $query = ListaDocumento::where('ativo', true)
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
        
        $documentosEnviadosInfo = $processo->documentos
            ->whereNotNull('tipo_documento_obrigatorio_id')
            ->groupBy('tipo_documento_obrigatorio_id')
            ->map(function($docs) {
                $ultimo = $docs->sortByDesc('created_at')->first();
                return [
                    'status' => $ultimo->status_aprovacao,
                    'id' => $ultimo->id,
                ];
            });

        $escopoCompetencia = $estabelecimento->getEscopoCompetencia();
        $tipoSetorEnum = $estabelecimento->tipo_setor;
        $tipoSetor = $tipoSetorEnum instanceof \App\Enums\TipoSetor ? $tipoSetorEnum->value : ($tipoSetorEnum ?? 'privado');

        // Documentos comuns
        $documentosComuns = TipoDocumentoObrigatorio::where('ativo', true)
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
            $statusEnvio = $infoEnviado['status'] ?? null;
            
            $documentos->push([
                'id' => $doc->id,
                'nome' => $doc->nome,
                'obrigatorio' => true,
                'status_envio' => $statusEnvio,
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
                    $statusEnvio = $infoEnviado['status'] ?? null;
                    $isObrigatorio = $doc->pivot->obrigatorio ?? true;
                    
                    $documentos->push([
                        'id' => $doc->id,
                        'nome' => $doc->nome,
                        'obrigatorio' => $isObrigatorio,
                        'status_envio' => $statusEnvio,
                    ]);
                }
            }
        }

        return $documentos;
    }
}
