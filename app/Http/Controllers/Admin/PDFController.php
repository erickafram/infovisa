<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PDFController extends Controller
{
    /**
     * Faz proxy de PDF para evitar problemas de CORS
     */
    public function proxy(Request $request): Response
    {
        $url = $request->query('url');
        
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'URL inválida');
        }
        
        // Validar domínios permitidos
        $allowedDomains = [
            'diariooficial.to.gov.br',
            'doe.to.gov.br',
            'to.gov.br'
        ];
        
        $urlHost = parse_url($url, PHP_URL_HOST);
        $isAllowed = false;
        
        foreach ($allowedDomains as $domain) {
            if (str_contains($urlHost, $domain)) {
                $isAllowed = true;
                break;
            }
        }
        
        if (!$isAllowed) {
            abort(403, 'Domínio não permitido');
        }
        
        // Fazer proxy do PDF
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Accept: application/pdf,*/*'
            ]
        ]);
        
        $content = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$content) {
            abort(404, 'Erro ao baixar o arquivo');
        }
        
        return response($content)
            ->header('Content-Type', $contentType ?: 'application/pdf')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Content-Disposition', 'inline');
    }
    
    /**
     * Exibe visualizador de PDF
     */
    public function viewer(Request $request): View
    {
        return view('admin.diario-oficial.viewer-standalone', [
            'pdfUrl' => $request->query('url'),
            'searchText' => $request->query('texto', ''),
            'title' => $request->query('titulo', 'Documento PDF')
        ]);
    }
    
    /**
     * Busca texto dentro do PDF
     */
    public function searchInPdf(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'texto' => 'required|string|min:1'
        ]);
        
        $pdfUrl = $request->input('url');
        $searchText = $request->input('texto');
        
        try {
            // Baixar PDF
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $pdfUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                CURLOPT_TIMEOUT => 60,
            ]);
            
            $pdfContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200 || !$pdfContent) {
                throw new \Exception('Erro ao baixar PDF');
            }
            
            // Extrair texto e buscar
            $results = $this->extractAndSearchText($pdfContent, $searchText);
            
            Log::info('Busca em PDF concluída', [
                'searchText' => $searchText,
                'totalPages' => count($results),
                'totalOccurrences' => array_sum(array_column($results, 'occurrences')),
                'pdfSize' => strlen($pdfContent)
            ]);
            
            return response()->json([
                'success' => true,
                'searchText' => $searchText,
                'totalOccurrences' => array_sum(array_column($results, 'occurrences')),
                'pages' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar no PDF', [
                'url' => $pdfUrl,
                'texto' => $searchText,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar PDF: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Extrai texto do PDF e busca ocorrências
     */
    private function extractAndSearchText(string $pdfContent, string $searchText): array
    {
        $results = [];
        
        try {
            // Método 1: Tentar usar pdftotext se disponível (Linux/Mac)
            if (function_exists('shell_exec') && !empty(shell_exec('which pdftotext 2>/dev/null'))) {
                Log::info('Usando pdftotext para extração');
                $tempFile = tempnam(sys_get_temp_dir(), 'pdf_search_');
                file_put_contents($tempFile, $pdfContent);
                
                $textOutput = shell_exec("pdftotext -layout '$tempFile' - 2>/dev/null");
                
                if ($textOutput) {
                    $results = $this->findTextInPdfOutput($textOutput, $searchText);
                    Log::info('pdftotext encontrou', ['pages' => count($results)]);
                }
                
                @unlink($tempFile);
            } else {
                Log::info('pdftotext não disponível, usando método binário');
            }
            
            // Método 2: Busca no conteúdo binário (fallback - funciona no Windows)
            if (empty($results)) {
                Log::info('Usando extração binária de PDF');
                $results = $this->findTextInBinaryPdf($pdfContent, $searchText);
                Log::info('Extração binária encontrou', ['pages' => count($results)]);
            }
            
        } catch (\Exception $e) {
            Log::warning('Erro na extração de texto PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $results;
    }
    
    /**
     * Busca texto na saída do pdftotext
     */
    private function findTextInPdfOutput(string $text, string $searchText): array
    {
        $pages = [];
        $lines = explode("\n", $text);
        $currentPage = 1;
        $pageContent = '';
        
        foreach ($lines as $line) {
            // Detectar quebra de página (form feed)
            if (strpos($line, "\f") !== false) {
                // Processar página atual
                if (stripos($pageContent, $searchText) !== false) {
                    $contexts = $this->extractContexts($pageContent, $searchText);
                    $pages[] = [
                        'page' => $currentPage,
                        'contexts' => $contexts,
                        'occurrences' => substr_count(strtolower($pageContent), strtolower($searchText))
                    ];
                }
                
                $currentPage++;
                $pageContent = '';
            } else {
                $pageContent .= $line . "\n";
            }
        }
        
        // Processar última página
        if (stripos($pageContent, $searchText) !== false) {
            $contexts = $this->extractContexts($pageContent, $searchText);
            $pages[] = [
                'page' => $currentPage,
                'contexts' => $contexts,
                'occurrences' => substr_count(strtolower($pageContent), strtolower($searchText))
            ];
        }
        
        return $pages;
    }
    
    /**
     * Busca texto no PDF binário (fallback)
     */
    private function findTextInBinaryPdf(string $pdfContent, string $searchText): array
    {
        $pages = [];
        
        // Método 1: Extrair comandos de texto do PDF (mais preciso)
        $textCommands = $this->extractPdfTextCommands($pdfContent);
        Log::info('Comandos de texto extraídos', ['total' => count($textCommands)]);
        
        if (!empty($textCommands)) {
            $pageNum = 1;
            $currentPageText = '';
            
            foreach ($textCommands as $command) {
                // Detectar quebra de página
                if (strpos($command, '/Type /Page') !== false || strpos($command, 'showpage') !== false) {
                    if (!empty($currentPageText) && stripos($currentPageText, $searchText) !== false) {
                        $contexts = $this->extractContexts($currentPageText, $searchText);
                        if (!empty($contexts)) {
                            $pages[] = [
                                'page' => $pageNum,
                                'contexts' => $contexts,
                                'occurrences' => substr_count(strtolower($currentPageText), strtolower($searchText))
                            ];
                        }
                    }
                    $pageNum++;
                    $currentPageText = '';
                } else {
                    $currentPageText .= ' ' . $command;
                }
            }
            
            // Processar última página
            if (!empty($currentPageText) && stripos($currentPageText, $searchText) !== false) {
                $contexts = $this->extractContexts($currentPageText, $searchText);
                if (!empty($contexts)) {
                    $pages[] = [
                        'page' => $pageNum,
                        'contexts' => $contexts,
                        'occurrences' => substr_count(strtolower($currentPageText), strtolower($searchText))
                    ];
                }
            }
        }
        
        // Método 2: Fallback - extrair de streams (se método 1 não encontrou nada)
        if (empty($pages)) {
            Log::info('Método 1 não encontrou resultados, tentando método 2 (streams)');
            preg_match_all('/stream(.*?)endstream/s', $pdfContent, $matches);
            Log::info('Streams encontrados', ['total' => count($matches[1])]);
            
            $pageNum = 1;
            foreach ($matches[1] as $stream) {
                // Tentar decodificar stream
                $decoded = @gzuncompress($stream);
                if ($decoded === false) {
                    $decoded = $stream;
                }
                
                // Garantir que $decoded não seja null
                if ($decoded === null || $decoded === false || empty($decoded)) {
                    $pageNum++;
                    continue;
                }
                
                // Extrair texto do stream
                $text = $this->extractTextFromStream($decoded);
                
                // Log para debug (apenas primeiros 200 chars)
                if (!empty($text) && $pageNum <= 3) {
                    Log::info("Texto extraído da página $pageNum", [
                        'preview' => substr($text, 0, 200),
                        'length' => strlen($text)
                    ]);
                }
                
                // Verificar se $text não é null/vazio antes de usar stripos
                if (!empty($text) && stripos($text, $searchText) !== false) {
                    $contexts = $this->extractContexts($text, $searchText);
                    if (!empty($contexts)) {
                        $pages[] = [
                            'page' => $pageNum,
                            'contexts' => $contexts,
                            'occurrences' => substr_count(strtolower($text), strtolower($searchText))
                        ];
                    }
                }
                
                $pageNum++;
            }
            
            Log::info('Método 2 concluído', ['pages_found' => count($pages)]);
        }
        
        return $pages;
    }
    
    /**
     * Extrai comandos de texto do PDF
     */
    private function extractPdfTextCommands(string $pdfContent): array
    {
        $textCommands = [];
        
        // Extrair strings entre parênteses (comandos Tj e TJ)
        preg_match_all('/\(((?:[^()\\\\]|\\\\.|\\([^)]*\\))*)\)\s*(?:Tj|TJ)/s', $pdfContent, $matches);
        foreach ($matches[1] as $text) {
            $decoded = $this->decodePdfString($text);
            if (!empty($decoded)) {
                $textCommands[] = $decoded;
            }
        }
        
        // Extrair strings hexadecimais
        preg_match_all('/<([0-9A-Fa-f]+)>\s*(?:Tj|TJ)/s', $pdfContent, $hexMatches);
        foreach ($hexMatches[1] as $hex) {
            $decoded = $this->decodeHexString($hex);
            if (!empty($decoded)) {
                $textCommands[] = $decoded;
            }
        }
        
        return $textCommands;
    }
    
    /**
     * Extrai texto de um stream PDF
     */
    private function extractTextFromStream(string $stream): string
    {
        $text = '';
        
        // Extrair strings entre parênteses
        preg_match_all('/\(((?:[^()\\\\]|\\\\.|\\([^)]*\\))*)\)/', $stream, $matches);
        foreach ($matches[1] as $match) {
            $decoded = $this->decodePdfString($match);
            $text .= ' ' . $decoded;
        }
        
        // Extrair strings hexadecimais
        preg_match_all('/<([0-9A-Fa-f]+)>/', $stream, $hexMatches);
        foreach ($hexMatches[1] as $hex) {
            $decoded = $this->decodeHexString($hex);
            $text .= ' ' . $decoded;
        }
        
        // Limpar e normalizar
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Decodifica string PDF
     */
    private function decodePdfString(string $str): string
    {
        // Decodificar escapes
        $str = stripcslashes($str);
        
        // Remover caracteres de controle
        $str = preg_replace('/[\x00-\x1F\x7F]/u', '', $str);
        
        return trim($str);
    }
    
    /**
     * Decodifica string hexadecimal
     */
    private function decodeHexString(string $hex): string
    {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i += 2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }
        return $str;
    }
    
    /**
     * Extrai contextos onde o texto foi encontrado
     */
    private function extractContexts(string $text, string $searchText): array
    {
        $contexts = [];
        $pattern = '/(.{0,100})' . preg_quote($searchText, '/') . '(.{0,100})/i';
        
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $context = trim($match[1] . $searchText . $match[2]);
            // Limpar espaços múltiplos e caracteres estranhos
            $context = preg_replace('/\s+/', ' ', $context);
            
            if (!empty($context) && strlen($context) > strlen($searchText)) {
                $contexts[] = $context;
            }
        }
        
        // Retornar contextos únicos (máximo 3 por página)
        return array_values(array_unique(array_slice($contexts, 0, 3)));
    }
}
