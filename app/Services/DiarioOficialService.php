<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Log;

class DiarioOficialService
{
    private const BASE_URL = 'https://diariooficial.to.gov.br/busca';
    private const TIMEOUT = 30;
    
    /**
     * Realiza busca no Diário Oficial
     */
    public function buscar(string $texto, string $dataInicial, string $dataFinal): array
    {
        try {
            $url = $this->buildSearchUrl($texto, $dataInicial, $dataFinal);
            Log::info('Buscando no Diário Oficial', ['url' => $url]);
            
            $html = $this->fetchHtml($url);
            $results = $this->parseResults($html, $texto);
            
            Log::info('Busca concluída', ['total_results' => count($results)]);
            
            return $results;
        } catch (Exception $e) {
            Log::error('Erro na busca do Diário Oficial', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Constrói URL de busca
     */
    private function buildSearchUrl(string $texto, string $dataInicial, string $dataFinal): string
    {
        return self::BASE_URL . '?' . http_build_query([
            'por' => 'texto',
            'texto' => $texto,
            'data-inicial' => $dataInicial,
            'data-final' => $dataFinal
        ]);
    }
    
    /**
     * Faz requisição HTTP e retorna HTML
     */
    private function fetchHtml(string $url): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ]
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$html) {
            throw new Exception("Erro ao acessar o site do Diário Oficial. HTTP Code: {$httpCode}. Error: {$error}");
        }
        
        return $html;
    }
    
    /**
     * Faz parsing dos resultados HTML
     */
    private function parseResults(string $html, string $searchText): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Tentar múltiplas estratégias de parsing
        $results = $this->parseTableResults($xpath);
        
        if (empty($results)) {
            $results = $this->parseArticleResults($xpath);
        }
        
        if (empty($results)) {
            $results = $this->parsePdfLinks($xpath);
        }
        
        if (empty($results)) {
            $results = $this->parseFallbackResults($html, $searchText);
        }
        
