<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessoPasta extends Model
{
    use HasFactory;

    protected $fillable = [
        'processo_id',
        'nome',
        'descricao',
        'cor',
        'ordem',
    ];

    /**
     * Relacionamento com Processo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    /**
     * Relacionamento com Documentos (arquivos)
     */
    public function documentos()
    {
        return $this->hasMany(ProcessoDocumento::class, 'pasta_id');
    }

    /**
     * Relacionamento com Documentos Digitais
     */
    public function documentosDigitais()
    {
        return $this->hasMany(DocumentoDigital::class, 'pasta_id');
    }

    /**
     * Conta total de itens na pasta
     */
    public function getTotalItensAttribute()
    {
        return $this->documentos()->count() + $this->documentosDigitais()->count();
    }
}
