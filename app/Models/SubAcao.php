<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubAcao extends Model
{
    use SoftDeletes;

    protected $table = 'sub_acoes';

    protected $fillable = [
        'tipo_acao_id',
        'descricao',
        'codigo_procedimento',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento com TipoAcao (ação pai)
     */
    public function tipoAcao()
    {
        return $this->belongsTo(TipoAcao::class);
    }

    /**
     * Scope para buscar apenas subações ativas
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
        return $query->orderBy('ordem')->orderBy('descricao');
    }

    /**
     * Retorna descrição completa (ação + subação)
     */
    public function getDescricaoCompletaAttribute()
    {
        return $this->tipoAcao->descricao . ' → ' . $this->descricao;
    }
}
