<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoPop extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documentos_pops';

    protected $fillable = [
        'titulo',
        'descricao',
        'arquivo_nome',
        'arquivo_path',
        'arquivo_mime_type',
        'arquivo_tamanho',
        'disponivel_ia',
        'conteudo_extraido',
        'indexado_em',
        'criado_por',
        'atualizado_por',
    ];

    protected $casts = [
        'disponivel_ia' => 'boolean',
        'indexado_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário que criou
     */
    public function criador()
    {
        return $this->belongsTo(UsuarioInterno::class, 'criado_por');
    }

    /**
     * Relacionamento com o usuário que atualizou
     */
    public function atualizador()
    {
        return $this->belongsTo(UsuarioInterno::class, 'atualizado_por');
    }

    /**
     * Relacionamento com categorias
     */
    public function categorias()
    {
        return $this->belongsToMany(CategoriaPop::class, 'categoria_documento_pop', 'documento_pop_id', 'categoria_pop_id')
            ->withTimestamps();
    }

    /**
     * Verifica se o documento está indexado
     */
    public function isIndexado(): bool
    {
        return !is_null($this->indexado_em) && !is_null($this->conteudo_extraido);
    }

    /**
     * Formata o tamanho do arquivo
     */
    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->arquivo_tamanho;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Retorna a extensão do arquivo
     */
    public function getExtensaoAttribute(): string
    {
        return pathinfo($this->arquivo_nome, PATHINFO_EXTENSION);
    }
}
