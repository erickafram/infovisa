<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\Estabelecimento;
use App\Models\TipoProcesso;
use App\Models\ProcessoDocumento;
use App\Models\ProcessoAcompanhamento;
use App\Models\ProcessoDesignacao;
use App\Models\ProcessoAlerta;
use App\Models\ProcessoEvento;
use App\Models\ModeloDocumento;
use App\Models\UsuarioInterno;
use App\Models\DocumentoResposta;
use App\Models\DocumentoDigital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ProcessoController extends Controller
{
    /**
     * Busca documentos obrigatórios para um processo baseado nas atividades exercidas do estabelecimento
     */
    private function buscarDocumentosObrigatoriosParaProcesso($processo)
    {
        $estabelecimento = $processo->estabelecimento;
        $tipoProcessoId = $processo->tipoProcesso->id ?? null;
        
        if (!$tipoProcessoId || !$estabelecimento) {
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

        // Consolida os documentos de todas as listas aplicáveis
        $documentos = collect();
        
        // Busca documentos já enviados neste processo com seus status
        $documentosEnviadosInfo = $processo->documentos
            ->whereNotNull('tipo_documento_obrigatorio_id')
            ->groupBy('tipo_documento_obrigatorio_id')
            ->map(function($docs) {
                // Pega o documento mais recente
                $docRecente = $docs->sortByDesc('created_at')->first();
                return [
                    'status' => $docRecente->status_aprovacao,
                    'documento' => $docRecente,
                ];
            });
        
        // ADICIONA DOCUMENTOS COMUNS PRIMEIRO
        $documentosComuns = \App\Models\TipoDocumentoObrigatorio::where('ativo', true)
            ->where('documento_comum', true)
            ->ordenado()
            ->get();
        
        foreach ($documentosComuns as $doc) {
            $infoEnviado = $documentosEnviadosInfo->get($doc->id);
            
            $documentos->push([
                'id' => $doc->id,
                'nome' => $doc->nome,
                'descricao' => $doc->descricao,
                'obrigatorio' => true, // Documentos comuns são sempre obrigatórios
                'ordem' => 0, // Ordem 0 para aparecer primeiro
                'observacao' => null,
                'lista_nome' => 'Documentos Comuns',
                'status' => $infoEnviado['status'] ?? null,
                'documento_enviado' => $infoEnviado['documento'] ?? null,
                'documento_comum' => true, // Flag para identificar
            ]);
        }
        
        // ADICIONA DOCUMENTOS ESPECÍFICOS DAS LISTAS
        foreach ($listas as $lista) {
            foreach ($lista->tiposDocumentoObrigatorio as $doc) {
                // Evita duplicatas pelo ID do tipo de documento
                if (!$documentos->contains('id', $doc->id)) {
                    $infoEnviado = $documentosEnviadosInfo->get($doc->id);
                    
                    $documentos->push([
                        'id' => $doc->id,
                        'nome' => $doc->nome,
                        'descricao' => $doc->descricao,
                        'obrigatorio' => $doc->pivot->obrigatorio,
                        'ordem' => $doc->pivot->ordem,
                        'observacao' => $doc->pivot->observacao,
                        'lista_nome' => $lista->nome,
                        'status' => $infoEnviado['status'] ?? null,
                        'documento_enviado' => $infoEnviado['documento'] ?? null,
                        'documento_comum' => false,
                    ]);
                } else {
                    // Se já existe, verifica se deve ser obrigatório
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
            ['nome', 'asc'],              // Por fim, alfabética
        ])->values();
    }

    /**
     * Exibe todos os processos do sistema com filtros
     */
    public function indexGeral(Request $request)
    {
        $usuario = auth('interno')->user();
        $query = Processo::with(['estabelecimento', 'usuario', 'tipoProcesso']);

        // ✅ FILTRO AUTOMÁTICO POR MUNICÍPIO/COMPETÊNCIA
        if (!$usuario->isAdmin()) {
            if ($usuario->isMunicipal() && $usuario->municipio_id) {
                // Gestor/Técnico Municipal: vê apenas processos do próprio município
                // A verificação de competência será feita depois, pois depende do método isCompetenciaEstadual()
                $query->whereHas('estabelecimento', function ($q) use ($usuario) {
                    $q->where('municipio_id', $usuario->municipio_id);
                });
            }
        }

        // Filtro por número do processo
        if ($request->filled('numero_processo')) {
            $query->where('numero_processo', 'like', '%' . $request->numero_processo . '%');
        }

        // Filtro por estabelecimento (nome ou CNPJ)
        if ($request->filled('estabelecimento')) {
            $query->whereHas('estabelecimento', function ($q) use ($request) {
                $q->where('nome_fantasia', 'like', '%' . $request->estabelecimento . '%')
                  ->orWhere('razao_social', 'like', '%' . $request->estabelecimento . '%')
                  ->orWhere('cnpj', 'like', '%' . $request->estabelecimento . '%');
            });
        }

        // Filtro por tipo de processo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por ano
        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }

        // Filtro por responsável/setor
        if ($request->filled('responsavel')) {
            switch ($request->responsavel) {
                case 'meus':
                    $query->where('responsavel_atual_id', $usuario->id);
                    break;
                case 'meu_setor':
                    if ($usuario->setor) {
                        $query->where('setor_atual', $usuario->setor);
                    }
                    break;
                case 'nao_atribuido':
                    $query->whereNull('responsavel_atual_id')->whereNull('setor_atual');
                    break;
            }
        }

        // Ordenação
        $ordenacao = $request->get('ordenacao', 'recentes');
        switch ($ordenacao) {
            case 'antigos':
                $query->orderBy('created_at', 'asc');
                break;
            case 'numero':
                $query->orderBy('ano', 'desc')->orderBy('numero_sequencial', 'desc');
                break;
            case 'estabelecimento':
                $query->join('estabelecimentos', 'processos.estabelecimento_id', '=', 'estabelecimentos.id')
                      ->orderBy('estabelecimentos.nome_fantasia', 'asc')
                      ->select('processos.*');
                break;
            default: // recentes
                $query->orderBy('created_at', 'desc');
        }

        $processos = $query->with('responsavelAtual')->paginate(20)->withQueryString();

        // ✅ FILTRO ADICIONAL POR COMPETÊNCIA (após paginação)
        if (!$usuario->isAdmin()) {
            $processos->getCollection()->transform(function ($processo) use ($usuario) {
                // Carrega o relacionamento se não estiver carregado
                if (!$processo->relationLoaded('estabelecimento')) {
                    $processo->load('estabelecimento');
                }
                return $processo;
            });
            
            // Filtra por competência
            if ($usuario->isEstadual()) {
                // Usuário estadual: só vê processos de competência estadual
                $processos->setCollection(
                    $processos->getCollection()->filter(function ($processo) {
                        return $processo->estabelecimento && $processo->estabelecimento->isCompetenciaEstadual();
                    })
                );
            } elseif ($usuario->isMunicipal()) {
                // Usuário municipal: só vê processos de competência municipal
                $processos->setCollection(
                    $processos->getCollection()->filter(function ($processo) {
                        return $processo->estabelecimento && !$processo->estabelecimento->isCompetenciaEstadual();
                    })
                );
            }
        }

        // Dados para filtros
        $tiposProcesso = TipoProcesso::ativos()
            ->paraUsuario(auth('interno')->user())
            ->ordenado()
            ->get();
        $statusDisponiveis = Processo::statusDisponiveis();
        $anos = Processo::select('ano')->distinct()->orderBy('ano', 'desc')->pluck('ano');

        // Busca IDs de processos com documentos pendentes de aprovação
        $processosComDocsPendentes = ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo')
            ->pluck('processo_id')
            ->unique();
        
        $processosComRespostasPendentes = DocumentoResposta::where('documento_respostas.status', 'pendente')
            ->join('documentos_digitais', 'documento_respostas.documento_digital_id', '=', 'documentos_digitais.id')
            ->pluck('documentos_digitais.processo_id')
            ->unique();
        
        $processosComPendencias = $processosComDocsPendentes->merge($processosComRespostasPendentes)->unique();

        // Calcula status de documentos obrigatórios para cada processo
        $statusDocsObrigatorios = [];
        foreach ($processos as $processo) {
            $docsObrigatorios = $this->buscarDocumentosObrigatoriosParaProcesso($processo);
            if ($docsObrigatorios->count() > 0) {
                $totalOk = $docsObrigatorios->where('status', 'aprovado')->count();
                $totalPendente = $docsObrigatorios->where('status', 'pendente')->count();
                $totalRejeitado = $docsObrigatorios->where('status', 'rejeitado')->count();
                $totalNaoEnviado = $docsObrigatorios->whereNull('status')->count();
                $total = $docsObrigatorios->count();
                
                $statusDocsObrigatorios[$processo->id] = [
                    'total' => $total,
                    'ok' => $totalOk,
                    'pendente' => $totalPendente,
                    'rejeitado' => $totalRejeitado,
                    'nao_enviado' => $totalNaoEnviado,
                    'completo' => $totalOk === $total,
                ];
            }
        }

        // Filtro por documentos obrigatórios (completos/pendentes)
        if ($request->filled('docs_obrigatorios')) {
            $filtroDocsObrigatorios = $request->docs_obrigatorios;
            $processos->setCollection(
                $processos->getCollection()->filter(function ($processo) use ($statusDocsObrigatorios, $filtroDocsObrigatorios) {
                    $status = $statusDocsObrigatorios[$processo->id] ?? null;
                    
                    if ($filtroDocsObrigatorios === 'completos') {
                        // Mostra apenas processos com docs completos (todos aprovados)
                        return $status && $status['completo'];
                    } elseif ($filtroDocsObrigatorios === 'pendentes') {
                        // Mostra processos com docs pendentes (não completos ou sem docs obrigatórios definidos)
                        return !$status || !$status['completo'];
                    }
                    
                    return true;
                })
            );
        }

        return view('processos.index', compact('processos', 'tiposProcesso', 'statusDisponiveis', 'anos', 'processosComPendencias', 'statusDocsObrigatorios'));
    }

    /**
     * Exibe lista de documentos pendentes de aprovação
     */
    public function documentosPendentes(Request $request)
    {
        $usuario = auth('interno')->user();
        
        // Query para ProcessoDocumento pendentes
        $docsQuery = ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo')
            ->with(['processo.estabelecimento', 'usuarioExterno']);
        
        // Query para DocumentoResposta pendentes
        $respostasQuery = DocumentoResposta::where('status', 'pendente')
            ->with(['documentoDigital.processo.estabelecimento', 'documentoDigital.tipoDocumento', 'usuarioExterno']);
        
        // Filtrar por competência do usuário
        if (!$usuario->isAdmin()) {
            if ($usuario->isEstadual()) {
                // Estadual - filtra por competência manual ou null
                $docsQuery->whereHas('processo.estabelecimento', function($q) {
                    $q->where('competencia_manual', 'estadual')
                      ->orWhereNull('competencia_manual');
                });
                $respostasQuery->whereHas('documentoDigital.processo.estabelecimento', function($q) {
                    $q->where('competencia_manual', 'estadual')
                      ->orWhereNull('competencia_manual');
                });
            } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
                $docsQuery->whereHas('processo.estabelecimento', function($q) use ($usuario) {
                    $q->where('municipio_id', $usuario->municipio_id);
                });
                $respostasQuery->whereHas('documentoDigital.processo.estabelecimento', function($q) use ($usuario) {
                    $q->where('municipio_id', $usuario->municipio_id);
                });
            }
        }
        
        // Filtro por estabelecimento
        if ($request->filled('estabelecimento')) {
            $termo = $request->estabelecimento;
            $docsQuery->whereHas('processo.estabelecimento', function($q) use ($termo) {
                $q->where('nome_fantasia', 'like', "%{$termo}%")
                  ->orWhere('razao_social', 'like', "%{$termo}%")
                  ->orWhere('cnpj', 'like', "%{$termo}%");
            });
            $respostasQuery->whereHas('documentoDigital.processo.estabelecimento', function($q) use ($termo) {
                $q->where('nome_fantasia', 'like', "%{$termo}%")
                  ->orWhere('razao_social', 'like', "%{$termo}%")
                  ->orWhere('cnpj', 'like', "%{$termo}%");
            });
        }
        
        $documentosPendentes = $docsQuery->orderBy('created_at', 'desc')->get();
        $respostasPendentes = $respostasQuery->orderBy('created_at', 'desc')->get();
        
        // Filtrar por competência em memória (lógica complexa baseada em atividades)
        if ($usuario->isEstadual()) {
            $documentosPendentes = $documentosPendentes->filter(fn($d) => $d->processo->estabelecimento->isCompetenciaEstadual());
            $respostasPendentes = $respostasPendentes->filter(fn($r) => $r->documentoDigital->processo->estabelecimento->isCompetenciaEstadual());
        } elseif ($usuario->isMunicipal()) {
            $documentosPendentes = $documentosPendentes->filter(fn($d) => $d->processo->estabelecimento->isCompetenciaMunicipal());
            $respostasPendentes = $respostasPendentes->filter(fn($r) => $r->documentoDigital->processo->estabelecimento->isCompetenciaMunicipal());
        }
        
        return view('processos.documentos-pendentes', compact('documentosPendentes', 'respostasPendentes'));
    }

    /**
     * Exibe a lista de processos do estabelecimento
     */
    public function index($estabelecimentoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        
        $processos = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->with(['usuario', 'tipoProcesso'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Busca tipos de processo ativos e ordenados (filtrados por usuário)
        $tiposProcesso = TipoProcesso::ativos()
            ->paraUsuario(auth('interno')->user())
            ->ordenado()
            ->get();
        
        return view('estabelecimentos.processos.index', compact('estabelecimento', 'processos', 'tiposProcesso'));
    }

    /**
     * Exibe formulário para criar novo processo
     */
    public function create($estabelecimentoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $tipos = Processo::tipos();
        
        return view('estabelecimentos.processos.create', compact('estabelecimento', 'tipos'));
    }

    /**
     * Salva novo processo
     */
    public function store(Request $request, $estabelecimentoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        
        // Busca códigos dos tipos ativos (filtrados por usuário)
        $codigosAtivos = TipoProcesso::ativos()
            ->paraUsuario(auth('interno')->user())
            ->pluck('codigo')
            ->toArray();
        
        $validated = $request->validate([
            'tipo' => 'required|in:' . implode(',', $codigosAtivos),
            'observacoes' => 'nullable|string|max:1000',
        ]);
        
        // Busca o tipo de processo para verificar se é anual
        $tipoProcesso = TipoProcesso::where('codigo', $validated['tipo'])->first();
        
        // Verifica se é processo anual e se já existe no ano atual
        if ($tipoProcesso && $tipoProcesso->anual) {
            $anoAtual = date('Y');
            $jaExiste = Processo::where('estabelecimento_id', $estabelecimento->id)
                ->where('tipo', $validated['tipo'])
                ->where('ano', $anoAtual)
                ->exists();
            
            if ($jaExiste) {
                return redirect()
                    ->back()
                    ->with('error', 'Já existe um processo de ' . $tipoProcesso->nome . ' para o ano ' . $anoAtual . ' neste estabelecimento.');
            }
        }

        // Verifica se é processo único por estabelecimento e se já existe (em qualquer ano)
        if ($tipoProcesso && $tipoProcesso->unico_por_estabelecimento) {
            $jaExisteUnico = Processo::where('estabelecimento_id', $estabelecimento->id)
                ->where('tipo', $validated['tipo'])
                ->exists();
            
            if ($jaExisteUnico) {
                return redirect()
                    ->back()
                    ->with('error', 'Este estabelecimento já possui um processo do tipo ' . $tipoProcesso->nome . '. Este tipo de processo é único e não pode ser aberto novamente.');
            }
        }
        
        // Usa transaction para evitar duplicação de número
        try {
            $processo = \DB::transaction(function () use ($estabelecimento, $validated) {
                // Gera número do processo dentro da transaction
                $numeroData = Processo::gerarNumeroProcesso();
                
                // Cria o processo
                return Processo::create([
                    'estabelecimento_id' => $estabelecimento->id,
                    'usuario_id' => Auth::guard('interno')->user()->id,
                    'tipo' => $validated['tipo'],
                    'ano' => $numeroData['ano'],
                    'numero_sequencial' => $numeroData['numero_sequencial'],
                    'numero_processo' => $numeroData['numero_processo'],
                    'status' => 'aberto',
                    'observacoes' => $validated['observacoes'] ?? null,
                ]);
            });
            
            return redirect()
                ->route('admin.estabelecimentos.processos.index', $estabelecimento->id)
                ->with('success', 'Processo ' . $processo->numero_processo . ' criado com sucesso!');
        } catch (\Exception $e) {
            \Log::error('Erro ao criar processo', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'estabelecimento_id' => $estabelecimento->id,
                'tipo' => $validated['tipo'] ?? null
            ]);
            
            return redirect()
                ->back()
                ->with('error', 'Erro ao criar processo: ' . $e->getMessage());
        }
    }

    /**
     * Exibe detalhes de um processo
     */
    public function show($estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::with([
                'usuario', 
                'estabelecimento', 
                'documentos' => function($query) {
                    $query->with(['documentoSubstituido'])->orderBy('created_at', 'desc');
                },
                'documentos.usuario', 
                'usuariosAcompanhando'
            ])
            ->where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        // Busca modelos de documentos ativos
        $modelosDocumento = ModeloDocumento::with('tipoDocumento')
            ->ativo()
            ->ordenado()
            ->get();
        
        // Busca documentos digitais do processo (incluindo rascunhos)
        $documentosDigitais = \App\Models\DocumentoDigital::with(['tipoDocumento', 'usuarioCriador', 'assinaturas', 'primeiraVisualizacao.usuarioExterno', 'respostas.usuarioExterno', 'respostas.avaliadoPor'])
            ->where('processo_id', $processoId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Verifica se algum documento de notificação precisa ter o prazo iniciado automaticamente (§1º - 5 dias úteis)
        foreach ($documentosDigitais as $doc) {
            if ($doc->prazo_notificacao && !$doc->prazo_iniciado_em && $doc->todasAssinaturasCompletas()) {
                $doc->verificarInicioAutomaticoPrazo();
            }
        }
        
        // Mescla documentos digitais e arquivos externos em uma única coleção ordenada por data
        $todosDocumentos = collect();
        
        // Adiciona documentos digitais com flag de tipo
        foreach ($documentosDigitais as $docDigital) {
            $todosDocumentos->push([
                'tipo' => 'digital',
                'documento' => $docDigital,
                'created_at' => $docDigital->created_at,
            ]);
        }
        
        // Adiciona arquivos externos (exceto documentos digitais e rejeitados que já foram substituídos)
        $documentosIds = $processo->documentos->pluck('id');
        $documentosSubstituidosIds = $processo->documentos->whereNotNull('documento_substituido_id')->pluck('documento_substituido_id');
        
        foreach ($processo->documentos->where('tipo_documento', '!=', 'documento_digital') as $arquivo) {
            // Não mostra documentos rejeitados que já foram substituídos
            if ($arquivo->status_aprovacao === 'rejeitado' && $documentosSubstituidosIds->contains($arquivo->id)) {
                continue;
            }
            $todosDocumentos->push([
                'tipo' => 'arquivo',
                'documento' => $arquivo,
                'created_at' => $arquivo->created_at,
            ]);
        }
        
        // Adiciona Ordens de Serviço vinculadas ao processo
        $ordensServico = \App\Models\OrdemServico::where('processo_id', $processoId)
            ->with(['estabelecimento', 'municipio'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        foreach ($ordensServico as $os) {
            $todosDocumentos->push([
                'tipo' => 'ordem_servico',
                'documento' => $os,
                'created_at' => $os->created_at,
            ]);
        }
        
        // Ordena todos os documentos por data (mais recente primeiro)
        $todosDocumentos = $todosDocumentos->sortByDesc('created_at')->values();
        
        // Busca designações do processo
        $designacoes = ProcessoDesignacao::where('processo_id', $processoId)
            ->with(['usuarioDesignado', 'usuarioDesignador'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Busca alertas do processo
        $alertas = ProcessoAlerta::where('processo_id', $processoId)
            ->with('usuarioCriador')
            ->orderBy('data_alerta', 'asc')
            ->get();
        
        // Busca documentos obrigatórios baseados nas atividades do estabelecimento
        $documentosObrigatorios = $this->buscarDocumentosObrigatoriosParaProcesso($processo);
        
        return view('estabelecimentos.processos.show', compact('estabelecimento', 'processo', 'modelosDocumento', 'documentosDigitais', 'todosDocumentos', 'designacoes', 'alertas', 'documentosObrigatorios'));
    }

    /**
     * Gera PDF do processo na íntegra (todos os documentos compilados)
     */
    public function integra($estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::with('municipio')->findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        $processo = Processo::with([
            'usuario', 
            'estabelecimento.municipio', 
            'tipoProcesso',
            'documentos' => function($query) {
                $query->orderBy('created_at', 'asc');
            }
        ])
        ->where('estabelecimento_id', $estabelecimentoId)
        ->findOrFail($processoId);
        
        // Busca documentos digitais do processo (apenas assinados)
        $documentosDigitais = \App\Models\DocumentoDigital::with(['tipoDocumento', 'usuarioCriador', 'assinaturas'])
            ->where('processo_id', $processoId)
            ->where('status', 'assinado')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Determina qual logomarca usar (mesma lógica dos PDFs)
        $logomarca = null;
        if ($estabelecimento->isCompetenciaEstadual()) {
            $logomarca = \App\Models\ConfiguracaoSistema::logomarcaEstadual();
        } elseif ($estabelecimento->municipio_id && $estabelecimento->municipio) {
            if (!empty($estabelecimento->municipio->logomarca)) {
                $logomarca = $estabelecimento->municipio->logomarca;
            } else {
                $logomarca = \App\Models\ConfiguracaoSistema::logomarcaEstadual();
            }
        } else {
            $logomarca = \App\Models\ConfiguracaoSistema::logomarcaEstadual();
        }
        
        // Prepara dados para o PDF
        $data = [
            'estabelecimento' => $estabelecimento,
            'processo' => $processo,
            'documentosDigitais' => $documentosDigitais,
            'logomarca' => $logomarca,
        ];
        
        // Gera o PDF inicial (capa + dados)
        $pdf = Pdf::loadView('estabelecimentos.processos.integra-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 15)
            ->setOption('margin-right', 15);
        
        // Nome do arquivo (remove caracteres inválidos)
        $numeroProcessoLimpo = str_replace(['/', '\\'], '_', $processo->numero_processo ?? 'sem_numero');
        $nomeArquivo = 'processo_integra_' . $numeroProcessoLimpo . '.pdf';
        
        // Salva o PDF inicial temporariamente
        $pdfInicial = $pdf->output();
        $tempInicial = storage_path('app/temp_integra_inicial.pdf');
        file_put_contents($tempInicial, $pdfInicial);
        
        // Mescla com os PDFs dos documentos digitais
        try {
            $fpdi = new \setasign\Fpdi\Fpdi();
            
            // Adiciona páginas do PDF inicial
            $pageCount = $fpdi->setSourceFile($tempInicial);
            for ($i = 1; $i <= $pageCount; $i++) {
                $template = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($template);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($template);
            }
            
            // Adiciona PDFs dos documentos digitais
            foreach ($documentosDigitais as $doc) {
                if ($doc->arquivo_pdf && Storage::disk('public')->exists($doc->arquivo_pdf)) {
                    $pdfPath = storage_path('app/public/' . $doc->arquivo_pdf);
                    
                    try {
                        $docPageCount = $fpdi->setSourceFile($pdfPath);
                        for ($i = 1; $i <= $docPageCount; $i++) {
                            $template = $fpdi->importPage($i);
                            $size = $fpdi->getTemplateSize($template);
                            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $fpdi->useTemplate($template);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Erro ao adicionar PDF do documento: ' . $doc->numero_documento, [
                            'erro' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            // Adiciona PDFs dos arquivos anexados
            foreach ($processo->documentos as $documento) {
                $extensao = strtolower(pathinfo($documento->nome_arquivo, PATHINFO_EXTENSION));
                if ($extensao === 'pdf' && !empty($documento->caminho_arquivo) && Storage::disk('public')->exists($documento->caminho_arquivo)) {
                    $pdfPath = storage_path('app/public/' . $documento->caminho_arquivo);
                    
                    try {
                        $docPageCount = $fpdi->setSourceFile($pdfPath);
                        for ($i = 1; $i <= $docPageCount; $i++) {
                            $template = $fpdi->importPage($i);
                            $size = $fpdi->getTemplateSize($template);
                            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $fpdi->useTemplate($template);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Erro ao adicionar PDF anexado: ' . $documento->nome_arquivo, [
                            'erro' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            // Remove arquivo temporário
            @unlink($tempInicial);
            
            // Retorna o PDF mesclado
            return response($fpdi->Output('S', $nomeArquivo))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $nomeArquivo . '"');
                
        } catch (\Exception $e) {
            // Se falhar a mesclagem, retorna apenas o PDF inicial
            \Log::error('Erro ao mesclar PDFs: ' . $e->getMessage());
            @unlink($tempInicial);
            return $pdf->download($nomeArquivo);
        }
    }

    /**
     * Adiciona/Remove acompanhamento do processo
     */
    public function toggleAcompanhamento($estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $usuarioId = Auth::guard('interno')->user()->id;
        
        $acompanhamento = ProcessoAcompanhamento::where('processo_id', $processoId)
            ->where('usuario_interno_id', $usuarioId)
            ->first();
        
        if ($acompanhamento) {
            // Remove acompanhamento
            $acompanhamento->delete();
            $mensagem = 'Você parou de acompanhar este processo.';
        } else {
            // Adiciona acompanhamento
            ProcessoAcompanhamento::create([
                'processo_id' => $processoId,
                'usuario_interno_id' => $usuarioId,
            ]);
            $mensagem = 'Você está acompanhando este processo.';
        }
        
        return redirect()->back()->with('success', $mensagem);
    }

    /**
     * Atualiza o status do processo
     */
    public function updateStatus(Request $request, $estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Processo::statusDisponiveis())),
        ]);
        
        $processo->update(['status' => $validated['status']]);
        
        return redirect()
            ->back()
            ->with('success', 'Status do processo atualizado com sucesso!');
    }

    /**
     * Remove um processo e todos os arquivos vinculados
     * APENAS ADMINISTRADOR pode excluir processos
     */
    public function destroy($estabelecimentoId, $processoId)
    {
        // Verifica se o usuário é administrador
        $usuario = auth('interno')->user();
        if (!$usuario->isAdmin()) {
            return redirect()
                ->back()
                ->with('error', 'Apenas administradores podem excluir processos.');
        }

        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $numeroProcesso = $processo->numero_processo;
        
        // Busca todos os documentos do processo
        $documentos = ProcessoDocumento::where('processo_id', $processoId)->get();
        
        // Exclui os arquivos físicos do storage
        foreach ($documentos as $documento) {
            if ($documento->caminho) {
                $caminhoCompleto = storage_path('app/' . $documento->caminho);
                if (file_exists($caminhoCompleto)) {
                    unlink($caminhoCompleto);
                }
            }
        }
        
        // Remove o diretório do processo se existir
        $diretorioProcesso = storage_path('app/processos/' . $processoId);
        if (is_dir($diretorioProcesso)) {
            // Remove arquivos restantes no diretório
            $arquivos = glob($diretorioProcesso . '/*');
            foreach ($arquivos as $arquivo) {
                if (is_file($arquivo)) {
                    unlink($arquivo);
                }
            }
            // Remove o diretório
            @rmdir($diretorioProcesso);
        }
        
        // Exclui os registros de documentos do banco
        ProcessoDocumento::where('processo_id', $processoId)->delete();
        
        // Exclui o processo
        $processo->delete();
        
        return redirect()
            ->route('admin.estabelecimentos.processos.index', $estabelecimentoId)
            ->with('success', 'Processo ' . $numeroProcesso . ' e todos os arquivos vinculados foram removidos com sucesso!');
    }

    /**
     * Upload de arquivo para o processo
     */
    public function uploadArquivo(Request $request, $estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $request->validate([
            'arquivo' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ], [
            'arquivo.required' => 'Selecione um arquivo para upload.',
            'arquivo.mimes' => 'Apenas arquivos PDF são permitidos.',
            'arquivo.max' => 'O arquivo não pode ser maior que 10MB.',
        ]);
        
        try {
            $arquivo = $request->file('arquivo');
            $nomeOriginal = $arquivo->getClientOriginalName();
            $extensao = $arquivo->getClientOriginalExtension();
            $tamanho = $arquivo->getSize();
            
            // Gera nome único para o arquivo
            $nomeArquivo = Str::slug(pathinfo($nomeOriginal, PATHINFO_FILENAME)) . '_' . time() . '.' . $extensao;
            
            // Define o diretório com DIRECTORY_SEPARATOR
            $diretorio = 'processos' . DIRECTORY_SEPARATOR . $processoId;
            
            // Garante que o diretório existe (cria recursivamente se necessário)
            $caminhoCompleto = storage_path('app') . DIRECTORY_SEPARATOR . $diretorio;
            if (!file_exists($caminhoCompleto)) {
                mkdir($caminhoCompleto, 0755, true);
            }
            
            // Move o arquivo manualmente para garantir que funcione
            $caminhoArquivo = $caminhoCompleto . DIRECTORY_SEPARATOR . $nomeArquivo;
            $arquivo->move($caminhoCompleto, $nomeArquivo);
            
            // Verifica se o arquivo foi realmente salvo
            if (!file_exists($caminhoArquivo)) {
                throw new \Exception('Falha ao salvar o arquivo. Caminho tentado: ' . $caminhoArquivo);
            }
            
            // Caminho relativo para salvar no banco (com barras normais)
            $caminhoRelativo = 'processos/' . $processoId . '/' . $nomeArquivo;
            
            // Cria registro no banco
            ProcessoDocumento::create([
                'processo_id' => $processoId,
                'usuario_id' => Auth::id(),
                'tipo_usuario' => 'interno',
                'nome_arquivo' => $nomeArquivo,
                'nome_original' => $nomeOriginal,
                'caminho' => $caminhoRelativo,
                'extensao' => $extensao,
                'tamanho' => $tamanho,
                'tipo_documento' => 'arquivo_externo',
            ]);
            
            return redirect()
                ->back()
                ->with('success', 'Arquivo enviado com sucesso!');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao fazer upload do arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Visualizar arquivo do processo
     */
    public function visualizarArquivo($estabelecimentoId, $processoId, $documentoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        // Tenta buscar como documento digital primeiro
        $docDigital = \App\Models\DocumentoDigital::where('processo_id', $processoId)
            ->where('id', $documentoId)
            ->first();
        
        if ($docDigital && $docDigital->arquivo_pdf) {
            // É um documento digital
            $caminhoCompleto = storage_path('app/public') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $docDigital->arquivo_pdf);
            
            if (!file_exists($caminhoCompleto)) {
                abort(404, 'PDF não encontrado');
            }
            
            return response()->file($caminhoCompleto, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="documento.pdf"'
            ]);
        }
        
        // Senão, busca como arquivo externo
        $documento = ProcessoDocumento::where('processo_id', $processoId)
            ->findOrFail($documentoId);
        
        // Verifica se é documento digital ou arquivo externo
        // Arquivos externos enviados por usuários externos são salvos em public
        if ($documento->tipo_documento === 'documento_digital' || $documento->tipo_usuario === 'externo') {
            $caminhoCompleto = storage_path('app/public') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
        } else {
            $caminhoCompleto = storage_path('app') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
        }
        
        if (!file_exists($caminhoCompleto)) {
            abort(404, 'Arquivo não encontrado: ' . $documento->caminho);
        }
        
        // Detecta o tipo MIME correto
        $mimeType = mime_content_type($caminhoCompleto);
        
        // Retorna o arquivo para visualização inline com headers corretos
        return response()->file($caminhoCompleto, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $documento->nome_original . '"'
        ]);
    }

    /**
     * Download de arquivo do processo
     */
    public function downloadArquivo($estabelecimentoId, $processoId, $documentoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $documento = ProcessoDocumento::where('processo_id', $processoId)
            ->findOrFail($documentoId);
        
        // Verifica se é documento digital ou arquivo externo
        // Arquivos externos enviados por usuários externos são salvos em public
        if ($documento->tipo_documento === 'documento_digital' || $documento->tipo_usuario === 'externo') {
            $caminhoCompleto = storage_path('app/public') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
        } else {
            $caminhoCompleto = storage_path('app') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
        }
        
        if (!file_exists($caminhoCompleto)) {
            abort(404, 'Arquivo não encontrado.');
        }
        
        return response()->download($caminhoCompleto, $documento->nome_original);
    }

    /**
     * Atualiza o nome do arquivo
     */
    public function updateNomeArquivo(Request $request, $estabelecimentoId, $processoId, $documentoId)
    {
        $documento = ProcessoDocumento::where('processo_id', $processoId)
            ->findOrFail($documentoId);
        
        $request->validate([
            'nome_original' => 'required|string|max:255',
        ], [
            'nome_original.required' => 'O nome do arquivo é obrigatório.',
            'nome_original.max' => 'O nome do arquivo não pode ter mais de 255 caracteres.',
        ]);
        
        try {
            $documento->update([
                'nome_original' => $request->nome_original,
            ]);
            
            return redirect()
                ->back()
                ->with('success', 'Nome do arquivo atualizado com sucesso!');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao atualizar nome do arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Remove arquivo do processo (requer senha de assinatura)
     */
    public function deleteArquivo(Request $request, $estabelecimentoId, $processoId, $documentoId)
    {
        $documento = ProcessoDocumento::where('processo_id', $processoId)
            ->findOrFail($documentoId);
        
        $usuario = auth('interno')->user();
        $processo = Processo::findOrFail($processoId);
        
        // Valida senha de assinatura
        if (!$usuario->temSenhaAssinatura()) {
            return response()->json([
                'success' => false, 
                'message' => 'Você precisa configurar sua senha de assinatura primeiro.'
            ], 400);
        }

        $senhaAssinatura = $request->input('senha_assinatura');
        
        if (!$senhaAssinatura || !Hash::check($senhaAssinatura, $usuario->senha_assinatura_digital)) {
            return response()->json([
                'success' => false, 
                'message' => 'Senha de assinatura incorreta.'
            ], 400);
        }
        
        try {
            $nomeArquivo = $documento->nome_original;
            
            // Registra no histórico antes de excluir
            ProcessoEvento::create([
                'processo_id' => $processo->id,
                'usuario_interno_id' => $usuario->id,
                'tipo_evento' => 'documento_excluido',
                'titulo' => 'Arquivo Excluído',
                'descricao' => $nomeArquivo,
                'dados_adicionais' => [
                    'nome_arquivo' => $nomeArquivo,
                    'tipo_usuario' => $documento->tipo_usuario,
                    'excluido_por' => $usuario->nome,
                ]
            ]);
            
            // Remove arquivo físico
            $caminhoCompleto = storage_path('app') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
            if (file_exists($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }
            
            // Remove registro do banco
            $documento->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Arquivo removido com sucesso!'
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprova documento enviado por usuário externo
     */
    public function aprovarDocumento($estabelecimentoId, $processoId, $documentoId)
    {
        $documento = ProcessoDocumento::where('processo_id', $processoId)
            ->where('status_aprovacao', 'pendente')
            ->findOrFail($documentoId);
        
        $documento->update([
            'status_aprovacao' => 'aprovado',
            'aprovado_por' => auth('interno')->id(),
            'aprovado_em' => now(),
        ]);
        
        return redirect()
            ->back()
            ->with('success', 'Documento aprovado com sucesso!');
    }

    /**
     * Rejeita documento enviado por usuário externo
     */
    public function rejeitarDocumento(Request $request, $estabelecimentoId, $processoId, $documentoId)
    {
        $request->validate([
            'motivo_rejeicao' => 'required|string|max:1000',
        ]);

        $documento = ProcessoDocumento::where('processo_id', $processoId)
            ->where('status_aprovacao', 'pendente')
            ->findOrFail($documentoId);
        
        $documento->update([
            'status_aprovacao' => 'rejeitado',
            'motivo_rejeicao' => $request->motivo_rejeicao,
            'aprovado_por' => auth('interno')->id(),
            'aprovado_em' => now(),
        ]);
        
        return redirect()
            ->back()
            ->with('success', 'Documento rejeitado. O usuário externo será notificado.');
    }

    /**
     * Revalida documento aprovado (volta para pendente)
     */
    public function revalidarDocumento($estabelecimentoId, $processoId, $documentoId)
    {
        $documento = ProcessoDocumento::where('processo_id', $processoId)
            ->where('status_aprovacao', 'aprovado')
            ->findOrFail($documentoId);
        
        $documento->update([
            'status_aprovacao' => 'pendente',
            'aprovado_por' => null,
            'aprovado_em' => null,
        ]);
        
        return redirect()
            ->back()
            ->with('success', 'Documento voltou para análise (pendente).');
    }

    /**
     * Visualiza uma resposta a documento digital
     */
    public function visualizarRespostaDocumento($estabelecimentoId, $processoId, $documentoId, $respostaId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);

        $documento = DocumentoDigital::where('processo_id', $processo->id)
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
     * Download de uma resposta a documento digital
     */
    public function downloadRespostaDocumento($estabelecimentoId, $processoId, $documentoId, $respostaId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);

        $documento = DocumentoDigital::where('processo_id', $processo->id)
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
     * Aprova uma resposta a documento digital
     */
    public function aprovarRespostaDocumento($estabelecimentoId, $processoId, $documentoId, $respostaId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);

        $documento = DocumentoDigital::where('processo_id', $processo->id)
            ->findOrFail($documentoId);

        $resposta = DocumentoResposta::where('documento_digital_id', $documento->id)
            ->where('status', 'pendente')
            ->findOrFail($respostaId);

        $resposta->aprovar(auth('interno')->id());
        
        // Registrar evento no histórico
        ProcessoEvento::registrarRespostaAprovada($processo, $resposta);

        return redirect()
            ->back()
            ->with('success', 'Resposta aprovada com sucesso!');
    }

    /**
     * Rejeita uma resposta a documento digital
     */
    public function rejeitarRespostaDocumento(Request $request, $estabelecimentoId, $processoId, $documentoId, $respostaId)
    {
        $request->validate([
            'motivo_rejeicao' => 'required|string|max:1000',
        ]);

        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);

        $documento = DocumentoDigital::where('processo_id', $processo->id)
            ->findOrFail($documentoId);

        $resposta = DocumentoResposta::where('documento_digital_id', $documento->id)
            ->where('status', 'pendente')
            ->findOrFail($respostaId);

        $resposta->rejeitar(auth('interno')->id(), $request->motivo_rejeicao);
        
        // Registrar evento no histórico
        ProcessoEvento::registrarRespostaRejeitada($processo, $resposta, $request->motivo_rejeicao);

        return redirect()
            ->back()
            ->with('success', 'Resposta rejeitada. O estabelecimento será notificado.');
    }

    /**
     * Exclui uma resposta a documento digital (requer senha de assinatura)
     */
    public function excluirRespostaDocumento(Request $request, $estabelecimentoId, $processoId, $documentoId, $respostaId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);

        $documento = DocumentoDigital::where('processo_id', $processo->id)
            ->findOrFail($documentoId);

        $resposta = DocumentoResposta::where('documento_digital_id', $documento->id)
            ->findOrFail($respostaId);

        // Valida senha de assinatura
        $usuario = auth('interno')->user();
        
        if (!$usuario->temSenhaAssinatura()) {
            return response()->json([
                'success' => false,
                'message' => 'Você precisa configurar sua senha de assinatura primeiro.'
            ], 400);
        }

        $senhaAssinatura = $request->input('senha_assinatura');
        
        if (!$senhaAssinatura || !\Hash::check($senhaAssinatura, $usuario->senha_assinatura_digital)) {
            return response()->json([
                'success' => false,
                'message' => 'Senha de assinatura incorreta.'
            ], 400);
        }

        $nomeArquivo = $resposta->nome_arquivo;

        // Exclui o arquivo físico se existir
        if ($resposta->caminho_arquivo) {
            $caminhoCompleto = storage_path('app/' . $resposta->caminho_arquivo);
            if (file_exists($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }
        }

        // Registra no histórico antes de excluir
        ProcessoEvento::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => $usuario->id,
            'tipo_evento' => 'documento_excluido',
            'titulo' => 'Resposta Excluída',
            'descricao' => $nomeArquivo,
            'dados_adicionais' => [
                'nome_arquivo' => $nomeArquivo,
                'documento_digital_id' => $documento->id,
                'documento_nome' => $documento->nome ?? $documento->tipoDocumento->nome ?? 'N/D',
                'excluido_por' => $usuario->nome,
                'tipo' => 'resposta',
            ]
        ]);

        $resposta->delete();

        return response()->json([
            'success' => true,
            'message' => "Resposta '{$nomeArquivo}' excluída com sucesso."
        ]);
    }

    /**
     * Gera documento digital a partir de um modelo
     */
    public function gerarDocumento(Request $request, $estabelecimentoId, $processoId)
    {
        $request->validate([
            'modelo_documento_id' => 'required|exists:modelo_documentos,id',
        ]);

        try {
            $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
            $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
                ->findOrFail($processoId);
            
            $modelo = ModeloDocumento::with('tipoDocumento')->findOrFail($request->modelo_documento_id);
            
            // Substitui variáveis no conteúdo HTML
            $conteudo = $this->substituirVariaveis($modelo->conteudo, $estabelecimento, $processo);
            
            // Gera PDF
            $pdf = Pdf::loadHTML($conteudo);
            $pdf->setPaper('A4', 'portrait');
            
            // Define nome do arquivo
            $nomeArquivo = Str::slug($modelo->tipoDocumento->nome) . '_' . time() . '.pdf';
            $nomeOriginal = $modelo->tipoDocumento->nome . ' - ' . $processo->numero_processo . '.pdf';
            
            // Define diretório
            $diretorio = 'processos' . DIRECTORY_SEPARATOR . $processoId;
            $caminhoCompleto = storage_path('app') . DIRECTORY_SEPARATOR . $diretorio;
            
            // Garante que o diretório existe
            if (!file_exists($caminhoCompleto)) {
                mkdir($caminhoCompleto, 0755, true);
            }
            
            // Salva PDF
            $caminhoArquivo = $caminhoCompleto . DIRECTORY_SEPARATOR . $nomeArquivo;
            $pdf->save($caminhoArquivo);
            
            // Caminho relativo para o banco
            $caminhoRelativo = 'processos/' . $processoId . '/' . $nomeArquivo;
            
            // Cria registro no banco
            ProcessoDocumento::create([
                'processo_id' => $processoId,
                'usuario_id' => Auth::id(),
                'tipo_usuario' => 'interno',
                'nome_arquivo' => $nomeArquivo,
                'nome_original' => $nomeOriginal,
                'caminho' => $caminhoRelativo,
                'extensao' => 'pdf',
                'tamanho' => filesize($caminhoArquivo),
                'tipo_documento' => 'documento_digital',
            ]);
            
            return redirect()
                ->back()
                ->with('success', 'Documento digital gerado com sucesso!');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao gerar documento: ' . $e->getMessage());
        }
    }

    /**
     * Substitui variáveis no conteúdo do modelo
     */
    private function substituirVariaveis($conteudo, $estabelecimento, $processo)
    {
        $variaveis = [
            '{estabelecimento_nome}' => $estabelecimento->nome_fantasia ?? $estabelecimento->nome_razao_social,
            '{estabelecimento_razao_social}' => $estabelecimento->nome_razao_social,
            '{estabelecimento_cnpj}' => $estabelecimento->cnpj_formatado,
            '{estabelecimento_endereco}' => $estabelecimento->endereco . ', ' . $estabelecimento->numero,
            '{estabelecimento_bairro}' => $estabelecimento->bairro,
            '{estabelecimento_cidade}' => $estabelecimento->cidade,
            '{estabelecimento_estado}' => $estabelecimento->estado,
            '{estabelecimento_cep}' => $estabelecimento->cep,
            '{estabelecimento_telefone}' => $estabelecimento->telefone_formatado ?? '',
            '{processo_numero}' => $processo->numero_processo,
            '{processo_tipo}' => $processo->tipo,
            '{processo_status}' => $processo->status_formatado,
            '{processo_data_criacao}' => $processo->created_at->format('d/m/Y'),
            '{processo_data_criacao_extenso}' => $processo->created_at->translatedFormat('d \d\e F \d\e Y'),
            '{data_atual}' => now()->format('d/m/Y'),
            '{data_atual_extenso}' => now()->translatedFormat('d \d\e F \d\e Y'),
            '{ano_atual}' => now()->format('Y'),
        ];
        
        return str_replace(array_keys($variaveis), array_values($variaveis), $conteudo);
    }

    /**
     * Arquivar processo
     */
    public function arquivar(Request $request, $estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $request->validate([
            'motivo_arquivamento' => 'required|string|min:10',
        ], [
            'motivo_arquivamento.required' => 'O motivo do arquivamento é obrigatório.',
            'motivo_arquivamento.min' => 'O motivo deve ter no mínimo 10 caracteres.',
        ]);

        try {
            $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
                ->findOrFail($processoId);

            $statusAntigo = $processo->status;

            // Guardar setor/responsável atual antes de arquivar (para restaurar depois)
            $setorAnterior = $processo->setor_atual;
            $responsavelAnteriorId = $processo->responsavel_atual_id;

            // Atualizar processo - limpa setor/responsável e guarda backup
            $processo->update([
                'status' => 'arquivado',
                'motivo_arquivamento' => $request->motivo_arquivamento,
                'data_arquivamento' => now(),
                'usuario_arquivamento_id' => Auth::guard('interno')->user()->id,
                // Guarda backup do setor/responsável
                'setor_antes_arquivar' => $setorAnterior,
                'responsavel_antes_arquivar_id' => $responsavelAnteriorId,
                // Limpa setor/responsável atual
                'setor_atual' => null,
                'responsavel_atual_id' => null,
                'responsavel_desde' => null,
            ]);

            // ✅ REGISTRAR EVENTO NO HISTÓRICO
            \App\Models\ProcessoEvento::registrarArquivamento(
                $processo,
                $request->motivo_arquivamento
            );

            return redirect()
                ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
                ->with('success', 'Processo arquivado com sucesso!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao arquivar processo: ' . $e->getMessage());
        }
    }

    /**
     * Desarquivar processo
     */
    public function desarquivar($estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        try {
            $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
                ->findOrFail($processoId);

            // Restaurar setor/responsável anterior (se existia)
            $setorRestaurar = $processo->setor_antes_arquivar;
            $responsavelRestaurarId = $processo->responsavel_antes_arquivar_id;

            // Atualizar processo - restaura setor/responsável
            $processo->update([
                'status' => 'aberto',
                // Restaura setor/responsável
                'setor_atual' => $setorRestaurar,
                'responsavel_atual_id' => $responsavelRestaurarId,
                'responsavel_desde' => $setorRestaurar || $responsavelRestaurarId ? now() : null,
                // Limpa backup
                'setor_antes_arquivar' => null,
                'responsavel_antes_arquivar_id' => null,
            ]);

            // ✅ REGISTRAR EVENTO NO HISTÓRICO
            \App\Models\ProcessoEvento::create([
                'processo_id' => $processo->id,
                'usuario_interno_id' => Auth::guard('interno')->user()->id,
                'tipo_evento' => 'processo_desarquivado',
                'titulo' => 'Processo Desarquivado',
                'descricao' => 'Processo foi desarquivado e reaberto' . 
                    ($setorRestaurar ? '. Restaurado para setor: ' . $setorRestaurar : '') .
                    ($responsavelRestaurarId ? '. Responsável restaurado.' : ''),
                'dados_adicionais' => [
                    'motivo_arquivamento_anterior' => $processo->motivo_arquivamento,
                    'data_arquivamento_anterior' => $processo->data_arquivamento?->toDateTimeString(),
                    'setor_restaurado' => $setorRestaurar,
                    'responsavel_restaurado_id' => $responsavelRestaurarId,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()
                ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
                ->with('success', 'Processo desarquivado com sucesso!' . 
                    ($setorRestaurar || $responsavelRestaurarId ? ' Setor/responsável anterior restaurado.' : ''));

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao desarquivar processo: ' . $e->getMessage());
        }
    }

    /**
     * Parar processo
     */
    public function parar(Request $request, $estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $request->validate([
            'motivo_parada' => 'required|string|min:10',
        ], [
            'motivo_parada.required' => 'O motivo da parada é obrigatório.',
            'motivo_parada.min' => 'O motivo deve ter no mínimo 10 caracteres.',
        ]);

        try {
            $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
                ->findOrFail($processoId);

            // Atualizar processo
            $processo->update([
                'status' => 'parado',
                'motivo_parada' => $request->motivo_parada,
                'data_parada' => now(),
                'usuario_parada_id' => Auth::guard('interno')->user()->id,
            ]);

            // ✅ REGISTRAR EVENTO NO HISTÓRICO
            \App\Models\ProcessoEvento::registrarParada(
                $processo,
                $request->motivo_parada
            );

            return redirect()
                ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
                ->with('success', 'Processo parado com sucesso!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao parar processo: ' . $e->getMessage());
        }
    }

    /**
     * Reiniciar processo
     */
    public function reiniciar($estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        try {
            $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
                ->findOrFail($processoId);

            // Atualizar processo
            $processo->update([
                'status' => 'aberto',
            ]);

            // ✅ REGISTRAR EVENTO NO HISTÓRICO
            \App\Models\ProcessoEvento::registrarReinicio($processo);

            return redirect()
                ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
                ->with('success', 'Processo reiniciado com sucesso!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao reiniciar processo: ' . $e->getMessage());
        }
    }

    /**
     * Valida se o usuário tem permissão para acessar o processo
     */
    private function validarPermissaoAcesso($estabelecimento)
    {
        $usuario = auth('interno')->user();
        
        // Administrador tem acesso total
        if ($usuario->isAdmin()) {
            return true;
        }
        
        // Usuário estadual só pode acessar processos de competência estadual
        if ($usuario->isEstadual()) {
            if (!$estabelecimento->isCompetenciaEstadual()) {
                abort(403, 'Você não tem permissão para acessar processos de competência municipal.');
            }
            return true;
        }
        
        // Usuário municipal só pode acessar processos do próprio município e de competência municipal
        if ($usuario->isMunicipal()) {
            if (!$usuario->municipio_id || $estabelecimento->municipio_id != $usuario->municipio_id) {
                abort(403, 'Você não tem permissão para acessar processos de outros municípios.');
            }
            if ($estabelecimento->isCompetenciaEstadual()) {
                abort(403, 'Você não tem permissão para acessar processos de competência estadual.');
            }
            return true;
        }
        
        return true;
    }

    /**
     * Carrega anotações de um PDF
     */
    public function carregarAnotacoes($documentoId)
    {
        $documento = ProcessoDocumento::findOrFail($documentoId);
        $processo = $documento->processo;
        $estabelecimento = $processo->estabelecimento;
        
        // Valida permissão de acesso
        $this->validarPermissaoAcesso($estabelecimento);
        
        // Busca anotações de TODOS os usuários para este documento (compartilhado)
        $anotacoes = \App\Models\ProcessoDocumentoAnotacao::where('processo_documento_id', $documentoId)
            ->with('usuario:id,nome')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($anotacao) {
                return [
                    'id' => $anotacao->id,
                    'tipo' => $anotacao->tipo,
                    'pagina' => $anotacao->pagina,
                    'dados' => $anotacao->dados,
                    'comentario' => $anotacao->comentario,
                    'usuario_id' => $anotacao->usuario_id,
                    'usuario_nome' => $anotacao->usuario->nome ?? 'Usuário',
                    'created_at' => $anotacao->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json($anotacoes);
    }

    /**
     * Salva anotações feitas em um PDF
     */
    public function salvarAnotacoes(Request $request, $documentoId)
    {
        try {
            $documento = ProcessoDocumento::findOrFail($documentoId);
            $processo = $documento->processo;
            
            if (!$processo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Processo não encontrado para este documento.'
                ], 404);
            }
            
            $estabelecimento = $processo->estabelecimento;
            
            if (!$estabelecimento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estabelecimento não encontrado para este processo.'
                ], 404);
            }
            
            // Valida permissão de acesso
            $this->validarPermissaoAcesso($estabelecimento);
            
            // Permitir array vazio para limpar anotações do usuário atual
            $anotacoes = $request->input('anotacoes', []);
            if (!is_array($anotacoes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato inválido de anotações.'
                ], 422);
            }

            // Se houver itens, validar o schema de cada anotação
            if (!empty($anotacoes)) {
                $request->validate([
                    'anotacoes.*.tipo' => 'required|string|in:highlight,text,drawing,area,comment',
                    'anotacoes.*.pagina' => 'required|integer|min:1',
                    'anotacoes.*.dados' => 'required|array',
                    'anotacoes.*.comentario' => 'nullable|string',
                ]);
            }

            $usuarioId = auth('interno')->id();
            
            // Pega os IDs das anotações que vieram do banco (IDs inteiros pequenos, não timestamps)
            // IDs do banco são inteiros sequenciais, IDs temporários do frontend são timestamps grandes
            $idsRecebidos = collect($anotacoes)
                ->filter(function($a) {
                    // Só considera como ID do banco se for inteiro e menor que 1000000000 (antes de 2001)
                    // Timestamps JavaScript são maiores que 1000000000000 (13 dígitos)
                    return isset($a['id']) && is_numeric($a['id']) && $a['id'] < 1000000000;
                })
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();
            
            // Remove apenas as anotações do usuário atual que não estão mais na lista
            \App\Models\ProcessoDocumentoAnotacao::where('processo_documento_id', $documentoId)
                ->where('usuario_id', $usuarioId)
                ->whereNotIn('id', $idsRecebidos)
                ->delete();

            // Salva novas anotações (apenas as que não têm ID do banco)
            foreach ($anotacoes as $anotacao) {
                // Se já tem ID do banco e é de outro usuário, pula (não pode editar)
                if (isset($anotacao['id']) && $anotacao['id'] < 1000000000 && isset($anotacao['usuario_id']) && $anotacao['usuario_id'] != $usuarioId) {
                    continue;
                }
                
                // Se não tem ID ou é um ID temporário (timestamp), é uma nova anotação
                $isNovaAnotacao = !isset($anotacao['id']) || $anotacao['id'] >= 1000000000;
                
                if ($isNovaAnotacao) {
                    \App\Models\ProcessoDocumentoAnotacao::create([
                        'processo_documento_id' => $documentoId,
                        'usuario_id' => $usuarioId,
                        'pagina' => $anotacao['pagina'],
                        'tipo' => $anotacao['tipo'],
                        'dados' => $anotacao['dados'],
                        'comentario' => $anotacao['comentario'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Anotações salvas com sucesso!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            \Log::error('Erro ao salvar anotações', [
                'documento_id' => $documentoId,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar anotações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca setores e usuários internos para designação
     */
    public function buscarUsuariosParaDesignacao($estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::with('tipoProcesso')
            ->where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $usuarioLogado = auth('interno')->user();
        $setorUsuarioLogado = $usuarioLogado->setor;
        $nivelAcessoUsuario = $usuarioLogado->nivel_acesso->value ?? $usuarioLogado->nivel_acesso;
        $municipioUsuario = $usuarioLogado->municipio_id;
        
        // Determina se o usuário logado é estadual ou municipal
        $isUsuarioEstadual = in_array($nivelAcessoUsuario, ['administrador', 'gestor_estadual', 'tecnico_estadual']);
        $isUsuarioMunicipal = in_array($nivelAcessoUsuario, ['gestor_municipal', 'tecnico_municipal']);
        
        // Busca setores disponíveis baseado no perfil do usuário logado
        $setores = \App\Models\TipoSetor::where('ativo', true)
            ->orderBy('nome')
            ->get();
        
        // Níveis que caracterizam setores estaduais e municipais
        $niveisSetorEstadual = ['gestor_estadual', 'tecnico_estadual'];
        $niveisSetorMunicipal = ['gestor_municipal', 'tecnico_municipal'];
        
        // Filtra setores por nível de acesso do usuário logado
        $setoresDisponiveis = $setores->filter(function($setor) use ($isUsuarioEstadual, $isUsuarioMunicipal, $niveisSetorEstadual, $niveisSetorMunicipal) {
            // Se não tem níveis de acesso definidos, disponível para todos
            if (!$setor->niveis_acesso || count($setor->niveis_acesso) === 0) {
                return true;
            }
            
            // Verifica se o setor é estadual ou municipal baseado nos níveis configurados
            $isSetorEstadual = !empty(array_intersect($setor->niveis_acesso, $niveisSetorEstadual));
            $isSetorMunicipal = !empty(array_intersect($setor->niveis_acesso, $niveisSetorMunicipal));
            
            // Se usuário é estadual (admin, gestor_estadual, tecnico_estadual), mostra setores estaduais
            if ($isUsuarioEstadual) {
                return $isSetorEstadual && !$isSetorMunicipal;
            }
            
            // Se usuário é municipal, mostra setores municipais
            if ($isUsuarioMunicipal) {
                return $isSetorMunicipal && !$isSetorEstadual;
            }
            
            return false;
        })->values();
        
        // Níveis de usuários estaduais e municipais para filtrar usuários
        $niveisUsuariosEstaduais = ['administrador', 'gestor_estadual', 'tecnico_estadual'];
        $niveisUsuariosMunicipais = ['gestor_municipal', 'tecnico_municipal'];
        
        // Busca usuários internos ativos
        $query = UsuarioInterno::where('ativo', true);
        
        // Filtra usuários baseado no perfil do usuário logado
        if ($isUsuarioEstadual) {
            // Usuário estadual vê todos os usuários estaduais
            $query->whereIn('nivel_acesso', $niveisUsuariosEstaduais);
        } elseif ($isUsuarioMunicipal) {
            // Usuário municipal vê apenas usuários do seu município
            $query->where('municipio_id', $municipioUsuario)
                  ->whereIn('nivel_acesso', $niveisUsuariosMunicipais);
        }
        
        $usuarios = $query->orderBy('nome')
            ->get(['id', 'nome', 'cargo', 'nivel_acesso', 'setor']);
        
        // Agrupa usuários por setor (apenas dos setores disponíveis para tramitação)
        $usuariosPorSetor = [];
        foreach ($setoresDisponiveis as $setor) {
            $usuariosDoSetor = $usuarios->where('setor', $setor->codigo)->values();
            if ($usuariosDoSetor->count() > 0) {
                $usuariosPorSetor[] = [
                    'setor' => [
                        'codigo' => $setor->codigo,
                        'nome' => $setor->nome,
                    ],
                    'usuarios' => $usuariosDoSetor
                ];
            }
        }
        
        // Adiciona usuários sem setor
        $usuariosSemSetor = $usuarios->whereNull('setor')->values();
        if ($usuariosSemSetor->count() > 0) {
            $usuariosPorSetor[] = [
                'setor' => [
                    'codigo' => null,
                    'nome' => 'Sem Setor',
                ],
                'usuarios' => $usuariosSemSetor
            ];
        }
        
        // Mapeia setores para array simples com apenas codigo e nome
        $setoresArray = $setoresDisponiveis->map(function($setor) {
            return [
                'codigo' => $setor->codigo,
                'nome' => $setor->nome,
            ];
        })->values();
        
        return response()->json([
            'setores' => $setoresArray,
            'usuariosPorSetor' => $usuariosPorSetor,
            'isUsuarioEstadual' => $isUsuarioEstadual,
            'setorUsuarioLogado' => $setorUsuarioLogado,
            'debug' => [
                'nivelAcessoUsuario' => $nivelAcessoUsuario,
                'totalSetores' => $setores->count(),
                'setoresFiltrados' => $setoresDisponiveis->count(),
                'totalUsuarios' => $usuarios->count(),
            ]
        ]);
    }

    /**
     * Designa responsáveis para o processo (apenas usuários)
     */
    public function designarResponsavel(Request $request, $estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $validated = $request->validate([
            'tipo_designacao' => 'required|in:usuario',
            'usuarios_designados' => 'required|array|min:1',
            'usuarios_designados.*' => 'required|exists:usuarios_internos,id',
            'descricao_tarefa' => 'required|string|max:1000',
            'data_limite' => 'nullable|date|after_or_equal:today',
            'definir_responsavel_atual' => 'nullable|boolean',
        ]);
        
        $designados = 0;
        $tipoProcesso = $processo->tipoProcesso;
        $isCompetenciaEstadual = $tipoProcesso && in_array($tipoProcesso->competencia, ['estadual', 'estadual_exclusivo']);
        $ultimoDesignado = null;
        
        // Designação apenas por usuário
        foreach ($validated['usuarios_designados'] as $usuarioId) {
            $usuarioDesignado = UsuarioInterno::find($usuarioId);
            
            // Verifica competência
            $podeDesignar = false;
            if ($isCompetenciaEstadual) {
                $podeDesignar = $usuarioDesignado && $usuarioDesignado->municipio_id === null;
            } else {
                $podeDesignar = $usuarioDesignado && $usuarioDesignado->municipio_id == $estabelecimento->municipio_id;
            }
            
            if ($podeDesignar) {
                ProcessoDesignacao::create([
                    'processo_id' => $processo->id,
                    'usuario_designado_id' => $usuarioId,
                    'usuario_designador_id' => auth('interno')->id(),
                    'descricao_tarefa' => $validated['descricao_tarefa'],
                    'data_limite' => $validated['data_limite'] ?? null,
                    'status' => 'pendente',
                ]);
                $designados++;
                $ultimoDesignado = $usuarioDesignado;
            }
        }
        
        // Se marcou para definir como responsável atual ou se é o único designado
        if ($designados > 0 && $ultimoDesignado) {
            $definirResponsavel = $request->boolean('definir_responsavel_atual', $designados === 1);
            
            if ($definirResponsavel) {
                $processo->atribuirPara(
                    $ultimoDesignado->setor,
                    $ultimoDesignado->id
                );
            }
        }
        
        if ($designados > 0) {
            $mensagem = $designados === 1 
                ? 'Responsável designado com sucesso!' 
                : "{$designados} responsáveis designados com sucesso!";
            
            return redirect()
                ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
                ->with('success', $mensagem);
        }
        
        return back()->withErrors([
            'usuarios_designados' => 'Nenhum responsável válido foi designado.'
        ]);
    }

    /**
     * Atribui o processo a um setor e/ou responsável (passar processo)
     */
    public function atribuirProcesso(Request $request, $estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $validated = $request->validate([
            'setor_atual' => 'nullable|string|max:255',
            'responsavel_atual_id' => 'nullable|exists:usuarios_internos,id',
            'motivo_atribuicao' => 'nullable|string|max:1000',
            'prazo_atribuicao' => 'nullable|date|after_or_equal:today',
        ]);
        
        $setorAnterior = $processo->setor_atual;
        $responsavelAnterior = $processo->responsavelAtual;
        $nomeSetorAnterior = $processo->setor_atual_nome;
        $prazoAnterior = $processo->prazo_atribuicao;
        
        // Busca o nome do setor se informado
        $nomeSetorNovo = null;
        if ($validated['setor_atual']) {
            $tipoSetor = \App\Models\TipoSetor::where('codigo', $validated['setor_atual'])->first();
            $nomeSetorNovo = $tipoSetor ? $tipoSetor->nome : $validated['setor_atual'];
        }
        
        // Atualiza o processo
        $processo->update([
            'setor_atual' => $validated['setor_atual'] ?: null,
            'responsavel_atual_id' => $validated['responsavel_atual_id'] ?: null,
            'responsavel_desde' => now(),
            'prazo_atribuicao' => $validated['prazo_atribuicao'] ?? null,
            'motivo_atribuicao' => $validated['motivo_atribuicao'] ?? null,
            'responsavel_ciente_em' => null, // Reseta para o novo responsável ver a notificação
        ]);
        
        // Registra no histórico
        $novoResponsavel = $validated['responsavel_atual_id'] ? UsuarioInterno::find($validated['responsavel_atual_id']) : null;
        
        $descricao = 'Processo atribuído';
        if ($nomeSetorNovo) {
            $descricao .= ' ao setor ' . $nomeSetorNovo;
        }
        if ($novoResponsavel) {
            $descricao .= ($nomeSetorNovo ? ' - ' : ' a ') . $novoResponsavel->nome;
        }
        if (!$validated['setor_atual'] && !$novoResponsavel) {
            $descricao = 'Atribuição do processo removida';
        }
        
        // Adiciona motivo se informado
        $motivoAtribuicao = $validated['motivo_atribuicao'] ?? null;
        if ($motivoAtribuicao) {
            $descricao .= '. Motivo: ' . $motivoAtribuicao;
        }
        
        // Adiciona prazo se informado
        $prazoAtribuicao = $validated['prazo_atribuicao'] ?? null;
        if ($prazoAtribuicao) {
            $descricao .= '. Prazo: ' . \Carbon\Carbon::parse($prazoAtribuicao)->format('d/m/Y');
        }
        
        ProcessoEvento::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => auth('interno')->id(),
            'tipo_evento' => 'processo_atribuido',
            'titulo' => 'Processo Atribuído',
            'descricao' => $descricao,
            'dados_adicionais' => [
                'setor_anterior' => $setorAnterior,
                'setor_anterior_nome' => $nomeSetorAnterior,
                'responsavel_anterior_id' => $responsavelAnterior ? $responsavelAnterior->id : null,
                'responsavel_anterior' => $responsavelAnterior ? $responsavelAnterior->nome : null,
                'setor_novo' => $validated['setor_atual'] ?? null,
                'setor_novo_nome' => $nomeSetorNovo,
                'responsavel_novo_id' => $novoResponsavel ? $novoResponsavel->id : null,
                'responsavel_novo' => $novoResponsavel ? $novoResponsavel->nome : null,
                'motivo' => $motivoAtribuicao,
                'prazo' => $prazoAtribuicao,
                'prazo_anterior' => $prazoAnterior ? $prazoAnterior->format('Y-m-d') : null,
            ],
        ]);
        
        return redirect()
            ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
            ->with('success', 'Processo atribuído com sucesso!');
    }

    /**
     * Marca que o responsável está ciente da atribuição
     */
    public function marcarCiente(Request $request, $estabelecimentoId, $processoId)
    {
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $usuario = auth('interno')->user();
        
        // Só pode marcar ciente se for o responsável atual
        if ($processo->responsavel_atual_id !== $usuario->id) {
            return response()->json(['success' => false, 'message' => 'Você não é o responsável atual deste processo.'], 403);
        }
        
        $processo->update([
            'responsavel_ciente_em' => now(),
        ]);
        
        // Busca o último evento de atribuição para adicionar a ciência
        $ultimoEventoAtribuicao = ProcessoEvento::where('processo_id', $processo->id)
            ->where('tipo_evento', 'processo_atribuido')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($ultimoEventoAtribuicao) {
            $dadosAdicionais = $ultimoEventoAtribuicao->dados_adicionais ?? [];
            $dadosAdicionais['ciente_em'] = now()->format('Y-m-d H:i:s');
            $dadosAdicionais['ciente_por_id'] = $usuario->id;
            $dadosAdicionais['ciente_por_nome'] = $usuario->nome;
            
            $ultimoEventoAtribuicao->update([
                'dados_adicionais' => $dadosAdicionais,
            ]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Atualiza o status de uma designação
     */
    public function atualizarDesignacao(Request $request, $estabelecimentoId, $processoId, $designacaoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $designacao = ProcessoDesignacao::where('processo_id', $processoId)
            ->findOrFail($designacaoId);
        
        $validated = $request->validate([
            'status' => 'required|in:pendente,em_andamento,concluida,cancelada',
            'observacoes_conclusao' => 'nullable|string|max:1000',
        ]);
        
        $designacao->status = $validated['status'];
        $designacao->observacoes_conclusao = $validated['observacoes_conclusao'] ?? null;
        
        if ($validated['status'] === 'concluida') {
            $designacao->concluida_em = now();
        }
        
        $designacao->save();
        
        return redirect()
            ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
            ->with('success', 'Status da designação atualizado!');
    }
    
    /**
     * Marca uma designação como concluída
     */
    public function concluirDesignacao($estabelecimentoId, $processoId, $designacaoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $designacao = ProcessoDesignacao::where('processo_id', $processoId)
            ->where('usuario_designado_id', auth('interno')->id())
            ->findOrFail($designacaoId);
        
        // Verifica se a designação já está concluída
        if ($designacao->status === 'concluida') {
            return redirect()
                ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
                ->with('warning', 'Esta tarefa já está marcada como concluída.');
        }
        
        // Atualiza o status para concluído
        $designacao->status = 'concluida';
        $designacao->concluida_em = now();
        $designacao->save();
        
        return redirect()
            ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
            ->with('success', 'Tarefa marcada como concluída com sucesso!');
    }

    /**
     * Cria um novo alerta para o processo
     */
    public function criarAlerta(Request $request, $estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $validated = $request->validate([
            'descricao' => 'required|string|max:500',
            'data_alerta' => 'required|date|after_or_equal:today',
        ]);
        
        ProcessoAlerta::create([
            'processo_id' => $processo->id,
            'usuario_criador_id' => auth('interno')->id(),
            'descricao' => $validated['descricao'],
            'data_alerta' => $validated['data_alerta'],
            'status' => 'pendente',
        ]);
        
        return redirect()
            ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
            ->with('success', 'Alerta criado com sucesso!');
    }

    /**
     * Marca um alerta como visualizado
     */
    public function visualizarAlerta($estabelecimentoId, $processoId, $alertaId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $alerta = ProcessoAlerta::where('processo_id', $processo->id)
            ->findOrFail($alertaId);
        
        $alerta->marcarComoVisualizado();
        
        return redirect()
            ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
            ->with('success', 'Alerta marcado como visualizado!');
    }

    /**
     * Marca um alerta como concluído
     */
    public function concluirAlerta($estabelecimentoId, $processoId, $alertaId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $alerta = ProcessoAlerta::where('processo_id', $processo->id)
            ->findOrFail($alertaId);
        
        $alerta->marcarComoConcluido();
        
        return redirect()
            ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
            ->with('success', 'Alerta marcado como concluído!');
    }

    /**
     * Exclui um alerta
     */
    public function excluirAlerta($estabelecimentoId, $processoId, $alertaId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $alerta = ProcessoAlerta::where('processo_id', $processo->id)
            ->findOrFail($alertaId);
        
        $alerta->delete();
        
        return redirect()
            ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
            ->with('success', 'Alerta excluído com sucesso!');
    }

    /**
     * Finaliza o prazo de um documento digital (marca como respondido)
     */
    public function finalizarPrazoDocumento(Request $request, $estabelecimentoId, $processoId, $documentoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);

        $documento = DocumentoDigital::where('processo_id', $processo->id)
            ->findOrFail($documentoId);

        // Verifica se o documento tem prazo
        if (!$documento->prazo_dias && !$documento->data_vencimento) {
            return back()->with('error', 'Este documento não possui prazo configurado.');
        }

        // Verifica se já está finalizado
        if ($documento->isPrazoFinalizado()) {
            return back()->with('warning', 'O prazo deste documento já foi finalizado.');
        }

        $motivo = $request->input('motivo', 'Resposta recebida e aceita');
        $documento->finalizarPrazo(auth('interno')->id(), $motivo);

        return redirect()
            ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
            ->with('success', 'Prazo do documento finalizado com sucesso!');
    }

    /**
     * Reabre o prazo de um documento digital
     */
    public function reabrirPrazoDocumento($estabelecimentoId, $processoId, $documentoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);

        $documento = DocumentoDigital::where('processo_id', $processo->id)
            ->findOrFail($documentoId);

        // Verifica se está finalizado
        if (!$documento->isPrazoFinalizado()) {
            return back()->with('warning', 'O prazo deste documento não está finalizado.');
        }

        $documento->reabrirPrazo();

        return redirect()
            ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
            ->with('success', 'Prazo do documento reaberto com sucesso!');
    }
}
