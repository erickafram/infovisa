<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoResposta extends Model
{
    protected $table = 'documento_respostas';

    protected $fillable = [
        'documento_digital_id',
        'usuario_externo_id',
        'nome_arquivo',
        'nome_original',
        'caminho',
        'extensao',
        'tamanho',
        'observacoes',
        'status',
        'motivo_rejeicao',
        'avaliado_por',
        'avaliado_em',
        'historico_rejeicao',
    ];

    protected $casts = [
        'avaliado_em' => 'datetime',
        'tamanho' => 'integer',
        'historico_rejeicao' => 'array',
    ];

    /**
     * Relacionamento com documento digital
     */
    public function documentoDigital()
    {
        return $this->belongsTo(DocumentoDigital::class);
    }

    /**
     * Relacionamento com usuário externo que enviou a resposta
     */
    public function usuarioExterno()
    {
        return $this->belongsTo(UsuarioExterno::class);
    }

    /**
     * Relacionamento com usuário interno que avaliou
     */
    public function avaliadoPor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'avaliado_por');
    }

    /**
     * Verifica se a resposta está pendente
     */
    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    /**
     * Verifica se a resposta foi aprovada
     */
    public function isAprovada(): bool
    {
        return $this->status === 'aprovado';
    }

    /**
     * Verifica se a resposta foi rejeitada
     */
    public function isRejeitada(): bool
    {
        return $this->status === 'rejeitado';
    }

    /**
     * Retorna o tamanho formatado (KB, MB)
     */
    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Aprova a resposta
     */
    public function aprovar($usuarioInternoId): void
    {
        $this->update([
            'status' => 'aprovado',
            'avaliado_por' => $usuarioInternoId,
            'avaliado_em' => now(),
        ]);
    }

    /**
     * Rejeita a resposta
     */
    public function rejeitar($usuarioInternoId, $motivo): void
    {
        $this->update([
            'status' => 'rejeitado',
            'motivo_rejeicao' => $motivo,
            'avaliado_por' => $usuarioInternoId,
            'avaliado_em' => now(),
        ]);
    }
}




