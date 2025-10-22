<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoProcesso extends Model
{
    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'anual',
        'usuario_externo_pode_abrir',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'anual' => 'boolean',
        'usuario_externo_pode_abrir' => 'boolean',
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * Scope para tipos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para tipos que usuário externo pode abrir
     */
    public function scopeParaUsuarioExterno($query)
    {
        return $query->where('usuario_externo_pode_abrir', true)->where('ativo', true);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }
}
