<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoVisualizacao extends Model
{
    protected $table = 'documento_visualizacoes';

    protected $fillable = [
        'documento_digital_id',
        'usuario_externo_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Relacionamento com documento digital
     */
    public function documentoDigital()
    {
        return $this->belongsTo(DocumentoDigital::class);
    }

    /**
     * Relacionamento com usuário externo
     */
    public function usuarioExterno()
    {
        return $this->belongsTo(UsuarioExterno::class);
    }
}
