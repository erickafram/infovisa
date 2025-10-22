<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessoDocumento extends Model
{
    protected $fillable = [
        'processo_id',
        'usuario_id',
        'tipo_usuario',
        'nome_arquivo',
        'nome_original',
        'caminho',
        'extensao',
        'tamanho',
        'tipo_documento',
        'observacoes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_id');
    }

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

    public function getIconeAttribute(): string
    {
        return match($this->extensao) {
            'pdf' => '📄',
            'doc', 'docx' => '📝',
            'xls', 'xlsx' => '📊',
            'jpg', 'jpeg', 'png', 'gif' => '🖼️',
            'zip', 'rar' => '📦',
            default => '📎'
        };
    }
}
