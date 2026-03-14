<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DocumentoAjuda extends Model
{
    protected $table = 'documentos_ajuda';

    protected $fillable = [
        'titulo',
        'descricao',
        'arquivo',
        'nome_original',
        'tamanho',
        'ativo',
        'ordem',
        'escopo_competencia',
        'municipio_id',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
        'tamanho' => 'integer',
        'municipio_id' => 'integer',
    ];

    /**
     * Relacionamento com município
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Relacionamento com tipos de processo
     */
    public function tiposProcesso(): BelongsToMany
    {
        return $this->belongsToMany(TipoProcesso::class, 'documento_ajuda_tipo_processo')
            ->withTimestamps();
    }

    /**
     * Scope para documentos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('titulo');
    }

    /**
     * Scope para documentos genéricos exibidos fora do contexto de processo
     */
    public function scopeGenericosGlobais($query)
    {
        return $query->where('escopo_competencia', 'todos')
            ->whereNull('municipio_id');
    }

    /**
     * Scope para documentos de um tipo de processo específico
     */
    public function scopeParaTipoProcesso($query, $tipoProcessoCodigo)
    {
        return $query->whereHas('tiposProcesso', function ($q) use ($tipoProcessoCodigo) {
            $q->where('codigo', $tipoProcessoCodigo);
        });
    }

    /**
     * Scope para documentos visíveis no contexto de um processo específico
     */
    public function scopeVisiveisParaProcesso($query, Processo $processo)
    {
        $estabelecimento = $processo->estabelecimento;
        $tipoProcesso = $processo->tipoProcesso;

        $escopoCompetencia = $tipoProcesso
            ? $tipoProcesso->resolverEscopoCompetencia($estabelecimento)
            : ($estabelecimento && $estabelecimento->isCompetenciaEstadual() ? 'estadual' : 'municipal');

        return $query
            ->where(function ($q) use ($escopoCompetencia) {
                $q->where('escopo_competencia', 'todos')
                    ->orWhere('escopo_competencia', $escopoCompetencia);
            })
            ->where(function ($q) use ($escopoCompetencia, $estabelecimento) {
                if ($escopoCompetencia === 'municipal' && $estabelecimento?->municipio_id) {
                    $q->whereNull('municipio_id')
                        ->orWhere('municipio_id', $estabelecimento->municipio_id);

                    return;
                }

                $q->whereNull('municipio_id');
            });
    }

    public function getEscopoCompetenciaLabelAttribute(): string
    {
        return match ($this->escopo_competencia) {
            'estadual' => 'Estadual',
            'municipal' => 'Municipal',
            default => 'Todos',
        };
    }

    /**
     * Retorna o tamanho formatado
     */
    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Retorna o caminho completo do arquivo
     */
    public function getCaminhoCompletoAttribute(): string
    {
        return storage_path('app/' . $this->arquivo);
    }
}
