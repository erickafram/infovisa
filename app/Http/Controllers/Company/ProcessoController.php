<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use App\Models\Processo;
use Illuminate\Http\Request;

class ProcessoController extends Controller
{
    public function index(Request $request)
    {
        $usuarioId = auth('externo')->id();
        
        // IDs dos estabelecimentos do usuário
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuarioId)->pluck('id');
        
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
        $estabelecimentos = Estabelecimento::where('usuario_externo_id', $usuarioId)
            ->orderBy('nome_fantasia')
            ->get();
        
        return view('company.processos.index', compact('processos', 'estatisticas', 'estabelecimentos'));
    }
    
    public function show($id)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuarioId)->pluck('id');
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->with(['estabelecimento', 'tipoProcesso', 'documentos.usuarioExterno', 'alertas'])
            ->findOrFail($id);
        
        // Documentos separados por status
        $documentosAprovados = $processo->documentos->where('status_aprovacao', 'aprovado');
        $documentosPendentes = $processo->documentos->where('status_aprovacao', 'pendente');
        $documentosRejeitados = $processo->documentos->where('status_aprovacao', 'rejeitado');
        
        // Documentos digitais da vigilância (assinados e não sigilosos)
        $documentosVigilancia = \App\Models\DocumentoDigital::where('processo_id', $processo->id)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->with(['tipoDocumento', 'usuarioCriador', 'assinaturas'])
            ->orderBy('created_at', 'desc')
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
        
        // Alertas do processo
        $alertas = $processo->alertas()->orderBy('data_alerta', 'asc')->get();
        
        return view('company.processos.show', compact(
            'processo',
            'documentosAprovados',
            'documentosPendentes',
            'documentosRejeitados',
            'documentosVigilancia',
            'alertas'
        ));
    }

    public function uploadDocumento(Request $request, $id)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuarioId)->pluck('id');
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($id);

        $request->validate([
            'arquivo' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'observacoes' => 'nullable|string|max:500',
        ], [
            'arquivo.required' => 'Selecione um arquivo para enviar.',
            'arquivo.max' => 'O arquivo não pode ter mais de 10MB.',
            'arquivo.mimes' => 'Formato de arquivo não permitido. Use: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG ou GIF.',
        ]);

        $arquivo = $request->file('arquivo');
        $nomeOriginal = $arquivo->getClientOriginalName();
        $extensao = $arquivo->getClientOriginalExtension();
        $tamanho = $arquivo->getSize();
        $nomeArquivo = time() . '_' . uniqid() . '.' . $extensao;
        
        // Salva o arquivo
        $caminho = $arquivo->storeAs(
            'processos/' . $processo->id . '/documentos',
            $nomeArquivo,
            'public'
        );

        // Cria o registro do documento com status pendente
        \App\Models\ProcessoDocumento::create([
            'processo_id' => $processo->id,
            'usuario_externo_id' => $usuarioId,
            'tipo_usuario' => 'externo',
            'nome_arquivo' => $nomeArquivo,
            'nome_original' => $nomeOriginal,
            'caminho' => $caminho,
            'extensao' => strtolower($extensao),
            'tamanho' => $tamanho,
            'tipo_documento' => 'arquivo_externo',
            'observacoes' => $request->observacoes,
            'status_aprovacao' => 'pendente',
        ]);

        return redirect()->route('company.processos.show', $processo->id)
            ->with('success', 'Arquivo enviado com sucesso! Aguarde a aprovação da Vigilância Sanitária.');
    }

    public function downloadDocumento($processoId, $documentoId)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuarioId)->pluck('id');
        
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
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuarioId)->pluck('id');
        
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
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuarioId)->pluck('id');
        
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
     * Visualiza um documento digital da vigilância
     * Registra a visualização para início da contagem de prazo (§1º)
     */
    public function visualizarDocumentoDigital($processoId, $documentoId)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuarioId)->pluck('id');
        
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
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuarioId)->pluck('id');
        
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
}
