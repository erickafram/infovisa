<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\ProcessoAlerta;
use App\Models\DocumentoResposta;
use Illuminate\Http\Request;

class ProcessoController extends Controller
{
    /**
     * Retorna IDs dos estabelecimentos do usuário (próprios e vinculados)
     */
    private function estabelecimentoIdsDoUsuario()
    {
        $usuarioId = auth('externo')->id();
        
        return Estabelecimento::where('usuario_externo_id', $usuarioId)
            ->orWhereHas('usuariosVinculados', function($q) use ($usuarioId) {
                $q->where('usuario_externo_id', $usuarioId);
            })
            ->pluck('id');
    }

    /**
     * Retorna estabelecimentos do usuário (próprios e vinculados)
     */
    private function estabelecimentosDoUsuario()
    {
        $usuarioId = auth('externo')->id();
        
        return Estabelecimento::where('usuario_externo_id', $usuarioId)
            ->orWhereHas('usuariosVinculados', function($q) use ($usuarioId) {
                $q->where('usuario_externo_id', $usuarioId);
            });
    }

    /**
     * Busca documentos obrigatórios para um processo baseado nas atividades exercidas do estabelecimento
     */
    private function buscarDocumentosObrigatoriosParaProcesso($processo)
    {
        $estabelecimento = $processo->estabelecimento;
        $tipoProcessoId = $processo->tipoProcesso->id ?? null;
        
        if (!$tipoProcessoId) {
            return collect();
        }

        // Pega as atividades exercidas do estabelecimento (apenas as marcadas)
        $atividadesExercidas = $estabelecimento->atividades_exercidas ?? [];
        
        if (empty($atividadesExercidas)) {
            return collect();
        }

        // Extrai os códigos CNAE das atividades exercidas
        $codigosCnae = collect($atividadesExercidas)->map(function($atividade) {
            $codigo = is_array($atividade) ? ($atividade['codigo'] ?? null) : $atividade;
            return $codigo ? preg_replace('/[^0-9]/', '', $codigo) : null;
        })->filter()->values()->toArray();

        if (empty($codigosCnae)) {
            return collect();
        }

        // Busca as atividades cadastradas que correspondem aos CNAEs exercidos
        $atividadeIds = \App\Models\Atividade::where('ativo', true)
            ->where(function($query) use ($codigosCnae) {
                foreach ($codigosCnae as $codigo) {
                    $query->orWhere('codigo_cnae', $codigo);
                }
            })
            ->pluck('id');

        if ($atividadeIds->isEmpty()) {
            return collect();
        }

        // Busca as listas de documentos aplicáveis para este tipo de processo
        $query = \App\Models\ListaDocumento::where('ativo', true)
            ->where('tipo_processo_id', $tipoProcessoId)
            ->whereHas('atividades', function($q) use ($atividadeIds) {
                $q->whereIn('atividades.id', $atividadeIds);
            })
            ->with(['tiposDocumentoObrigatorio' => function($q) {
                $q->orderBy('lista_documento_tipo.ordem');
            }]);

        // Filtra por escopo (estadual ou do município do estabelecimento)
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

        // Se não há listas para este tipo de processo e atividades, retorna vazio
        if ($listas->isEmpty()) {
            return collect();
        }

        // Consolida os documentos de todas as listas aplicáveis
        $documentos = collect();
        
        // Busca documentos já enviados neste processo com seus status
        $documentosEnviadosInfo = $processo->documentos
            ->whereNotNull('tipo_documento_obrigatorio_id')
            ->groupBy('tipo_documento_obrigatorio_id')
            ->map(function($docs) {
                // Pega o documento mais recente
                $ultimo = $docs->sortByDesc('created_at')->first();
                return [
                    'status' => $ultimo->status_aprovacao,
                    'id' => $ultimo->id,
                ];
            });

        // Determina o escopo de competência e tipo de setor do estabelecimento
        $escopoCompetencia = $estabelecimento->getEscopoCompetencia();
        $tipoSetorEnum = $estabelecimento->tipo_setor;
        $tipoSetor = $tipoSetorEnum instanceof \App\Enums\TipoSetor ? $tipoSetorEnum->value : ($tipoSetorEnum ?? 'privado');

        // ADICIONA DOCUMENTOS COMUNS PRIMEIRO (filtrados por escopo e tipo_setor)
        // Documentos comuns são aplicados automaticamente quando há listas configuradas
        // para o tipo de processo e atividades do estabelecimento
        $documentosComuns = \App\Models\TipoDocumentoObrigatorio::where('ativo', true)
            ->where('documento_comum', true)
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
            $jaEnviado = in_array($statusEnvio, ['pendente', 'aprovado']);
            
            $documentos->push([
                'id' => $doc->id,
                'nome' => $doc->nome,
                'descricao' => $doc->descricao,
                'obrigatorio' => true, // Documentos comuns são sempre obrigatórios
                'observacao' => null,
                'lista_nome' => 'Documentos Comuns',
                'ja_enviado' => $jaEnviado,
                'status_envio' => $statusEnvio,
                'documento_comum' => true, // Flag para identificar
            ]);
        }

