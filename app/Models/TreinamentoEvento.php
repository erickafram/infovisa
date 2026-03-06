<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreinamentoEvento extends Model
{
    use HasFactory;

    protected $table = 'treinamento_eventos';

    protected $fillable = [
        'titulo',
        'descricao',
        'local',
        'data_inicio',
        'data_fim',
        'status',
        'inscricoes_ativas',
        'link_inscricao_token',
        'criado_por',
        'atualizado_por',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'inscricoes_ativas' => 'boolean',
    ];

    public function inscricoes(): HasMany
    {
        return $this->hasMany(TreinamentoInscricao::class, 'treinamento_evento_id');
    }

    public function apresentacoes(): HasMany
    {
        return $this->hasMany(TreinamentoApresentacao::class, 'treinamento_evento_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'criado_por');
    }

    public function atualizador(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'atualizado_por');
    }
}
