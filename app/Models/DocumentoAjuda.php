<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
        'tamanho' => 'integer',
    ];

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
     * Scope para documentos de um tipo de processo específico
     */
    public function scopeParaTipoProcesso($query, $tipoProcessoCodigo)
    {
        return $query->whereHas('tiposProcesso', function ($q) use ($tipoProcessoCodigo) {
            $q->where('codigo', $tipoProcessoCodigo);
        });
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
