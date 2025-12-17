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
        
        // Alertas do processo
        $alertas = $processo->alertas()->orderBy('data_alerta', 'asc')->get();
        
        return view('company.processos.show', compact(
            'processo',
            'documentosAprovados',
            'documentosPendentes',
            'documentosRejeitados',
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
}
