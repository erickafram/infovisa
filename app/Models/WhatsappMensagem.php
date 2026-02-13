<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMensagem extends Model
{
    protected $table = 'whatsapp_mensagens';

    protected $fillable = [
        'documento_digital_id',
        'estabelecimento_id',
        'usuario_externo_id',
        'telefone',
        'nome_destinatario',
        'mensagem',
        'status',
        'erro_mensagem',
        'whatsapp_message_id',
        'enviado_em',
        'entregue_em',
        'lido_em',
        'tentativas',
        'proxima_tentativa',
    ];

    protected function casts(): array
    {
        return [
            'enviado_em' => 'datetime',
            'entregue_em' => 'datetime',
            'lido_em' => 'datetime',
            'proxima_tentativa' => 'datetime',
        ];
    }

    // ============================
    // Relacionamentos
    // ============================

    public function documentoDigital()
    {
        return $this->belongsTo(DocumentoDigital::class);
    }

    public function estabelecimento()
    {
        return $this->belongsTo(Estabelecimento::class);
    }

    public function usuarioExterno()
    {
        return $this->belongsTo(UsuarioExterno::class);
    }

    // ============================
    // Scopes
    // ============================

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeEnviados($query)
    {
        return $query->where('status', 'enviado');
    }

    public function scopeComErro($query)
    {
        return $query->where('status', 'erro');
    }

    public function scopeEntregues($query)
    {
        return $query->where('status', 'entregue');
    }

    // ============================
    // Accessors
    // ============================

    public function getStatusTextoAttribute(): string
    {
        return match ($this->status) {
            'pendente' => 'Pendente',
            'enviado' => 'Enviado',
            'entregue' => 'Entregue',
            'lido' => 'Lido',
            'erro' => 'Erro',
            default => 'Desconhecido',
        };
    }

    public function getStatusCorAttribute(): string
    {
        return match ($this->status) {
            'pendente' => 'yellow',
            'enviado' => 'blue',
            'entregue' => 'green',
            'lido' => 'emerald',
            'erro' => 'red',
            default => 'gray',
        };
    }

    public function getStatusIconeAttribute(): string
    {
        return match ($this->status) {
            'pendente' => '⏳',
            'enviado' => '✓',
            'entregue' => '✓✓',
            'lido' => '👁️',
            'erro' => '❌',
            default => '❓',
        };
    }

    /**
     * Marca como enviado
     */
    public function marcarEnviado(?string $messageId = null): void
    {
        $this->update([
            'status' => 'enviado',
            'enviado_em' => now(),
            'whatsapp_message_id' => $messageId,
        ]);
    }

    /**
     * Marca como erro
     */
    public function marcarErro(string $erro): void
    {
        $this->update([
            'status' => 'erro',
            'erro_mensagem' => $erro,
            'tentativas' => $this->tentativas + 1,
            'proxima_tentativa' => now()->addMinutes(5 * ($this->tentativas + 1)),
        ]);
    }

    /**
     * Verifica se pode retentar
     */
    public function podeRetentar(): bool
    {
        return $this->status === 'erro' && $this->tentativas < 3;
    }
}
