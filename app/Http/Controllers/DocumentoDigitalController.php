<?php

namespace App\Http\Controllers;

use App\Models\DocumentoDigital;
use App\Models\DocumentoAssinatura;
use App\Models\TipoDocumento;
use App\Models\ModeloDocumento;
use App\Models\UsuarioInterno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentoDigitalController extends Controller
{
    /**
     * Lista todos os documentos digitais
     */
    public function index()
    {
        $documentos = DocumentoDigital::with(['tipoDocumento', 'usuarioCriador', 'processo'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('documentos.index', compact('documentos'));
    }

    /**
     * Exibe formulário para criar novo documento
     */
    public function create(Request $request)
    {
        $tiposDocumento = TipoDocumento::where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $usuariosInternos = UsuarioInterno::where('ativo', true)
            ->orderBy('nome')
            ->get();

        $processoId = $request->get('processo_id');
        $processo = null;

        if ($processoId) {
            $processo = \App\Models\Processo::with('estabelecimento')->find($processoId);
        }

        return view('documentos.create', compact('tiposDocumento', 'usuariosInternos', 'processo'));
    }

    /**
     * Busca modelos por tipo de documento (AJAX)
     */
    public function buscarModelos(Request $request)
    {
        $modelos = ModeloDocumento::where('tipo_documento_id', $request->tipo_documento_id)
            ->where('ativo', true)
            ->orderBy('ordem')
            ->get(['id', 'descricao', 'conteudo']);

        return response()->json($modelos);
    }

    /**
     * Salva novo documento
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'conteudo' => 'required',
            'sigiloso' => 'boolean',
            'assinaturas' => 'required|array|min:1',
            'assinaturas.*' => 'exists:usuarios_internos,id',
        ]);

        try {
            DB::beginTransaction();

            $documento = DocumentoDigital::create([
                'tipo_documento_id' => $request->tipo_documento_id,
                'processo_id' => $request->processo_id,
                'usuario_criador_id' => Auth::guard('interno')->id(),
                'numero_documento' => DocumentoDigital::gerarNumeroDocumento(),
                'conteudo' => $request->conteudo,
                'sigiloso' => $request->sigiloso ?? false,
                'status' => $request->acao === 'finalizar' ? 'aguardando_assinatura' : 'rascunho',
            ]);

            // Criar assinaturas
            foreach ($request->assinaturas as $index => $usuarioId) {
                DocumentoAssinatura::create([
                    'documento_digital_id' => $documento->id,
                    'usuario_interno_id' => $usuarioId,
                    'ordem' => $index + 1,
                    'obrigatoria' => true,
                    'status' => 'pendente',
                ]);
            }

            DB::commit();

            // Se veio de um processo, redireciona de volta para o processo
            if ($request->processo_id) {
                $processo = \App\Models\Processo::find($request->processo_id);
                return redirect()->route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id])
                    ->with('success', 'Documento criado com sucesso!');
            }

            return redirect()->route('admin.documentos.show', $documento->id)
                ->with('success', 'Documento criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao criar documento: ' . $e->getMessage());
        }
    }

    /**
     * Exibe documento
     */
    public function show($id)
    {
        $documento = DocumentoDigital::with(['tipoDocumento', 'usuarioCriador', 'processo', 'assinaturas.usuarioInterno'])
            ->findOrFail($id);

        return view('documentos.show', compact('documento'));
    }

    /**
     * Gera PDF do documento
     */
    public function gerarPdf($id)
    {
        $documento = DocumentoDigital::findOrFail($id);

        $pdf = Pdf::loadHTML($documento->conteudo);
        
        return $pdf->download($documento->numero_documento . '.pdf');
    }

    /**
     * Assina documento
     */
    public function assinar(Request $request, $id)
    {
        $documento = DocumentoDigital::findOrFail($id);
        $usuarioId = Auth::guard('interno')->id();

        $assinatura = DocumentoAssinatura::where('documento_digital_id', $id)
            ->where('usuario_interno_id', $usuarioId)
            ->where('status', 'pendente')
            ->firstOrFail();

        $assinatura->update([
            'status' => 'assinado',
            'assinado_em' => now(),
            'hash_assinatura' => hash('sha256', $documento->id . $usuarioId . now()),
        ]);

        // Verifica se todas assinaturas foram feitas
        if ($documento->todasAssinaturasCompletas()) {
            $documento->update([
                'status' => 'assinado',
                'finalizado_em' => now(),
            ]);
        }

        return back()->with('success', 'Documento assinado com sucesso!');
    }
}
