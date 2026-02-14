<?php

namespace App\Services;

use App\Models\WhatsappConfiguracao;
use App\Models\WhatsappMensagem;
use App\Models\DocumentoDigital;
use App\Models\Estabelecimento;
use App\Models\UsuarioExterno;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected ?WhatsappConfiguracao $config;

    public function __construct()
    {
        $this->config = WhatsappConfiguracao::getConfig();
    }

    /**
     * Verifica se o serviço está operacional
     */
    public function estaOperacional(): bool
    {
        return $this->config && $this->config->estaOperacional();
    }

    /**
     * Monta os headers para as requisições ao Baileys Server
     */
    protected function getHeaders(): array
    {
        $headers = ['Content-Type' => 'application/json'];

        if ($this->config->api_key) {
            $headers['Authorization'] = 'Bearer ' . $this->config->api_key;
        }

        return $headers;
    }

    /**
     * Retorna a base URL do Baileys Server
     */
    protected function getBaseUrl(): string
    {
        return rtrim($this->config->baileys_server_url, '/');
    }

    /**
     * Verifica o status da conexão com o Baileys Server
     */
    public function verificarStatus(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get($this->getBaseUrl() . '/sessions/' . $this->config->session_name . '/status');

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['status'] ?? 'desconectado';

                $this->config->update([
                    'status_conexao' => $status,
                    'ultima_verificacao' => now(),
                    'qr_code' => $data['qr'] ?? null,
                ]);

                return [
                    'sucesso' => true,
                    'status' => $status,
                    'qr' => $data['qr'] ?? null,
                    'qr_code' => $data['qr'] ?? null,
                    'dados' => $data,
                ];
            }

            return [
                'sucesso' => false,
                'status' => 'erro',
                'mensagem' => 'Servidor Baileys não respondeu corretamente. Status: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp: Erro ao verificar status', ['erro' => $e->getMessage()]);

            $this->config->update([
                'status_conexao' => 'desconectado',
                'ultima_verificacao' => now(),
            ]);

            return [
                'sucesso' => false,
                'status' => 'desconectado',
                'mensagem' => 'Não foi possível conectar ao servidor Baileys: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Inicia uma nova sessão no Baileys Server
     */
    public function iniciarSessao(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post($this->getBaseUrl() . '/sessions', [
                    'sessionId' => $this->config->session_name,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $this->config->update([
                    'status_conexao' => 'aguardando_qr',
                    'qr_code' => $data['qr'] ?? null,
                    'ultima_verificacao' => now(),
                ]);

                return [
                    'sucesso' => true,
                    'mensagem' => 'Sessão iniciada. Escaneie o QR Code.',
                    'qr' => $data['qr'] ?? null,
                    'qr_code' => $data['qr'] ?? null,
                ];
            }

            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao iniciar sessão: ' . ($response->json()['message'] ?? 'Erro desconhecido'),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp: Erro ao iniciar sessão', ['erro' => $e->getMessage()]);
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao conectar com servidor Baileys: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Encerra a sessão do WhatsApp
     */
    public function encerrarSessao(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->delete($this->getBaseUrl() . '/sessions/' . $this->config->session_name);

            $this->config->update([
                'status_conexao' => 'desconectado',
                'qr_code' => null,
                'ultima_verificacao' => now(),
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Sessão encerrada com sucesso.',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp: Erro ao encerrar sessão', ['erro' => $e->getMessage()]);
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao encerrar sessão: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Envia uma mensagem de texto via WhatsApp
     */
    public function enviarMensagem(string $telefone, string $mensagem): array
    {
        if (!$this->estaOperacional()) {
            return [
                'sucesso' => false,
                'mensagem' => 'WhatsApp não está operacional. Verifique a configuração e conexão.',
            ];
        }

        $telefoneFormatado = $this->formatarTelefone($telefone);

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post($this->getBaseUrl() . '/sessions/' . $this->config->session_name . '/messages/send', [
                    'jid' => $telefoneFormatado . '@s.whatsapp.net',
                    'type' => 'text',
                    'message' => $mensagem,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'sucesso' => true,
                    'mensagem' => 'Mensagem enviada com sucesso.',
                    'message_id' => $data['messageId'] ?? $data['key']['id'] ?? null,
                ];
            }

            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao enviar mensagem: ' . ($response->json()['message'] ?? 'Status ' . $response->status()),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp: Erro ao enviar mensagem', [
                'telefone' => $telefoneFormatado,
                'erro' => $e->getMessage(),
            ]);
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao enviar mensagem: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Formata telefone para padrão WhatsApp (55 + DDD + Número)
     */
    public function formatarTelefone(string $telefone): string
    {
        // Remove tudo que não é dígito
        $telefone = preg_replace('/\D/', '', $telefone);

        // Se já começa com 55 e tem 12-13 dígitos, retorna direto
        if (str_starts_with($telefone, '55') && strlen($telefone) >= 12) {
            return $telefone;
        }

        // Adiciona código do país se não tiver
        if (strlen($telefone) === 10 || strlen($telefone) === 11) {
            $telefone = '55' . $telefone;
        }

        return $telefone;
    }

    /**
     * Monta a mensagem a partir do template e variáveis
     */
    public function montarMensagem(DocumentoDigital $documento, Estabelecimento $estabelecimento, UsuarioExterno $usuario): string
    {
        $template = $this->config->mensagem_template ?? WhatsappConfiguracao::getTemplatePadrao();

        // Monta o link para o documento
        $linkDocumento = route('verificar.autenticidade', [
            'codigo' => $documento->codigo_autenticidade,
        ]);

        $variaveis = [
            '{nome_usuario}' => $usuario->nome,
            '{nome_documento}' => $documento->tipoDocumento->nome ?? 'Documento',
            '{numero_documento}' => $documento->numero_formatado ?? $documento->id,
            '{nome_estabelecimento}' => $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social ?? 'Estabelecimento',
            '{link_documento}' => $linkDocumento,
        ];

        return str_replace(array_keys($variaveis), array_values($variaveis), $template);
    }

    /**
     * Envia notificação WhatsApp para todos os usuários vinculados ao estabelecimento
     * quando um documento é totalmente assinado
     */
    public function notificarDocumentoAssinado(DocumentoDigital $documento): array
    {
        $resultados = [
            'total' => 0,
            'enviados' => 0,
            'erros' => 0,
            'detalhes' => [],
        ];

        if (!$this->estaOperacional() || !$this->config->enviar_ao_assinar) {
            Log::info('WhatsApp: Envio desativado ou não operacional', [
                'documento_id' => $documento->id,
                'config_existe' => (bool) $this->config,
                'ativo' => $this->config?->ativo,
                'status_conexao' => $this->config?->status_conexao,
                'enviar_ao_assinar' => $this->config?->enviar_ao_assinar,
            ]);
            return $resultados;
        }

        // Carrega o processo e o estabelecimento do documento
        $documento->loadMissing([
            'processo.usuarioExterno',
            'processo.estabelecimento.usuariosVinculados',
            'processo.estabelecimento.usuarioExterno',
            'tipoDocumento'
        ]);

        $processo = $documento->processo;
        if (!$processo) {
            Log::warning('WhatsApp: Documento sem processo vinculado', ['documento_id' => $documento->id]);
            return $resultados;
        }

        $estabelecimento = $processo->estabelecimento;
        if (!$estabelecimento) {
            Log::warning('WhatsApp: Processo sem estabelecimento vinculado', [
                'documento_id' => $documento->id,
                'processo_id' => $processo->id,
            ]);
            return $resultados;
        }

        // Busca usuários vinculados ao estabelecimento com telefone
        $usuariosVinculados = $estabelecimento->usuariosVinculados()
            ->whereNotNull('usuarios_externos.telefone')
            ->where('usuarios_externos.telefone', '!=', '')
            ->get();

        // Inclui também o usuário criador do estabelecimento (quando houver)
        $destinatarios = $usuariosVinculados;
        if ($estabelecimento->usuarioExterno && !empty($estabelecimento->usuarioExterno->telefone)) {
            $destinatarios->push($estabelecimento->usuarioExterno);
        }

        // Inclui também o usuário externo que abriu o processo (quando houver)
        if ($processo->usuarioExterno && !empty($processo->usuarioExterno->telefone)) {
            $destinatarios->push($processo->usuarioExterno);
        }

        // Remove duplicados pelo ID
        $destinatarios = $destinatarios->unique('id')->values();

        if ($destinatarios->isEmpty()) {
            Log::warning('WhatsApp: Nenhum destinatário com telefone para notificação', [
                'documento_id' => $documento->id,
                'estabelecimento_id' => $estabelecimento->id,
            ]);
            return $resultados;
        }

        $resultados['total'] = $destinatarios->count();

        foreach ($destinatarios as $usuario) {
            $mensagemTexto = $this->montarMensagem($documento, $estabelecimento, $usuario);

            // Cria o registro da mensagem
            $mensagemLog = WhatsappMensagem::create([
                'documento_digital_id' => $documento->id,
                'estabelecimento_id' => $estabelecimento->id,
                'usuario_externo_id' => $usuario->id,
                'telefone' => $usuario->telefone,
                'nome_destinatario' => $usuario->nome,
                'mensagem' => $mensagemTexto,
                'status' => 'pendente',
                'tentativas' => 0,
            ]);

            // Tenta enviar
            $resultado = $this->enviarMensagem($usuario->telefone, $mensagemTexto);

            if ($resultado['sucesso']) {
                $mensagemLog->marcarEnviado($resultado['message_id'] ?? null);
                $resultados['enviados']++;
                $resultados['detalhes'][] = [
                    'usuario' => $usuario->nome,
                    'status' => 'enviado',
                ];
            } else {
                $mensagemLog->marcarErro($resultado['mensagem']);
                $resultados['erros']++;
                $resultados['detalhes'][] = [
                    'usuario' => $usuario->nome,
                    'status' => 'erro',
                    'erro' => $resultado['mensagem'],
                ];
            }
        }

        Log::info('WhatsApp: Notificações enviadas', [
            'documento_id' => $documento->id,
            'estabelecimento_id' => $estabelecimento->id,
            'total' => $resultados['total'],
            'enviados' => $resultados['enviados'],
            'erros' => $resultados['erros'],
        ]);

        return $resultados;
    }

    /**
     * Retenta enviar mensagens com erro
     */
    public function retentarMensagensComErro(): array
    {
        $mensagens = WhatsappMensagem::where('status', 'erro')
            ->where('tentativas', '<', 3)
            ->where(function ($query) {
                $query->whereNull('proxima_tentativa')
                      ->orWhere('proxima_tentativa', '<=', now());
            })
            ->get();

        $resultados = ['total' => $mensagens->count(), 'sucesso' => 0, 'falha' => 0];

        foreach ($mensagens as $mensagem) {
            $resultado = $this->enviarMensagem($mensagem->telefone, $mensagem->mensagem);

            if ($resultado['sucesso']) {
                $mensagem->marcarEnviado($resultado['message_id'] ?? null);
                $resultados['sucesso']++;
            } else {
                $mensagem->marcarErro($resultado['mensagem']);
                $resultados['falha']++;
            }
        }

        return $resultados;
    }

    /**
     * Envia uma mensagem de teste
     */
    public function enviarMensagemTeste(string $telefone): array
    {
        $mensagem = "✅ *INFOVISA - Mensagem de Teste*\n\n"
            . "Esta é uma mensagem de teste do sistema de notificações por WhatsApp.\n\n"
            . "Se você recebeu esta mensagem, a integração está funcionando corretamente!\n\n"
            . "📅 Data: " . now()->format('d/m/Y H:i:s');

        return $this->enviarMensagem($telefone, $mensagem);
    }
}
