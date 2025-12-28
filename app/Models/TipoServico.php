<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoServico extends Model
{
    use SoftDeletes;

    protected $table = 'tipos_servico';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * Relacionamento com atividades
     */
    public function atividades()
    {
        return $this->hasMany(Atividade::class);
    }

    /**
     * Atividades ativas
     */
    public function atividadesAtivas()
    {
        return $this->atividades()->where('ativo', true)->orderBy('ordem')->orderBy('nome');
    }

    /**
     * Scope para tipos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenação
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }
}
