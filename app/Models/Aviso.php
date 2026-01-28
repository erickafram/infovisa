<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\NivelAcesso;

class Aviso extends Model
{
    protected $fillable = [
        'titulo',
        'mensagem',
        'link',
        'tipo',
        'niveis_acesso',
        'data_expiracao',
        'ativo',
        'criado_por',
    ];

    protected $casts = [
        'niveis_acesso' => 'array',
        'data_expiracao' => 'date',
        'ativo' => 'boolean',
    ];

    public function criador(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'criado_por');
    }

    /**
     * Scope para avisos ativos e não expirados
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)
            ->where(function ($q) {
                $q->whereNull('data_expiracao')
                  ->orWhere('data_expiracao', '>=', now()->toDateString());
            });
    }

    /**
     * Scope para avisos visíveis para um nível de acesso específico
     */
    public function scopeParaNivel($query, string $nivel)
    {
        return $query->whereJsonContains('niveis_acesso', $nivel);
    }

    /**
     * Verifica se o aviso está expirado
     */
    public function isExpirado(): bool
    {
        return $this->data_expiracao && $this->data_expiracao->lt(now()->startOfDay());
    }

    /**
     * Retorna a cor do badge baseado no tipo
     */
    public function getTipoColorAttribute(): string
    {
        return match($this->tipo) {
            'urgente' => 'bg-red-100 text-red-700 border-red-300',
            'aviso' => 'bg-amber-100 text-amber-700 border-amber-300',
            default => 'bg-blue-100 text-blue-700 border-blue-300',
        };
    }

    /**
     * Retorna o ícone baseado no tipo
     */
    public function getTipoIconeAttribute(): string
    {
        return match($this->tipo) {
            'urgente' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            'aviso' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    /**
     * Retorna os labels dos níveis de acesso
     */
    public function getNiveisLabelsAttribute(): array
    {
        return collect($this->niveis_acesso)->map(function ($nivel) {
            return NivelAcesso::tryFrom($nivel)?->label() ?? $nivel;
        })->toArray();
    }
}
