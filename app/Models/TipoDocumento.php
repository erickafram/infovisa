<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumento extends Model
{
    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'ativo',
        'ordem',
        'tem_prazo',
        'prazo_padrao_dias',
        'prazo_notificacao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'tem_prazo' => 'boolean',
        'prazo_notificacao' => 'boolean',
        'prazo_padrao_dias' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com modelos de documentos
     */
    public function modelosDocumento(): HasMany
    {
        return $this->hasMany(ModeloDocumento::class);
    }

    /**
     * Scope para buscar apenas tipos ativos
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
        return $query->orderBy('ordem')->orderBy('nome');
    }
}