        // ADICIONA DOCUMENTOS ESPECÍFICOS DAS LISTAS (filtrados por escopo e tipo_setor)
        foreach ($listas as $lista) {
            foreach ($lista->tiposDocumentoObrigatorio as $doc) {
                // Filtra por escopo_competencia
                $aplicaEscopo = $doc->escopo_competencia === 'todos' || $doc->escopo_competencia === $escopoCompetencia;
                // Filtra por tipo_setor
                $aplicaTipoSetor = $doc->tipo_setor === 'todos' || $doc->tipo_setor === $tipoSetor;
                
                if (!$aplicaEscopo || !$aplicaTipoSetor) {
                    continue; // Pula documentos que não se aplicam
                }
                
                // Evita duplicatas pelo ID do tipo de documento
                if (!$documentos->contains('id', $doc->id)) {
                    $infoEnviado = $documentosEnviadosInfo->get($doc->id);
                    $statusEnvio = $infoEnviado['status'] ?? null;
                    
                    // Considera como "já enviado" se está pendente ou aprovado
                    // Se rejeitado, permite reenviar
                    $jaEnviado = in_array($statusEnvio, ['pendente', 'aprovado']);
                    
                    $documentos->push([
                        'id' => $doc->id,
                        'nome' => $doc->nome,
                        'descricao' => $doc->descricao,
                        'obrigatorio' => $doc->pivot->obrigatorio,
                        'observacao' => $doc->pivot->observacao,
                        'lista_nome' => $lista->nome,
                        'ja_enviado' => $jaEnviado,
                        'status_envio' => $statusEnvio,
                        'documento_comum' => false,
                    ]);
                } else {
                    // Se já existe, verifica se deve ser obrigatório (se qualquer lista marcar como obrigatório)
                    $documentos = $documentos->map(function($item) use ($doc) {
                        if ($item['id'] === $doc->id && $doc->pivot->obrigatorio) {
                            $item['obrigatorio'] = true;
                        }
                        return $item;
                    });
                }
            }
        }

