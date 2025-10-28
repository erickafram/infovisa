<?php

namespace App\Http\Controllers;

use App\Models\DocumentoDigital;
use App\Models\DocumentoAssinatura;
use App\Models\UsuarioInterno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class AssinaturaDigitalController extends Controller
{
    /**
     * Exibe formulário para configurar senha de assinatura
     */
    public function configurarSenha()
    {
        $usuario = auth('interno')->user();
        return view('assinatura.configurar-senha', compact('usuario'));
    }

    /**
     * Salva/atualiza senha de assinatura digital
     */
    public function salvarSenha(Request $request)
    {
        $usuario = auth('interno')->user();

        $request->validate([
            'senha_atual' => 'required',
            'senha_assinatura' => 'required|min:6|confirmed',
        ], [
            'senha_atual.required' => 'Digite sua senha de login para confirmar',
            'senha_assinatura.required' => 'Digite a senha de assinatura digital',
            'senha_assinatura.min' => 'A senha deve ter no mínimo 6 caracteres',
            'senha_assinatura.confirmed' => 'As senhas não conferem',
        ]);

        // Verifica se a senha atual está correta
        if (!Hash::check($request->senha_atual, $usuario->password)) {
            return back()->withErrors(['senha_atual' => 'Senha de login incorreta'])->withInput();
        }

        // Atualiza a senha de assinatura
        $usuario->senha_assinatura_digital = $request->senha_assinatura;
        $usuario->save();

        return redirect()
            ->route('admin.assinatura.configurar-senha')
            ->with('success', 'Senha de assinatura digital configurada com sucesso!');
    }

    /**
     * Lista documentos pendentes de assinatura do usuário
     */
    public function documentosPendentes()
    {
        $usuario = auth('interno')->user();

        $assinaturasPendentes = DocumentoAssinatura::where('usuario_interno_id', $usuario->id)
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', '!=', 'rascunho');
            })
            ->with(['documentoDigital.tipoDocumento', 'documentoDigital.processo'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('assinatura.pendentes', compact('assinaturasPendentes'));
    }

    /**
     * Exibe página para assinar documento
     */
    public function assinar($documentoId)
    {
        try {
            $usuario = auth('interno')->user();
            
            \Log::info('Tentando acessar página de assinatura', [
                'documento_id' => $documentoId,
                'usuario_id' => $usuario->id,
                'tem_senha_assinatura' => $usuario->temSenhaAssinatura()
            ]);

            // Verifica se o usuário tem senha de assinatura cadastrada
            if (!$usuario->temSenhaAssinatura()) {
                \Log::info('Usuário sem senha de assinatura, redirecionando para configuração');
                return redirect()
                    ->route('admin.assinatura.configurar-senha')
                    ->with('warning', 'Você precisa configurar sua senha de assinatura digital primeiro.');
            }

            $documento = DocumentoDigital::with(['tipoDocumento', 'processo.estabelecimento'])
                ->findOrFail($documentoId);

            // Verifica se o usuário está na lista de assinantes
            $assinatura = DocumentoAssinatura::where('documento_digital_id', $documentoId)
                ->where('usuario_interno_id', $usuario->id)
                ->firstOrFail();

            // Verifica se já assinou
            if ($assinatura->status === 'assinado') {
                \Log::info('Documento já foi assinado pelo usuário');
                return redirect()
                    ->route('admin.assinatura.pendentes')
                    ->with('info', 'Você já assinou este documento.');
            }

            \Log::info('Exibindo página de assinatura');
            return view('assinatura.assinar', compact('documento', 'assinatura'));
        } catch (\Exception $e) {
            \Log::error('Erro ao acessar página de assinatura', [
                'documento_id' => $documentoId,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->route('admin.assinatura.pendentes')
                ->with('error', 'Erro ao acessar documento: ' . $e->getMessage());
        }
    }

    /**
     * Processa a assinatura do documento
     */
    public function processar(Request $request, $documentoId)
    {
        $usuario = auth('interno')->user();

        $request->validate([
            'senha_assinatura' => 'required',
            'acao' => 'required|in:assinar,recusar',
            'motivo_recusa' => 'required_if:acao,recusar',
        ], [
            'senha_assinatura.required' => 'Digite sua senha de assinatura digital',
            'motivo_recusa.required_if' => 'Informe o motivo da recusa',
        ]);

        // Verifica se a senha de assinatura está correta
        if (!Hash::check($request->senha_assinatura, $usuario->senha_assinatura_digital)) {
            return back()->withErrors(['senha_assinatura' => 'Senha de assinatura incorreta'])->withInput();
        }

        $documento = DocumentoDigital::with('processo')->findOrFail($documentoId);
        
        $assinatura = DocumentoAssinatura::where('documento_digital_id', $documentoId)
            ->where('usuario_interno_id', $usuario->id)
            ->firstOrFail();

        if ($request->acao === 'assinar') {
            // Assina o documento
            $assinatura->status = 'assinado';
            $assinatura->assinado_em = now();
            $assinatura->hash_assinatura = hash('sha256', $documento->id . $usuario->id . now());
            $assinatura->save();

            // Verifica se todos assinaram
            $todasAssinaturas = DocumentoAssinatura::where('documento_digital_id', $documentoId)
                ->where('obrigatoria', true)
                ->get();

            $todasAssinadas = $todasAssinaturas->every(function ($assinatura) {
                return $assinatura->status === 'assinado';
            });

            if ($todasAssinadas) {
                // Gera código de autenticidade se ainda não tiver
                if (!$documento->codigo_autenticidade) {
                    $documento->codigo_autenticidade = DocumentoDigital::gerarCodigoAutenticidade();
                }
                
                // Atualiza status do documento para "assinado"
                $documento->status = 'assinado';
                $documento->save();

                // Gerar PDF com as assinaturas
                $this->gerarPdfComAssinaturas($documento);
            }

            // Redireciona para a lista de documentos pendentes
            return redirect()
                ->route('admin.documentos.index')
                ->with('success', 'Documento assinado com sucesso!');
        } else {
            // Recusa o documento
            $assinatura->status = 'recusado';
            $assinatura->observacao = $request->motivo_recusa;
            $assinatura->save();

            // Atualiza status do documento para "recusado"
            $documento->status = 'recusado';
            $documento->save();

            return redirect()
                ->route('admin.assinatura.pendentes')
                ->with('info', 'Documento recusado.');
        }
    }

    /**
     * Gera PDF do documento com assinaturas eletrônicas
     */
    private function gerarPdfComAssinaturas(DocumentoDigital $documento)
    {
        // Recarrega o documento com todos os relacionamentos
        $documento = DocumentoDigital::with([
            'tipoDocumento',
            'processo.tipoProcesso',
            'processo.estabelecimento.responsaveis',
            'assinaturas' => function($query) {
                $query->where('status', 'assinado')->orderBy('ordem');
            },
            'assinaturas.usuarioInterno'
        ])->findOrFail($documento->id);

        // Gera URL de autenticidade
        $urlAutenticidade = route('verificar.autenticidade', ['codigo' => $documento->codigo_autenticidade]);

        // Gera QR Code
        $qrCode = new QrCode($urlAutenticidade);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        $qrCodeBase64 = base64_encode($result->getString());

        // Debug: Log do processo
        \Log::info('Gerando PDF - Processo:', [
            'documento_id' => $documento->id,
            'processo_id' => $documento->processo_id,
            'processo_existe' => $documento->processo ? 'sim' : 'não',
            'processo_numero' => $documento->processo->numero ?? 'null',
            'processo_tipo' => $documento->processo->tipo ?? 'null',
        ]);

        // Prepara dados para o PDF
        $data = [
            'documento' => $documento,
            'estabelecimento' => $documento->processo->estabelecimento ?? null,
            'processo' => $documento->processo ?? null,
            'assinaturas' => $documento->assinaturas,
            'urlAutenticidade' => $urlAutenticidade,
            'codigoAutenticidade' => $documento->codigo_autenticidade,
            'qrCodeBase64' => $qrCodeBase64,
        ];

        // Gera o PDF
        $pdf = Pdf::loadView('documentos.pdf-assinado', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 15)
            ->setOption('margin-right', 15);

        // Salva o PDF
        $nomeArquivo = 'documentos/' . $documento->numero_documento . '.pdf';
        Storage::disk('public')->put($nomeArquivo, $pdf->output());

        // Atualiza o documento com o caminho do PDF
        $documento->arquivo_pdf = $nomeArquivo;
        $documento->save();

        return $nomeArquivo;
    }
}
