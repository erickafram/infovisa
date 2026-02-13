<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappConfiguracao extends Model
{
    protected $table = 'whatsapp_configuracoes';

    protected $fillable = [
        'baileys_server_url',
        'api_key',
        'session_name',
        'ativo',
        'enviar_ao_assinar',
        'mensagem_template',
        'status_conexao',
        'qr_code',
        'ultima_verificacao',
        'configurado_por',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'enviar_ao_assinar' => 'boolean',
            'ultima_verificacao' => 'datetime',
        ];
    }

    public function configuradoPor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'configurado_por');
    }

    /**
     * Retorna a configuração ativa (singleton pattern - só existe uma config)
     */
    public static function getConfig(): ?self
    {
        return static::first();
    }

    /**
     * Retorna a configuração ou cria uma padrão
     */
    public static function getOrCreate(): self
    {
        return static::firstOrCreate([], [
            'baileys_server_url' => 'http://localhost:3000',
            'session_name' => 'infovisa',
            'ativo' => false,
            'enviar_ao_assinar' => true,
            'mensagem_template' => self::getTemplatePadrao(),
            'status_conexao' => 'desconectado',
        ]);
    }

    /**
     * Template padrão da mensagem
     */
    public static function getTemplatePadrao(): string
    {
        return <<<'TEMPLATE'
🏥 *INFOVISA - Vigilância Sanitária*

Olá, *{nome_usuario}*!

Um novo documento foi emitido pela Vigilância Sanitária para o estabelecimento *{nome_estabelecimento}*:

📄 *Documento:* {nome_documento}
📋 *Número:* {numero_documento}

🔗 Acesse o documento pelo link:
{link_documento}

_Esta é uma mensagem automática do sistema INFOVISA._
TEMPLATE;
    }

    /**
     * Verifica se o WhatsApp está configurado e ativo
     */
    public function estaOperacional(): bool
    {
        return $this->ativo
            && $this->status_conexao === 'conectado'
            && !empty($this->baileys_server_url);
    }
}
