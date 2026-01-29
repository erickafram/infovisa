<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatBroadcastLeitura extends Model
{
    protected $table = 'chat_broadcast_leituras';

    protected $fillable = [
        'broadcast_id',
        'usuario_id',
        'lida_em',
    ];

    protected $casts = [
        'lida_em' => 'datetime',
    ];

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(ChatBroadcast::class, 'broadcast_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_id');
    }
}
