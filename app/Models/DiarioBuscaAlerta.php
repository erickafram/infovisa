<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiarioBuscaAlerta extends Model
{
    protected $table = 'diario_busca_alertas';

    protected $fillable = [
        'diario_busca_salva_id',
        'usuario_interno_id',
        'titulo',
        'edicao',
        'data_publicacao',
        'url_download',
        'lido'
    ];

    protected $casts = [
        'data_publicacao' => 'date',
        'lido' => 'boolean'
    ];

    public function buscaSalva(): BelongsTo
    {
        return $this->belongsTo(DiarioBuscaSalva::class, 'diario_busca_salva_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_interno_id');
    }
}
