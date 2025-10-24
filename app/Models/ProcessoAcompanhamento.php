<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessoAcompanhamento extends Model
{
    protected $fillable = [
        'processo_id',
        'usuario_interno_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function usuarioInterno(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class);
    }
}
