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
        'documento_comum',
        'escopo_competencia',
        'tipo_setor',
        'observacao_publica',
        'observacao_privada',
        'prazo_validade_dias',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'documento_comum' => 'boolean',
        'ordem' => 'integer',
        'prazo_validade_dias' => 'integer',
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

    /**
     * Scope para documentos comuns
     */
    public function scopeDocumentosComuns($query)
    {
        return $query->where('documento_comum', true);
    }

    /**
     * Scope para documentos por escopo de competência
     */
    public function scopePorEscopoCompetencia($query, $escopo)
    {
        return $query->where(function($q) use ($escopo) {
            $q->where('escopo_competencia', 'todos')
              ->orWhere('escopo_competencia', $escopo);
        });
    }

    /**
     * Scope para documentos por tipo de setor
     */
    public function scopePorTipoSetor($query, $tipoSetor)
    {
        return $query->where(function($q) use ($tipoSetor) {
            $q->where('tipo_setor', 'todos')
              ->orWhere('tipo_setor', $tipoSetor);
        });
    }

    /**
     * Verifica se é documento comum
     */
    public function isDocumentoComum(): bool
    {
        return $this->documento_comum === true;
    }

    /**
     * Verifica se se aplica ao escopo de competência
     */
    public function aplicaAoEscopo(string $escopo): bool
    {
        return $this->escopo_competencia === 'todos' || $this->escopo_competencia === $escopo;
    }

    /**
     * Verifica se se aplica ao tipo de setor
     */
    public function aplicaAoTipoSetor(string $tipoSetor): bool
    {
        return $this->tipo_setor === 'todos' || $this->tipo_setor === $tipoSetor;
    }

    /**
     * Retorna a observação apropriada baseada no tipo de setor
     */
    public function getObservacaoParaTipoSetor(string $tipoSetor): ?string
    {
        if ($tipoSetor === 'publico' && $this->observacao_publica) {
            return $this->observacao_publica;
        }
        
        if ($tipoSetor === 'privado' && $this->observacao_privada) {
            return $this->observacao_privada;
        }
        
        return $this->descricao;
    }

    /**
     * Retorna o label do escopo de competência
     */
    public function getEscopoCompetenciaLabelAttribute(): string
    {
        return match($this->escopo_competencia) {
            'estadual' => 'Estadual',
            'municipal' => 'Municipal',
            'todos' => 'Todos',
            default => 'Todos'
        };
    }

    /**
     * Retorna o label do tipo de setor
     */
    public function getTipoSetorLabelAttribute(): string
    {
        return match($this->tipo_setor) {
            'publico' => 'Público',
            'privado' => 'Privado',
            'todos' => 'Todos',
            default => 'Todos'
        };
    }

    /**
     * Busca documentos comuns aplicáveis para um estabelecimento
     */
    public static function buscarDocumentosComuns(string $escopoCompetencia, string $tipoSetor)
    {
        return self::ativo()
            ->documentosComuns()
            ->porEscopoCompetencia($escopoCompetencia)
            ->porTipoSetor($tipoSetor)
            ->ordenado()
            ->get();
    }
}
