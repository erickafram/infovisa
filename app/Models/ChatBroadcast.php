<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatBroadcast extends Model
{
    protected $table = 'chat_broadcasts';

    protected $fillable = [
        'enviado_por',
        'conteudo',
        'niveis_acesso',
        'tipo',
        'arquivo_path',
        'arquivo_nome',
    ];

    protected $casts = [
        'niveis_acesso' => 'array',
    ];

    public function remetente(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'enviado_por');
    }

    public function leituras(): HasMany
    {
        return $this->hasMany(ChatBroadcastLeitura::class, 'broadcast_id');
    }

    /**
     * Verifica se o usuário pode ver este broadcast
     */
    public function podeVer(UsuarioInterno $usuario): bool
    {
        $niveis = $this->niveis_acesso;
        
        if (in_array('todos', $niveis)) {
            return true;
        }

        return in_array($usuario->nivel_acesso->value, $niveis);
    }

    /**
     * Verifica se foi lido pelo usuário
     */
    public function foiLidoPor(int $usuarioId): bool
    {
        return $this->leituras()->where('usuario_id', $usuarioId)->whereNotNull('lida_em')->exists();
    }
}
