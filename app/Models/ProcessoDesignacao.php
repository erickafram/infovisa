<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessoDesignacao extends Model
{
    use SoftDeletes;

    protected $table = 'processo_designacoes';

    protected $fillable = [
        'processo_id',
        'usuario_designado_id',
        'setor_designado',
        'usuario_designador_id',
        'descricao_tarefa',
        'data_limite',
        'status',
        'observacoes_conclusao',
        'concluida_em',
    ];

    protected $casts = [
        'data_limite' => 'date',
        'concluida_em' => 'datetime',
    ];

    /**
     * Relacionamento com o processo
     */
    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    /**
     * Relacionamento com o usuário designado
     */
    public function usuarioDesignado(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_designado_id');
    }

    /**
     * Relacionamento com o usuário que fez a designação
     */
    public function usuarioDesignador(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_designador_id');
    }

    /**
     * Verifica se a designação está atrasada
     */
    public function isAtrasada(): bool
    {
        if (!$this->data_limite || $this->status !== 'pendente') {
            return false;
        }

        return $this->data_limite->isPast();
    }

    /**
     * Verifica se está próximo do prazo (3 dias ou menos)
     */
    public function isProximoDoPrazo(): bool
    {
        if (!$this->data_limite || $this->status !== 'pendente') {
            return false;
        }

        return $this->data_limite->diffInDays(now()) <= 3 && !$this->isAtrasada();
    }

    /**
     * Scope para designações pendentes
     */
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    /**
     * Scope para designações de um usuário específico
     */
    public function scopeDoUsuario($query, $usuarioId)
    {
        return $query->where('usuario_designado_id', $usuarioId);
    }
}
