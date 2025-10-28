<?php

namespace App\Http\Controllers;

use App\Models\DocumentoDigital;
use Illuminate\Http\Request;

class AutenticidadeController extends Controller
{
    /**
     * Exibe formulário de verificação de autenticidade
     */
    public function index()
    {
        return view('public.verificar-autenticidade');
    }

    /**
     * Verifica autenticidade do documento pelo código
     */
    public function verificar(Request $request, $codigo = null)
    {
        // Se o código vier pela URL (QR Code)
        if ($codigo) {
            $codigoVerificar = $codigo;
        } 
        // Se vier pelo formulário
        else {
            $request->validate([
                'codigo' => 'required|string',
            ], [
                'codigo.required' => 'Digite o código do documento',
            ]);
            $codigoVerificar = $request->codigo;
        }

        // Busca o documento
        $documento = DocumentoDigital::where('codigo_autenticidade', $codigoVerificar)
            ->with([
                'tipoDocumento',
                'processo.tipoProcesso',
                'processo.estabelecimento',
                'assinaturas' => function($query) {
                    $query->where('status', 'assinado')->orderBy('ordem');
                },
                'assinaturas.usuarioInterno'
            ])
            ->first();

        if (!$documento) {
            return view('public.verificar-autenticidade', [
                'erro' => 'Código de autenticidade inválido ou documento não encontrado.',
                'codigo' => $codigoVerificar
            ]);
        }

        // Verifica se o documento está assinado
        if ($documento->status !== 'assinado') {
            return view('public.verificar-autenticidade', [
                'erro' => 'Este documento ainda não foi finalizado ou assinado.',
                'codigo' => $codigoVerificar
            ]);
        }

        return view('public.documento-autenticado', compact('documento'));
    }

    /**
     * Visualiza o PDF do documento autenticado
     */
    public function visualizarPdf($codigo)
    {
        $documento = DocumentoDigital::where('codigo_autenticidade', $codigo)
            ->where('status', 'assinado')
            ->firstOrFail();

        if (!$documento->arquivo_pdf) {
            abort(404, 'PDF não encontrado');
        }

        $caminhoPdf = storage_path('app/public/' . $documento->arquivo_pdf);
        
        if (!file_exists($caminhoPdf)) {
            abort(404, 'Arquivo PDF não encontrado');
        }

        return response()->file($caminhoPdf);
    }
}
