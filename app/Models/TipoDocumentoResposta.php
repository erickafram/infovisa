<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoDocumentoResposta extends Model
{
    protected $table = 'tipo_documento_respostas';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
        'tipo_setor',
        'ordem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    public function tiposDocumento(): BelongsToMany
    {
        return $this->belongsToMany(TipoDocumento::class, 'tipo_documento_tipo_resposta')
            ->withPivot('obrigatorio', 'ordem')
            ->withTimestamps()
            ->orderByPivot('ordem');
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }
}
