<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\ConfiguracaoSistema;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\OrdemServico;
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
        // Log para debug
        \Log::info('Chat request recebido', [
            'has_documento_contexto' => $request->has('documento_contexto'),
            'documento_keys' => $request->has('documento_contexto') ? array_keys($request->documento_contexto) : null,
        ]);

        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
            'documento_contexto' => 'nullable|array',
            'documento_contexto.nome' => 'required_with:documento_contexto|string|max:500',
            'documento_contexto.conteudo' => 'required_with:documento_contexto|string|max:50000', // 50KB de texto
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
        $tipoConsulta = $request->input('tipo_consulta', 'geral');
        
        // Obtém usuário logado
        $usuario = auth('interno')->user();

        try {
            // Analisa a mensagem para ver se precisa de dados do sistema
            // Se for consulta de relatórios, busca TODOS os dados
            $contextoDados = $this->obterContextoDados($userMessage, $usuario, $tipoConsulta === 'relatorios');
            
            // Adiciona contexto do documento se fornecido
            if ($documentoContexto) {
                \Log::info('Adicionando documento ao contexto', [
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
        // Prioriza configuração do documento, depois configuração global
        $buscaWebAtiva = false;
        
        // Se tem documento com configuração de busca
        if (isset($documentoContexto['buscar_internet'])) {
            $buscaWebAtiva = $documentoContexto['buscar_internet'] === true;
        } 
        // Senão, verifica configuração global do sistema
        else {
            $buscaWebAtiva = ConfiguracaoSistema::where('chave', 'ia_busca_web')->value('valor') === 'true';
        }
        
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
            // Se tem documento PDF, usa prompt simplificado para economizar tokens
            $temDocumento = isset($contextoDados['documento_pdf']) && !empty($contextoDados['documento_pdf']);
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
     * Constrói prompt simplificado quando há documento PDF (economiza tokens)
     */
    private function construirPromptSimplificadoDocumento($contextoDados)
    {
        $docPdf = $contextoDados['documento_pdf'];
        $nomeDoc = is_array($docPdf['nome'] ?? null) ? json_encode($docPdf['nome']) : ($docPdf['nome'] ?? 'Documento');
        $conteudoDoc = is_array($docPdf['conteudo'] ?? null) ? json_encode($docPdf['conteudo']) : ($docPdf['conteudo'] ?? '');
        $buscarInternet = $docPdf['buscar_internet'] ?? false;
        
        $prompt = "Você é um assistente especializado em análise de documentos.\n\n";
        $prompt .= "🚨 DOCUMENTO CARREGADO PELO USUÁRIO:\n\n";
        $prompt .= "**Nome:** {$nomeDoc}\n\n";
        $prompt .= "**CONTEÚDO:**\n{$conteudoDoc}\n\n";
        
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
            
            // ===== PRIORIDADE MÁXIMA: DOCUMENTO PDF CARREGADO =====
            // Adiciona contexto do documento PDF se disponível (ANTES de tudo)
            if (isset($contextoDados['documento_pdf']) && !empty($contextoDados['documento_pdf'])) {
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
        // Se houver documento PDF carregado, verifica a configuração buscar_internet
        if (isset($contextoDados['documento_pdf']) && !empty($contextoDados['documento_pdf'])) {
            // Se buscar_internet estiver definido, retorna esse valor
            if (isset($contextoDados['documento_pdf']['buscar_internet'])) {
                $deveBuscar = $contextoDados['documento_pdf']['buscar_internet'] === true;
                
                \Log::info('Verificação de busca (documento)', [
                    'deve_buscar' => $deveBuscar,
                    'buscar_internet_config' => $contextoDados['documento_pdf']['buscar_internet']
                ]);
                
                return $deveBuscar;
            }
            // Por padrão, não busca na internet para documentos
            \Log::info('Documento sem configuração de busca - não busca');
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
            
            // Tenta primeiro no DuckDuckGo (mais simples e permissivo)
            $resultados = $this->buscarNoDuckDuckGo($query);
            
            // Se DuckDuckGo não retornar, tenta Bing
            if (empty($resultados)) {
                \Log::info('DuckDuckGo não retornou resultados, tentando Bing...');
                $resultados = $this->buscarNoBing($query);
            }
            
            // Se Bing não retornar, tenta Google
            if (empty($resultados)) {
                \Log::info('Bing não retornou resultados, tentando Google...');
                $resultados = $this->buscarNoGoogle($query);
            }
            
            if (empty($resultados)) {
                \Log::info('Nenhum resultado encontrado em nenhum buscador');
                return [];
            }
            
            \Log::info('Resultados encontrados', [
                'total' => count($resultados)
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
                'success' => false,
                'message' => 'Erro ao processar PDF: ' . $e->getMessage()
            ], 500);
        }
    }
} 