        // Ordena: documentos comuns primeiro, depois obrigatórios, depois por nome
        return $documentos->sortBy([
            ['documento_comum', 'desc'], // Comuns primeiro
            ['obrigatorio', 'desc'],      // Depois obrigatórios
            ['nome', 'asc'],              // Por fim, ordem alfabética
        ])->values();
    }

    /**
     * Lista todos os alertas dos processos do usuário
     */
    public function alertasIndex(Request $request)
    {
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        $processoIds = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)->pluck('id');
        
        $query = ProcessoAlerta::whereIn('processo_id', $processoIds)
            ->with(['processo.estabelecimento', 'processo.tipoProcesso', 'usuarioCriador']);
        
        // Filtro por status
        if ($request->filled('status')) {
            if ($request->status === 'pendente') {
                $query->where('status', '!=', 'concluido');
            } elseif ($request->status === 'concluido') {
                $query->where('status', 'concluido');
            }
        }
        
        // Filtro por estabelecimento
        if ($request->filled('estabelecimento_id')) {
            $query->whereHas('processo', function($q) use ($request) {
                $q->where('estabelecimento_id', $request->estabelecimento_id);
            });
        }
        
        // Ordenação: vencidos primeiro, depois por data
        $alertas = $query->orderByRaw("CASE WHEN status != 'concluido' AND data_alerta < CURRENT_DATE THEN 0 ELSE 1 END")
            ->orderBy('data_alerta', 'asc')
            ->paginate(15);
        
        // Estatísticas
        $totalAlertas = ProcessoAlerta::whereIn('processo_id', $processoIds)->count();
        $alertasPendentes = ProcessoAlerta::whereIn('processo_id', $processoIds)
            ->where('status', '!=', 'concluido')
            ->count();
        $alertasVencidos = ProcessoAlerta::whereIn('processo_id', $processoIds)
            ->where('status', '!=', 'concluido')
            ->where('data_alerta', '<', now()->toDateString())
            ->count();
        $alertasConcluidos = ProcessoAlerta::whereIn('processo_id', $processoIds)
            ->where('status', 'concluido')
            ->count();
        
        $estatisticas = [
            'total' => $totalAlertas,
            'pendentes' => $alertasPendentes,
            'vencidos' => $alertasVencidos,
            'concluidos' => $alertasConcluidos,
        ];
        
        // Lista de estabelecimentos para filtro
        $estabelecimentos = $this->estabelecimentosDoUsuario()
            ->orderBy('nome_fantasia')
            ->get();
        
        // Documentos pendentes de visualização
        $documentosPendentes = \App\Models\DocumentoDigital::whereIn('processo_id', $processoIds)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->whereDoesntHave('visualizacoes')
            ->with(['processo.estabelecimento', 'tipoDocumento'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Documentos rejeitados que precisam de correção
        $documentosRejeitados = \App\Models\ProcessoDocumento::whereIn('processo_id', $processoIds)
            ->where('status_aprovacao', 'rejeitado')
            ->with(['processo.estabelecimento', 'tipoDocumentoObrigatorio'])
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Documentos com prazo pendente (notificações que precisam de resposta)
        $documentosComPrazo = \App\Models\DocumentoDigital::whereIn('processo_id', $processoIds)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->where('prazo_notificacao', true)
            ->whereNotNull('prazo_iniciado_em')
            ->whereNull('prazo_finalizado_em')
            ->with(['processo.estabelecimento', 'tipoDocumento'])
            ->orderBy('data_vencimento', 'asc')
            ->get()
            ->filter(fn ($doc) => $doc->todasAssinaturasCompletas());
        
        return view('company.alertas.index', compact('alertas', 'estatisticas', 'estabelecimentos', 'documentosPendentes', 'documentosRejeitados', 'documentosComPrazo'));
    }

    public function index(Request $request)
    {
        // IDs dos estabelecimentos do usuário
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $query = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->with(['estabelecimento', 'tipoProcesso']);
        
        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtro por estabelecimento
        if ($request->filled('estabelecimento_id')) {
            $query->where('estabelecimento_id', $request->estabelecimento_id);
        }
        
        // Busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_processo', 'like', "%{$search}%");
            });
        }
        
        $processos = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Estatísticas
        $estatisticas = [
            'total' => Processo::whereIn('estabelecimento_id', $estabelecimentoIds)->count(),
            'em_andamento' => Processo::whereIn('estabelecimento_id', $estabelecimentoIds)->where('status', 'em_andamento')->count(),
            'concluidos' => Processo::whereIn('estabelecimento_id', $estabelecimentoIds)->where('status', 'concluido')->count(),
            'arquivados' => Processo::whereIn('estabelecimento_id', $estabelecimentoIds)->where('status', 'arquivado')->count(),
        ];
        
        // Lista de estabelecimentos para filtro
        $estabelecimentos = $this->estabelecimentosDoUsuario()
            ->orderBy('nome_fantasia')
            ->get();
        
        return view('company.processos.index', compact('processos', 'estatisticas', 'estabelecimentos'));
    }
    
    public function show($id)
    {
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->with(['estabelecimento', 'tipoProcesso', 'documentos.usuarioExterno', 'alertas', 'pastas'])
            ->findOrFail($id);
        
        // Documentos separados por status
        $documentosAprovados = $processo->documentos->where('status_aprovacao', 'aprovado');
        $documentosPendentes = $processo->documentos->where('status_aprovacao', 'pendente');
        
        // IDs de tipo_documento_obrigatorio que já têm documento pendente ou aprovado
        $tiposComDocumentoPendenteOuAprovado = $processo->documentos
            ->whereIn('status_aprovacao', ['pendente', 'aprovado'])
            ->whereNotNull('tipo_documento_obrigatorio_id')
            ->pluck('tipo_documento_obrigatorio_id')
            ->toArray();
        
        // Documentos rejeitados que ainda não foram substituídos (não têm correção pendente)
        $documentosRejeitados = $processo->documentos->where('status_aprovacao', 'rejeitado')
            ->filter(function ($doc) use ($processo, $tiposComDocumentoPendenteOuAprovado) {
                // Se tem tipo_documento_obrigatorio_id e já existe pendente/aprovado para esse tipo, não mostra
                if ($doc->tipo_documento_obrigatorio_id && in_array($doc->tipo_documento_obrigatorio_id, $tiposComDocumentoPendenteOuAprovado)) {
                    return false;
                }
                // Verifica se existe algum documento que substitui este (método antigo)
                return !$processo->documentos->where('documento_substituido_id', $doc->id)->count();
            });
        
        // Documentos digitais da vigilância (assinados e não sigilosos)
        $documentosVigilancia = \App\Models\DocumentoDigital::where('processo_id', $processo->id)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->with(['tipoDocumento', 'usuarioCriador', 'assinaturas', 'respostas.usuarioExterno'])
            ->get()
            ->filter(function ($doc) {
                // Só mostra documentos com todas as assinaturas completas
                return $doc->todasAssinaturasCompletas();
            });

        // Verifica se algum documento de notificação precisa ter o prazo iniciado automaticamente (§1º - 5 dias úteis)
        foreach ($documentosVigilancia as $doc) {
            if ($doc->prazo_notificacao && !$doc->prazo_iniciado_em) {
                $doc->verificarInicioAutomaticoPrazo();
            }
        }
        
        // Mescla documentos da vigilância e aprovados, ordenando por data mais recente
        $todosDocumentos = collect();
        
        // Adiciona documentos da vigilância com tipo identificador
        foreach ($documentosVigilancia as $doc) {
            $todosDocumentos->push([
                'tipo' => 'vigilancia',
                'documento' => $doc,
                'data' => $doc->created_at,
                'pasta_id' => $doc->pasta_id,
            ]);
        }
        
        // Adiciona documentos aprovados com tipo identificador
        foreach ($documentosAprovados as $doc) {
            $todosDocumentos->push([
                'tipo' => 'aprovado',
                'documento' => $doc,
                'data' => $doc->created_at,
                'pasta_id' => $doc->pasta_id,
            ]);
        }
        
        // Ordena por data decrescente (mais recente primeiro)
        $todosDocumentos = $todosDocumentos->sortByDesc('data')->values();
        
        // Alertas do processo
        $alertas = $processo->alertas()->orderBy('data_alerta', 'asc')->get();
        
        // Pastas do processo
        $pastas = $processo->pastas()->orderBy('ordem')->get();

        // Busca documentos obrigatórios baseados nas atividades exercidas
        $documentosObrigatorios = $this->buscarDocumentosObrigatoriosParaProcesso($processo);
        
        // Busca documentos de ajuda vinculados ao tipo de processo
        $documentosAjuda = \App\Models\DocumentoAjuda::ativos()
            ->ordenado()
            ->paraTipoProcesso($processo->tipo)
            ->get();
        
        return view('company.processos.show', compact(
            'processo',
            'documentosAprovados',
            'documentosPendentes',
            'documentosRejeitados',
            'documentosVigilancia',
            'todosDocumentos',
            'alertas',
            'documentosObrigatorios',
            'pastas',
            'documentosAjuda'
        ));
    }

    public function uploadDocumento(Request $request, $id)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($id);

        $request->validate([
            'arquivo' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
            'observacoes' => 'nullable|string|max:500',
            'tipo_documento_obrigatorio_id' => 'nullable|exists:tipos_documento_obrigatorio,id',
            'documento_id' => 'nullable|integer|exists:processo_documentos,id',
        ], [
            'arquivo.required' => 'Selecione um arquivo para enviar.',
            'arquivo.max' => 'O arquivo não pode ter mais de 10MB.',
            'arquivo.mimes' => 'Apenas arquivos PDF, JPG e PNG são permitidos.',
        ]);

        $arquivo = $request->file('arquivo');
        $nomeOriginalUpload = $arquivo->getClientOriginalName();
        $extensao = $arquivo->getClientOriginalExtension();
        $tamanho = $arquivo->getSize();

        // Busca o nome do tipo de documento se informado
        $tipoDocumentoId = $request->tipo_documento_obrigatorio_id;
        $observacoes = $request->observacoes;
        $nomeDocumento = null;
        
        if ($tipoDocumentoId) {
            $tipoDoc = \App\Models\TipoDocumentoObrigatorio::find($tipoDocumentoId);
            if ($tipoDoc) {
                $nomeDocumento = $tipoDoc->nome;
                if (!$observacoes) {
                    $observacoes = $tipoDoc->nome;
                }
            }
        }

        // Define o nome do arquivo: se for documento obrigatório, usa o nome da lista
        // Caso contrário, usa o nome original do arquivo
        if ($nomeDocumento) {
            // Remove caracteres especiais e espaços do nome do documento
            $nomeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nomeDocumento);
            $nomeArquivo = $nomeBase . '_' . time() . '.' . strtolower($extensao);
            $nomeOriginal = $nomeDocumento . '.' . strtolower($extensao);
        } else {
            $nomeArquivo = time() . '_' . uniqid() . '.' . $extensao;
            $nomeOriginal = $nomeOriginalUpload;
        }
        
        // Salva o arquivo
        $caminho = $arquivo->storeAs(
            'processos/' . $processo->id . '/documentos',
            $nomeArquivo,
            'public'
        );

        // Verifica se é um documento obrigatório e se já existe um rejeitado do mesmo tipo
        $documentoExistente = null;
        if ($tipoDocumentoId) {
            $documentoExistente = \App\Models\ProcessoDocumento::where('processo_id', $processo->id)
                ->where('tipo_documento_obrigatorio_id', $tipoDocumentoId)
                ->where('status_aprovacao', 'rejeitado')
                ->first();
        }
        
        // Se foi passado documento_id para substituir, busca o documento rejeitado específico
        if ($request->documento_id) {
            $documentoExistente = \App\Models\ProcessoDocumento::where('processo_id', $processo->id)
                ->where('id', $request->documento_id)
                ->where('status_aprovacao', 'rejeitado')
                ->first();
        }

        if ($documentoExistente) {
            // Guarda histórico da rejeição anterior
            $historicoRejeicao = $documentoExistente->historico_rejeicao ?? [];
            $historicoRejeicao[] = [
                'arquivo_anterior' => $documentoExistente->nome_original,
                'motivo' => $documentoExistente->motivo_rejeicao,
                'rejeitado_em' => $documentoExistente->updated_at->toISOString(),
            ];
            
            // Remove o arquivo antigo do storage
            if ($documentoExistente->caminho && \Storage::disk('public')->exists($documentoExistente->caminho)) {
                \Storage::disk('public')->delete($documentoExistente->caminho);
            }
            
            // Atualiza o documento existente com o novo arquivo
            $documentoExistente->update([
                'nome_arquivo' => $nomeArquivo,
                'nome_original' => $nomeOriginal,
                'caminho' => $caminho,
                'extensao' => strtolower($extensao),
                'tamanho' => $tamanho,
                'status_aprovacao' => 'pendente',
                'motivo_rejeicao' => null,
                'historico_rejeicao' => $historicoRejeicao,
            ]);
            
            $documento = $documentoExistente;
        } else {
            // Cria o registro do documento com status pendente
            $documento = \App\Models\ProcessoDocumento::create([
                'processo_id' => $processo->id,
                'usuario_externo_id' => $usuarioId,
                'tipo_usuario' => 'externo',
                'nome_arquivo' => $nomeArquivo,
                'nome_original' => $nomeOriginal,
                'caminho' => $caminho,
                'extensao' => strtolower($extensao),
                'tamanho' => $tamanho,
                'tipo_documento' => $tipoDocumentoId ? 'documento_obrigatorio' : 'arquivo_externo',
                'tipo_documento_obrigatorio_id' => $tipoDocumentoId,
                'observacoes' => $observacoes,
                'status_aprovacao' => 'pendente',
            ]);
        }

        // Se for requisição AJAX, retorna JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Arquivo enviado com sucesso!',
                'documento' => [
                    'id' => $documento->id,
                    'nome_original' => $nomeOriginal,
                    'tipo_documento_obrigatorio_id' => $tipoDocumentoId,
                ]
            ]);
        }

        return redirect()->route('company.processos.show', $processo->id)
            ->with('success', 'Arquivo enviado com sucesso! Aguarde a aprovação da Vigilância Sanitária.');
    }

    public function downloadDocumento($processoId, $documentoId)
    {
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        $documento = \App\Models\ProcessoDocumento::where('processo_id', $processo->id)
            ->findOrFail($documentoId);

        $path = storage_path('app/public/' . $documento->caminho);
        
        if (!file_exists($path)) {
            return back()->with('error', 'Arquivo não encontrado.');
        }

        return response()->download($path, $documento->nome_original);
    }

    public function visualizarDocumento($processoId, $documentoId)
    {
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        $documento = \App\Models\ProcessoDocumento::where('processo_id', $processo->id)
            ->findOrFail($documentoId);

        $path = storage_path('app/public/' . $documento->caminho);
        
        if (!file_exists($path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        $mimeType = mime_content_type($path);
        
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $documento->nome_original . '"'
        ]);
    }

    public function deleteDocumento($processoId, $documentoId)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        // Só pode excluir documentos pendentes que foram enviados pelo próprio usuário
        $documento = \App\Models\ProcessoDocumento::where('processo_id', $processo->id)
            ->where('usuario_externo_id', $usuarioId)
            ->where('status_aprovacao', 'pendente')
            ->findOrFail($documentoId);

        // Remove o arquivo físico
        $path = storage_path('app/public/' . $documento->caminho);
        if (file_exists($path)) {
            unlink($path);
        }

        // Remove o registro
        $documento->delete();

        return redirect()->route('company.processos.show', $processo->id)
            ->with('success', 'Arquivo excluído com sucesso!');
    }

    /**
     * Reenvia um documento que foi rejeitado
     * Substitui o arquivo do documento rejeitado mantendo o histórico de rejeição
     */
    public function reenviarDocumento(Request $request, $processoId, $documentoId)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        // Busca o documento rejeitado
        $documentoRejeitado = \App\Models\ProcessoDocumento::where('processo_id', $processo->id)
            ->where('status_aprovacao', 'rejeitado')
            ->findOrFail($documentoId);

        $request->validate([
            'arquivo' => 'required|file|max:10240|mimes:pdf',
            'observacoes' => 'nullable|string|max:500',
        ], [
            'arquivo.required' => 'Selecione um arquivo para enviar.',
            'arquivo.max' => 'O arquivo não pode ter mais de 10MB.',
            'arquivo.mimes' => 'Apenas arquivos PDF são permitidos.',
        ]);

        $arquivo = $request->file('arquivo');
        $nomeOriginal = $arquivo->getClientOriginalName();
        $extensao = $arquivo->getClientOriginalExtension();
        $tamanho = $arquivo->getSize();
        $nomeArquivo = time() . '_' . uniqid() . '.' . $extensao;
        
        // Remove o arquivo antigo se existir
        if ($documentoRejeitado->caminho && \Storage::disk('public')->exists($documentoRejeitado->caminho)) {
            \Storage::disk('public')->delete($documentoRejeitado->caminho);
        }
        
        // Salva o novo arquivo
        $caminho = $arquivo->storeAs(
            'processos/' . $processo->id . '/documentos',
            $nomeArquivo,
            'public'
        );

        // Guarda o histórico de rejeição antes de atualizar
        $historicoRejeicao = $documentoRejeitado->historico_rejeicao ?? [];
        $historicoRejeicao[] = [
            'motivo' => $documentoRejeitado->motivo_rejeicao,
            'arquivo_anterior' => $documentoRejeitado->nome_original,
            'rejeitado_em' => $documentoRejeitado->updated_at->toISOString(),
            'rejeitado_por' => $documentoRejeitado->aprovado_por,
        ];

        // Atualiza o documento existente com o novo arquivo
        $documentoRejeitado->update([
            'nome_arquivo' => $nomeArquivo,
            'nome_original' => $nomeOriginal,
            'caminho' => $caminho,
            'extensao' => strtolower($extensao),
            'tamanho' => $tamanho,
            'observacoes' => $request->observacoes,
            'status_aprovacao' => 'pendente',
            'motivo_rejeicao' => null,
            'aprovado_por' => null,
            'aprovado_em' => null,
            'tentativas_envio' => $documentoRejeitado->tentativas_envio + 1,
            'historico_rejeicao' => $historicoRejeicao,
        ]);

        return redirect()->route('company.processos.show', $processo->id)
            ->with('success', 'Arquivo reenviado com sucesso! Aguarde a aprovação da Vigilância Sanitária.');
    }

    /**
     * Visualiza um documento digital da vigilância
     * Registra a visualização para início da contagem de prazo (§1º)
     */
    public function visualizarDocumentoDigital($processoId, $documentoId)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        $documento = \App\Models\DocumentoDigital::where('processo_id', $processo->id)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->findOrFail($documentoId);

        // Verifica se todas as assinaturas estão completas
        if (!$documento->todasAssinaturasCompletas()) {
            abort(403, 'Documento ainda não está disponível.');
        }

        // Registra a visualização (isso também inicia o prazo se for documento de notificação)
        $documento->registrarVisualizacao(
            $usuarioId,
            request()->ip(),
            request()->userAgent()
        );

        // Retorna o PDF
        if (!$documento->arquivo_pdf || !file_exists(storage_path('app/public/' . $documento->arquivo_pdf))) {
            abort(404, 'Arquivo não encontrado.');
        }

        $path = storage_path('app/public/' . $documento->arquivo_pdf);
        
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $documento->numero_documento . '.pdf"'
        ]);
    }

    /**
     * Download de documento digital da vigilância
     */
    public function downloadDocumentoDigital($processoId, $documentoId)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        $documento = \App\Models\DocumentoDigital::where('processo_id', $processo->id)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->findOrFail($documentoId);

        // Verifica se todas as assinaturas estão completas
        if (!$documento->todasAssinaturasCompletas()) {
            abort(403, 'Documento ainda não está disponível.');
        }

        // Registra a visualização
        $documento->registrarVisualizacao(
            $usuarioId,
            request()->ip(),
            request()->userAgent()
        );

        if (!$documento->arquivo_pdf || !file_exists(storage_path('app/public/' . $documento->arquivo_pdf))) {
            return back()->with('error', 'Arquivo não encontrado.');
        }

        $path = storage_path('app/public/' . $documento->arquivo_pdf);
        $nomeArquivo = $documento->numero_documento . '_' . ($documento->tipoDocumento->nome ?? 'documento') . '.pdf';
        
        return response()->download($path, $nomeArquivo);
    }

    /**
     * Marca um alerta do processo como concluído/resolvido
     */
    public function concluirAlerta($processoId, $alertaId)
    {
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        $alerta = ProcessoAlerta::where('processo_id', $processo->id)
            ->where('status', '!=', 'concluido')
            ->findOrFail($alertaId);

        $alerta->marcarComoConcluido();

        return redirect()->route('company.processos.show', $processo->id)
            ->with('success', 'Alerta marcado como resolvido!');
    }

    /**
     * Envia uma resposta a um documento digital (ex: resposta a notificação sanitária)
     * Se existir uma resposta rejeitada, substitui mantendo o histórico
     */
    public function enviarRespostaDocumento(Request $request, $processoId, $documentoId)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        $documento = \App\Models\DocumentoDigital::where('processo_id', $processo->id)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->findOrFail($documentoId);

        // Verifica se o tipo de documento permite resposta
        if (!$documento->permiteResposta()) {
            return back()->with('error', 'Este tipo de documento não permite envio de resposta.');
        }

        $request->validate([
            'arquivo' => 'required|file|max:10240|mimes:pdf',
            'observacoes' => 'nullable|string|max:1000',
        ], [
            'arquivo.required' => 'Selecione um arquivo PDF para enviar.',
            'arquivo.max' => 'O arquivo não pode ter mais de 10MB.',
            'arquivo.mimes' => 'Apenas arquivos PDF são permitidos.',
        ]);

        $arquivo = $request->file('arquivo');
        $nomeOriginal = $arquivo->getClientOriginalName();
        $extensao = $arquivo->getClientOriginalExtension();
        $tamanho = $arquivo->getSize();
        $nomeArquivo = time() . '_resposta_' . uniqid() . '.' . $extensao;
        
        // Salva o arquivo
        $caminho = $arquivo->storeAs(
            'processos/' . $processo->id . '/respostas',
            $nomeArquivo,
            'public'
        );

        // Verifica se existe uma resposta rejeitada para substituir
        $respostaRejeitada = DocumentoResposta::where('documento_digital_id', $documento->id)
            ->where('usuario_externo_id', $usuarioId)
            ->where('status', 'rejeitado')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($respostaRejeitada) {
            // Guarda o histórico de rejeição antes de atualizar
            $historicoRejeicao = $respostaRejeitada->historico_rejeicao ?? [];
            $historicoRejeicao[] = [
                'motivo' => $respostaRejeitada->motivo_rejeicao,
                'arquivo_anterior' => $respostaRejeitada->nome_original,
                'rejeitado_em' => $respostaRejeitada->avaliado_em ? $respostaRejeitada->avaliado_em->toISOString() : now()->toISOString(),
                'rejeitado_por' => $respostaRejeitada->avaliado_por,
            ];

            // Remove o arquivo antigo se existir
            if ($respostaRejeitada->caminho && \Storage::disk('public')->exists($respostaRejeitada->caminho)) {
                \Storage::disk('public')->delete($respostaRejeitada->caminho);
            }

            // Atualiza a resposta existente com o novo arquivo
            $respostaRejeitada->update([
                'nome_arquivo' => $nomeArquivo,
                'nome_original' => $nomeOriginal,
                'caminho' => $caminho,
                'extensao' => strtolower($extensao),
                'tamanho' => $tamanho,
                'observacoes' => $request->observacoes,
                'status' => 'pendente',
                'motivo_rejeicao' => null,
                'avaliado_por' => null,
                'avaliado_em' => null,
                'historico_rejeicao' => $historicoRejeicao,
            ]);
        } else {
            // Cria o registro da resposta
            DocumentoResposta::create([
                'documento_digital_id' => $documento->id,
                'usuario_externo_id' => $usuarioId,
                'nome_arquivo' => $nomeArquivo,
                'nome_original' => $nomeOriginal,
                'caminho' => $caminho,
                'extensao' => strtolower($extensao),
                'tamanho' => $tamanho,
                'observacoes' => $request->observacoes,
                'status' => 'pendente',
            ]);
        }

        return redirect()->route('company.processos.show', $processo->id)
            ->with('success', 'Resposta enviada com sucesso! Aguarde a análise da Vigilância Sanitária.');
    }

    /**
     * Download de uma resposta a documento digital
     */
    public function downloadRespostaDocumento($processoId, $documentoId, $respostaId)
    {
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        $documento = \App\Models\DocumentoDigital::where('processo_id', $processo->id)
            ->findOrFail($documentoId);

        $resposta = DocumentoResposta::where('documento_digital_id', $documento->id)
            ->findOrFail($respostaId);

        $path = storage_path('app/public/' . $resposta->caminho);
        
        if (!file_exists($path)) {
            return back()->with('error', 'Arquivo não encontrado.');
        }

        return response()->download($path, $resposta->nome_original);
    }

    /**
     * Visualiza uma resposta a documento digital
     */
    public function visualizarRespostaDocumento($processoId, $documentoId, $respostaId)
    {
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        $documento = \App\Models\DocumentoDigital::where('processo_id', $processo->id)
            ->findOrFail($documentoId);

        $resposta = DocumentoResposta::where('documento_digital_id', $documento->id)
            ->findOrFail($respostaId);

        $path = storage_path('app/public/' . $resposta->caminho);
        
        if (!file_exists($path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $resposta->nome_original . '"'
        ]);
    }

    /**
     * Exclui uma resposta a documento digital (apenas se pendente)
     */
    public function excluirRespostaDocumento($processoId, $documentoId, $respostaId)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);

        $documento = \App\Models\DocumentoDigital::where('processo_id', $processo->id)
            ->findOrFail($documentoId);

        $resposta = DocumentoResposta::where('documento_digital_id', $documento->id)
            ->where('usuario_externo_id', $usuarioId)
            ->where('status', 'pendente')
            ->findOrFail($respostaId);

        // Remove o arquivo físico
        if ($resposta->caminho && \Illuminate\Support\Facades\Storage::disk('public')->exists($resposta->caminho)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($resposta->caminho);
        }

        $resposta->delete();

        return redirect()->route('company.processos.show', $processo->id)
            ->with('success', 'Resposta excluída com sucesso!');
    }

    /**
     * Gera o PDF do protocolo de abertura do processo
     */
    public function protocoloAbertura($id)
    {
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->with(['estabelecimento.municipioRelacionado', 'tipoProcesso'])
            ->findOrFail($id);

        $estabelecimento = $processo->estabelecimento;

        // Busca a logomarca do município ou configuração do sistema
        $logomarca = null;
        $municipioObj = $estabelecimento->municipioRelacionado;
        if ($municipioObj && $municipioObj->logomarca) {
            $logomarca = $municipioObj->logomarca;
        } else {
            $config = \App\Models\ConfiguracaoSistema::first();
            if ($config && $config->logomarca) {
                $logomarca = $config->logomarca;
            }
        }

        $data = [
            'processo' => $processo,
            'estabelecimento' => $estabelecimento,
            'logomarca' => $logomarca,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('company.processos.protocolo-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        $nomeArquivo = 'Protocolo_' . str_replace('/', '-', $processo->numero_processo) . '.pdf';

        return $pdf->stream($nomeArquivo);
    }

    /**
     * Visualiza um documento de ajuda vinculado ao tipo de processo
     */
    public function visualizarDocumentoAjuda($processoId, $documentoId)
    {
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        // Verifica se o processo pertence ao usuário
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($processoId);
        
        // Busca o documento de ajuda
        $documento = \App\Models\DocumentoAjuda::ativos()
            ->paraTipoProcesso($processo->tipo)
            ->findOrFail($documentoId);
        
        // Verifica se o arquivo existe
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($documento->arquivo)) {
            abort(404, 'Arquivo não encontrado.');
        }
        
        $caminho = \Illuminate\Support\Facades\Storage::disk('local')->path($documento->arquivo);
        
        return response()->file($caminho, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $documento->nome_original . '"',
        ]);
    }
}
