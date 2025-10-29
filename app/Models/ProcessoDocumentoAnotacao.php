<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessoDocumentoAnotacao extends Model
{
    protected $table = 'processo_documento_anotacoes';

    protected $fillable = [
        'processo_documento_id',
        'usuario_id',
        'pagina',
        'tipo',
        'dados',
        'comentario',
    ];

    protected $casts = [
        'dados' => 'array',
        'pagina' => 'integer',
    ];

    /**
     * Relacionamento com o documento do processo
     */
    public function documento(): BelongsTo
    {
        return $this->belongsTo(ProcessoDocumento::class, 'processo_documento_id');
    }

    /**
     * Relacionamento com o usuário que criou a anotação
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_id');
    }
}
