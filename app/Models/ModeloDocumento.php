<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModeloDocumento extends Model
{
    protected $fillable = [
        'tipo_documento_id',
        'codigo',
        'descricao',
        'conteudo',
        'variaveis',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'variaveis' => 'array',
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com tipo de documento
     */
    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    /**
     * Scope para buscar apenas modelos ativos
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenar por ordem
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem');
    }
}
