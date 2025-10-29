<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoriaPop extends Model
{
    use HasFactory;

    protected $table = 'categorias_pops';

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'cor',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * Relacionamento com documentos POPs
     */
    public function documentos()
    {
        return $this->belongsToMany(DocumentoPop::class, 'categoria_documento_pop', 'categoria_pop_id', 'documento_pop_id')
            ->withTimestamps();
    }

    /**
     * Gera slug automaticamente ao criar
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nome);
            }
        });

        static::updating(function ($categoria) {
            if ($categoria->isDirty('nome') && empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nome);
            }
        });
    }

    /**
     * Scope para categorias ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenação
     */
    public function scopeOrdenadas($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    /**
     * Conta documentos da categoria
     */
    public function getDocumentosCountAttribute()
    {
        return $this->documentos()->count();
    }
}
