<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversa extends Model
{
    protected $table = 'chat_conversas';

    protected $fillable = [
        'usuario1_id',
        'usuario2_id',
        'ultima_mensagem_at',
    ];

    protected $casts = [
        'ultima_mensagem_at' => 'datetime',
    ];

    public function usuario1(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario1_id');
    }

    public function usuario2(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario2_id');
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(ChatMensagem::class, 'conversa_id');
    }

    public function ultimaMensagem()
    {
        return $this->hasOne(ChatMensagem::class, 'conversa_id')->latest();
    }

    /**
     * Retorna o outro usuário da conversa
     */
    public function getOutroUsuario(int $usuarioAtualId): ?UsuarioInterno
    {
        if ($this->usuario1_id === $usuarioAtualId) {
            return $this->usuario2;
        }
        return $this->usuario1;
    }

    /**
     * Conta mensagens não lidas para um usuário
     */
    public function mensagensNaoLidas(int $usuarioId): int
    {
        return $this->mensagens()
            ->where('remetente_id', '!=', $usuarioId)
            ->whereNull('lida_em')
            ->count();
    }

    /**
     * Encontra ou cria uma conversa entre dois usuários
     */
    public static function encontrarOuCriar(int $usuario1Id, int $usuario2Id): self
    {
        // Ordena os IDs para garantir consistência
        $ids = [$usuario1Id, $usuario2Id];
        sort($ids);

        return self::firstOrCreate(
            ['usuario1_id' => $ids[0], 'usuario2_id' => $ids[1]]
        );
    }
}
