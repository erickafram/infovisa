<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\ConfiguracaoSistema;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\DocumentoDigital;

class AssistenteIAController extends Controller
{
    /**
     * Envia mensagem para a IA e retorna resposta
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
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
                'temperature' => 0.7,
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
            ]);

            return response()->json([
                'error' => 'Erro ao processar sua mensagem',
                'message' => 'Desculpe, ocorreu um erro. Tente novamente.',
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

REGRAS CRÍTICAS:
1. Use APENAS os dados fornecidos abaixo - eles já estão filtrados pela competência do usuário
2. NUNCA invente funcionalidades, menus ou caminhos que não foram mencionados
3. Seja EXTREMAMENTE preciso nas instruções - siga EXATAMENTE os passos descritos
4. Se não souber algo, diga claramente que não sabe
5. Use os números exatos fornecidos nos dados
6. Responda considerando o perfil e permissões do usuário

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
                $label = str_replace('_', ' ', ucfirst($key));
                $prompt .= "- {$label}: {$value}\n";
            }
            
            // Adiciona contexto sobre o filtro
            if ($usuario->isEstadual()) {
                $prompt .= "\n(Dados filtrados: apenas competência ESTADUAL de todos os municípios)\n";
            } elseif ($usuario->isMunicipal() && !empty($municipioNome)) {
                $prompt .= "\n(Dados filtrados: apenas competência MUNICIPAL de {$municipioNome})\n";
            }
        }

        return $prompt;
    }
}
