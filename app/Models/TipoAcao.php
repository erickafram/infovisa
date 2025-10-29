<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoAcao extends Model
{
    use SoftDeletes;

    protected $table = 'tipo_acoes';

    protected $fillable = [
        'descricao',
        'codigo_procedimento',
        'atividade_sia',
        'competencia',
        'ativo',
    ];

    protected $casts = [
        'atividade_sia' => 'boolean',
        'ativo' => 'boolean',
    ];

    /**
     * Scope para buscar apenas ações ativas
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para filtrar por competência
     */
    public function scopeCompetencia($query, $competencia)
    {
        return $query->where('competencia', $competencia);
    }

    /**
     * Scope para buscar ações do SIA
     */
    public function scopeSia($query)
    {
        return $query->where('atividade_sia', true);
    }

    /**
     * Retorna label formatado da competência
     */
    public function getCompetenciaLabelAttribute()
    {
        return match($this->competencia) {
            'estadual' => 'Estadual',
            'municipal' => 'Municipal',
            'ambos' => 'Ambos',
            default => $this->competencia
        };
    }

    /**
     * Retorna badge HTML para competência
     */
    public function getCompetenciaBadgeAttribute()
    {
        $colors = [
            'estadual' => 'bg-blue-100 text-blue-800',
            'municipal' => 'bg-green-100 text-green-800',
            'ambos' => 'bg-purple-100 text-purple-800',
        ];

        $color = $colors[$this->competencia] ?? 'bg-gray-100 text-gray-800';
        
        return "<span class='px-2 py-1 text-xs font-medium rounded {$color}'>{$this->competencia_label}</span>";
    }
}
