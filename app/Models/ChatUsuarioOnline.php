<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatUsuarioOnline extends Model
{
    protected $table = 'chat_usuarios_online';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'ultimo_acesso',
        'digitando',
        'digitando_para',
    ];

    protected $casts = [
        'ultimo_acesso' => 'datetime',
        'digitando' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_id');
    }

    /**
     * Verifica se o usuário está online (ativo nos últimos 2 minutos)
     */
    public function isOnline(): bool
    {
        return $this->ultimo_acesso && $this->ultimo_acesso->diffInMinutes(now()) < 2;
    }

    /**
     * Atualiza o status online do usuário
     */
    public static function atualizarStatus(int $usuarioId): void
    {
        self::updateOrCreate(
            ['usuario_id' => $usuarioId],
            ['ultimo_acesso' => now(), 'digitando' => false]
        );
    }

    /**
     * Define que o usuário está digitando
     */
    public static function setDigitando(int $usuarioId, ?int $paraUsuarioId): void
    {
        self::updateOrCreate(
            ['usuario_id' => $usuarioId],
            ['ultimo_acesso' => now(), 'digitando' => true, 'digitando_para' => $paraUsuarioId]
        );
    }

    /**
     * Retorna IDs dos usuários online
     */
    public static function getUsuariosOnlineIds(): array
    {
        return self::where('ultimo_acesso', '>=', now()->subMinutes(2))
            ->pluck('usuario_id')
            ->toArray();
    }
}
