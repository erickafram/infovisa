<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoSetor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_setores';

    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'niveis_acesso',
        'ativo',
    ];

    protected $casts = [
        'niveis_acesso' => 'array',
        'ativo' => 'boolean',
    ];

    /**
     * Verifica se o setor está disponível para um determinado nível de acesso
     */
    public function disponivelParaNivel(string $nivelAcesso): bool
    {
        if (!$this->niveis_acesso || empty($this->niveis_acesso)) {
            return true; // Se não há restrição, disponível para todos
        }

        return in_array($nivelAcesso, $this->niveis_acesso);
    }

    /**
     * Retorna os setores disponíveis para um nível de acesso específico
     */
    public static function paraNivelAcesso(string $nivelAcesso)
    {
        return static::where('ativo', true)
            ->get()
            ->filter(function ($setor) use ($nivelAcesso) {
                return $setor->disponivelParaNivel($nivelAcesso);
            });
    }

    /**
     * Retorna os labels dos níveis de acesso associados
     */
    public function getNiveisAcessoLabelsAttribute(): array
    {
        if (!$this->niveis_acesso || empty($this->niveis_acesso)) {
            return ['Todos os níveis'];
        }

        return array_map(function ($nivel) {
            return \App\Enums\NivelAcesso::from($nivel)->label();
        }, $this->niveis_acesso);
    }
}