        return $results;
    }
    
    /**
     * Estratégia 1: Parse de tabela de resultados
     */
    private function parseTableResults(DOMXPath $xpath): array
    {
        $results = [];
        
        // Buscar especificamente por tbody > tr com td
        $rows = $xpath->query("//tbody/tr[td]");
        
        Log::info('Linhas de tabela encontradas', ['count' => $rows->length]);
        
        foreach ($rows as $row) {
            $cells = $xpath->query(".//td", $row);
            
            if ($cells->length >= 6) {
                $result = $this->extractResultFromTableRow($cells, $xpath);
                if ($result) {
                    $results[] = $result;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Extrai resultado de uma linha de tabela
     * Estrutura esperada: Edição | Data | Páginas | Tamanho | Downloads | Link
     */
    private function extractResultFromTableRow($cells, DOMXPath $xpath): ?array
    {
        try {
            // Coluna 0: Edição (ex: "Nº 6934")
            $edicaoText = trim($cells->item(0)->textContent);
            preg_match('/(\d+)/', $edicaoText, $edicaoMatch);
            $edicao = $edicaoMatch[1] ?? 'N/A';
            
            // Coluna 1: Data (ex: "05/11/2025")
            $data = trim($cells->item(1)->textContent);
            
            // Coluna 2: Páginas (ex: "66 pág.")
            $paginas = trim($cells->item(2)->textContent);
            
            // Coluna 3: Tamanho (ex: "1.46 MB")
            $tamanho = trim($cells->item(3)->textContent);
            
            // Coluna 5: Link de download
            $linkCell = $cells->item(5);
            $link = $xpath->query(".//a[@href]", $linkCell)->item(0);
            
            if (!$link) {
                Log::warning('Link não encontrado na célula');
                return null;
            }
            
            $pdfUrl = $this->normalizeUrl($link->getAttribute('href'));
            $titulo = $link->getAttribute('title') ?: "Diário Oficial - Edição $edicao";
            
            return [
                'data' => $data,
                'titulo' => $titulo,
                'edicao' => $edicao,
                'paginas' => $paginas,
                'tamanho' => $tamanho,
                'pdf_url' => $pdfUrl,
                'tipo' => 'Diário Oficial'
            ];
            
        } catch (Exception $e) {
            Log::warning('Erro ao extrair resultado da tabela', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return null;
    }
    
    /**
     * Estratégia 2: Parse de artigos/divs
     */
    private function parseArticleResults(DOMXPath $xpath): array
    {
        $results = [];
        
        // Buscar por artigos ou divs que contenham links para PDF
        $articles = $xpath->query("//article | //div[contains(@class, 'resultado') or contains(@class, 'item')]");
        
        foreach ($articles as $article) {
            $result = $this->extractResultFromArticle($article, $xpath);
            if ($result) {
                $results[] = $result;
            }
        }
        
        return $results;
    }
    
    /**
     * Extrai resultado de um artigo/div
     */
    private function extractResultFromArticle($article, DOMXPath $xpath): ?array
    {
        try {
            $links = $xpath->query(".//a[@href[contains(., '.pdf')]]", $article);
            
            if ($links->length > 0) {
                $link = $links->item(0);
                $pdfUrl = $this->normalizeUrl($link->getAttribute('href'));
                
                // Buscar data
                $text = $article->textContent;
                preg_match('/\d{2}\/\d{2}\/\d{4}/', $text, $dataMatches);
                $data = $dataMatches[0] ?? date('d/m/Y');
                
                // Buscar edição
                preg_match('/Edição\s*(\d+)/i', $text, $edicaoMatches);
                $edicao = $edicaoMatches[1] ?? 'N/A';
                
                // Título
                $titulo = trim($link->textContent) ?: 'Diário Oficial';
                
                return [
                    'data' => $data,
                    'titulo' => $titulo,
                    'edicao' => $edicao,
                    'pdf_url' => $pdfUrl,
                    'tipo' => 'Diário Oficial'
                ];
            }
        } catch (Exception $e) {
            Log::warning('Erro ao extrair resultado do artigo', ['error' => $e->getMessage()]);
        }
        
        return null;
    }
    
    /**
     * Estratégia 3: Buscar todos os links PDF
     */
    private function parsePdfLinks(DOMXPath $xpath): array
    {
        $results = [];
        
        $links = $xpath->query("//a[@href[contains(., '.pdf')]]");
        
        foreach ($links as $link) {
            $pdfUrl = $this->normalizeUrl($link->getAttribute('href'));
            $titulo = trim($link->textContent) ?: 'Diário Oficial';
            
            // Tentar encontrar data próxima ao link
            $parent = $link->parentNode;
            $text = $parent ? $parent->textContent : '';
            preg_match('/\d{2}\/\d{2}\/\d{4}/', $text, $dataMatches);
            $data = $dataMatches[0] ?? date('d/m/Y');
            
            $results[] = [
                'data' => $data,
                'titulo' => $titulo,
                'edicao' => 'N/A',
                'pdf_url' => $pdfUrl,
                'tipo' => 'Diário Oficial'
            ];
        }
        
        return $results;
    }
    
    /**
     * Estratégia 4: Fallback com regex no HTML
     */
    private function parseFallbackResults(string $html, string $searchText): array
    {
        $results = [];
        
        // Buscar URLs de PDF no HTML
        preg_match_all('/href=["\']([^"\']*\.pdf[^"\']*)["\']/', $html, $matches);
        
        if (!empty($matches[1])) {
            foreach (array_unique($matches[1]) as $pdfUrl) {
                $results[] = [
                    'data' => date('d/m/Y'),
                    'titulo' => 'Diário Oficial - ' . basename($pdfUrl),
                    'edicao' => 'N/A',
                    'pdf_url' => $this->normalizeUrl($pdfUrl),
                    'tipo' => 'Diário Oficial'
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Normaliza URL (adiciona domínio se necessário)
     */
    private function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }
        
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        
        if (str_starts_with($url, '/')) {
            return 'https://diariooficial.to.gov.br' . $url;
        }
        
        return 'https://diariooficial.to.gov.br/' . $url;
    }
}
