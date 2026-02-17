<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\ConfiguracaoSistema;
use App\Models\DocumentoAjuda;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\ProcessoAlerta;
use App\Models\OrdemServico;
use App\Models\DocumentoDigital;
use App\Models\DocumentoPop;
use App\Models\CategoriaPop;
use Smalot\PdfParser\Parser as PdfParser;

class AssistenteIAController extends Controller
{
    /**
     * Envia mensagem para a IA e retorna resposta
     */
    public function chat(Request $request)
    {
        // Log para debug
        \Log::info('Chat request recebido', [
            'has_documento_contexto' => $request->has('documento_contexto'),
            'has_documentos_contexto' => $request->has('documentos_contexto'),
            'documento_keys' => $request->filled('documento_contexto') ? array_keys($request->input('documento_contexto')) : null,
        ]);

        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
            'documento_contexto' => 'nullable|array',
            'documento_contexto.nome' => 'required_with:documento_contexto|string|max:500',
            'documento_contexto.conteudo' => 'required_with:documento_contexto|string|max:50000', // 50KB de texto
            'documentos_contexto' => 'nullable|array',
            'documentos_contexto.*.nome' => 'required|string|max:500',
            'documentos_contexto.*.conteudo' => 'required|string|max:50000',
            'tipo_consulta' => 'nullable|string|in:relatorios,geral',
        ]);

        // Verifica se IA está ativa
        $iaAtiva = ConfiguracaoSistema::where('chave', 'ia_ativa')->value('valor');
        if ($iaAtiva !== 'true') {
            return response()->json([
                'error' => 'Assistente de IA está desativado'
            ], 403);
        }

        $userMessage = $request->input('message');
        $history = $request->input('history', []);
        $documentoContexto = $request->input('documento_contexto');
        $documentosContexto = $request->input('documentos_contexto');
        $tipoConsulta = $request->input('tipo_consulta', 'geral');
        
        // Obtém usuário logado
        $usuario = auth('interno')->user();

        try {
            // Analisa a mensagem para ver se precisa de dados do sistema
            // Se for consulta de relatórios, busca TODOS os dados
            $contextoDados = $this->obterContextoDados($userMessage, $usuario, $tipoConsulta === 'relatorios');
            
            // Adiciona contexto de múltiplos documentos se fornecido
            if ($documentosContexto && is_array($documentosContexto) && count($documentosContexto) > 0) {
                \Log::info('Adicionando múltiplos documentos ao contexto', [
                    'total' => count($documentosContexto),
                    'nomes' => array_map(function($doc) { return $doc['nome'] ?? 'N/A'; }, $documentosContexto)
                ]);
                $contextoDados['documentos_pdf'] = $documentosContexto;
            }
            // Fallback para documento único (compatibilidade)
            elseif ($documentoContexto) {
                \Log::info('Adicionando documento único ao contexto', [
                    'nome' => $documentoContexto['nome'] ?? 'N/A',
                    'tamanho_conteudo' => strlen($documentoContexto['conteudo'] ?? ''),
                ]);
                $contextoDados['documento_pdf'] = $documentoContexto;
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao preparar contexto', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Erro ao preparar contexto',
                'response' => 'Desculpe, ocorreu um erro ao processar o documento. Detalhes: ' . $e->getMessage(),
                'success' => false,
            ], 200);
        }
        
        // Verifica se deve buscar na internet
        // REGRA: Busca na internet APENAS se houver documento PDF carregado E checkbox marcado
        $buscaWebAtiva = false;
        
        // Se tem documentos múltiplos com configuração de busca
        if ($documentosContexto && is_array($documentosContexto)) {
            foreach ($documentosContexto as $doc) {
                if (isset($doc['buscar_internet']) && $doc['buscar_internet'] === true) {
                    $buscaWebAtiva = true;
                    break;
                }
            }
        }
        // Fallback para documento único
        elseif (isset($documentoContexto['buscar_internet'])) {
            $buscaWebAtiva = $documentoContexto['buscar_internet'] === true;
        } 
        // Chat geral (sem documento) NUNCA busca na internet - apenas POPs
        // Configuração global ia_busca_web foi DESABILITADA para chat geral
        
        if ($buscaWebAtiva && $this->deveBuscarNaInternet($userMessage, $contextoDados)) {
            \Log::info('Iniciando busca na internet', [
                'message' => $userMessage,
                'tem_documento' => isset($documentoContexto),
                'buscar_internet_doc' => $documentoContexto['buscar_internet'] ?? null
            ]);
            
            $resultadosWeb = $this->buscarNaInternet($userMessage);
            if (!empty($resultadosWeb)) {
                $contextoDados['resultados_web'] = $resultadosWeb;
                \Log::info('Resultados da busca adicionados ao contexto', [
                    'total' => $resultadosWeb['total'] ?? 0
                ]);
            }
        }

        // Prepara o contexto do sistema
        try {
            // Se tem documentos PDF (único ou múltiplos), usa prompt simplificado para economizar tokens
            $temDocumento = (isset($contextoDados['documento_pdf']) && !empty($contextoDados['documento_pdf'])) ||
                           (isset($contextoDados['documentos_pdf']) && !empty($contextoDados['documentos_pdf']));
            $systemPrompt = $this->construirSystemPrompt($contextoDados, $usuario, $temDocumento);
        } catch (\Exception $e) {
            \Log::error('Erro ao construir system prompt', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Erro ao construir prompt',
                'response' => 'Desculpe, ocorreu um erro ao preparar a mensagem. Erro na linha ' . $e->getLine() . ': ' . $e->getMessage(),
                'success' => false,
            ], 200);
        }

        // Prepara mensagens para a API
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Adiciona histórico
        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        // Adiciona mensagem atual
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        // Limpa caracteres UTF-8 malformados de todas as mensagens
        $messages = $this->limparMensagensUTF8($messages);

        try {
            // Busca configurações da IA
            $apiKey = ConfiguracaoSistema::where('chave', 'ia_api_key')->value('valor');
            $apiUrl = ConfiguracaoSistema::where('chave', 'ia_api_url')->value('valor');
            $model = ConfiguracaoSistema::where('chave', 'ia_model')->value('valor');

            // Valida se configurações existem
            if (empty($apiKey) || empty($apiUrl) || empty($model)) {
                \Log::error('Configurações da IA não encontradas', [
                    'apiKey' => !empty($apiKey) ? 'OK' : 'MISSING',
                    'apiUrl' => $apiUrl ?? 'MISSING',
                    'model' => $model ?? 'MISSING',
                ]);

                return response()->json([
                    'error' => 'Configurações da IA não encontradas',
                    'response' => 'Desculpe, o assistente de IA não está configurado corretamente. Entre em contato com o administrador.',
                    'success' => false,
                ], 500);
            }

            // Chama API da IA
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($apiUrl, [ // Aumenta timeout para 60s
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 1000, // Reduz para 1000 tokens (resposta mais curta)
                'temperature' => 0.3, // Aumenta um pouco para respostas mais naturais
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $assistantMessage = $data['choices'][0]['message']['content'] ?? 'Desculpe, não consegui processar sua pergunta.';

                return response()->json([
                    'response' => $assistantMessage, // CORRIGIDO: era 'message', agora é 'response'
                    'success' => true,
                ]);
            } else {
                \Log::error('Erro na API da IA', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'api_url' => $apiUrl,
                    'model' => $model,
                    'message_count' => count($messages),
                ]);

                // Tenta extrair mensagem de erro mais específica
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? 'Erro desconhecido';

                return response()->json([
                    'error' => 'Erro ao comunicar com a IA',
                    'response' => "Desculpe, a IA está com dificuldades. Erro: {$errorMessage}",
                    'success' => false,
                ], 200); // CORRIGIDO: retorna 200 com success=false
            }
        } catch (\Exception $e) {
            \Log::error('Exceção ao chamar IA', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_message' => $userMessage,
            ]);

            return response()->json([
                'error' => 'Erro ao processar sua mensagem',
                'response' => 'Desculpe, ocorreu um erro. Tente novamente.',
                'success' => false,
            ], 200); // CORRIGIDO: retorna 200 com success=false
        }
    }

    /**
     * Chat do assistente IA para usuários externos (área company)
     */
    public function chatExterno(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
        ]);

        $iaExternaAtiva = ConfiguracaoSistema::where('chave', 'ia_externa_ativa')->value('valor');
        if ($iaExternaAtiva !== 'true') {
            return response()->json([
                'error' => 'Assistente de IA para usuários externos está desativado',
                'success' => false,
            ], 403);
        }

        $usuario = auth('externo')->user();
        if (!$usuario) {
            return response()->json([
                'error' => 'Usuário não autenticado',
                'success' => false,
            ], 401);
        }

        $userMessage = $request->input('message');
        $history = $request->input('history', []);

        try {
            $documentosAjuda = $this->carregarBaseConhecimentoDocumentosAjuda();

            \Log::info('Chat externo IA - documentos carregados', [
                'total_docs' => count($documentosAjuda),
                'titulos' => array_map(fn($d) => $d['titulo'] ?? 'sem titulo', $documentosAjuda),
                'tamanhos' => array_map(fn($d) => mb_strlen($d['conteudo'] ?? ''), $documentosAjuda),
                'pergunta' => $userMessage,
            ]);

            if (empty($documentosAjuda)) {
                return response()->json([
                    'response' => 'Não há conteúdo disponível nos Documentos de Ajuda para responder no momento. Solicite ao administrador o upload/ativação dos PDFs em Configurações > Documentos de Ajuda.',
                    'success' => false,
                ], 200);
            }

            $contextoDocumentos = $this->montarContextoPerguntaDocumentosAjuda($documentosAjuda, $userMessage);
            $systemPrompt = $this->construirSystemPromptExterno($contextoDocumentos, $usuario->nome);

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            foreach ($history as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $messages[] = [
                        'role' => $msg['role'],
                        'content' => $msg['content'],
                    ];
                }
            }

            $messages[] = ['role' => 'user', 'content' => $userMessage];
            $messages = $this->limparMensagensUTF8($messages);

            $apiKey = ConfiguracaoSistema::where('chave', 'ia_api_key')->value('valor');
            $apiUrl = ConfiguracaoSistema::where('chave', 'ia_api_url')->value('valor');
            $model = ConfiguracaoSistema::where('chave', 'ia_model')->value('valor');

            if (empty($apiKey) || empty($apiUrl) || empty($model)) {
                return response()->json([
                    'error' => 'Configurações da IA não encontradas',
                    'response' => 'O assistente está temporariamente indisponível. Tente novamente em instantes.',
                    'success' => false,
                ], 500);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($apiUrl, [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 1200,
                'temperature' => 0.15,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $assistantMessage = $data['choices'][0]['message']['content'] ?? 'Não consegui gerar uma resposta agora.';
                $titulosFontes = $this->obterTitulosFontesMaisRelevantes($documentosAjuda, $userMessage);
                $assistantMessage = $this->garantirLinhaFontesExterno($assistantMessage, $titulosFontes);

                return response()->json([
                    'response' => $assistantMessage,
                    'success' => true,
                ]);
            }

            \Log::error('Erro API IA (externo)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'Erro ao comunicar com a IA',
                'response' => 'Estou com instabilidade no momento. Tente novamente em alguns segundos.',
                'success' => false,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Exceção chat externo IA', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Erro ao processar sua mensagem',
                'response' => 'Desculpe, ocorreu um erro ao processar sua pergunta. Tente novamente.',
                'success' => false,
            ], 200);
        }
    }

    /**
     * Prompt dedicado para assistência de usuários externos.
     */
    private function construirSystemPromptExterno(string $contextoDocumentos, string $nomeUsuario): string
    {
        return "Você é o Assistente do InfoVISA para usuários EXTERNOS (empresas/estabelecimentos).\n"
            . "Responda sempre em português do Brasil, com linguagem simples e didática.\n"
            . "\n"
            . "REGRA ABSOLUTA (OBRIGATÓRIA):\n"
            . "- Você SÓ pode responder com base no conteúdo dos DOCUMENTOS DE AJUDA fornecidos abaixo.\n"
            . "- É PROIBIDO usar conhecimento externo, memória do modelo ou suposições.\n"
            . "- Se a resposta não estiver explícita nos documentos, responda EXATAMENTE:\n"
            . "  'Não encontrei essa informação nos documentos de ajuda disponíveis.'\n"
            . "\n"
            . "SELEÇÃO DO DOCUMENTO CORRETO:\n"
            . "- Cada documento trata de um ASSUNTO ESPECÍFICO. Identifique qual documento é o correto para a pergunta.\n"
            . "- O documento marcado como [MAIS RELEVANTE] foi selecionado automaticamente, mas CONFIRME se o conteúdo realmente responde à pergunta.\n"
            . "- 'INSTRUTIVO DARE' → trata de gerar boletos/guias DARE para pagamento de taxas.\n"
            . "- 'Instrutivo INFOVISA' ou 'MANUAL DE CADASTRO' → trata de cadastrar estabelecimentos, abrir processos, usar o sistema InfoVISA.\n"
            . "- Se a pergunta é sobre cadastro de estabelecimento, use o Instrutivo INFOVISA ou MANUAL DE CADASTRO, NÃO o INSTRUTIVO DARE.\n"
            . "- Se a pergunta é sobre gerar DARE/boleto/guia de pagamento, use o INSTRUTIVO DARE.\n"
            . "\n"
            . "FORMATO DE RESPOSTA:\n"
            . "- Quando houver instrução, responda em passos numerados.\n"
            . "- No final, inclua: 'Fonte(s): [título(s) do(s) documento(s)]'.\n"
            . "\n"
            . "USUÁRIO ATUAL:\n"
            . "- Nome: {$nomeUsuario}\n"
            . "\n"
            . "==== BASE DE CONHECIMENTO (DOCUMENTOS DE AJUDA) ====\n"
            . $contextoDocumentos;
    }

    /**
     * Carrega e extrai texto de todos os PDFs ativos de Documentos de Ajuda.
     */
    private function carregarBaseConhecimentoDocumentosAjuda(): array
    {
        $documentos = DocumentoAjuda::ativos()->ordenado()->get(['id', 'titulo', 'arquivo', 'updated_at']);

        if ($documentos->isEmpty()) {
            return [];
        }

        $hashBase = md5(
            $documentos->count() . '|' .
            optional($documentos->max('updated_at'))->timestamp . '|' .
            $documentos->pluck('id')->implode('-')
        );

        return Cache::remember("ia_externo_docs_{$hashBase}", now()->addMinutes(20), function () use ($documentos) {
            $parser = new PdfParser();
            $base = [];

            foreach ($documentos as $doc) {
                try {
                    if (empty($doc->arquivo)) {
                        \Log::warning('DocumentoAjuda sem arquivo para IA externa', [
                            'documento_id' => $doc->id,
                            'titulo' => $doc->titulo,
                        ]);
                        continue;
                    }

                    if (!Storage::disk('local')->exists($doc->arquivo)) {
                        \Log::warning('Arquivo de DocumentoAjuda não encontrado no disco', [
                            'documento_id' => $doc->id,
                            'titulo' => $doc->titulo,
                            'arquivo' => $doc->arquivo,
                            'path_tentado' => Storage::disk('local')->path($doc->arquivo),
                        ]);
                        continue;
                    }

                    $caminhoArquivo = Storage::disk('local')->path($doc->arquivo);
                    $pdf = $parser->parseFile($caminhoArquivo);
                    $texto = $this->normalizarTextoDocumentoAjuda($pdf->getText() ?? '');

                    \Log::info('DocumentoAjuda extraído para IA externa', [
                        'documento_id' => $doc->id,
                        'titulo' => $doc->titulo,
                        'texto_length' => mb_strlen($texto),
                    ]);

                    if (mb_strlen($texto) < 40) {
                        \Log::warning('DocumentoAjuda com texto muito curto', [
                            'documento_id' => $doc->id,
                            'titulo' => $doc->titulo,
                            'texto_length' => mb_strlen($texto),
                        ]);
                        continue;
                    }

                    $base[] = [
                        'titulo' => $doc->titulo,
                        'conteudo' => mb_substr($texto, 0, 50000),
                    ];
                } catch (\Throwable $e) {
                    \Log::warning('Falha ao extrair DocumentoAjuda para IA externa', [
                        'documento_id' => $doc->id,
                        'titulo' => $doc->titulo,
                        'erro' => $e->getMessage(),
                    ]);
                }
            }

            return $base;
        });
    }

    /**
     * Monta contexto textual com base em todos os documentos, priorizando os mais relevantes.
     */
    private function montarContextoPerguntaDocumentosAjuda(array $documentos, string $pergunta): string
    {
        // Pontuar cada documento por relevância
        $documentosPontuados = [];

        foreach ($documentos as $indice => $doc) {
            $titulo = $doc['titulo'] ?? ('Documento ' . ($indice + 1));
            $conteudo = $doc['conteudo'] ?? '';
            $score = $this->pontuarRelevanciaDocumento($titulo, $conteudo, $pergunta);
            $documentosPontuados[] = [
                'indice' => $indice,
                'titulo' => $titulo,
                'conteudo' => $conteudo,
                'score' => $score,
            ];
        }

        // Ordenar por relevância (maior score primeiro)
        usort($documentosPontuados, fn($a, $b) => $b['score'] <=> $a['score']);

        // Alocar mais espaço para os documentos mais relevantes
        $limitesChars = [12000, 6000, 3000, 2000, 2000]; // proporcional à relevância
        $blocos = [];

        foreach ($documentosPontuados as $pos => $doc) {
            $limiteChars = $limitesChars[$pos] ?? 2000;
            $titulo = $doc['titulo'];
            $conteudo = $doc['conteudo'];

            // Se o conteúdo cabe no limite, envia tudo
            if (mb_strlen($conteudo) <= $limiteChars) {
                $trecho = $conteudo;
            } else {
                $trecho = $this->extrairTrechoRelevanteDocumentoAjuda($conteudo, $pergunta, $limiteChars);
                if (empty($trecho)) {
                    $trecho = mb_substr($conteudo, 0, $limiteChars);
                }
            }

            $relevancia = $pos === 0 ? ' [MAIS RELEVANTE]' : '';
            $blocos[] = "[DOC " . ($doc['indice'] + 1) . "] {$titulo}{$relevancia}\n{$trecho}";
        }

        return implode("\n\n------------------------------\n\n", $blocos);
    }

    /**
     * Pontua a relevância de um documento para uma pergunta.
     */
    private function pontuarRelevanciaDocumento(string $titulo, string $conteudo, string $pergunta): float
    {
        $perguntaLower = mb_strtolower($pergunta);
        $tituloLower = mb_strtolower($titulo);
        $conteudoLower = mb_strtolower($conteudo);
        $score = 0.0;

        // Extrair termos significativos da pergunta (>= 4 chars)
        $termos = preg_split('/\s+/u', $perguntaLower, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $termos = array_values(array_filter($termos, fn($t) => mb_strlen(trim($t)) >= 4));

        // Bonus alto para matches no título (mais indicativo)
        foreach ($termos as $termo) {
            if (mb_strpos($tituloLower, $termo) !== false) {
                $score += 20;
            }
        }

        // Verificar combinações de 2 palavras consecutivas (bigramas) no título
        for ($i = 0; $i < count($termos) - 1; $i++) {
            $bigrama = $termos[$i] . ' ' . $termos[$i + 1];
            if (mb_strpos($tituloLower, $bigrama) !== false) {
                $score += 30;
            }
        }

        // Contar ocorrências de cada termo no conteúdo
        foreach ($termos as $termo) {
            $ocorrencias = mb_substr_count($conteudoLower, $termo);
            $score += min($ocorrencias, 50); // cap para não distorcer documentos longos
        }

        // Verificar bigramas no conteúdo (2 palavras juntas = muito relevante)
        for ($i = 0; $i < count($termos) - 1; $i++) {
            $bigrama = $termos[$i] . ' ' . $termos[$i + 1];
            $ocorrencias = mb_substr_count($conteudoLower, $bigrama);
            $score += $ocorrencias * 5;
        }

        // Normalizar pelo tamanho do documento (densidade)
        $tamanho = max(mb_strlen($conteudo), 1);
        $densityBonus = ($score / ($tamanho / 1000)) * 0.5;
        $score += $densityBonus;

        return $score;
    }

    /**
     * Retorna os títulos dos documentos mais relevantes para a pergunta.
     */
    private function obterTitulosFontesMaisRelevantes(array $documentos, string $pergunta, int $maxFontes = 2): array
    {
        $pontuados = [];

        foreach ($documentos as $indice => $doc) {
            $titulo = trim((string) ($doc['titulo'] ?? ('Documento ' . ($indice + 1))));
            $conteudo = (string) ($doc['conteudo'] ?? '');
            $score = $this->pontuarRelevanciaDocumento($titulo, $conteudo, $pergunta);

            $pontuados[] = [
                'titulo' => $titulo,
                'score' => $score,
            ];
        }

        if (empty($pontuados)) {
            return [];
        }

        usort($pontuados, fn($a, $b) => $b['score'] <=> $a['score']);

        $fontes = [];
        foreach ($pontuados as $item) {
            if ($item['titulo'] === '') {
                continue;
            }

            if ($item['score'] <= 0 && !empty($fontes)) {
                continue;
            }

            $fontes[] = $item['titulo'];
            if (count($fontes) >= $maxFontes) {
                break;
            }
        }

        if (empty($fontes)) {
            $fontes[] = $pontuados[0]['titulo'];
        }

        return array_values(array_unique($fontes));
    }

    /**
     * Garante que a resposta do assistente externo sempre termine com a linha de Fonte(s).
     */
    private function garantirLinhaFontesExterno(string $resposta, array $titulosFontes): string
    {
        $respostaLimpa = trim((string) $resposta);
        $titulosFontes = array_values(array_filter(array_map('trim', $titulosFontes)));

        if (empty($titulosFontes)) {
            return $respostaLimpa;
        }

        $linhaFontes = 'Fonte(s): ' . implode(' ', array_map(function ($titulo) {
            return '[' . $titulo . ']';
        }, $titulosFontes));

        // Remove qualquer linha de fonte já existente (Fonte(s), Fontes, markdown, blockquote) para evitar duplicação.
        $respostaSemFonte = preg_replace('/^\s*(?:>\s*)?(?:\*\*)?\s*fontes?(?:\(s\))?\s*:\s*.*$/imu', '', $respostaLimpa);
        $respostaSemFonte = preg_replace('/^\s*(?:>\s*)?(?:\*\*)?\s*fonte(?:\(s\))?\s*:\s*.*$/imu', '', (string) $respostaSemFonte);
        $respostaSemFonte = trim((string) $respostaSemFonte);

        if ($respostaSemFonte === '') {
            return $linhaFontes;
        }

        return $respostaSemFonte . "\n\n" . $linhaFontes;
    }

    /**
     * Extrai trecho de maior relevância do documento com base na pergunta,
     * usando janela deslizante com pontuação de densidade de termos.
     */
    private function extrairTrechoRelevanteDocumentoAjuda(string $conteudo, string $pergunta, int $limiteChars = 6000): string
    {
        $conteudo = trim($conteudo);
        if ($conteudo === '') {
            return '';
        }

        $termos = preg_split('/\s+/u', mb_strtolower($pergunta), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $termos = array_values(array_filter($termos, fn($t) => mb_strlen(trim($t)) >= 4));

        if (empty($termos)) {
            return mb_substr($conteudo, 0, $limiteChars);
        }

        $conteudoLower = mb_strtolower($conteudo);
        $tamanhoConteudo = mb_strlen($conteudo);
        $janelaSize = min($limiteChars, $tamanhoConteudo);

        // Se o conteúdo cabe no limite, retorna tudo
        if ($tamanhoConteudo <= $limiteChars) {
            return $conteudo;
        }

        // Bigramas da pergunta para bonus de proximidade
        $bigramas = [];
        for ($i = 0; $i < count($termos) - 1; $i++) {
            $bigramas[] = $termos[$i] . ' ' . $termos[$i + 1];
        }

        // Janela deslizante: encontrar a posição com maior densidade de termos
        $melhorScore = -1;
        $melhorPos = 0;
        $passo = max(200, intdiv($tamanhoConteudo, 100)); // ~100 amostras máximo

        for ($pos = 0; $pos <= $tamanhoConteudo - $janelaSize; $pos += $passo) {
            $janela = mb_substr($conteudoLower, $pos, $janelaSize);
            $score = 0;

            foreach ($termos as $termo) {
                $score += mb_substr_count($janela, $termo);
            }

            foreach ($bigramas as $bigrama) {
                $score += mb_substr_count($janela, $bigrama) * 3;
            }

            if ($score > $melhorScore) {
                $melhorScore = $score;
                $melhorPos = $pos;
            }
        }

        // Refinar: tentar ajustar ao redor da melhor posição encontrada
        $refinaInicio = max(0, $melhorPos - $passo);
        $refinaFim = min($tamanhoConteudo - $janelaSize, $melhorPos + $passo);

        for ($pos = $refinaInicio; $pos <= $refinaFim; $pos += 50) {
            $janela = mb_substr($conteudoLower, $pos, $janelaSize);
            $score = 0;

            foreach ($termos as $termo) {
                $score += mb_substr_count($janela, $termo);
            }

            foreach ($bigramas as $bigrama) {
                $score += mb_substr_count($janela, $bigrama) * 3;
            }

            if ($score > $melhorScore) {
                $melhorScore = $score;
                $melhorPos = $pos;
            }
        }

        $trecho = mb_substr($conteudo, $melhorPos, $janelaSize);

        if ($melhorPos > 0) {
            $trecho = '... ' . $trecho;
        }

        if ($tamanhoConteudo > ($melhorPos + $janelaSize)) {
            $trecho .= ' ...';
        }

        return $trecho;
    }

    /**
     * Normaliza texto extraído de PDFs.
     */
    private function normalizarTextoDocumentoAjuda(string $texto): string
    {
        $texto = preg_replace('/\x{00A0}/u', ' ', $texto);
        $texto = preg_replace('/[ \t]+/u', ' ', $texto);
        $texto = preg_replace('/\n{3,}/u', "\n\n", $texto);

        return trim($texto ?? '');
    }

    /**
     * Obtém dados do sistema baseado na pergunta do usuário
     */
    private function obterContextoDados($message, $usuario, $buscarTodosDados = false)
    {
        $message = strtolower($message);
        $dados = [];

        try {
            // Se for consulta de relatórios, busca TODOS os dados sempre
            $buscarEstabelecimentos = $buscarTodosDados || preg_match('/(quantos|quantidade|total|tenho).*estabelecimento/i', $message);
            $buscarProcessos = $buscarTodosDados || preg_match('/(quantos|quantidade|total|tenho).*processo/i', $message);
            $buscarOrdens = $buscarTodosDados || preg_match('/(quantos|quantidade|total|tenho).*(ordem|os|ordens)/i', $message);
            $buscarDocumentos = $buscarTodosDados || preg_match('/(quantos|quantidade|total|tenho).*documento/i', $message);
            
            // Detecta perguntas sobre estabelecimentos
            if ($buscarEstabelecimentos) {
                $query = Estabelecimento::query();
                
                // Detecta filtro por município na pergunta
                if (preg_match('/(de|em|do município de|da cidade de)\s+([a-záàâãéèêíïóôõöúçñ\s]+)/ui', $message, $matches)) {
                    $nomeMunicipio = trim($matches[2]);
                    $query->whereHas('municipio', function($q) use ($nomeMunicipio) {
                        $q->whereRaw('LOWER(nome) LIKE ?', ['%' . strtolower($nomeMunicipio) . '%']);
                    });
                    $dados['municipio_filtrado'] = $nomeMunicipio;
                }
            
            // Filtra por competência (Admin vê tudo)
            if ($usuario->isEstadual()) {
                // Estadual: apenas estabelecimentos de competência estadual
                $query->whereRaw('
                    (
                        competencia_manual = \'estadual\'
                        OR
                        (
                            competencia_manual IS NULL
                            AND EXISTS (
                                SELECT 1 FROM atividade_estabelecimento ae
                                INNER JOIN atividades a ON ae.atividade_id = a.id
                                WHERE ae.estabelecimento_id = estabelecimentos.id
                                AND a.competencia = \'estadual\'
                                AND NOT EXISTS (
                                    SELECT 1 FROM descentralizacoes d
                                    WHERE d.atividade_id = a.id
                                    AND d.municipio_id = estabelecimentos.municipio_id
                                    AND d.ativo = true
                                )
                            )
                        )
                    )
                ');
            } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
                // Municipal: apenas do próprio município e competência municipal
                $query->where('municipio_id', $usuario->municipio_id)
                      ->whereRaw('
                        (
                            competencia_manual = \'municipal\'
                            OR
                            (
                                competencia_manual IS NULL
                                AND NOT EXISTS (
                                    SELECT 1 FROM atividade_estabelecimento ae
                                    INNER JOIN atividades a ON ae.atividade_id = a.id
                                    WHERE ae.estabelecimento_id = estabelecimentos.id
                                    AND a.competencia = \'estadual\'
                                    AND NOT EXISTS (
                                        SELECT 1 FROM descentralizacoes d
                                        WHERE d.atividade_id = a.id
                                        AND d.municipio_id = estabelecimentos.municipio_id
                                        AND d.ativo = true
                                    )
                                )
                            )
                        )
                      ');
            }
            
                $dados['estabelecimentos_total'] = $query->count();
                $dados['estabelecimentos_ativos'] = (clone $query)->where('status', 'ativo')->count();
                $dados['estabelecimentos_inativos'] = (clone $query)->where('status', 'inativo')->count();
                
                // Detecta perguntas sobre estabelecimentos COM processos específicos
                if (preg_match('/estabelecimento.*(?:com|tem|possui|que tem).*processo/i', $message)) {
                    // Detecta tipo de processo
                    $tipoProcesso = null;
                    if (preg_match('/licenciamento/i', $message)) {
                        $tipoProcesso = 'licenciamento';
                    } elseif (preg_match('/rotulagem/i', $message)) {
                        $tipoProcesso = 'analise_rotulagem';
                    } elseif (preg_match('/projeto|arquitet[oô]nico/i', $message)) {
                        $tipoProcesso = 'projeto_arquitetonico';
                    } elseif (preg_match('/administrativo/i', $message)) {
                        $tipoProcesso = 'administrativo';
                    } elseif (preg_match('/descentraliza[çc][ãa]o/i', $message)) {
                        $tipoProcesso = 'descentralizacao';
                    }
                    
                    // Detecta ano
                    $ano = null;
                    if (preg_match('/\b(20\d{2})\b/', $message, $matches)) {
                        $ano = $matches[1];
                    }
                    
                    // Conta estabelecimentos ÚNICOS que têm processos
                    $queryEstabComProcessos = clone $query;
                    $queryEstabComProcessos->whereHas('processos', function($q) use ($tipoProcesso, $ano) {
                        if ($tipoProcesso) {
                            $q->where('tipo_processo_id', function($subq) use ($tipoProcesso) {
                                $subq->select('id')
                                     ->from('tipos_processo')
                                     ->where('slug', $tipoProcesso)
                                     ->limit(1);
                            });
                        }
                        if ($ano) {
                            $q->where('ano', $ano);
                        }
                    });
                    
                    $totalEstabComProcessos = $queryEstabComProcessos->count();
                    
                    // Se a pergunta pede "quais" estabelecimentos, lista os nomes
                    if (preg_match('/\b(quais|liste|listar|mostrar|nomes?)\b/i', $message)) {
                        $estabelecimentosLista = $queryEstabComProcessos
                            ->select('id', 'nome_fantasia', 'razao_social', 'cnpj')
                            ->limit(50)
                            ->get()
                            ->map(function($estab) {
                                return "- {$estab->nome_fantasia} (CNPJ: {$estab->cnpj})";
                            })
                            ->toArray();
                        
                        if ($tipoProcesso && $ano) {
                            $dados["lista_estabelecimentos_com_processo_{$tipoProcesso}_{$ano}"] = implode("\n", $estabelecimentosLista);
                        } elseif ($tipoProcesso) {
                            $dados["lista_estabelecimentos_com_processo_{$tipoProcesso}"] = implode("\n", $estabelecimentosLista);
                        } else {
                            $dados['lista_estabelecimentos_com_processos'] = implode("\n", $estabelecimentosLista);
                        }
                    }
                    
                    if ($tipoProcesso && $ano) {
                        $dados["estabelecimentos_com_processo_{$tipoProcesso}_{$ano}"] = $totalEstabComProcessos;
                    } elseif ($tipoProcesso) {
                        $dados["estabelecimentos_com_processo_{$tipoProcesso}"] = $totalEstabComProcessos;
                    } elseif ($ano) {
                        $dados["estabelecimentos_com_processo_{$ano}"] = $totalEstabComProcessos;
                    } else {
                        $dados['estabelecimentos_com_processos'] = $totalEstabComProcessos;
                    }
                }
            }

            // Detecta perguntas sobre processos
            if ($buscarProcessos) {
                $query = Processo::query();
                
                // Filtra por competência (Admin vê tudo)
                if ($usuario->isEstadual()) {
                    $query->whereHas('estabelecimento', function ($q) {
                        $q->whereRaw('
                            (
                                competencia_manual = \'estadual\'
                                OR
                                (
                                    competencia_manual IS NULL
                                    AND EXISTS (
                                        SELECT 1 FROM atividade_estabelecimento ae
                                        INNER JOIN atividades a ON ae.atividade_id = a.id
                                        WHERE ae.estabelecimento_id = estabelecimentos.id
                                        AND a.competencia = \'estadual\'
                                        AND NOT EXISTS (
                                            SELECT 1 FROM descentralizacoes d
                                            WHERE d.atividade_id = a.id
                                            AND d.municipio_id = estabelecimentos.municipio_id
                                            AND d.ativo = true
                                        )
                                    )
                                )
                            )
                        ');
                    });
                } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
                    $query->whereHas('estabelecimento', function ($q) use ($usuario) {
                        $q->where('municipio_id', $usuario->municipio_id)
                          ->whereRaw('
                            (
                                competencia_manual = \'municipal\'
                                OR
                                (
                                    competencia_manual IS NULL
                                    AND NOT EXISTS (
                                        SELECT 1 FROM atividade_estabelecimento ae
                                        INNER JOIN atividades a ON ae.atividade_id = a.id
                                        WHERE ae.estabelecimento_id = estabelecimentos.id
                                        AND a.competencia = \'estadual\'
                                        AND NOT EXISTS (
                                            SELECT 1 FROM descentralizacoes d
                                            WHERE d.atividade_id = a.id
                                            AND d.municipio_id = estabelecimentos.municipio_id
                                            AND d.ativo = true
                                        )
                                    )
                                )
                            )
                          ');
                    });
                }
            
                $dados['processos_total'] = $query->count();
                $dados['processos_abertos'] = (clone $query)->where('status', 'aberto')->count();
                $dados['processos_em_analise'] = (clone $query)->where('status', 'em_analise')->count();
                $dados['processos_concluidos'] = (clone $query)->where('status', 'concluido')->count();
                $dados['processos_arquivados'] = (clone $query)->where('status', 'arquivado')->count();
                
                // Detecta tipo de processo e status
                $tipoProcesso = null;
                $statusProcesso = null;
                $ano = null;
                
                if (preg_match('/licenciamento/i', $message)) {
                    $tipoProcesso = 'licenciamento';
                }
                if (preg_match('/\b(aberto|abertas?)\b/i', $message)) {
                    $statusProcesso = 'aberto';
                } elseif (preg_match('/\b(em análise|analise)\b/i', $message)) {
                    $statusProcesso = 'em_analise';
                } elseif (preg_match('/\b(concluído|concluidas?)\b/i', $message)) {
                    $statusProcesso = 'concluido';
                } elseif (preg_match('/\b(arquivado|arquivadas?)\b/i', $message)) {
                    $statusProcesso = 'arquivado';
                }
                
                // Filtra por ano se mencionado
                if (preg_match('/\b(20\d{2})\b/', $message, $matches)) {
                    $ano = $matches[1];
                    $queryAno = clone $query;
                    $queryAno->whereYear('created_at', $ano);
                    
                    if ($tipoProcesso) {
                        $queryAno->where('tipo_processo_id', function($subq) use ($tipoProcesso) {
                            $subq->select('id')
                                 ->from('tipos_processo')
                                 ->where('slug', $tipoProcesso)
                                 ->limit(1);
                        });
                    }
                    if ($statusProcesso) {
                        $queryAno->where('status', $statusProcesso);
                    }
                    
                    $dados['processos_ano_' . $ano] = $queryAno->count();
                    
                    if ($tipoProcesso && $statusProcesso) {
                        $dados["processos_{$tipoProcesso}_{$statusProcesso}_{$ano}"] = $queryAno->count();
                    } elseif ($tipoProcesso) {
                        $dados["processos_{$tipoProcesso}_{$ano}"] = $queryAno->count();
                    } elseif ($statusProcesso) {
                        $dados["processos_{$statusProcesso}_{$ano}"] = $queryAno->count();
                    }
                }
            }

            // Detecta perguntas sobre ordens de serviço
            if ($buscarOrdens) {
                $query = OrdemServico::query();
                
                // Ordens de serviço não têm filtro de competência direto
                // Mas podem ser filtradas por município se o usuário for municipal
                if ($usuario->isMunicipal() && $usuario->municipio_id) {
                    $query->whereHas('estabelecimento', function ($q) use ($usuario) {
                        $q->where('municipio_id', $usuario->municipio_id);
                    });
                }
                
                $dados['ordens_servico_total'] = $query->count();
                $dados['ordens_servico_em_andamento'] = (clone $query)->where('status', 'em_andamento')->count();
                $dados['ordens_servico_concluidas'] = (clone $query)->where('status', 'concluida')->count();
                $dados['ordens_servico_canceladas'] = (clone $query)->where('status', 'cancelada')->count();
                
                // Filtra por ano se mencionado
                if (preg_match('/\b(20\d{2})\b/', $message, $matches)) {
                    $ano = $matches[1];
                    $queryAno = clone $query;
                    $queryAno->whereYear('created_at', $ano);
                    $dados['ordens_servico_ano_' . $ano] = $queryAno->count();
                }
            }
            
            // Detecta perguntas sobre documentos
            if ($buscarDocumentos) {
                $query = DocumentoDigital::query();
                
                // Filtra por competência através do processo
                if ($usuario->isEstadual() || ($usuario->isMunicipal() && $usuario->municipio_id)) {
                    $query->whereHas('processo.estabelecimento', function ($q) use ($usuario) {
                        if ($usuario->isEstadual()) {
                            $q->whereRaw('
                                (
                                    competencia_manual = \'estadual\'
                                    OR
                                    (
                                        competencia_manual IS NULL
                                        AND EXISTS (
                                            SELECT 1 FROM atividade_estabelecimento ae
                                            INNER JOIN atividades a ON ae.atividade_id = a.id
                                            WHERE ae.estabelecimento_id = estabelecimentos.id
                                            AND a.competencia = \'estadual\'
                                            AND NOT EXISTS (
                                                SELECT 1 FROM descentralizacoes d
                                                WHERE d.atividade_id = a.id
                                                AND d.municipio_id = estabelecimentos.municipio_id
                                                AND d.ativo = true
                                            )
                                        )
                                    )
                                )
                            ');
                        } else {
                            $q->where('municipio_id', $usuario->municipio_id)
                              ->whereRaw('
                                (
                                    competencia_manual = \'municipal\'
                                    OR
                                    (
                                        competencia_manual IS NULL
                                        AND NOT EXISTS (
                                            SELECT 1 FROM atividade_estabelecimento ae
                                            INNER JOIN atividades a ON ae.atividade_id = a.id
                                            WHERE ae.estabelecimento_id = estabelecimentos.id
                                            AND a.competencia = \'estadual\'
                                            AND NOT EXISTS (
                                                SELECT 1 FROM descentralizacoes d
                                                WHERE d.atividade_id = a.id
                                                AND d.municipio_id = estabelecimentos.municipio_id
                                                AND d.ativo = true
                                            )
                                        )
                                    )
                                )
                              ');
                        }
                    });
                }
                
                $dados['documentos_total'] = $query->count();
                $dados['documentos_assinados'] = (clone $query)->where('status', 'assinado')->count();
                $dados['documentos_aguardando'] = (clone $query)->where('status', 'aguardando_assinaturas')->count();
                $dados['documentos_rascunho'] = (clone $query)->where('status', 'rascunho')->count();
            }
            
            // Busca documentos POPs relevantes para a pergunta
            $documentosPops = $this->buscarDocumentosPopsRelevantes($message);
            if (!empty($documentosPops)) {
                // Verifica se retornou com categoria filtrada
                if (isset($documentosPops['documentos'])) {
                    $dados['documentos_pops'] = $documentosPops['documentos'];
                    $dados['categoria_filtrada'] = $documentosPops['categoria_filtrada'];
                } else {
                    $dados['documentos_pops'] = $documentosPops;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao obter contexto de dados para IA', [
                'usuario_id' => $usuario->id,
                'message' => $message,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $dados;
    }
    
    /**
     * Busca documentos POPs relevantes baseado na pergunta
     */
    private function buscarDocumentosPopsRelevantes($message)
    {
        try {
            // Busca documentos marcados para IA que estão indexados
            $query = DocumentoPop::where('disponivel_ia', true)
                ->whereNotNull('conteudo_extraido')
                ->whereNotNull('indexado_em')
                ->with('categorias');
            
            // Palavras-chave para busca
            $palavrasChave = $this->extrairPalavrasChave($message);
            
            \Log::info('Busca POPs - Palavras-chave extraídas', [
                'message' => $message,
                'palavras_chave' => $palavrasChave
            ]);
            
            // Detecta se a pergunta menciona uma categoria específica
            $categoriaFiltro = $this->detectarCategoria($message);
            
            if ($categoriaFiltro) {
                // Filtra apenas documentos da categoria mencionada
                $query->whereHas('categorias', function($q) use ($categoriaFiltro) {
                    $q->where('categorias_pops.id', $categoriaFiltro->id);
                });
                
                \Log::info('Busca POPs - Categoria filtrada', [
                    'categoria' => $categoriaFiltro->nome
                ]);
            }
            
            $documentos = $query->get();
            
            \Log::info('Busca POPs - Documentos encontrados', [
                'total' => $documentos->count(),
                'titulos' => $documentos->pluck('titulo')->toArray()
            ]);
            
            if ($documentos->isEmpty()) {
                return [];
            }
            
            $documentosRelevantes = [];
            
            foreach ($documentos as $doc) {
                $relevancia = 0;
                $conteudoLower = strtolower($doc->conteudo_extraido);
                $tituloLower = strtolower($doc->titulo);
                
                $palavrasEncontradas = [];
                
                // BÔNUS MASSIVO se o título contém "NBR"
                $messageLower = strtolower($message);
                if (strpos($tituloLower, 'nbr') !== false) {
                    // NBR mencionada explicitamente na pergunta
                    if (strpos($messageLower, 'nbr') !== false) {
                        $relevancia += 500;
                        $palavrasEncontradas[] = 'NBR(mencionada-PRIORIDADE)';
                    }
                    // Pergunta sobre especificações técnicas (cores, dimensões, etc) - NBR tem prioridade
                    elseif (preg_match('/\b(cor|cores|dimensão|dimensões|tamanho|medida|especificação|identificação|padrão)\b/i', $messageLower)) {
                        $relevancia += 800; // PRIORIDADE ALTÍSSIMA - NBR é norma técnica
                        $palavrasEncontradas[] = 'NBR(especificação-técnica-PRIORIDADE-MÁXIMA)';
                    }
                }
                
                // BÔNUS MASSIVO se o título contém número de RDC/NBR específico mencionado na pergunta
                if (preg_match('/\b(\d{3,5})\b/', $tituloLower, $matchesTitulo)) {
                    if (preg_match('/\b' . preg_quote($matchesTitulo[1], '/') . '\b/', $messageLower)) {
                        $relevancia += 1000; // Prioridade ABSOLUTA quando número específico é mencionado
                        $palavrasEncontradas[] = 'Número-Específico(' . $matchesTitulo[1] . '-PRIORIDADE-MÁXIMA)';
                    }
                }
                
                // BÔNUS EXTRA se o título menciona o tema principal da pergunta
                // Ex: título "NBR 12176 e as cores dos cilindros" + pergunta sobre "cores cilindros"
                $palavrasChaveTitulo = ['cor', 'cores', 'cilindro', 'cilindros', 'identificação', 'rotulagem'];
                $countPalavrasTitulo = 0;
                foreach ($palavrasChaveTitulo as $palavraTema) {
                    if (strpos($tituloLower, $palavraTema) !== false && strpos($messageLower, $palavraTema) !== false) {
                        $countPalavrasTitulo++;
                    }
                }
                if ($countPalavrasTitulo >= 2) {
                    $relevancia += 300; // Bônus grande se título menciona 2+ palavras-chave do tema
                    $palavrasEncontradas[] = 'Tema-no-Título(' . $countPalavrasTitulo . '-palavras)';
                }
                
                // Verifica relevância baseado em palavras-chave
                foreach ($palavrasChave as $palavra) {
                    if (strlen($palavra) < 3) continue; // Ignora palavras muito curtas
                    
                    // Título tem peso MUITO maior (50 ao invés de 10)
                    if (strpos($tituloLower, $palavra) !== false) {
                        $relevancia += 50;
                        $palavrasEncontradas[] = $palavra . '(título)';
                    }
                    
                    // Conteúdo
                    $ocorrencias = substr_count($conteudoLower, $palavra);
                    if ($ocorrencias > 0) {
                        $relevancia += $ocorrencias;
                        $palavrasEncontradas[] = $palavra . '(conteúdo:' . $ocorrencias . 'x)';
                    }
                }
                
                \Log::info('Busca POPs - Score do documento', [
                    'titulo' => $doc->titulo,
                    'relevancia' => $relevancia,
                    'palavras_encontradas' => $palavrasEncontradas
                ]);
                
                // Se tem relevância, adiciona
                if ($relevancia > 0) {
                    $documentosRelevantes[] = [
                        'titulo' => $doc->titulo,
                        'relevancia' => $relevancia,
                        'conteudo' => $this->extrairTrechoRelevante($doc->conteudo_extraido, $palavrasChave),
                        'categorias' => $doc->categorias->pluck('nome')->toArray(),
                    ];
                }
            }
            
            // Ordena por relevância e pega os 3 mais relevantes
            usort($documentosRelevantes, function($a, $b) {
                return $b['relevancia'] - $a['relevancia'];
            });
            
            $resultado = array_slice($documentosRelevantes, 0, 3);
            
            \Log::info('Busca POPs - Documentos selecionados', [
                'total_relevantes' => count($documentosRelevantes),
                'selecionados' => array_map(function($doc) {
                    return [
                        'titulo' => $doc['titulo'],
                        'relevancia' => $doc['relevancia']
                    ];
                }, $resultado)
            ]);
            
            // Se foi filtrado por categoria, adiciona informação
            if ($categoriaFiltro) {
                return [
                    'documentos' => $resultado,
                    'categoria_filtrada' => $categoriaFiltro->nome,
                ];
            }
            
            return $resultado;
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar documentos POPs relevantes', [
                'erro' => $e->getMessage(),
            ]);
            return [];
        }
    }
    
    /**
     * Extrai palavras-chave da mensagem
     */
    private function extrairPalavrasChave($message)
    {
        // Remove palavras comuns (stop words)
        $stopWords = ['o', 'a', 'os', 'as', 'um', 'uma', 'de', 'da', 'do', 'para', 'com', 'em', 'no', 'na', 'por', 'como', 'qual', 'quais', 'que', 'e', 'ou', 'é', 'são', 'fala', 'diz', 'segundo', 'conforme'];
        
        $palavras = preg_split('/\s+/', strtolower($message));
        $palavrasLimpas = [];
        
        foreach ($palavras as $palavra) {
            // Remove pontuação da palavra
            $palavraLimpa = preg_replace('/[^\w\d]/u', '', $palavra);
            
            if (!in_array($palavraLimpa, $stopWords) && strlen($palavraLimpa) >= 3) {
                $palavrasLimpas[] = $palavraLimpa;
                
                // Adiciona variações plural/singular para palavras-chave importantes
                if (substr($palavraLimpa, -1) === 's' && strlen($palavraLimpa) > 4) {
                    // Remove 's' final para pegar singular (ex: "cilindros" -> "cilindro")
                    $palavrasLimpas[] = substr($palavraLimpa, 0, -1);
                } elseif (substr($palavraLimpa, -1) !== 's') {
                    // Adiciona 's' para pegar plural (ex: "cilindro" -> "cilindros")
                    $palavrasLimpas[] = $palavraLimpa . 's';
                }
            }
        }
        
        $palavras = array_unique($palavrasLimpas);
        
        $messageLower = strtolower($message);
        
        // Detecta menção a NBR específica (ex: "NBR 12176")
        if (preg_match('/nbr\s*(\d+)/i', $messageLower, $matches)) {
            $palavras[] = 'nbr';
            $palavras[] = $matches[1]; // número da NBR
        }
        
        // Se a pergunta menciona "artigo" ou "rdc", adiciona palavras-chave relacionadas
        if (strpos($messageLower, 'artigo') !== false || strpos($messageLower, 'art.') !== false) {
            $palavras[] = 'aplica-se';
            $palavras[] = 'resolução';
        }
        
        // Detecta menção a RDC específica (ex: "RDC 887")
        if (preg_match('/rdc\s*n?[º°]?\s*(\d+)/i', $messageLower, $matches)) {
            $palavras[] = 'rdc';
            $palavras[] = $matches[1]; // número da RDC
        }
        
        // Adiciona sinônimos e variações importantes
        if (in_array('cor', $palavras) || in_array('cores', $palavras)) {
            $palavras[] = 'identificação';
            $palavras[] = 'pintura';
        }
        
        return array_values($palavras);
    }
    
    /**
     * Extrai trecho relevante do conteúdo
     */
    private function extrairTrechoRelevante($conteudo, $palavrasChave)
    {
        // Limpa caracteres UTF-8 malformados do conteúdo
        $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'UTF-8');
        $conteudo = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $conteudo);
        
        $conteudoLower = strtolower($conteudo);
        
        // Tenta buscar por frase exata (sequência de 5+ palavras-chave consecutivas)
        if (count($palavrasChave) >= 5) {
            // Tenta encontrar a maior sequência possível de palavras
            for ($tamanho = min(8, count($palavrasChave)); $tamanho >= 5; $tamanho--) {
                for ($i = 0; $i <= count($palavrasChave) - $tamanho; $i++) {
                    $palavrasBusca = array_slice($palavrasChave, $i, $tamanho);
                    // Permite até 3 palavras entre cada palavra-chave
                    $fraseBusca = implode('(?:\s+\S+){0,3}\s+', array_map('preg_quote', $palavrasBusca, array_fill(0, count($palavrasBusca), '/')));
                    
                    if (preg_match('/' . $fraseBusca . '/i', $conteudoLower, $matches, PREG_OFFSET_CAPTURE)) {
                        $pos = $matches[0][1];
                        // Procura o artigo mais próximo antes desta posição
                        $textoAntes = substr($conteudo, max(0, $pos - 2000), 2000);
                        if (preg_match_all('/(?:Art\.|Artigo)\s*\d+[º°]?/i', $textoAntes, $artigosAntes, PREG_OFFSET_CAPTURE)) {
                            $ultimoArtigo = end($artigosAntes[0]);
                            $posArtigo = max(0, $pos - 2000 + $ultimoArtigo[1]);
                            $inicio = max(0, $posArtigo - 300);
                            $trecho = substr($conteudo, $inicio, 4000);
                            
                            if ($inicio > 0) {
                                $trecho = '...' . $trecho;
                            }
                            if (strlen($conteudo) > $inicio + 4000) {
                                $trecho .= '...';
                            }
                            
                            return $trecho;
                        }
                    }
                }
            }
        }
        
        // Procura todos os artigos E parágrafos no documento (até 20 linhas após para pegar conteúdo completo)
        // Captura: Art. 1º, Art. 2º, §1º, §2º, Parágrafo único, etc.
        preg_match_all('/(?:Art\.|Artigo|§|Parágrafo)\s*(?:\d+[º°]?|único)[^\n]*(?:\n[^\n]+){0,20}/i', $conteudo, $artigos, PREG_OFFSET_CAPTURE);
        
        $melhorMatch = null;
        $melhorScore = 0;
        
        // Avalia cada artigo encontrado
        foreach ($artigos[0] as $artigo) {
            $textoArtigo = strtolower($artigo[0]);
            $score = 0;
            
            // Conta quantas palavras-chave aparecem neste artigo
            $palavrasEncontradas = 0;
            foreach ($palavrasChave as $palavra) {
                if (strpos($textoArtigo, $palavra) !== false) {
                    $count = substr_count($textoArtigo, $palavra);
                    $score += $count * 10;
                    $palavrasEncontradas++;
                }
            }
            
            // BÔNUS MASSIVO se contém a maioria das palavras-chave (frase muito similar)
            $percentualPalavras = $palavrasEncontradas / count($palavrasChave);
            if ($percentualPalavras >= 0.7) { // 70% ou mais das palavras
                $score += 500; // Bônus enorme para frases muito similares
            } elseif ($percentualPalavras >= 0.5) { // 50% ou mais
                $score += 200;
            }
            
            // Bônus se contém sequências de 3+ palavras-chave seguidas
            $palavrasNoArtigo = preg_split('/\s+/', $textoArtigo);
            $sequenciaAtual = 0;
            $maiorSequencia = 0;
            foreach ($palavrasNoArtigo as $palavraArtigo) {
                if (in_array($palavraArtigo, $palavrasChave)) {
                    $sequenciaAtual++;
                    $maiorSequencia = max($maiorSequencia, $sequenciaAtual);
                } else {
                    $sequenciaAtual = 0;
                }
            }
            
            // Bônus progressivo para sequências longas
            if ($maiorSequencia >= 5) {
                $score += 300; // Sequência muito longa
            } elseif ($maiorSequencia >= 4) {
                $score += 150;
            } elseif ($maiorSequencia >= 3) {
                $score += 50;
            }
            
            // Se este artigo tem melhor score, guarda
            if ($score > $melhorScore) {
                $melhorScore = $score;
                $melhorMatch = $artigo;
            }
        }
        
        // Se encontrou um artigo relevante, extrai contexto ao redor dele
        if ($melhorMatch) {
            $posArtigo = $melhorMatch[1];
            
            // Extrai um trecho MUITO maior para incluir vários artigos adjacentes
            $inicio = max(0, $posArtigo - 1000); // Muito mais contexto antes (vários artigos anteriores)
            $tamanho = 4000; // Aumentado para 4000 caracteres (muitos artigos)
            $trecho = substr($conteudo, $inicio, $tamanho);
            
            if ($inicio > 0) {
                $trecho = '...' . $trecho;
            }
            if (strlen($conteudo) > $inicio + $tamanho) {
                $trecho .= '...';
            }
            
            return $trecho;
        }
        
        // Fallback: busca por palavra-chave normal
        foreach ($palavrasChave as $palavra) {
            $pos = strpos($conteudoLower, $palavra);
            if ($pos !== false) {
                $inicio = max(0, $pos - 400);
                $trecho = substr($conteudo, $inicio, 800);
                
                if ($inicio > 0) {
                    $trecho = '...' . $trecho;
                }
                if (strlen($conteudo) > $inicio + 800) {
                    $trecho .= '...';
                }
                
                return $trecho;
            }
        }
        
        // Se não encontrou nada, retorna início do documento (muito maior)
        return substr($conteudo, 0, 3000) . '...';
    }
    
    /**
     * Detecta se a pergunta menciona uma categoria específica
     */
    private function detectarCategoria($message)
    {
        try {
            $messageLower = strtolower($message);
            
            // Busca todas as categorias ativas
            $categorias = CategoriaPop::ativas()->get();
            
            foreach ($categorias as $categoria) {
                $nomeCategoria = strtolower($categoria->nome);
                $slugCategoria = strtolower($categoria->slug);
                
                // Verifica se o nome ou slug da categoria aparece na mensagem
                if (strpos($messageLower, $nomeCategoria) !== false || 
                    strpos($messageLower, $slugCategoria) !== false) {
                    return $categoria;
                }
            }
            
            return null;
            
        } catch (\Exception $e) {
            \Log::error('Erro ao detectar categoria', [
                'erro' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Constrói prompt simplificado quando há documento(s) PDF (economiza tokens)
     */
    private function construirPromptSimplificadoDocumento($contextoDados)
    {
        $prompt = "Você é um assistente especializado em análise de documentos.\n\n";
        $buscarInternet = false;
        
        // Verifica se há múltiplos documentos
        if (isset($contextoDados['documentos_pdf']) && !empty($contextoDados['documentos_pdf'])) {
            $documentos = $contextoDados['documentos_pdf'];
            $totalDocs = count($documentos);
            
            // Verifica se algum documento tem busca na internet ativada
            foreach ($documentos as $doc) {
                if (isset($doc['buscar_internet']) && $doc['buscar_internet'] === true) {
                    $buscarInternet = true;
                    break;
                }
            }
            
            $prompt .= "🚨 {$totalDocs} DOCUMENTO(S) CARREGADO(S):\n\n";
            
            foreach ($documentos as $index => $docPdf) {
                $nomeDoc = is_array($docPdf['nome'] ?? null) ? json_encode($docPdf['nome']) : ($docPdf['nome'] ?? 'Documento');
                $conteudoDoc = is_array($docPdf['conteudo'] ?? null) ? json_encode($docPdf['conteudo']) : ($docPdf['conteudo'] ?? '');
                
                $prompt .= "DOC " . ($index + 1) . ": {$nomeDoc}\n";
                $prompt .= $conteudoDoc . "\n\n---\n\n";
            }
        }
        // Fallback para documento único
        elseif (isset($contextoDados['documento_pdf']) && !empty($contextoDados['documento_pdf'])) {
            $docPdf = $contextoDados['documento_pdf'];
            $buscarInternet = $docPdf['buscar_internet'] ?? false;
            $nomeDoc = is_array($docPdf['nome'] ?? null) ? json_encode($docPdf['nome']) : ($docPdf['nome'] ?? 'Documento');
            $conteudoDoc = is_array($docPdf['conteudo'] ?? null) ? json_encode($docPdf['conteudo']) : ($docPdf['conteudo'] ?? '');
            
            $prompt .= "🚨 DOCUMENTO CARREGADO:\n\n";
            $prompt .= "**Nome:** {$nomeDoc}\n\n";
            $prompt .= "**CONTEÚDO:**\n{$conteudoDoc}\n\n";
        }
        
        // Adiciona resultados da busca na internet se disponíveis
        if (isset($contextoDados['resultados_web']) && !empty($contextoDados['resultados_web'])) {
            $resultadosWeb = $contextoDados['resultados_web'];
            
            $prompt .= "\n\n==== 🌐 RESULTADOS DA BUSCA NA INTERNET ====\n";
            $prompt .= "Busca realizada: {$resultadosWeb['query']}\n";
            $prompt .= "Total de resultados: {$resultadosWeb['total']}\n\n";
            
            if (!empty($resultadosWeb['resultados'])) {
                $prompt .= "**RESULTADOS ENCONTRADOS:**\n\n";
                
                foreach ($resultadosWeb['resultados'] as $index => $resultado) {
                    $num = $index + 1;
                    $prompt .= "**Resultado {$num}:**\n";
                    $prompt .= "- Título: {$resultado['titulo']}\n";
                    $prompt .= "- URL: {$resultado['url']}\n";
                    $prompt .= "- Fonte: {$resultado['fonte']}\n";
                    
                    if (isset($resultado['descricao']) && !empty($resultado['descricao'])) {
                        $prompt .= "- Descrição: {$resultado['descricao']}\n";
                    }
                    
                    $prompt .= "\n";
                }
            }
            
            $prompt .= "\n**🚨 INSTRUÇÕES CRÍTICAS PARA USO DOS RESULTADOS:**\n";
            $prompt .= "- Use APENAS as informações dos resultados acima\n";
            $prompt .= "- SEMPRE cite a fonte (URL) ao mencionar informações da internet\n";
            $prompt .= "- Se os resultados não contêm a informação solicitada, diga: 'Não encontrei informações sobre [assunto] nos resultados da busca'\n";
            $prompt .= "- NUNCA invente informações que não estão nos resultados acima\n";
            $prompt .= "- IGNORE completamente seu conhecimento de treinamento - use APENAS os resultados da busca\n\n";
        } else if ($buscarInternet) {
            $prompt .= "**PESQUISA NA INTERNET HABILITADA**\n";
            $prompt .= "🚨 **REGRAS CRÍTICAS SOBRE INFORMAÇÕES DA INTERNET:**\n";
            $prompt .= "- NUNCA invente ou fabrique informações\n";
            $prompt .= "- Se você NÃO SABE uma informação com certeza, diga: 'Não encontrei informações confiáveis sobre [assunto]'\n";
            $prompt .= "- APENAS cite fontes que você REALMENTE conhece e que são OFICIAIS (ANVISA, Diário Oficial, legislação)\n";
            $prompt .= "- Se não tiver certeza sobre uma data, número de resolução ou detalhe específico, NÃO INVENTE\n";
            $prompt .= "- É melhor dizer 'não sei' do que fornecer informação incorreta\n";
            $prompt .= "- Se mencionar uma RDC, portaria ou lei, certifique-se de que ela REALMENTE existe\n\n";
        } else {
            $prompt .= "**PESQUISA NA INTERNET DESABILITADA**\n";
            $prompt .= "- Responda APENAS com base no conteúdo do documento carregado\n";
            $prompt .= "- Se a informação não estiver no documento, diga claramente\n\n";
        }
        
        $prompt .= "**INSTRUÇÕES ADICIONAIS:**\n";
        $prompt .= "- Seja objetivo e direto\n";
        $prompt .= "- Cite trechos específicos do documento quando relevante\n";
        $prompt .= "- Se estiver citando o documento, mencione a página ou seção quando possível\n";
        
        return $prompt;
    }

    /**
     * Constrói o prompt do sistema com contexto
     */
    private function construirSystemPrompt($contextoDados, $usuario, $temDocumento = false)
    {
        // Se tem documento PDF, usa prompt MUITO simplificado
        if ($temDocumento) {
            return $this->construirPromptSimplificadoDocumento($contextoDados);
        }
        // Informações do usuário
        $perfilUsuario = '';
        $municipioNome = '';
        
        try {
            if ($usuario->isAdmin()) {
                $perfilUsuario = 'Administrador (acesso total ao sistema)';
            } elseif ($usuario->isEstadual()) {
                $perfilUsuario = 'Gestor/Técnico Estadual (acesso apenas a processos de competência estadual de todos os municípios)';
            } elseif ($usuario->isMunicipal()) {
                if ($usuario->municipio_id && $usuario->municipio) {
                    $municipioNome = $usuario->municipio->nome;
                    $perfilUsuario = "Gestor/Técnico Municipal de {$municipioNome} (acesso apenas a processos de competência municipal do próprio município)";
                } else {
                    $perfilUsuario = 'Gestor/Técnico Municipal (acesso apenas a processos de competência municipal)';
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao construir perfil do usuário para IA', [
                'usuario_id' => $usuario->id,
                'erro' => $e->getMessage()
            ]);
            $perfilUsuario = 'Usuário do sistema';
        }
        
        $prompt = "Você é um assistente virtual do Sistema InfoVisa, um sistema de gestão de vigilância sanitária. 

CONTEXTO DO USUÁRIO:
- Nome: {$usuario->nome}
- Perfil: {$perfilUsuario}
" . ($municipioNome ? "- Município: {$municipioNome}\n" : "") . "

REGRAS CRÍTICAS DE COMPORTAMENTO:

**DIFERENCIE O TIPO DE PERGUNTA:**

1. **PERGUNTAS SOBRE DOCUMENTOS POPs (Procedimentos Operacionais Padrão):**
   - Se a pergunta é sobre NORMAS, PROCEDIMENTOS, REGULAMENTAÇÕES, REQUISITOS TÉCNICOS
   - Exemplos: \"normas de gases medicinais\", \"como armazenar\", \"requisitos para\", \"o que diz a RDC\"
   - RESPONDA APENAS COM BASE NOS DOCUMENTOS POPs fornecidos abaixo
   - NÃO mencione funcionalidades do sistema
   - NÃO diga \"acesse o menu\", \"clique em\", \"vá em estabelecimentos\"
   - Cite os documentos POPs usados na resposta
   - Seja técnico e objetivo

2. **PERGUNTAS SOBRE FUNCIONALIDADES DO SISTEMA:**
   - Se a pergunta é sobre COMO USAR O SISTEMA, ONDE ENCONTRAR ALGO, COMO CRIAR/EDITAR
   - Exemplos: \"como criar processo\", \"onde vejo estabelecimentos\", \"como gerar documento\"
   - RESPONDA com instruções passo a passo do sistema
   - Use as funcionalidades descritas abaixo
   - NÃO mencione documentos POPs
   - Seja prático e didático

3. **PERGUNTAS SOBRE DADOS DO SISTEMA:**
   - Se a pergunta é sobre QUANTIDADES, ESTATÍSTICAS, LISTAGENS
   - Exemplos: \"quantos estabelecimentos\", \"quantos processos\", \"qual o status\"
   - RESPONDA com os números exatos fornecidos nos dados
   - Pode sugerir onde ver mais detalhes no sistema

**REGRAS GERAIS:**
- Use APENAS os dados fornecidos abaixo - eles já estão filtrados pela competência do usuário
- NUNCA invente funcionalidades, menus ou caminhos que não foram mencionados
- NUNCA invente informações de POPs que não estão nos documentos fornecidos
- **CRÍTICO: NUNCA invente números de artigos, RDCs, resoluções ou leis que não estão EXPLICITAMENTE nos documentos POPs fornecidos**
- **CRÍTICO: Se você citar um artigo ou resolução, ele DEVE estar LITERALMENTE no texto do documento POP fornecido**
- **CRÍTICO: NÃO combine informações de diferentes documentos para criar citações falsas**
- Seja EXTREMAMENTE preciso nas instruções - siga EXATAMENTE os passos descritos
- Se não souber algo, diga claramente que não sabe
- Use os números exatos fornecidos nos dados
- Responda considerando o perfil e permissões do usuário

**🚨 REGRA CRÍTICA - DOCUMENTO PDF CARREGADO TEM PRIORIDADE ABSOLUTA:**
- Se houver um documento PDF carregado pelo usuário (indicado com 🚨), responda APENAS sobre ele
- IGNORE completamente os documentos POPs quando houver PDF carregado
- NÃO mencione categorias (Gases Medicinais, etc) se o usuário carregou um PDF específico

**REGRA CRÍTICA - NÃO MISTURE POPs COM FUNCIONALIDADES:**
- Se a pergunta é sobre NORMAS/POPs: responda APENAS com o conteúdo dos documentos POPs
- NÃO invente tipos de processo (ex: \"Notificação de Mau Uso de Gases Medicinais\" NÃO EXISTE)
- NÃO crie passos de sistema para cumprir normas dos POPs
- Se o POP diz \"deve notificar\", responda APENAS o que o POP diz, SEM inventar como fazer no sistema
- O sistema tem tipos de processo GENÉRICOS, não específicos para cada norma
- NUNCA combine \"De acordo com RDC...\" + \"Acesse o menu...\" na mesma resposta

FUNCIONALIDADES REAIS DO SISTEMA:

**1. ESTABELECIMENTOS:**
Acesso: Menu lateral > Ícone de prédio (segundo ícone)
- Listar todos os estabelecimentos (filtrados por competência)
- Botão 'Novo Estabelecimento' no topo da lista
- Clicar em um estabelecimento para ver detalhes
- Dentro dos detalhes: abas Dados, Processos, Histórico

**2. PROCESSOS:**
Acesso: Menu lateral > Ícone de pasta (terceiro ícone)
- Lista todos os processos (filtrados por competência)
- Mostra: número, estabelecimento, tipo, status, data

**TIPOS DE PROCESSO DISPONÍVEIS NO SISTEMA (LISTA COMPLETA):**
1. Licenciamento - Processo de licenciamento sanitário anual
2. Análise de Rotulagem - Análise e aprovação de rótulos
3. Projeto Arquitetônico - Análise de projeto para adequação sanitária
4. Administrativo - Processos administrativos diversos
5. Descentralização - Processos de descentralização de ações

IMPORTANTE: Estes são os ÚNICOS tipos de processo que existem no sistema.
NÃO EXISTE tipo de processo específico para cada norma (ex: \"Notificação de Mau Uso de Gases Medicinais\" NÃO EXISTE).
Se precisar registrar algo relacionado a normas, use o tipo \"Administrativo\" de forma genérica.

**COMO ABRIR UM PROCESSO (PASSO A PASSO EXATO):**
1. Vá em Estabelecimentos (menu lateral, ícone de prédio)
2. Encontre o estabelecimento na lista
3. Clique no botão 'Ver Detalhes' do estabelecimento
4. Clique na aba 'Processos'
5. Clique no botão 'Novo Processo' (canto superior direito)
6. Preencha:
   - Tipo de Processo (selecione da lista)
   - Descrição (opcional)
7. Clique em 'Salvar'

**COMO CRIAR UM DOCUMENTO DIGITAL (PASSO A PASSO EXATO):**
1. Abra um processo existente (veja passos acima)
2. Role até a seção 'Documentos Digitais'
3. Clique no botão 'Criar Documento'
4. Preencha:
   - Tipo de Documento (Alvará, Licença, Termo, etc.)
   - Número do Documento (gerado automaticamente)
   - Conteúdo (editor de texto rico)
5. Clique em 'Salvar'
6. O documento é criado como rascunho
7. Para assinar: clique em 'Adicionar Assinatura' no documento
8. Quando todas as assinaturas forem coletadas, o documento muda para status 'Assinado'
9. O PDF é gerado automaticamente quando o documento é assinado

**OUTRAS FUNCIONALIDADES:**
- Anexar arquivos ao processo (PDF, imagens, etc.)
- Gerar 'Processo na Íntegra' (PDF compilado com todos os documentos)
- Acompanhar processo (receber notificações)
- Arquivar/Desarquivar processo
- Parar/Reiniciar processo

**CONFIGURAÇÕES (apenas Administradores):**
Acesso: Menu lateral > Ícone de engrenagem
- Usuários internos
- Municípios
- Pactuação (competências)
- Logomarca estadual
- Assistente de IA

";

        // Adiciona dados do sistema se disponíveis
        if (!empty($contextoDados)) {
            $prompt .= "\n\n==== DADOS ATUAIS DO SISTEMA ====\n";
            $prompt .= "IMPORTANTE: Estes números já estão filtrados pela competência e município do usuário.\n\n";
            
            foreach ($contextoDados as $key => $value) {
                // Documentos POPs e outros arrays são tratados separadamente
                if (in_array($key, ['documentos_pops', 'categoria_filtrada', 'resultados_web', 'documento_pdf'])) {
                    continue;
                }
                
                $label = str_replace('_', ' ', ucfirst($key));
                // Converte arrays para string se necessário
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $prompt .= "- {$label}: {$value}\n";
            }
            
            // Adiciona contexto sobre o filtro
            if (isset($contextoDados['municipio_filtrado'])) {
                $prompt .= "\n**IMPORTANTE:** Dados filtrados para o município de {$contextoDados['municipio_filtrado']}\n";
            } elseif ($usuario->isEstadual()) {
                $prompt .= "\n(Dados filtrados: apenas competência ESTADUAL de todos os municípios)\n";
            } elseif ($usuario->isMunicipal() && !empty($municipioNome)) {
                $prompt .= "\n(Dados filtrados: apenas competência MUNICIPAL de {$municipioNome})\n";
            }
            
            // ===== PRIORIDADE MÁXIMA: DOCUMENTOS PDF CARREGADOS =====
            // Adiciona contexto de múltiplos documentos PDF se disponível (ANTES de tudo)
            if (isset($contextoDados['documentos_pdf']) && !empty($contextoDados['documentos_pdf'])) {
                $documentos = $contextoDados['documentos_pdf'];
                $totalDocs = count($documentos);
                
                $prompt .= "\n\n🚨 {$totalDocs} DOCUMENTO(S) CARREGADO(S):\n\n";
                
                foreach ($documentos as $index => $docPdf) {
                    $nomeDoc = is_array($docPdf['nome'] ?? null) ? json_encode($docPdf['nome']) : ($docPdf['nome'] ?? 'Documento');
                    $conteudoDoc = is_array($docPdf['conteudo'] ?? null) ? json_encode($docPdf['conteudo']) : ($docPdf['conteudo'] ?? '');
                    
                    $prompt .= "DOC " . ($index + 1) . ": {$nomeDoc}\n";
                    $prompt .= $conteudoDoc . "\n\n---\n\n";
                }
                
                $prompt .= "INSTRUÇÕES:\n";
                $prompt .= "- Responda APENAS sobre estes {$totalDocs} documentos\n";
                $prompt .= "- Mencione qual documento ao citar informações\n";
                $prompt .= "- IGNORE POPs e outras categorias\n\n";
            }
            // Fallback para documento único (compatibilidade)
            elseif (isset($contextoDados['documento_pdf']) && !empty($contextoDados['documento_pdf'])) {
                $docPdf = $contextoDados['documento_pdf'];
                $nomeDoc = is_array($docPdf['nome'] ?? null) ? json_encode($docPdf['nome']) : ($docPdf['nome'] ?? 'Documento');
                $conteudoDoc = is_array($docPdf['conteudo'] ?? null) ? json_encode($docPdf['conteudo']) : ($docPdf['conteudo'] ?? '');
                
                $prompt .= "\n\n╔════════════════════════════════════════════════════════════╗\n";
                $prompt .= "║  🚨 ATENÇÃO: DOCUMENTO PDF CARREGADO PELO USUÁRIO 🚨     ║\n";
                $prompt .= "╚════════════════════════════════════════════════════════════╝\n\n";
                $prompt .= "**Nome do documento:** {$nomeDoc}\n\n";
                $prompt .= "**CONTEÚDO DO DOCUMENTO:**\n";
                $prompt .= $conteudoDoc . "\n\n";
                $prompt .= "**⚠️ INSTRUÇÕES CRÍTICAS - PRIORIDADE ABSOLUTA:**\n";
                $prompt .= "- ❗ O usuário ABRIU ESTE DOCUMENTO e quer fazer perguntas SOBRE ELE\n";
                $prompt .= "- ❗ Use APENAS o conteúdo acima para responder\n";
                $prompt .= "- ❗ IGNORE completamente os documentos POPs abaixo\n";
                $prompt .= "- ❗ IGNORE qualquer categoria mencionada (Gases Medicinais, etc)\n";
                $prompt .= "- ❗ NÃO responda sobre POPs, responda APENAS sobre este documento específico\n";
                $prompt .= "- ❗ Se a pergunta não puder ser respondida com base NESTE documento, diga claramente\n";
                $prompt .= "- ❗ Cite trechos específicos DESTE documento quando relevante\n";
                $prompt .= "- ❗ Se o documento mencionar artigos, RDCs ou normas, cite-os exatamente como aparecem NESTE documento\n";
                $prompt .= "- ❗ Este documento tem PRIORIDADE ABSOLUTA sobre qualquer outro contexto\n\n";
                $prompt .= "═══════════════════════════════════════════════════════════\n\n";
            }
            
            // Lista categorias POPs disponíveis
            $categoriasDisponiveis = \App\Models\CategoriaPop::ativas()
                ->whereHas('documentos', function($q) {
                    $q->where('disponivel_ia', true)
                      ->whereNotNull('conteudo_extraido');
                })
                ->pluck('nome')
                ->toArray();
            
            if (!empty($categoriasDisponiveis)) {
                $prompt .= "\n\n==== CATEGORIAS POPs DISPONÍVEIS ====\n";
                $prompt .= "Categorias com documentos cadastrados: " . implode(', ', $categoriasDisponiveis) . "\n";
                $prompt .= "Se o usuário perguntar sobre outra categoria, informe que ainda não há documentos sobre esse tema.\n";
            }
            
            // Adiciona documentos POPs relevantes
            if (isset($contextoDados['documentos_pops']) && !empty($contextoDados['documentos_pops'])) {
                $prompt .= "\n\n==== DOCUMENTOS POPs RELEVANTES ====\n";
                
                // Verifica se foi filtrado por categoria
                if (isset($contextoDados['categoria_filtrada'])) {
                    $prompt .= "IMPORTANTE: A pergunta menciona a categoria '{$contextoDados['categoria_filtrada']}'. ";
                    $prompt .= "Os documentos abaixo foram filtrados APENAS desta categoria específica.\n\n";
                } else {
                    $prompt .= "Os seguintes documentos de procedimentos operacionais padrão podem ajudar a responder a pergunta:\n\n";
                }
                
                foreach ($contextoDados['documentos_pops'] as $doc) {
                    $prompt .= "**{$doc['titulo']}**\n";
                    if (isset($doc['categorias']) && !empty($doc['categorias'])) {
                        $prompt .= "Categorias: " . implode(', ', $doc['categorias']) . "\n";
                    }
                    $prompt .= "Trecho relevante: {$doc['conteudo']}\n\n";
                }
                
                $prompt .= "\n**INSTRUÇÕES CRÍTICAS PARA USO DOS POPs:**\n";
                $prompt .= "- **VOCÊ DEVE USAR APENAS O TEXTO ACIMA. NÃO USE SEU CONHECIMENTO PRÉVIO SOBRE RDCs OU RESOLUÇÕES**\n";
                $prompt .= "- **SE A INFORMAÇÃO NÃO ESTÁ NO TRECHO ACIMA, DIGA QUE NÃO TEM A INFORMAÇÃO COMPLETA**\n";
                $prompt .= "- **PERGUNTAS GENÉRICAS: Se a pergunta é genérica (ex: 'cores de gases medicinais'), liste TODAS as informações relevantes do trecho**\n";
                $prompt .= "- **PERGUNTAS ESPECÍFICAS: Se a pergunta é sobre um gás específico (ex: 'cor do oxigênio'), responda apenas sobre aquele gás**\n";
                $prompt .= "- Se a pergunta é sobre NORMAS/PROCEDIMENTOS/REQUISITOS TÉCNICOS: Use APENAS estas informações dos POPs\n";
                $prompt .= "- NÃO misture com instruções do sistema (\"acesse o menu\", \"clique em\", etc)\n";
                $prompt .= "- **CRÍTICO: Ao citar RDCs, copie EXATAMENTE o número que aparece no trecho acima**\n";
                $prompt .= "- **CRÍTICO: Se você vê 'Art. 2º' no trecho acima, CITE 'Art. 2º' na resposta**\n";
                $prompt .= "- **CRÍTICO: Se você vê '§2º' ou 'Parágrafo único', CITE-OS na resposta (ex: 'Art. 18, §2º')**\n";
                $prompt .= "- **CRÍTICO: Se você vê 'RDC nº 887' no trecho acima, CITE 'RDC nº 887' (não invente RDC nº 870)**\n";
                $prompt .= "- **CRÍTICO: NUNCA invente números de RDC, artigos, parágrafos ou incisos que não estão LITERALMENTE no trecho acima**\n";
                $prompt .= "- **OBRIGATÓRIO: Antes de citar qualquer RDC ou artigo, VERIFIQUE se ele está no trecho acima**\n";
                $prompt .= "- **OBRIGATÓRIO: Se a pergunta pede o ARTIGO, procure por 'Art.' ou '§' no trecho e cite-o COMPLETO**\n";
                $prompt .= "- **OBRIGATÓRIO: Se a informação está em um PARÁGRAFO (§), cite 'Art. X, §Y' e não apenas 'Art. X'**\n";
                $prompt .= "- **FORMATO DE RESPOSTA: 'De acordo com a [RDC/NBR completa], [conteúdo]'**\n";
                $prompt .= "- Cite o nome do documento usado: \"De acordo com a NBR [número]...\" ou \"De acordo com a RDC nº [número]...\"\n";
                $prompt .= "- Seja técnico e objetivo, focando APENAS no conteúdo dos trechos fornecidos\n";
                $prompt .= "- CRÍTICO: Se o POP menciona uma obrigação (ex: 'deve notificar'), responda APENAS o que o POP diz\n";
                $prompt .= "- NÃO invente como fazer essa obrigação no sistema\n";
                $prompt .= "- NÃO crie tipos de processo específicos para normas\n";
                $prompt .= "- Se o usuário perguntar COMO fazer algo relacionado a norma, diga que o sistema tem processos genéricos\n";
                
                // Se tem categoria filtrada, instrui a IA a mencionar
                if (isset($contextoDados['categoria_filtrada'])) {
                    $prompt .= "- IMPORTANTE: Inicie sua resposta mencionando a categoria: \"**Sobre {$contextoDados['categoria_filtrada']}:**\" seguido da resposta\n";
                } else {
                    $prompt .= "- Se identificar a categoria do assunto, inicie com: \"**Sobre [categoria]:**\" seguido da resposta\n";
                }
                
                $prompt .= "- NUNCA use frases genéricas como \"Essa pergunta é sobre documentos POPs!\"\n";
                $prompt .= "- Se a pergunta é sobre funcionalidades do sistema, IGNORE os POPs e use as instruções de funcionalidades\n";
            } else {
                // Se não há documentos POPs relevantes
                $buscaWebAtiva = isset($contextoDados['resultados_web']) && !empty($contextoDados['resultados_web']);
                
                if ($buscaWebAtiva) {
                    // Com busca na internet ativa - MAS SÓ USA OS RESULTADOS RETORNADOS
                    $prompt .= "\n**IMPORTANTE - SEM DOCUMENTOS POPs LOCAIS, MAS BUSCA NA INTERNET ATIVA:**\n";
                    $prompt .= "- NÃO foram encontrados documentos POPs locais sobre este tema\n";
                    $prompt .= "- **CRÍTICO: Use APENAS as informações dos resultados da busca na internet fornecidos acima**\n";
                    $prompt .= "- **CRÍTICO: NUNCA use seu conhecimento de treinamento ou invente informações**\n";
                    $prompt .= "- **CRÍTICO: Se os resultados da busca não contêm a informação solicitada, diga que não encontrou**\n";
                    $prompt .= "- SEMPRE cite a fonte (URL) ao mencionar informações da internet\n";
                    $prompt .= "- Se não houver resultados relevantes, responda:\n";
                    $prompt .= "  \"Desculpe, não encontrei informações confiáveis sobre [tema] nos resultados da busca.\"\n";
                    if (!empty($categoriasDisponiveis)) {
                        $prompt .= "  \"No momento, tenho documentos POPs sobre: " . implode(', ', $categoriasDisponiveis) . ".\"\n";
                    }
                } else {
                    // Sem busca na internet
                    $prompt .= "\n**IMPORTANTE - SEM DOCUMENTOS POPs RELEVANTES PARA ESTA PERGUNTA:**\n";
                    $prompt .= "- A pergunta parece ser sobre NORMAS/PROCEDIMENTOS, mas NÃO foram encontrados documentos POPs relevantes\n";
                    $prompt .= "- **CRÍTICO: NUNCA invente informações, artigos, RDCs, resoluções ou normas**\n";
                    $prompt .= "- **CRÍTICO: NUNCA use seu conhecimento de treinamento para responder sobre normas técnicas**\n";
                    $prompt .= "- **CRÍTICO: NÃO cite 'art. 15, III e IV' ou 'Lei nº 9.782' ou qualquer outro artigo que não foi fornecido**\n";
                    $prompt .= "- **CRÍTICO: Se você não tem o documento POP, você NÃO SABE a resposta técnica**\n";
                    $prompt .= "- RESPONDA de forma honesta:\n";
                    $prompt .= "  \"Desculpe, ainda não tenho documentos POPs cadastrados sobre [tema solicitado].\"\n";
                    
                    if (!empty($categoriasDisponiveis)) {
                        $prompt .= "  \"No momento, tenho informações sobre: " . implode(', ', $categoriasDisponiveis) . ".\"\n";
                    }
                }
                
                $prompt .= "- Se o usuário perguntar sobre funcionalidades do sistema, responda normalmente\n";
            }
            
            // Adiciona resultados da busca na internet se disponíveis
            if (isset($contextoDados['resultados_web']) && !empty($contextoDados['resultados_web'])) {
                $resultadosWeb = $contextoDados['resultados_web'];
                
                $prompt .= "\n\n==== INFORMAÇÕES COMPLEMENTARES DA INTERNET ====\n";
                $prompt .= "Busca realizada no Google: {$resultadosWeb['query']}\n";
                $prompt .= "Total de resultados encontrados: {$resultadosWeb['total']}\n\n";
                
                if (!empty($resultadosWeb['resultados'])) {
                    $prompt .= "**RESULTADOS ENCONTRADOS:**\n\n";
                    
                    foreach ($resultadosWeb['resultados'] as $index => $resultado) {
                        $num = $index + 1;
                        $prompt .= "**Resultado {$num}:**\n";
                        $prompt .= "- Título: {$resultado['titulo']}\n";
                        $prompt .= "- URL: {$resultado['url']}\n";
                        $prompt .= "- Fonte: {$resultado['fonte']}\n";
                        
                        if (isset($resultado['descricao']) && !empty($resultado['descricao'])) {
                            $prompt .= "- Descrição: {$resultado['descricao']}\n";
                        }
                        
                        if (isset($resultado['texto']) && !empty($resultado['texto'])) {
                            $prompt .= "- Conteúdo: {$resultado['texto']}\n";
                        }
                        
                        $prompt .= "\n";
                    }
                }
                
                $prompt .= "\n**INSTRUÇÕES PARA USO DOS RESULTADOS:**\n";
                $prompt .= "- Use APENAS informações dos resultados acima\n";
                $prompt .= "- SEMPRE cite a fonte (URL) ao mencionar informações da internet\n";
                $prompt .= "- Se os resultados não contêm a informação solicitada, diga: 'Não encontrei informações sobre [assunto] nos resultados da busca'\n";
                $prompt .= "- NUNCA invente informações que não estão nos resultados acima\n\n";
            }
        }

        return $prompt;
    }

    /**
     * Limpa caracteres UTF-8 malformados das mensagens
     */
    private function limparMensagensUTF8($messages)
    {
        foreach ($messages as &$message) {
            if (isset($message['content'])) {
                // Remove caracteres UTF-8 inválidos
                $message['content'] = mb_convert_encoding($message['content'], 'UTF-8', 'UTF-8');
                // Remove caracteres de controle problemáticos, mantendo quebras de linha
                $message['content'] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $message['content']);
            }
        }
        return $messages;
    }
    
    /**
     * Verifica se deve buscar na internet
     */
    private function deveBuscarNaInternet($message, $contextoDados)
    {
        // Se houver múltiplos documentos PDF carregados
        if (isset($contextoDados['documentos_pdf']) && !empty($contextoDados['documentos_pdf'])) {
            foreach ($contextoDados['documentos_pdf'] as $doc) {
                if (isset($doc['buscar_internet']) && $doc['buscar_internet'] === true) {
                    \Log::info('Busca na internet ativada por documento (múltiplos)');
                    return true;
                }
            }
            return false;
        }

        // Se houver documento PDF único carregado
        if (isset($contextoDados['documento_pdf']) && !empty($contextoDados['documento_pdf'])) {
            // Se buscar_internet estiver definido, retorna esse valor
            if (isset($contextoDados['documento_pdf']['buscar_internet'])) {
                $deveBuscar = $contextoDados['documento_pdf']['buscar_internet'] === true;
                
                \Log::info('Verificação de busca (documento único)', [
                    'deve_buscar' => $deveBuscar,
                    'buscar_internet_config' => $contextoDados['documento_pdf']['buscar_internet']
                ]);
                
                return $deveBuscar;
            }
            // Por padrão, não busca na internet para documentos
            return false;
        }

        // Se não encontrou documentos POPs relevantes, busca na internet
        if (!isset($contextoDados['documentos_pops']) || empty($contextoDados['documentos_pops'])) {
            // Verifica se é uma pergunta sobre normas/regulamentações
            $palavrasChaveNormas = ['rdc', 'resolução', 'portaria', 'lei', 'norma', 'anvisa', 'regulamento', 'artigo'];
            $messageLower = strtolower($message);
            
            foreach ($palavrasChaveNormas as $palavra) {
                if (strpos($messageLower, $palavra) !== false) {
                    \Log::info('Palavra-chave de norma encontrada - deve buscar', [
                        'palavra' => $palavra,
                        'message' => $message
                    ]);
                    return true;
                }
            }
        }
        
        \Log::info('Não deve buscar na internet', [
            'tem_pops' => isset($contextoDados['documentos_pops']),
            'message' => $message
        ]);
        
        return false;
    }
    
    /**
     * Busca informações na internet
     */
    private function buscarNaInternet($message)
    {
        try {
            // Monta query de busca
            // Se menciona RDC, busca mais ampla; senão, foca em sites oficiais
            $messageLower = strtolower($message);
            if (strpos($messageLower, 'rdc') !== false || strpos($messageLower, 'resolução') !== false) {
                // Busca ampla para RDCs (inclui sites não oficiais que podem ter a informação)
                $query = $message . ' anvisa';
            } else {
                // Busca focada em sites oficiais
                $query = $message . ' site:anvisa.gov.br OR site:in.gov.br';
            }
            
            \Log::info('Iniciando busca na internet', [
                'query' => $query,
                'message' => $message,
                'busca_ampla' => strpos($messageLower, 'rdc') !== false
            ]);
            
            // PRIORIDADE 1: Tenta DuckDuckGo Instant Answer API (gratuita, sem bloqueios)
            $resultados = $this->buscarDuckDuckGoAPI($query);
            
            // PRIORIDADE 2: Se API não retornar, tenta scraping do DuckDuckGo HTML
            if (empty($resultados)) {
                \Log::info('DuckDuckGo API não retornou, tentando scraping HTML...');
                $resultados = $this->buscarNoDuckDuckGo($query);
            }
            
            // PRIORIDADE 3: Se DuckDuckGo não retornar, tenta Bing
            if (empty($resultados)) {
                \Log::info('DuckDuckGo não retornou resultados, tentando Bing...');
                $resultados = $this->buscarNoBing($query);
            }
            
            // PRIORIDADE 4: Se Bing não retornar, tenta Google
            if (empty($resultados)) {
                \Log::info('Bing não retornou resultados, tentando Google...');
                $resultados = $this->buscarNoGoogle($query);
            }
            
            if (empty($resultados)) {
                \Log::warning('❌ Nenhum resultado encontrado em nenhum buscador', [
                    'query' => $query
                ]);
                return [];
            }
            
            \Log::info('✅ Resultados encontrados!', [
                'total' => count($resultados),
                'fonte' => 'Internet Real'
            ]);
            
            return [
                'fonte' => 'Busca na Internet',
                'query' => $query,
                'resultados' => $resultados,
                'total' => count($resultados)
            ];
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar na internet', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
    
    /**
     * Busca usando DuckDuckGo Instant Answer API + HTML Lite (híbrido)
     */
    private function buscarDuckDuckGoAPI($query)
    {
        try {
            $resultados = [];
            
            // MÉTODO 1: API Instant Answer (para definições e info geral)
            $apiUrl = 'https://api.duckduckgo.com/?q=' . urlencode($query) . '&format=json&no_html=1&skip_disambig=1';
            \Log::info('🦆 Buscando via DuckDuckGo API', ['url' => $apiUrl]);
            
            $response = Http::timeout(10)->get($apiUrl);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Extrai Abstract (resposta direta)
                if (!empty($data['Abstract'])) {
                    $resultados[] = [
                        'titulo' => $data['Heading'] ?? 'Resposta Direta',
                        'snippet' => $data['Abstract'],
                        'url' => $data['AbstractURL'] ?? '',
                        'fonte' => $data['AbstractSource'] ?? 'DuckDuckGo'
                    ];
                }
                
                // Extrai Related Topics (tópicos relacionados com links)
                if (!empty($data['RelatedTopics'])) {
                    foreach (array_slice($data['RelatedTopics'], 0, 5) as $topic) {
                        if (isset($topic['Text']) && isset($topic['FirstURL'])) {
                            $resultados[] = [
                                'titulo' => strip_tags($topic['Text']),
                                'snippet' => strip_tags($topic['Text']),
                                'url' => $topic['FirstURL'],
                                'fonte' => 'DuckDuckGo'
                            ];
                        }
                    }
                }
                
                // Extrai Results (resultados de busca)
                if (!empty($data['Results'])) {
                    foreach (array_slice($data['Results'], 0, 5) as $result) {
                        if (isset($result['Text']) && isset($result['FirstURL'])) {
                            $resultados[] = [
                                'titulo' => strip_tags($result['Text']),
                                'snippet' => strip_tags($result['Text']),
                                'url' => $result['FirstURL'],
                                'fonte' => 'DuckDuckGo'
                            ];
                        }
                    }
                }
            }
            
            // MÉTODO 2: Se API não retornou links úteis, usa busca HTML lite
            if (empty($resultados) || count($resultados) < 2) {
                \Log::info('🔍 Tentando DuckDuckGo Lite para obter mais links...');
                $liteResults = $this->buscarDuckDuckGoLite($query);
                $resultados = array_merge($resultados, $liteResults);
            }
            
            if (!empty($resultados)) {
                \Log::info('✅ DuckDuckGo retornou resultados', ['total' => count($resultados)]);
            } else {
                \Log::info('⚠️ DuckDuckGo não retornou resultados úteis');
            }
            
            return $resultados;
            
        } catch (\Exception $e) {
            \Log::error('Erro na DuckDuckGo API', ['erro' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Busca usando DuckDuckGo Lite (versão simplificada que retorna links reais)
     */
    private function buscarDuckDuckGoLite($query)
    {
        try {
            // DuckDuckGo Lite é mais fácil de parsear e retorna links reais
            $searchUrl = 'https://lite.duckduckgo.com/lite/?q=' . urlencode($query);
            
            \Log::info('🔍 Buscando no DuckDuckGo Lite', ['url' => $searchUrl]);
            
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])->timeout(10)->get($searchUrl);
            
            if (!$response->successful()) {
                return [];
            }
            
            $html = $response->body();
            $resultados = [];
            
            // Parseia HTML do DuckDuckGo Lite (estrutura simples)
            // Procura por links de resultados: <a rel="nofollow" href="URL">
            preg_match_all('/<a\s+rel="nofollow"\s+href="([^"]+)"[^>]*>([^<]+)<\/a>/i', $html, $matches, PREG_SET_ORDER);
            
            foreach (array_slice($matches, 0, 5) as $match) {
                $url = html_entity_decode($match[1]);
                $titulo = html_entity_decode(strip_tags($match[2]));
                
                // Filtra URLs válidas (não links internos do DuckDuckGo)
                if (strpos($url, 'http') === 0 && strpos($url, 'duckduckgo.com') === false) {
                    $resultados[] = [
                        'titulo' => $titulo,
                        'snippet' => $titulo,
                        'url' => $url,
                        'fonte' => 'DuckDuckGo Lite'
                    ];
                }
            }
            
            // Também procura por snippets (descrições)
            preg_match_all('/<td\s+class="result-snippet"[^>]*>([^<]+)<\/td>/i', $html, $snippets);
            if (!empty($snippets[1])) {
                foreach ($resultados as $idx => &$resultado) {
                    if (isset($snippets[1][$idx])) {
                        $resultado['snippet'] = trim(strip_tags($snippets[1][$idx]));
                    }
                }
            }
            
            \Log::info('DuckDuckGo Lite encontrou', ['total' => count($resultados)]);
            
            return $resultados;
            
        } catch (\Exception $e) {
            \Log::error('Erro no DuckDuckGo Lite', ['erro' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Busca no DuckDuckGo (HTML mais simples)
     */
    private function buscarNoDuckDuckGo($query)
    {
        try {
            $searchUrl = 'https://html.duckduckgo.com/html/?q=' . urlencode($query);
            
            \Log::info('Buscando no DuckDuckGo', ['url' => $searchUrl]);
            
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(10)->get($searchUrl);
            
            if (!$response->successful()) {
                \Log::warning('Falha na busca do DuckDuckGo', ['status' => $response->status()]);
                return [];
            }
            
            $html = $response->body();
            return $this->extrairResultadosDuckDuckGo($html);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar no DuckDuckGo', ['erro' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Busca no Bing
     */
    private function buscarNoBing($query)
    {
        try {
            $searchUrl = 'https://www.bing.com/search?q=' . urlencode($query) . '&setlang=pt-BR';
            
            \Log::info('Buscando no Bing', ['url' => $searchUrl]);
            
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            ])->timeout(10)->get($searchUrl);
            
            if (!$response->successful()) {
                \Log::warning('Falha na busca do Bing', ['status' => $response->status()]);
                return [];
            }
            
            $html = $response->body();
            return $this->extrairResultadosBing($html);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar no Bing', ['erro' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Busca no Google
     */
    private function buscarNoGoogle($query)
    {
        try {
            $searchUrl = 'https://www.google.com/search?q=' . urlencode($query) . '&hl=pt-BR';
            
            \Log::info('Buscando no Google', ['url' => $searchUrl]);
            
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1'
            ])->timeout(10)->get($searchUrl);
            
            if (!$response->successful()) {
                \Log::warning('Falha na busca do Google', ['status' => $response->status()]);
                return [];
            }
            
            $html = $response->body();
            return $this->extrairResultadosGoogle($html);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar no Google', ['erro' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Extrai resultados da página de busca do Google
     */
    private function extrairResultadosGoogle($html)
    {
        $resultados = [];
        
        try {
            // Log amostra do HTML
            \Log::info('HTML Google (amostra)', [
                'html_inicio' => mb_substr($html, 0, 1000)
            ]);
            
            // Remove quebras de linha para facilitar regex
            $html = str_replace(["\r", "\n"], '', $html);
            
            // Padrão para extrair resultados orgânicos do Google
            // Busca por divs com classe que contém resultados
            preg_match_all('/<div class="[^"]*g[^"]*"[^>]*>.*?<a href="\/url\?q=([^"&]+)"[^>]*>.*?<h3[^>]*>(.*?)<\/h3>.*?<\/div>/is', $html, $matches, PREG_SET_ORDER);
            
            if (empty($matches)) {
                // Tenta padrão alternativo (Google muda frequentemente)
                preg_match_all('/<a href="([^"]+)"[^>]*><h3[^>]*>(.*?)<\/h3>/is', $html, $matches2, PREG_SET_ORDER);
                
                foreach ($matches2 as $match) {
                    $url = $match[1];
                    $titulo = strip_tags($match[2]);
                    
                    // Filtra apenas URLs válidas (não links internos do Google)
                    // Aceita qualquer site, mas exclui Google e sites irrelevantes
                    if (strpos($url, 'http') === 0 && 
                        strpos($url, 'google.com') === false &&
                        strpos($url, 'youtube.com') === false &&
                        strpos($url, 'facebook.com') === false) {
                        
                        $resultados[] = [
                            'titulo' => html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                            'url' => $url,
                            'fonte' => $this->extrairDominio($url)
                        ];
                        
                        if (count($resultados) >= 5) break; // Limita a 5 resultados
                    }
                }
            } else {
                foreach ($matches as $match) {
                    $url = urldecode($match[1]);
                    $titulo = strip_tags($match[2]);
                    
                    $resultados[] = [
                        'titulo' => html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'url' => $url,
                        'fonte' => $this->extrairDominio($url)
                    ];
                    
                    if (count($resultados) >= 5) break; // Limita a 5 resultados
                }
            }
            
            // Fallback 1: Buscar snippets de featured snippets
            if (empty($resultados)) {
                preg_match_all('/<div[^>]*class="[^"]*BNeawe[^"]*"[^>]*>(.*?)<\/div>/is', $html, $snippets);
                
                if (!empty($snippets[1])) {
                    $texto = '';
                    foreach (array_slice($snippets[1], 0, 3) as $snippet) {
                        $texto .= strip_tags($snippet) . ' ';
                    }
                    
                    if (!empty(trim($texto))) {
                        $resultados[] = [
                            'titulo' => 'Informação encontrada no Google',
                            'descricao' => trim($texto),
                            'url' => 'https://www.google.com',
                            'fonte' => 'Google Search'
                        ];
                    }
                }
            }
            
            // Fallback 2: Extrai QUALQUER link que contenha "anvisa" ou "rdc"
            if (empty($resultados)) {
                preg_match_all('/<a[^>]+href="([^"]+)"[^>]*>([^<]+)<\/a>/is', $html, $allLinks, PREG_SET_ORDER);
                
                foreach ($allLinks as $link) {
                    $url = $link[1];
                    $titulo = strip_tags($link[2]);
                    
                    // Limpa URL do Google
                    if (strpos($url, '/url?q=') !== false) {
                        parse_str(parse_url($url, PHP_URL_QUERY), $params);
                        $url = $params['q'] ?? $url;
                    }
                    
                    $urlLower = strtolower($url);
                    $tituloLower = strtolower($titulo);
                    
                    if (strpos($url, 'http') === 0 &&
                        strpos($url, 'google.com') === false &&
                        (strpos($urlLower, 'anvisa') !== false || 
                         strpos($urlLower, 'rdc') !== false ||
                         strpos($tituloLower, 'rdc') !== false) &&
                        !empty(trim($titulo))) {
                        
                        $resultados[] = [
                            'titulo' => html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                            'url' => urldecode($url),
                            'fonte' => $this->extrairDominio($url),
                            'descricao' => ''
                        ];
                        
                        if (count($resultados) >= 3) break;
                    }
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Erro ao extrair resultados do Google', [
                'erro' => $e->getMessage()
            ]);
        }
        
        return $resultados;
    }
    
    /**
     * Extrai resultados do DuckDuckGo
     */
    private function extrairResultadosDuckDuckGo($html)
    {
        $resultados = [];
        
        try {
            // Salva HTML para debug (apenas primeiros 5000 caracteres)
            \Log::info('HTML DuckDuckGo (amostra)', [
                'html_inicio' => mb_substr($html, 0, 1000)
            ]);
            
            // Tenta múltiplos padrões para DuckDuckGo
            
            // Padrão 1: Links diretos
            preg_match_all('/<a[^>]+class="[^"]*result[^"]*"[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);
            
            if (!empty($matches)) {
                foreach ($matches as $match) {
                    $url = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $titulo = strip_tags($match[2]);
                    
                    // Limpa URL do DuckDuckGo (remove redirect)
                    if (strpos($url, '//duckduckgo.com/l/?') !== false) {
                        parse_str(parse_url($url, PHP_URL_QUERY), $params);
                        $url = $params['uddg'] ?? $url;
                    }
                    
                    // Filtra URLs válidas
                    if (strpos($url, 'http') === 0 && 
                        strpos($url, 'duckduckgo.com') === false &&
                        !empty(trim($titulo))) {
                        
                        $resultados[] = [
                            'titulo' => html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                            'url' => $url,
                            'fonte' => $this->extrairDominio($url),
                            'descricao' => ''
                        ];
                        
                        if (count($resultados) >= 5) break;
                    }
                }
            }
            
            // Padrão 2: Fallback - qualquer link HTTP
            if (empty($resultados)) {
                preg_match_all('/<a[^>]+href="(https?:\/\/[^"]+)"[^>]*>([^<]+)<\/a>/is', $html, $matches2, PREG_SET_ORDER);
                
                foreach ($matches2 as $match) {
                    $url = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $titulo = strip_tags($match[2]);
                    
                    if (strpos($url, 'duckduckgo.com') === false &&
                        strpos($url, 'anvisa') !== false &&
                        !empty(trim($titulo))) {
                        
                        $resultados[] = [
                            'titulo' => html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                            'url' => $url,
                            'fonte' => $this->extrairDominio($url),
                            'descricao' => ''
                        ];
                        
                        if (count($resultados) >= 3) break;
                    }
                }
            }
            
            \Log::info('Resultados extraídos do DuckDuckGo', ['total' => count($resultados)]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao extrair resultados do DuckDuckGo', ['erro' => $e->getMessage()]);
        }
        
        return $resultados;
    }
    
    /**
     * Extrai resultados da página de busca do Bing
     */
    private function extrairResultadosBing($html)
    {
        $resultados = [];
        
        try {
            // Remove quebras de linha
            $html = str_replace(["\r", "\n"], '', $html);
            
            // Padrão do Bing: <li class="b_algo">
            preg_match_all('/<li class="b_algo[^"]*">(.*?)<\/li>/is', $html, $matches);
            
            if (!empty($matches[1])) {
                foreach ($matches[1] as $item) {
                    // Extrai URL e título
                    if (preg_match('/<a href="([^"]+)"[^>]*>(.*?)<\/a>/is', $item, $link)) {
                        $url = html_entity_decode($link[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $titulo = strip_tags($link[2]);
                        
                        // Filtra URLs válidas
                        if (strpos($url, 'http') === 0 && 
                            strpos($url, 'bing.com') === false &&
                            strpos($url, 'microsoft.com') === false) {
                            
                            // Extrai descrição se disponível
                            $descricao = '';
                            if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $item, $desc)) {
                                $descricao = strip_tags($desc[1]);
                                $descricao = html_entity_decode($descricao, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                $descricao = mb_substr($descricao, 0, 300); // Limita a 300 caracteres
                            }
                            
                            $resultados[] = [
                                'titulo' => html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                                'url' => $url,
                                'fonte' => $this->extrairDominio($url),
                                'descricao' => $descricao
                            ];
                            
                            if (count($resultados) >= 5) break;
                        }
                    }
                }
            }
            
            \Log::info('Resultados extraídos do Bing', ['total' => count($resultados)]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao extrair resultados do Bing', ['erro' => $e->getMessage()]);
        }
        
        return $resultados;
    }
    
    /**
     * Extrai domínio de uma URL
     */
    private function extrairDominio($url)
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? 'Desconhecido';
    }

    /**
     * Extrai texto de um PDF para uso pela IA
     */
    public function extrairPdf(Request $request)
    {
        $request->validate([
            'documento_id' => 'required|integer',
            'estabelecimento_id' => 'required|integer',
            'processo_id' => 'required|integer',
        ]);

        try {
            $documentoId = $request->input('documento_id');
            $processoId = $request->input('processo_id');

            // Tenta buscar como documento digital primeiro
            $docDigital = DocumentoDigital::where('processo_id', $processoId)
                ->where('id', $documentoId)
                ->first();

            $caminhoArquivo = null;
            $nomeDocumento = null;

            if ($docDigital && $docDigital->arquivo_pdf) {
                // É um documento digital
                $caminhoArquivo = storage_path('app/public') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $docDigital->arquivo_pdf);
                $nomeDocumento = $docDigital->nome_documento ?? 'Documento Digital';
            } else {
                // Busca como arquivo externo
                $documento = \App\Models\ProcessoDocumento::where('processo_id', $processoId)
                    ->findOrFail($documentoId);

                if ($documento->tipo_documento === 'documento_digital') {
                    $caminhoArquivo = storage_path('app/public') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
                } else if ($documento->tipo_usuario === 'externo') {
                    // Arquivos de usuários externos são salvos em storage/app/public/
                    $caminhoArquivo = storage_path('app/public') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
                } else {
                    $caminhoArquivo = storage_path('app') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
                }
                $nomeDocumento = $documento->nome_original ?? 'Documento';
            }

            if (!file_exists($caminhoArquivo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo PDF não encontrado'
                ], 404);
            }

            // Extrai texto do PDF usando Smalot\PdfParser
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($caminhoArquivo);
            
            // Extrai texto de TODAS as páginas
            $pages = $pdf->getPages();
            $textoCompleto = '';
            $totalPaginas = count($pages);
            
            foreach ($pages as $pageNum => $page) {
                $textoPagina = $page->getText();
                if (!empty($textoPagina)) {
                    $textoCompleto .= "=== PÁGINA " . ($pageNum + 1) . " de {$totalPaginas} ===\n";
                    $textoCompleto .= $textoPagina . "\n\n";
                }
            }

            // Se não conseguiu extrair por páginas, tenta método geral
            if (empty($textoCompleto)) {
                $textoCompleto = $pdf->getText();
            }

            // Limpa o texto
            $texto = trim($textoCompleto);
            $texto = preg_replace('/\s+/', ' ', $texto); // Remove espaços múltiplos
            
            // Limita a aproximadamente 20.000 caracteres (~5.000 tokens)
            // Isso deixa espaço para o prompt do sistema + histórico + resposta
            $texto = mb_substr($texto, 0, 20000);

            if (empty($texto)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível extrair texto do PDF. O documento pode estar protegido ou ser uma imagem.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'conteudo' => $texto,
                'nome_documento' => $nomeDocumento,
                'total_caracteres' => mb_strlen($texto)
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao extrair PDF', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erro ao processar mensagem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Chat especializado para auxiliar na edição/criação de documentos
     */
    public function chatEdicaoDocumento(Request $request)
    {
        $request->validate([
            'mensagem' => 'required|string|max:2000',
            'historico' => 'nullable|array',
            'texto_atual' => 'nullable|string|max:50000',
            'conhecimento_geral' => 'nullable|boolean',
            'documentos_contexto' => 'nullable|array',
        ]);

        // Verifica se o Assistente de Redação está ativo (independente do Assistente de IA geral)
        $assistenteRedacaoAtivo = ConfiguracaoSistema::where('chave', 'assistente_redacao_ativo')->value('valor');
        if ($assistenteRedacaoAtivo !== 'true') {
            return response()->json([
                'error' => 'Assistente de Redação está desativado'
            ], 403);
        }

        $mensagem = $request->input('mensagem');
        $historico = $request->input('historico', []);
        $textoAtual = $request->input('texto_atual', '');
        $conhecimentoGeral = $request->input('conhecimento_geral', false);
        $dadosEstabelecimento = $request->input('dados_estabelecimento', []);
        $documentosContexto = $request->input('documentos_contexto', []);

        try {
            // Se conhecimento geral está ativo, busca na internet primeiro
            $resultadosBusca = '';
            if ($conhecimentoGeral) {
                \Log::info('🌐 ASSISTENTE REDAÇÃO: Busca na internet ATIVADA', [
                    'mensagem' => $mensagem,
                    'timestamp' => now()->toDateTimeString()
                ]);
                
                // Usa a mesma lógica de busca do assistente principal (centralizada no método buscarNaInternet)
                // Não construímos a query aqui para evitar duplicação de filtros
                
                // Tenta busca na internet
                $resultadosBusca = $this->buscarNaInternet($mensagem);
                
                if (!empty($resultadosBusca)) {
                    $totalResultados = is_array($resultadosBusca) ? ($resultadosBusca['total'] ?? count($resultadosBusca)) : 0;
                    \Log::info('✅ ASSISTENTE REDAÇÃO: Resultados da busca encontrados!', [
                        'total_resultados' => $totalResultados,
                        'tem_array_resultados' => isset($resultadosBusca['resultados']),
                        'query_usada' => $resultadosBusca['query'] ?? 'N/A'
                    ]);
                } else {
                    \Log::warning('⚠️ ASSISTENTE REDAÇÃO: Nenhum resultado encontrado na busca', [
                        'mensagem' => $mensagem
                    ]);
                    
                    // Se não encontrou resultados, informa isso explicitamente no prompt
                    $resultadosBusca = [
                        'fonte' => 'Busca na Internet',
                        'query' => $mensagem,
                        'resultados' => [],
                        'total' => 0,
                        'aviso' => 'A busca foi executada mas não retornou resultados. Possível bloqueio de scraping ou query muito específica.'
                    ];
                }
            }
            
            // Monta o prompt do sistema
            $systemPrompt = $this->construirPromptEdicaoDocumento($textoAtual, $conhecimentoGeral, $resultadosBusca, $dadosEstabelecimento, $documentosContexto);

            // Monta histórico de mensagens
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];

            // Adiciona histórico (últimas 10 mensagens)
            foreach (array_slice($historico, -10) as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $messages[] = [
                        'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                        'content' => strip_tags($msg['content'])
                    ];
                }
            }

            // Adiciona mensagem atual
            $messages[] = [
                'role' => 'user',
                'content' => $mensagem
            ];

            // Busca configurações da IA do banco de dados
            $apiKey = ConfiguracaoSistema::where('chave', 'ia_api_key')->value('valor');
            $apiUrl = ConfiguracaoSistema::where('chave', 'ia_api_url')->value('valor');
            $model = ConfiguracaoSistema::where('chave', 'ia_model')->value('valor');

            if (empty($apiKey)) {
                throw new \Exception('Chave da API não configurada no sistema');
            }

            if (empty($apiUrl)) {
                throw new \Exception('URL da API não configurada no sistema');
            }

            if (empty($model)) {
                throw new \Exception('Modelo de IA não configurado no sistema');
            }

            // Chama API do Together AI
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($apiUrl, [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $errorBody['message'] ?? 'Erro desconhecido da IA';
                
                \Log::error('Erro na API Together AI', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception("Erro na IA: $errorMessage");
            }

            $data = $response->json();
            $resposta = $data['choices'][0]['message']['content'] ?? 'Desculpe, não consegui processar sua solicitação.';

            return response()->json([
                'resposta' => $resposta
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro no chat de edição de documento', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Constrói o prompt do sistema para edição de documentos
     */
    private function construirPromptEdicaoDocumento($textoAtual, $conhecimentoGeral, $resultadosBusca = '', $dadosEstabelecimento = [], $documentosContexto = [])
    {
        $prompt = "Você é um assistente especializado em redação e correção de textos para documentos oficiais.\n\n";
        
        // Adiciona dados do estabelecimento se disponíveis
        if (!empty($dadosEstabelecimento) && (isset($dadosEstabelecimento['nome']) || isset($dadosEstabelecimento['cnpj']))) {
            $prompt .= "**DADOS DO ESTABELECIMENTO/PROCESSO:**\n";
            if (!empty($dadosEstabelecimento['nome'])) {
                $prompt .= "- Nome: " . $dadosEstabelecimento['nome'] . "\n";
            }
            if (!empty($dadosEstabelecimento['cnpj'])) {
                $prompt .= "- CNPJ: " . $dadosEstabelecimento['cnpj'] . "\n";
            }
            if (!empty($dadosEstabelecimento['telefone'])) {
                $prompt .= "- Telefone: " . $dadosEstabelecimento['telefone'] . "\n";
            }
            if (!empty($dadosEstabelecimento['endereco'])) {
                $prompt .= "- Endereço: " . $dadosEstabelecimento['endereco'] . "\n";
            }
            if (!empty($dadosEstabelecimento['processo_numero'])) {
                $prompt .= "- Processo nº: " . $dadosEstabelecimento['processo_numero'] . "\n";
            }
            $prompt .= "\n⚠️ IMPORTANTE: Use estes dados automaticamente quando o usuário pedir para criar ofícios, despachos, notificações ou outros documentos.\n\n";
        }

        // Adiciona documentos de contexto
        if (!empty($documentosContexto)) {
            $prompt .= "**DOCUMENTOS DE REFERÊNCIA CARREGADOS:**\n";
            $prompt .= "Use as informações contidas nestes documentos para embasar suas respostas e redações.\n";
            $prompt .= "Se o usuário pedir para 'resumir', 'analisar' ou 'extrair informações' destes documentos, use o conteúdo abaixo.\n\n";
            
            // Calcula orçamento de caracteres para não estourar tokens
            // Limite total seguro: 20.000 caracteres (~5.000 tokens)
            // Isso deixa espaço para histórico (2000 tokens) e resposta (1000 tokens)
            $totalDocs = count($documentosContexto);
            $orcamentoTotal = 20000;
            $maxCharsPorDoc = $totalDocs > 0 ? floor($orcamentoTotal / $totalDocs) : 20000;
            
            // Garante um mínimo de 2000 caracteres por documento se possível
            if ($maxCharsPorDoc < 2000) $maxCharsPorDoc = 2000;
            
            foreach ($documentosContexto as $index => $doc) {
                $nome = $doc['nome_documento'] ?? 'Documento ' . ($index + 1);
                $conteudo = $doc['conteudo'] ?? '';
                
                // Limita tamanho dinamicamente
                if (mb_strlen($conteudo) > $maxCharsPorDoc) {
                    $conteudo = mb_substr($conteudo, 0, $maxCharsPorDoc) . "\n[...texto truncado pelo sistema...]";
                }
                
                $prompt .= "--- INÍCIO DO DOCUMENTO: {$nome} ---\n";
                $prompt .= $conteudo . "\n";
                $prompt .= "--- FIM DO DOCUMENTO: {$nome} ---\n\n";
            }
        }
        
        $prompt .= "**SUA FUNÇÃO:**\n";
        $prompt .= "- Auxiliar na redação de documentos oficiais (notificações, ofícios, pareceres, despachos, etc.)\n";
        $prompt .= "- Corrigir erros de português (gramática, ortografia, concordância)\n";
        $prompt .= "- Melhorar a clareza e objetividade do texto\n";
        $prompt .= "- Sugerir redações mais formais e técnicas\n";
        $prompt .= "- Ajudar a estruturar argumentos e parágrafos\n\n";
        
        $prompt .= "**DIRETRIZES:**\n";
        $prompt .= "- Use linguagem formal e técnica adequada para documentos oficiais\n";
        $prompt .= "- Seja objetivo e direto nas correções\n";
        $prompt .= "- Explique as correções quando necessário\n";
        $prompt .= "- Mantenha o tom respeitoso e profissional\n";
        $prompt .= "- Preserve a intenção original do texto\n\n";
        
        $prompt .= "**FORMATO DE RESPOSTA PARA CORREÇÕES E ESTRUTURAÇÃO:**\n";
        $prompt .= "⚠️ IMPORTANTE: Quando o usuário pedir para 'corrigir', 'melhorar', 'revisar', 'estruturar', 'reorganizar' ou 'formatar' o texto, você DEVE usar este formato EXATO:\n\n";
        
        $prompt .= "**PARA CRIAÇÃO DE DOCUMENTOS (OFÍCIOS, DESPACHOS, NOTIFICAÇÕES):**\n";
        $prompt .= "Quando o usuário pedir para 'criar', 'fazer', 'redigir' um ofício, despacho, notificação ou similar, use UM ÚNICO bloco:\n";
        $prompt .= "```TEXTO_CORRIGIDO\n";
        $prompt .= "[corpo do documento]\n";
        $prompt .= "```\n\n";
        $prompt .= "⚠️ REGRAS IMPORTANTES:\n";
        $prompt .= "1. NÃO inclua cabeçalho (número do despacho, CNPJ, endereço) - isso já vem no PDF gerado\n";
        $prompt .= "2. NÃO coloque título como 'DESPACHO:', 'OFÍCIO:', etc.\n";
        $prompt .= "3. INCLUA o nome do estabelecimento no corpo do texto (ex: 'o estabelecimento SUPERMERCADO ROCHA...')\n";
        $prompt .= "4. Use os dados fornecidos (nome, processo, valores) naturalmente no texto\n";
        $prompt .= "5. Mantenha texto estruturado, coerente, resumido mas com detalhes necessários\n";
        $prompt .= "6. Use linguagem formal e profissional\n";
        $prompt .= "7. Comece direto com o conteúdo (ex: 'Senhor(a) Responsável,' ou direto com o assunto)\n\n";
        $prompt .= "EXEMPLO CORRETO:\n";
        $prompt .= "```TEXTO_CORRIGIDO\n";
        $prompt .= "Senhor(a) Responsável,\n\n";
        $prompt .= "Em cumprimento às normas vigentes, solicitamos que o estabelecimento SUPERMERCADO ROCHA efetue o pagamento da taxa no valor de R$ 50,00 (cinquenta reais), referente ao processo nº 2025/00006.\n\n";
        $prompt .= "O pagamento deverá ser realizado no prazo de 30 (trinta) dias corridos, a contar da data de ciência deste despacho.\n\n";
        $prompt .= "Atenciosamente.\n";
        $prompt .= "```\n\n";
        
        $prompt .= "**PARA ESTRUTURAÇÃO/REORGANIZAÇÃO DE TEXTO:**\n";
        $prompt .= "Quando o usuário pedir para 'estruturar', 'reorganizar' ou 'formatar' o texto, use UM ÚNICO bloco:\n";
        $prompt .= "```TEXTO_CORRIGIDO\n";
        $prompt .= "[texto completo estruturado com títulos, seções, numeração, etc.]\n";
        $prompt .= "```\n\n";
        $prompt .= "Depois do bloco, explique as melhorias feitas na estrutura.\n\n";
        
        $prompt .= "**PARA CORREÇÃO DE TEXTO:**\n";
        $prompt .= "⚠️ MUITO IMPORTANTE: Quando o usuário pedir para 'corrigir', 'revisar' ou 'melhorar' o texto, você DEVE SEMPRE usar o formato de PARÁGRAFOS para dar controle ao usuário:\n\n";
        $prompt .= "1. Identifique cada parágrafo ou seção do texto\n";
        $prompt .= "2. Para CADA parágrafo, crie um bloco separado:\n\n";
        $prompt .= "```PARAGRAFO_1\n";
        $prompt .= "[parágrafo 1 corrigido OU 'SEM_ERROS' se não tiver erros]\n";
        $prompt .= "```\n\n";
        $prompt .= "```PARAGRAFO_2\n";
        $prompt .= "[parágrafo 2 corrigido OU 'SEM_ERROS' se não tiver erros]\n";
        $prompt .= "```\n\n";
        $prompt .= "E assim por diante para cada parágrafo.\n\n";
        $prompt .= "3. Isso permite que o usuário escolha quais parágrafos aplicar no editor\n";
        $prompt .= "4. O usuário pode aplicar um parágrafo de cada vez, mantendo controle total\n\n";
        $prompt .= "⚠️ NÃO use ```TEXTO_CORRIGIDO``` para correções, APENAS para estruturação!\n\n";
        $prompt .= "EXCEÇÃO: Se o texto tiver APENAS UM PARÁGRAFO CURTO (menos de 3 linhas), use:\n";
        $prompt .= "```TEXTO_CORRIGIDO\n";
        $prompt .= "[texto corrigido]\n";
        $prompt .= "```\n\n";
        $prompt .= "Depois dos blocos de código, explique as correções feitas em cada parágrafo.\n\n";
        $prompt .= "EXEMPLO DE RESPOSTA CORRETA (múltiplos parágrafos):\n";
        $prompt .= "```PARAGRAFO_1\n";
        $prompt .= "Quero o coração para mim. A notificação está errada.\n";
        $prompt .= "```\n\n";
        $prompt .= "```PARAGRAFO_2\n";
        $prompt .= "SEM_ERROS\n";
        $prompt .= "```\n\n";
        $prompt .= "```PARAGRAFO_3\n";
        $prompt .= "Este estabelecimento está correto agora.\n";
        $prompt .= "```\n\n";
        $prompt .= "**Correções realizadas:**\n";
        $prompt .= "- Parágrafo 1: 'coracao' → 'coração' (acento), 'erada' → 'errada' (ortografia)\n";
        $prompt .= "- Parágrafo 2: Sem erros\n";
        $prompt .= "- Parágrafo 3: 'estabeleccimento' → 'estabelecimento' (ortografia)\n\n";
        
        $prompt .= "EXEMPLO DE RESPOSTA CORRETA (estruturação de texto):\n";
        $prompt .= "```TEXTO_CORRIGIDO\n";
        $prompt .= "I. INTRODUÇÃO\n\n";
        $prompt .= "No dia 04 de novembro de 2025, durante fiscalização sanitária...\n\n";
        $prompt .= "II. NOTIFICAÇÃO\n\n";
        $prompt .= "Fica o estabelecimento NOTIFICADO que...\n\n";
        $prompt .= "III. REQUISITOS\n\n";
        $prompt .= "O estabelecimento deverá providenciar:\n";
        $prompt .= "1. Item um\n";
        $prompt .= "2. Item dois\n";
        $prompt .= "```\n\n";
        $prompt .= "**Melhorias realizadas:**\n";
        $prompt .= "- Organizado em seções com títulos claros\n";
        $prompt .= "- Adicionada numeração sequencial\n";
        $prompt .= "- Melhorada a hierarquia visual\n\n";
        
        $prompt .= "EXEMPLO DE RESPOSTA CORRETA (correção de documento longo):\n";
        $prompt .= "Usuário pede: 'corrija o texto'\n\n";
        $prompt .= "```PARAGRAFO_1\n";
        $prompt .= "I. INTRODUÇÃO\n\n";
        $prompt .= "No dia 04 de novembro de 2025, durante a fiscalização sanitária...\n";
        $prompt .= "```\n\n";
        $prompt .= "```PARAGRAFO_2\n";
        $prompt .= "II. NOTIFICAÇÃO\n\n";
        $prompt .= "Fica o estabelecimento notificado que foram identificadas...\n";
        $prompt .= "```\n\n";
        $prompt .= "```PARAGRAFO_3\n";
        $prompt .= "III. REQUISITOS\n\n";
        $prompt .= "O estabelecimento deverá providenciar:\n1. Item um\n2. Item dois\n";
        $prompt .= "```\n\n";
        $prompt .= "**Correções realizadas:**\n";
        $prompt .= "- Parágrafo 1: Corrigido 'fiscalizaçao' → 'fiscalização'\n";
        $prompt .= "- Parágrafo 2: Corrigido 'notificado' → 'NOTIFICADO' (ênfase)\n";
        $prompt .= "- Parágrafo 3: Sem erros de ortografia\n\n";
        $prompt .= "⚠️ Isso permite que o usuário aplique cada seção separadamente!\n\n";
        
        if (!empty($textoAtual)) {
            $prompt .= "**TEXTO ATUAL DO DOCUMENTO:**\n";
            $prompt .= "```\n" . mb_substr($textoAtual, 0, 5000) . "\n```\n\n";
            $prompt .= "Este é o texto que o usuário está escrevendo. Use-o como contexto para suas sugestões.\n\n";
        }
        
        if ($conhecimentoGeral) {
            $prompt .= "**CONHECIMENTO GERAL ATIVADO:**\n";
            $prompt .= "Você pode buscar informações gerais e exemplos de documentos oficiais para auxiliar o usuário.\n";
            $prompt .= "Pode sugerir modelos, templates e boas práticas de redação oficial.\n\n";
            
            if (!empty($resultadosBusca)) {
                // Verifica se realmente tem resultados ou se está vazio
                $listaResultados = is_array($resultadosBusca) ? ($resultadosBusca['resultados'] ?? []) : [];
                $totalResultados = is_array($listaResultados) ? count($listaResultados) : 0;
                
                if ($totalResultados > 0) {
                    $prompt .= "**RESULTADOS DA PESQUISA NA INTERNET REALIZADA PELO SISTEMA:**\n";
                    $prompt .= "⚠️ INSTRUÇÃO CRÍTICA: O sistema acessou a internet em tempo real para você. As informações abaixo SÃO resultados reais da web obtidos AGORA.\n";
                    $prompt .= "NÃO diga 'não tenho acesso à internet'. USE APENAS as informações abaixo. NÃO INVENTE LINKS.\n\n";
                    
                    foreach ($listaResultados as $idx => $result) {
                        // Normaliza chaves (pode vir como title/titulo, snippet/descricao/resumo)
                        $titulo = $result['title'] ?? $result['titulo'] ?? 'Resultado ' . ($idx + 1);
                        $snippet = $result['snippet'] ?? $result['descricao'] ?? $result['resumo'] ?? '';
                        $link = $result['link'] ?? $result['url'] ?? '';
                        
                        // Se não tiver snippet mas tiver titulo, usa titulo
                        if (empty($snippet) && !empty($titulo)) {
                            $snippet = "Ver link para mais detalhes.";
                        }
                        
                        if (!empty($titulo) || !empty($link)) {
                            $prompt .= "--- Resultado " . ($idx + 1) . " ---\n";
                            $prompt .= "Título: {$titulo}\n";
                            $prompt .= "Link: {$link}\n";
                            $prompt .= "Resumo: {$snippet}\n\n";
                        }
                    }
                    
                    $prompt .= "**FIM DOS RESULTADOS DA WEB**\n\n";
                    $prompt .= "**IMPORTANTE:** Use APENAS os links acima. NÃO invente URLs. Se o usuário pedir links, forneça EXATAMENTE os que estão listados acima.\n\n";
                } else {
                    // Busca foi executada mas não retornou resultados
                    $prompt .= "**AVISO: BUSCA NA INTERNET EXECUTADA SEM RESULTADOS**\n";
                    $prompt .= "⚠️ INSTRUÇÃO CRÍTICA: A busca na internet foi realizada, mas não retornou resultados válidos.\n";
                    $prompt .= "Possíveis causas: bloqueio de scraping pelos buscadores, query muito específica, ou sites fora do ar.\n";
                    $prompt .= "VOCÊ DEVE informar ao usuário que:\n";
                    $prompt .= "1. A busca FOI executada em tempo real\n";
                    $prompt .= "2. Mas NÃO foram encontrados resultados\n";
                    $prompt .= "3. NÃO INVENTE links ou informações\n";
                    $prompt .= "4. Sugira que o usuário tente uma busca manual ou reformule a pergunta\n\n";
                }
            }
        }
        
        $prompt .= "**EXEMPLOS DE AJUDA:**\n";
        $prompt .= "- Correção: \"Corrija este texto: [texto]\"\n";
        $prompt .= "- Melhoria: \"Melhore a redação deste parágrafo\"\n";
        $prompt .= "- Formalização: \"Como posso escrever isso de forma mais formal?\"\n";
        $prompt .= "- Sugestão: \"Sugira um texto para notificar sobre irregularidades\"\n";
        $prompt .= "- Estrutura: \"Como organizar melhor este documento?\"\n\n";
        
        $prompt .= "Seja prestativo, claro e objetivo em suas respostas!";
        
        return $prompt;
    }

    /**
     * Lista documentos disponíveis de um processo
     */
    public function listarDocumentosProcesso($estabelecimentoId, $processoId)
    {
        try {
            $processo = \App\Models\Processo::findOrFail($processoId);
            
            // Busca documentos digitais
            $documentosDigitais = \App\Models\DocumentoDigital::where('processo_id', $processoId)
                ->where('status', '!=', 'rascunho')
                ->whereNotNull('arquivo_pdf')
                ->get()
                ->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'nome' => $doc->nome_documento ?? 'Documento Digital',
                        'tamanho' => $this->formatarTamanho($doc->tamanho_arquivo ?? 0),
                        'tipo' => 'documento_digital'
                    ];
                })
                ->values()
                ->toArray();

            // Busca arquivos externos (ProcessoDocumento)
            $arquivosExternos = [];
            try {
                $arquivosExternos = \App\Models\ProcessoDocumento::where('processo_id', $processoId)
                    ->get()
                    ->map(function($doc) {
                        return [
                            'id' => $doc->id,
                            'nome' => $doc->nome_original ?? $doc->nome_arquivo ?? 'Arquivo',
                            'tamanho' => $this->formatarTamanho($doc->tamanho ?? 0),
                            'tipo' => 'arquivo_externo'
                        ];
                    })
                    ->values()
                    ->toArray();
            } catch (\Exception $e) {
                \Log::warning('Erro ao buscar arquivos externos', [
                    'erro' => $e->getMessage()
                ]);
            }

            $documentos = array_merge($documentosDigitais, $arquivosExternos);

            return response()->json([
                'success' => true,
                'documentos' => $documentos
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao listar documentos do processo', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'processo_id' => $processoId,
                'estabelecimento_id' => $estabelecimentoId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar documentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extrai texto de múltiplos PDFs
     */
    public function extrairMultiplosPdfs(Request $request)
    {
        $request->validate([
            'documento_ids' => 'required|array',
            'documento_ids.*' => 'required|integer',
            'estabelecimento_id' => 'required|integer',
            'processo_id' => 'required|integer',
        ]);

        try {
            $documentoIds = $request->input('documento_ids');
            $processoId = $request->input('processo_id');
            $documentosExtraidos = [];

            foreach ($documentoIds as $documentoId) {
                // Tenta buscar como documento digital primeiro
                $docDigital = \App\Models\DocumentoDigital::where('processo_id', $processoId)
                    ->where('id', $documentoId)
                    ->first();

                $caminhoArquivo = null;
                $nomeDocumento = null;

                if ($docDigital && $docDigital->arquivo_pdf) {
                    // É um documento digital
                    $caminhoArquivo = storage_path('app/public') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $docDigital->arquivo_pdf);
                    $nomeDocumento = $docDigital->nome_documento ?? 'Documento Digital';
                } else {
                    // Busca como arquivo externo
                    $documento = \App\Models\ProcessoDocumento::where('processo_id', $processoId)
                        ->find($documentoId);

                    if (!$documento) {
                        continue; // Pula se não encontrar
                    }

                    if ($documento->tipo_documento === 'documento_digital') {
                        $caminhoArquivo = storage_path('app/public') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
                    } else {
                        $caminhoArquivo = storage_path('app') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $documento->caminho);
                    }
                    $nomeDocumento = $documento->nome_original ?? 'Documento';
                }

                if (!file_exists($caminhoArquivo)) {
                    \Log::warning('Arquivo PDF não encontrado', ['caminho' => $caminhoArquivo]);
                    continue; // Pula se arquivo não existir
                }

                // Extrai texto do PDF usando Smalot\PdfParser
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($caminhoArquivo);
                    
                    // Extrai texto de TODAS as páginas
                    $pages = $pdf->getPages();
                    $textoCompleto = '';
                    $totalPaginas = count($pages);
                    
                    foreach ($pages as $pageNum => $page) {
                        $textoPagina = $page->getText();
                        if (!empty($textoPagina)) {
                            $textoCompleto .= "=== PÁGINA " . ($pageNum + 1) . " de {$totalPaginas} ===\n";
                            $textoCompleto .= $textoPagina . "\n\n";
                        }
                    }

                    // Se não conseguiu extrair por páginas, tenta método geral
                    if (empty($textoCompleto)) {
                        $textoCompleto = $pdf->getText();
                    }

                    // Limpa o texto
                    $texto = trim($textoCompleto);
                    $texto = preg_replace('/\s+/', ' ', $texto); // Remove espaços múltiplos
                    
                    // Limita a aproximadamente 5.000 caracteres por documento
                    // Com 3 documentos = ~15.000 caracteres = ~3.750 tokens
                    // Deixa espaço para prompt do sistema (~2.000 tokens) + histórico + resposta
                    $texto = mb_substr($texto, 0, 5000);

                    if (!empty($texto)) {
                        $documentosExtraidos[] = [
                            'documento_id' => $documentoId,
                            'nome_documento' => $nomeDocumento,
                            'conteudo' => $texto,
                            'total_caracteres' => mb_strlen($texto)
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro ao extrair PDF individual', [
                        'documento_id' => $documentoId,
                        'erro' => $e->getMessage()
                    ]);
                    continue; // Pula se houver erro na extração
                }
            }

            if (empty($documentosExtraidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível extrair texto de nenhum documento'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'documentos' => $documentosExtraidos
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao extrair múltiplos PDFs', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao extrair documentos'
            ], 500);
        }
    }

    /**
     * Formata tamanho de arquivo
     */
    private function formatarTamanho($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}