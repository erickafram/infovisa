<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoDocumentoObrigatorio extends Model
{
    use SoftDeletes;

    protected $table = 'tipos_documento_obrigatorio';

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
     * Relacionamento com listas de documento
     */
    public function listasDocumento()
    {
        return $this->belongsToMany(ListaDocumento::class, 'lista_documento_tipo', 'tipo_documento_obrigatorio_id', 'lista_documento_id')
            ->withPivot(['obrigatorio', 'observacao', 'ordem'])
            ->withTimestamps();
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
