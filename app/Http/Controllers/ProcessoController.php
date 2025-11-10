<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\Estabelecimento;
use App\Models\TipoProcesso;
use App\Models\ProcessoDocumento;
use App\Models\ProcessoAcompanhamento;
use App\Models\ProcessoDesignacao;
use App\Models\ProcessoAlerta;
use App\Models\ModeloDocumento;
use App\Models\UsuarioInterno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ProcessoController extends Controller
{
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

        $processos = $query->paginate(20)->withQueryString();

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

        return view('processos.index', compact('processos', 'tiposProcesso', 'statusDisponiveis', 'anos'));
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
        
        // Usa transaction para evitar duplicação de número
        try {
            $processo = \DB::transaction(function () use ($estabelecimento, $validated) {
                // Gera número do processo dentro da transaction
                $numeroData = Processo::gerarNumeroProcesso();
                
                // Cria o processo
                return Processo::create([
                    'estabelecimento_id' => $estabelecimento->id,
                    'usuario_id' => Auth::guard('interno')->id(),
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
                    $query->orderBy('created_at', 'desc');
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
        $documentosDigitais = \App\Models\DocumentoDigital::with(['tipoDocumento', 'usuarioCriador', 'assinaturas'])
            ->where('processo_id', $processoId)
            ->orderBy('created_at', 'desc')
            ->get();
        
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
        
        // Adiciona arquivos externos (exceto documentos digitais)
        foreach ($processo->documentos->where('tipo_documento', '!=', 'documento_digital') as $arquivo) {
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
        
        return view('estabelecimentos.processos.show', compact('estabelecimento', 'processo', 'modelosDocumento', 'documentosDigitais', 'todosDocumentos', 'designacoes', 'alertas'));
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
        
        $usuarioId = Auth::guard('interno')->id();
        
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
     * Remove um processo
     */
    public function destroy($estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $this->validarPermissaoAcesso($estabelecimento);
        
        $processo = Processo::where('estabelecimento_id', $estabelecimentoId)
            ->findOrFail($processoId);
        
        $numeroProcesso = $processo->numero_processo;
        $processo->delete();
        
        return redirect()
            ->route('admin.estabelecimentos.processos.index', $estabelecimentoId)
            ->with('success', 'Processo ' . $numeroProcesso . ' removido com sucesso!');
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
        
        // Verifica se é documento digital (salvo em public) ou arquivo externo (salvo em app)
        if ($documento->tipo_documento === 'documento_digital') {
            $caminhoCompleto = storage_path('app/public') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
        } else {
            $caminhoCompleto = storage_path('app') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
        }
        
        if (!file_exists($caminhoCompleto)) {
            abort(404, 'Arquivo não encontrado');
        }
        
        // Retorna o arquivo para visualização inline com headers corretos
        return response()->file($caminhoCompleto, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($caminhoCompleto) . '"'
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
        
        // Verifica se é documento digital (salvo em public) ou arquivo externo (salvo em app)
        if ($documento->tipo_documento === 'documento_digital') {
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
     * Remove arquivo do processo
     */
    public function deleteArquivo($estabelecimentoId, $processoId, $documentoId)
    {
        $documento = ProcessoDocumento::where('processo_id', $processoId)
            ->findOrFail($documentoId);
        
        try {
            // Remove arquivo físico
            $caminhoCompleto = storage_path('app') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
            if (file_exists($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }
            
            // Remove registro do banco
            $documento->delete();
            
            return redirect()
                ->back()
                ->with('success', 'Arquivo removido com sucesso!');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao remover arquivo: ' . $e->getMessage());
        }
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

            // Atualizar processo
            $processo->update([
                'status' => 'arquivado',
                'motivo_arquivamento' => $request->motivo_arquivamento,
                'data_arquivamento' => now(),
                'usuario_arquivamento_id' => Auth::guard('interno')->id(),
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

            // Atualizar processo
            $processo->update([
                'status' => 'aberto',
            ]);

            // ✅ REGISTRAR EVENTO NO HISTÓRICO
            \App\Models\ProcessoEvento::create([
                'processo_id' => $processo->id,
                'usuario_interno_id' => Auth::guard('interno')->id(),
                'tipo_evento' => 'processo_desarquivado',
                'titulo' => 'Processo Desarquivado',
                'descricao' => 'Processo foi desarquivado e reaberto',
                'dados_adicionais' => [
                    'motivo_arquivamento_anterior' => $processo->motivo_arquivamento,
                    'data_arquivamento_anterior' => $processo->data_arquivamento?->toDateTimeString(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()
                ->route('admin.estabelecimentos.processos.show', [$estabelecimentoId, $processoId])
                ->with('success', 'Processo desarquivado com sucesso!');

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
                'usuario_parada_id' => Auth::guard('interno')->id(),
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
        
        // Busca anotações do usuário atual para este documento
        $anotacoes = \App\Models\ProcessoDocumentoAnotacao::where('processo_documento_id', $documentoId)
            ->where('usuario_id', auth('interno')->id())
            ->get()
            ->map(function($anotacao) {
                return [
                    'tipo' => $anotacao->tipo,
                    'pagina' => $anotacao->pagina,
                    'dados' => $anotacao->dados,
                    'comentario' => $anotacao->comentario,
                ];
            });

        return response()->json($anotacoes);
    }

    /**
     * Salva anotações feitas em um PDF
     */
    public function salvarAnotacoes(Request $request, $documentoId)
    {
        $documento = ProcessoDocumento::findOrFail($documentoId);
        $processo = $documento->processo;
        $estabelecimento = $processo->estabelecimento;
        
        // Valida permissão de acesso
        $this->validarPermissaoAcesso($estabelecimento);
        
        // Permitir array vazio para limpar anotações
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

        try {
            // Remove anotações antigas deste documento do usuário atual
            \App\Models\ProcessoDocumentoAnotacao::where('processo_documento_id', $documentoId)
                ->where('usuario_id', auth('interno')->id())
                ->delete();

            // Salva novas anotações (se houver)
            foreach ($anotacoes as $anotacao) {
                \App\Models\ProcessoDocumentoAnotacao::create([
                    'processo_documento_id' => $documentoId,
                    'usuario_id' => auth('interno')->id(),
                    'pagina' => $anotacao['pagina'],
                    'tipo' => $anotacao['tipo'],
                    'dados' => $anotacao['dados'],
                    'comentario' => $anotacao['comentario'] ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => empty($anotacoes) ? 'Anotações limpas com sucesso!' : 'Anotações salvas com sucesso!'
            ]);

        } catch (\Exception $e) {
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
        
        // Determina se é competência estadual ou municipal baseado no tipo de processo
        $tipoProcesso = $processo->tipoProcesso;
        $isCompetenciaEstadual = $tipoProcesso && in_array($tipoProcesso->competencia, ['estadual', 'estadual_exclusivo']);
        
        // Busca setores disponíveis baseado na competência
        $setores = \App\Models\TipoSetor::where('ativo', true)
            ->orderBy('nome')
            ->get();
        
        // Filtra setores por nível de acesso
        $setoresDisponiveis = $setores->filter(function($setor) use ($isCompetenciaEstadual) {
            if (!$setor->niveis_acesso || count($setor->niveis_acesso) === 0) {
                return true;
            }
            
            // Se é competência estadual, mostra setores estaduais
            if ($isCompetenciaEstadual) {
                return in_array('gestor_estadual', $setor->niveis_acesso) || 
                       in_array('tecnico_estadual', $setor->niveis_acesso);
            } else {
                // Se é municipal, mostra setores municipais
                return in_array('gestor_municipal', $setor->niveis_acesso) || 
                       in_array('tecnico_municipal', $setor->niveis_acesso);
            }
        })->values();
        
        // Busca usuários internos ativos
        $query = UsuarioInterno::where('ativo', true);
        
        // Filtra por município se for competência municipal
        if (!$isCompetenciaEstadual) {
            $query->where('municipio_id', $estabelecimento->municipio_id);
        } else {
            // Se for estadual, pega apenas usuários estaduais (sem município)
            $query->whereNull('municipio_id');
        }
        
        $usuarios = $query->orderBy('nome')
            ->get(['id', 'nome', 'cargo', 'nivel_acesso', 'setor']);
        
        // Agrupa usuários por setor
        $usuariosPorSetor = [];
        foreach ($setoresDisponiveis as $setor) {
            $usuariosDoSetor = $usuarios->where('setor', $setor->codigo)->values();
            $usuariosPorSetor[] = [
                'setor' => [
                    'codigo' => $setor->codigo,
                    'nome' => $setor->nome,
                ],
                'usuarios' => $usuariosDoSetor
            ];
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
        
        return response()->json([
            'setores' => $setoresDisponiveis,
            'usuariosPorSetor' => $usuariosPorSetor,
            'isCompetenciaEstadual' => $isCompetenciaEstadual
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
        ]);
        
        $designados = 0;
        $tipoProcesso = $processo->tipoProcesso;
        $isCompetenciaEstadual = $tipoProcesso && in_array($tipoProcesso->competencia, ['estadual', 'estadual_exclusivo']);
        
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
}
