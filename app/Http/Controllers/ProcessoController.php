<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\Estabelecimento;
use App\Models\TipoProcesso;
use App\Models\ProcessoDocumento;
use App\Models\ProcessoAcompanhamento;
use App\Models\ModeloDocumento;
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
        $query = Processo::with(['estabelecimento', 'usuario', 'tipoProcesso']);

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
            return redirect()
                ->back()
                ->with('error', 'Erro ao criar processo. Por favor, tente novamente.');
        }
    }

    /**
     * Exibe detalhes de um processo
     */
    public function show($estabelecimentoId, $processoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $processo = Processo::with(['usuario', 'estabelecimento', 'documentos.usuario', 'usuariosAcompanhando'])
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
        
        return view('estabelecimentos.processos.show', compact('estabelecimento', 'processo', 'modelosDocumento', 'documentosDigitais'));
    }

    /**
     * Adiciona/Remove acompanhamento do processo
     */
    public function toggleAcompanhamento($estabelecimentoId, $processoId)
    {
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
            
            return response()->file($caminhoCompleto);
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
        
        // Retorna o arquivo para visualização inline
        return response()->file($caminhoCompleto);
    }

    /**
     * Download de arquivo do processo
     */
    public function downloadArquivo($estabelecimentoId, $processoId, $documentoId)
    {
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
}
