<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentoPop;
use App\Models\CategoriaPop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentoPopController extends Controller
{
    /**
     * Lista todos os documentos POPs
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'documentos');
        
        $documentos = DocumentoPop::with(['criador', 'categorias'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $categorias = CategoriaPop::withCount('documentos')
            ->ordenadas()
            ->paginate(20, ['*'], 'categorias_page');

        return view('admin.documentos-pops.index', compact('documentos', 'categorias', 'tab'));
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        $categorias = CategoriaPop::ativas()->ordenadas()->get();
        return view('admin.documentos-pops.create', compact('categorias'));
    }

    /**
     * Armazena novo documento (ou múltiplos documentos)
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'arquivos' => 'required|array|min:1',
            'arquivos.*' => 'file|mimes:pdf,doc,docx,txt|max:10240', // 10MB cada
            'disponivel_ia' => 'nullable|boolean',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias_pops,id',
        ]);

        try {
            $arquivos = $request->file('arquivos');
            $documentosCriados = 0;
            $erros = [];
            
            foreach ($arquivos as $arquivo) {
                try {
                    $nomeOriginal = $arquivo->getClientOriginalName();
                    $mimeType = $arquivo->getMimeType();
                    $tamanho = $arquivo->getSize();
                    
                    // Salva arquivo
                    $path = $arquivo->store('documentos-pops', 'public');

                    // Define título: se múltiplos arquivos, usa nome do arquivo; senão usa título fornecido
                    $titulo = count($arquivos) > 1 
                        ? pathinfo($nomeOriginal, PATHINFO_FILENAME) 
                        : $request->titulo;

                    // Cria registro
                    $documento = DocumentoPop::create([
                        'titulo' => $titulo,
                        'descricao' => $request->descricao,
                        'arquivo_nome' => $nomeOriginal,
                        'arquivo_path' => $path,
                        'arquivo_mime_type' => $mimeType,
                        'arquivo_tamanho' => $tamanho,
                        'disponivel_ia' => $request->has('disponivel_ia'),
                        'criado_por' => auth('interno')->id(),
                    ]);

                    // Sincroniza categorias
                    if ($request->has('categorias')) {
                        $documento->categorias()->sync($request->categorias);
                    }

                    // Se marcado para IA, extrai conteúdo
                    if ($documento->disponivel_ia) {
                        $this->extrairConteudo($documento);
                    }
                    
                    $documentosCriados++;
                    
                } catch (\Exception $e) {
                    $erros[] = "Erro ao processar '{$nomeOriginal}': " . $e->getMessage();
                    Log::error('Erro ao processar arquivo individual', [
                        'arquivo' => $nomeOriginal,
                        'erro' => $e->getMessage(),
                    ]);
                }
            }

            // Mensagem de sucesso
            if ($documentosCriados > 0) {
                $mensagem = $documentosCriados === 1 
                    ? 'Documento POP criado com sucesso!' 
                    : "{$documentosCriados} documentos POPs criados com sucesso!";
                
                if (!empty($erros)) {
                    $mensagem .= ' Alguns arquivos apresentaram erros: ' . implode('; ', $erros);
                }
                
                return redirect()
                    ->route('admin.configuracoes.documentos-pops.index')
                    ->with('success', $mensagem);
            } else {
                return back()
                    ->withInput()
                    ->with('error', 'Nenhum documento foi criado. Erros: ' . implode('; ', $erros));
            }

        } catch (\Exception $e) {
            Log::error('Erro ao criar documentos POP', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar documentos: ' . $e->getMessage());
        }
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(DocumentoPop $documentoPop)
    {
        $categorias = CategoriaPop::ativas()->ordenadas()->get();
        $categoriasSelecionadas = $documentoPop->categorias->pluck('id')->toArray();
        
        return view('admin.documentos-pops.edit', compact('documentoPop', 'categorias', 'categoriasSelecionadas'));
    }

    /**
     * Atualiza documento
     */
    public function update(Request $request, DocumentoPop $documentoPop)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'arquivo' => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240',
            'disponivel_ia' => 'nullable|boolean',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias_pops,id',
        ]);

        try {
            $dados = [
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'disponivel_ia' => $request->has('disponivel_ia'),
                'atualizado_por' => auth('interno')->id(),
            ];

            // Se enviou novo arquivo
            if ($request->hasFile('arquivo')) {
                // Remove arquivo antigo
                Storage::disk('public')->delete($documentoPop->arquivo_path);

                $arquivo = $request->file('arquivo');
                $dados['arquivo_nome'] = $arquivo->getClientOriginalName();
                $dados['arquivo_path'] = $arquivo->store('documentos-pops', 'public');
                $dados['arquivo_mime_type'] = $arquivo->getMimeType();
                $dados['arquivo_tamanho'] = $arquivo->getSize();
                
                // Reseta indexação
                $dados['conteudo_extraido'] = null;
                $dados['indexado_em'] = null;
            }

            $documentoPop->update($dados);

            // Sincroniza categorias
            if ($request->has('categorias')) {
                $documentoPop->categorias()->sync($request->categorias);
            } else {
                $documentoPop->categorias()->detach();
            }

            // Se marcado para IA e não está indexado, extrai conteúdo
            if ($documentoPop->disponivel_ia && !$documentoPop->isIndexado()) {
                $this->extrairConteudo($documentoPop);
            }

            // Se desmarcou IA, limpa indexação
            if (!$documentoPop->disponivel_ia && $documentoPop->isIndexado()) {
                $documentoPop->update([
                    'conteudo_extraido' => null,
                    'indexado_em' => null,
                ]);
            }

            return redirect()
                ->route('admin.configuracoes.documentos-pops.index')
                ->with('success', 'Documento POP atualizado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar documento POP', [
                'documento_id' => $documentoPop->id,
                'erro' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar documento: ' . $e->getMessage());
        }
    }

    /**
     * Remove documento
     */
    public function destroy(DocumentoPop $documentoPop)
    {
        try {
            // Remove arquivo
            Storage::disk('public')->delete($documentoPop->arquivo_path);
            
            // Remove registro
            $documentoPop->delete();

            return redirect()
                ->route('admin.configuracoes.documentos-pops.index')
                ->with('success', 'Documento POP excluído com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao excluir documento POP', [
                'documento_id' => $documentoPop->id,
                'erro' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Erro ao excluir documento: ' . $e->getMessage());
        }
    }

    /**
     * Download do arquivo
     */
    public function download(DocumentoPop $documentoPop)
    {
        try {
            $path = storage_path('app/public/' . $documentoPop->arquivo_path);
            
            if (!file_exists($path)) {
                abort(404, 'Arquivo não encontrado');
            }

            return response()->download($path, $documentoPop->arquivo_nome);

        } catch (\Exception $e) {
            Log::error('Erro ao fazer download de documento POP', [
                'documento_id' => $documentoPop->id,
                'erro' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Erro ao fazer download do documento');
        }
    }

    /**
     * Visualiza o arquivo no navegador
     */
    public function visualizar(DocumentoPop $documentoPop)
    {
        try {
            $path = storage_path('app/public/' . $documentoPop->arquivo_path);
            
            if (!file_exists($path)) {
                abort(404, 'Arquivo não encontrado');
            }

            return response()->file($path);

        } catch (\Exception $e) {
            Log::error('Erro ao visualizar documento POP', [
                'documento_id' => $documentoPop->id,
                'erro' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Erro ao visualizar documento');
        }
    }

    /**
     * Reindexar documento para IA
     */
    public function reindexar(DocumentoPop $documentoPop)
    {
        try {
            if (!$documentoPop->disponivel_ia) {
                return back()
                    ->with('error', 'Este documento não está marcado para uso do Assistente IA');
            }

            $this->extrairConteudo($documentoPop);

            return back()
                ->with('success', 'Documento reindexado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao reindexar documento POP', [
                'documento_id' => $documentoPop->id,
                'erro' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Erro ao reindexar documento: ' . $e->getMessage());
        }
    }

    /**
     * Extrai conteúdo do documento para indexação
     */
    private function extrairConteudo(DocumentoPop $documento)
    {
        try {
            $path = storage_path('app/public/' . $documento->arquivo_path);
            $conteudo = '';

            // Extrai baseado no tipo de arquivo
            if ($documento->arquivo_mime_type === 'application/pdf') {
                $conteudo = $this->extrairPdf($path);
            } elseif (in_array($documento->arquivo_mime_type, [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ])) {
                $conteudo = $this->extrairWord($path);
            } elseif ($documento->arquivo_mime_type === 'text/plain') {
                $conteudo = file_get_contents($path);
            }

            // Limpa e normaliza o conteúdo
            $conteudo = $this->limparConteudo($conteudo);

            // Atualiza documento
            $documento->update([
                'conteudo_extraido' => $conteudo,
                'indexado_em' => now(),
            ]);

            Log::info('Conteúdo extraído com sucesso', [
                'documento_id' => $documento->id,
                'tamanho_conteudo' => strlen($conteudo),
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao extrair conteúdo do documento', [
                'documento_id' => $documento->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Extrai texto de PDF
     */
    private function extrairPdf($path): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($path);
            return $pdf->getText();
        } catch (\Exception $e) {
            Log::warning('Erro ao extrair PDF, tentando método alternativo', [
                'erro' => $e->getMessage()
            ]);
            
            // Método alternativo usando pdftotext se disponível
            if (function_exists('shell_exec')) {
                $output = shell_exec("pdftotext '$path' -");
                if ($output) {
                    return $output;
                }
            }
            
            return '';
        }
    }

    /**
     * Extrai texto de Word
     */
    private function extrairWord($path): string
    {
        try {
            // Para .docx (ZIP-based)
            if (strpos($path, '.docx') !== false) {
                $zip = new \ZipArchive();
                if ($zip->open($path) === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    $zip->close();
                    
                    if ($xml) {
                        $xml = simplexml_load_string($xml);
                        $text = strip_tags($xml->asXML());
                        return $text;
                    }
                }
            }
            
            // Para .doc (formato antigo) - mais complexo, retorna vazio
            return '';
            
        } catch (\Exception $e) {
            Log::warning('Erro ao extrair Word', [
                'erro' => $e->getMessage()
            ]);
            return '';
        }
    }

    /**
     * Limpa e normaliza conteúdo extraído
     */
    private function limparConteudo($conteudo): string
    {
        // Remove espaços múltiplos
        $conteudo = preg_replace('/\s+/', ' ', $conteudo);
        
        // Remove caracteres especiais problemáticos
        $conteudo = preg_replace('/[^\p{L}\p{N}\s\.,;:!?\-()]/u', '', $conteudo);
        
        // Trim
        $conteudo = trim($conteudo);
        
        return $conteudo;
    }
}
