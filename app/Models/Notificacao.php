<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    protected $table = 'notificacoes';

    protected $fillable = [
        'usuario_interno_id',
        'tipo',
        'titulo',
        'mensagem',
        'link',
        'ordem_servico_id',
        'lida',
        'lida_em',
        'prioridade',
    ];

    protected $casts = [
        'lida' => 'boolean',
        'lida_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com usuário interno
     */
    public function usuarioInterno()
    {
        return $this->belongsTo(UsuarioInterno::class);
    }

    /**
     * Relacionamento com ordem de serviço
     */
    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }

    /**
     * Marcar notificação como lida
     */
    public function marcarComoLida()
    {
        $this->update([
            'lida' => true,
            'lida_em' => now(),
        ]);
    }

    /**
     * Scope para notificações não lidas
     */
    public function scopeNaoLidas($query)
    {
        return $query->where('lida', false);
    }

    /**
     * Scope para notificações de um usuário
     */
    public function scopeDoUsuario($query, $usuarioId)
    {
        return $query->where('usuario_interno_id', $usuarioId);
    }

    /**
     * Scope para ordenar por mais recentes
     */
    public function scopeRecentes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Accessor para ícone baseado no tipo
     */
    public function getIconeAttribute()
    {
        return match($this->tipo) {
            'ordem_servico_atribuida' => 'clipboard-check',
            'ordem_servico_prazo' => 'clock',
            'ordem_servico_finalizada' => 'check-circle',
            default => 'bell',
        };
    }

    /**
     * Accessor para cor baseado na prioridade
     */
    public function getCorAttribute()
    {
        return match($this->prioridade) {
            'baixa' => 'gray',
            'normal' => 'blue',
            'alta' => 'orange',
            'urgente' => 'red',
            default => 'blue',
        };
    }
}
