<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreinamentoInscricao extends Model
{
    use HasFactory;

    protected $table = 'treinamento_inscricoes';

    protected $fillable = [
        'treinamento_evento_id',
        'nome',
        'email',
        'telefone',
        'instituicao',
        'cargo',
        'cidade',
        'observacoes',
        'token',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(TreinamentoEvento::class, 'treinamento_evento_id');
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(TreinamentoPerguntaResposta::class, 'treinamento_inscricao_id');
    }
}
