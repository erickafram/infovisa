<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Atividade extends Model
{
    use SoftDeletes;

    protected $table = 'atividades';

    protected $fillable = [
        'tipo_servico_id',
        'nome',
        'codigo_cnae',
        'descricao',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * Relacionamento com tipo de serviço
     */
    public function tipoServico()
    {
        return $this->belongsTo(TipoServico::class);
    }

    /**
     * Relacionamento com listas de documento
     */
    public function listasDocumento()
    {
        return $this->belongsToMany(ListaDocumento::class, 'lista_documento_atividade')
            ->withTimestamps();
    }

    /**
     * Scope para atividades ativas
     */
    public function scopeAtivas($query)
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

    /**
     * Retorna nome completo com tipo de serviço
     */
    public function getNomeCompletoAttribute(): string
    {
        return $this->tipoServico ? "{$this->tipoServico->nome} - {$this->nome}" : $this->nome;
    }
}
