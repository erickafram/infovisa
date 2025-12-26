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
        
        return view('company.alertas.index', compact('alertas', 'estatisticas', 'estabelecimentos'));
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
            ->with(['estabelecimento', 'tipoProcesso', 'documentos.usuarioExterno', 'alertas'])
            ->findOrFail($id);
        
        // Documentos separados por status
        $documentosAprovados = $processo->documentos->where('status_aprovacao', 'aprovado');
        $documentosPendentes = $processo->documentos->where('status_aprovacao', 'pendente');
        // Documentos rejeitados que ainda não foram substituídos (não têm correção pendente)
        $documentosRejeitados = $processo->documentos->where('status_aprovacao', 'rejeitado')
            ->filter(function ($doc) use ($processo) {
                // Verifica se existe algum documento que substitui este
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
            ]);
        }
        
        // Adiciona documentos aprovados com tipo identificador
        foreach ($documentosAprovados as $doc) {
            $todosDocumentos->push([
                'tipo' => 'aprovado',
                'documento' => $doc,
                'data' => $doc->created_at,
            ]);
        }
        
        // Ordena por data decrescente (mais recente primeiro)
        $todosDocumentos = $todosDocumentos->sortByDesc('data')->values();
        
        // Alertas do processo
        $alertas = $processo->alertas()->orderBy('data_alerta', 'asc')->get();
        
        return view('company.processos.show', compact(
            'processo',
            'documentosAprovados',
            'documentosPendentes',
            'documentosRejeitados',
            'documentosVigilancia',
            'todosDocumentos',
            'alertas'
        ));
    }

    public function uploadDocumento(Request $request, $id)
    {
        $usuarioId = auth('externo')->id();
        $estabelecimentoIds = $this->estabelecimentoIdsDoUsuario();
        
        $processo = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->findOrFail($id);

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
}
