<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiarioBuscaSalva extends Model
{
    protected $fillable = [
        'usuario_interno_id',
        'nome',
        'texto',
        'data_inicial',
        'data_final',
        'executar_diariamente',
        'ultima_execucao'
    ];

    protected $casts = [
        'data_inicial' => 'date',
        'data_final' => 'date',
        'executar_diariamente' => 'boolean',
        'ultima_execucao' => 'datetime'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_interno_id');
    }
}
