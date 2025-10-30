<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\ConfiguracaoSistema;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\DocumentoDigital;
use App\Models\DocumentoPop;
use App\Models\CategoriaPop;

class AssistenteIAController extends Controller
{
    /**
     * Envia mensagem para a IA e retorna resposta
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000', // Aumentado para permitir perguntas mais longas
            'history' => 'nullable|array',
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
        
        // Obtém usuário logado
        $usuario = auth('interno')->user();

        // Analisa a mensagem para ver se precisa de dados do sistema
        $contextoDados = $this->obterContextoDados($userMessage, $usuario);
        
        // Verifica se deve buscar na internet
        $buscaWebAtiva = ConfiguracaoSistema::where('chave', 'ia_busca_web')->value('valor') === 'true';
        if ($buscaWebAtiva && $this->deveBuscarNaInternet($userMessage, $contextoDados)) {
            $resultadosWeb = $this->buscarNaInternet($userMessage);
            if (!empty($resultadosWeb)) {
                $contextoDados['resultados_web'] = $resultadosWeb;
            }
        }

        // Prepara o contexto do sistema
        $systemPrompt = $this->construirSystemPrompt($contextoDados, $usuario);

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

            // Chama API da IA
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($apiUrl, [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => 0.1, // Temperatura muito baixa para respostas determinísticas e sem alucinações
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $assistantMessage = $data['choices'][0]['message']['content'] ?? 'Desculpe, não consegui processar sua pergunta.';

                return response()->json([
                    'message' => $assistantMessage,
                    'success' => true,
                ]);
            } else {
                \Log::error('Erro na API da IA', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'Erro ao comunicar com a IA',
                    'message' => 'Desculpe, estou com dificuldades no momento. Tente novamente mais tarde.',
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Exceção ao chamar IA', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_message' => $userMessage,
            ]);

            return response()->json([
                'error' => 'Erro ao processar sua mensagem',
                'message' => 'Desculpe, ocorreu um erro. Tente novamente.',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Obtém dados do sistema baseado na pergunta do usuário
     */
    private function obterContextoDados($message, $usuario)
    {
        $message = strtolower($message);
        $dados = [];

        try {
            // Detecta perguntas sobre estabelecimentos
            if (preg_match('/(quantos|quantidade|total|tenho).*estabelecimento/i', $message)) {
                $query = Estabelecimento::query();
            
            // Filtra por competência
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
            }

            // Detecta perguntas sobre processos
            if (preg_match('/(quantos|quantidade|total|tenho).*processo/i', $message)) {
                $query = Processo::query();
                
                // Filtra por competência
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
            }

            // Detecta perguntas sobre documentos
            if (preg_match('/(quantos|quantidade|total|tenho).*documento/i', $message)) {
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
            
            // Detecta se a pergunta menciona uma categoria específica
            $categoriaFiltro = $this->detectarCategoria($message);
            
            if ($categoriaFiltro) {
                // Filtra apenas documentos da categoria mencionada
                $query->whereHas('categorias', function($q) use ($categoriaFiltro) {
                    $q->where('categorias_pops.id', $categoriaFiltro->id);
                });
            }
            
            $documentos = $query->get();
            
            if ($documentos->isEmpty()) {
                return [];
            }
            
            $documentosRelevantes = [];
            
            foreach ($documentos as $doc) {
                $relevancia = 0;
                $conteudoLower = strtolower($doc->conteudo_extraido);
                $tituloLower = strtolower($doc->titulo);
                
                // Verifica relevância baseado em palavras-chave
                foreach ($palavrasChave as $palavra) {
                    if (strlen($palavra) < 3) continue; // Ignora palavras muito curtas
                    
                    // Título tem peso maior
                    if (strpos($tituloLower, $palavra) !== false) {
                        $relevancia += 10;
                    }
                    
                    // Conteúdo
                    $ocorrencias = substr_count($conteudoLower, $palavra);
                    $relevancia += $ocorrencias;
                }
                
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
        $stopWords = ['o', 'a', 'os', 'as', 'um', 'uma', 'de', 'da', 'do', 'para', 'com', 'em', 'no', 'na', 'por', 'como', 'qual', 'quais', 'que', 'e', 'ou', 'é', 'são', 'fala', 'diz'];
        
        $palavras = preg_split('/\s+/', strtolower($message));
        $palavras = array_filter($palavras, function($palavra) use ($stopWords) {
            return !in_array($palavra, $stopWords) && strlen($palavra) >= 3;
        });
        
        // Se a pergunta menciona "artigo" ou "rdc", adiciona palavras-chave relacionadas
        $messageLower = strtolower($message);
        if (strpos($messageLower, 'artigo') !== false || strpos($messageLower, 'art.') !== false) {
            $palavras[] = 'aplica-se';
            $palavras[] = 'resolução';
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
     * Constrói o prompt do sistema com contexto
     */
    private function construirSystemPrompt($contextoDados, $usuario)
    {
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
                // Documentos POPs são tratados separadamente
                if ($key === 'documentos_pops') {
                    continue;
                }
                
                $label = str_replace('_', ' ', ucfirst($key));
                $prompt .= "- {$label}: {$value}\n";
            }
            
            // Adiciona contexto sobre o filtro
            if ($usuario->isEstadual()) {
                $prompt .= "\n(Dados filtrados: apenas competência ESTADUAL de todos os municípios)\n";
            } elseif ($usuario->isMunicipal() && !empty($municipioNome)) {
                $prompt .= "\n(Dados filtrados: apenas competência MUNICIPAL de {$municipioNome})\n";
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
                $prompt .= "- **FORMATO DE RESPOSTA: 'De acordo com a [RDC completa], [Artigo e parágrafo se houver], [conteúdo]'**\n";
                $prompt .= "- Cite o nome do documento usado: \"De acordo com o documento [nome exato do documento]...\"\n";
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
                    // Com busca na internet ativa
                    $prompt .= "\n**IMPORTANTE - SEM DOCUMENTOS POPs LOCAIS, MAS BUSCA NA INTERNET ATIVA:**\n";
                    $prompt .= "- NÃO foram encontrados documentos POPs locais sobre este tema\n";
                    $prompt .= "- **VOCÊ PODE usar seu conhecimento sobre vigilância sanitária brasileira para responder**\n";
                    $prompt .= "- Foque em informações oficiais da ANVISA e legislação brasileira\n";
                    $prompt .= "- **SEMPRE indique**: \"Segundo conhecimento sobre legislação sanitária brasileira...\"\n";
                    $prompt .= "- Se mencionar RDCs ou resoluções, cite os números corretos que você conhece\n";
                    $prompt .= "- Seja preciso e técnico, baseado em normas reais da ANVISA\n";
                    $prompt .= "- Se não souber com certeza, diga que não tem a informação\n";
                } else {
                    // Sem busca na internet
                    $prompt .= "\n**IMPORTANTE - SEM DOCUMENTOS POPs RELEVANTES PARA ESTA PERGUNTA:**\n";
                    $prompt .= "- A pergunta parece ser sobre NORMAS/PROCEDIMENTOS, mas NÃO foram encontrados documentos POPs relevantes\n";
                    $prompt .= "- **CRÍTICO: NUNCA invente informações, artigos, RDCs, resoluções ou normas**\n";
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
                $prompt .= "\n\n==== INFORMAÇÕES COMPLEMENTARES DA INTERNET ====\n";
                $prompt .= "AVISO: Busca complementar foi realizada na internet (sites oficiais como anvisa.gov.br e in.gov.br).\n";
                $prompt .= "- **PRIORIZE SEMPRE os documentos POPs cadastrados localmente**\n";
                $prompt .= "- Use informações da internet apenas para COMPLEMENTAR quando não houver POPs\n";
                $prompt .= "- SEMPRE indique a fonte: \"Segundo busca complementar na internet...\"\n";
                $prompt .= "- NUNCA misture informações dos POPs com informações da internet sem deixar claro\n\n";
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
        // Se não encontrou documentos POPs relevantes, busca na internet
        if (!isset($contextoDados['documentos_pops']) || empty($contextoDados['documentos_pops'])) {
            // Verifica se é uma pergunta sobre normas/regulamentações
            $palavrasChaveNormas = ['rdc', 'resolução', 'portaria', 'lei', 'norma', 'anvisa', 'regulamento', 'artigo'];
            $messageLower = strtolower($message);
            
            foreach ($palavrasChaveNormas as $palavra) {
                if (strpos($messageLower, $palavra) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Busca informações na internet
     */
    private function buscarNaInternet($message)
    {
        try {
            // Monta query de busca focada em vigilância sanitária
            $query = $message . ' site:anvisa.gov.br OR site:in.gov.br';
            
            // Usa API do Google Custom Search ou DuckDuckGo
            // Por enquanto, vamos usar uma busca simples com file_get_contents
            $searchUrl = 'https://www.google.com/search?q=' . urlencode($query);
            
            // Nota: Em produção, você deve usar uma API oficial como Google Custom Search API
            // ou implementar um scraper mais robusto
            
            \Log::info('Busca na internet realizada', [
                'query' => $query,
                'message' => $message
            ]);
            
            // Por enquanto, retorna vazio - você pode implementar com uma API real
            // Exemplo: Google Custom Search API, Bing Search API, etc.
            return [
                'fonte' => 'Busca na internet',
                'aviso' => 'Busca complementar realizada. Priorize sempre os documentos POPs cadastrados.',
            ];
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar na internet', [
                'erro' => $e->getMessage(),
            ]);
            return [];
        }
    }
} 