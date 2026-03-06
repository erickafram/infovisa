<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreinamentoPergunta extends Model
{
    use HasFactory;

    protected $table = 'treinamento_perguntas';

    protected $fillable = [
        'treinamento_slide_id',
        'enunciado',
        'token',
        'ativa',
    ];

    protected $casts = [
        'ativa' => 'boolean',
    ];

    public function slide(): BelongsTo
    {
        return $this->belongsTo(TreinamentoSlide::class, 'treinamento_slide_id');
    }

    public function opcoes(): HasMany
    {
        return $this->hasMany(TreinamentoPerguntaOpcao::class, 'treinamento_pergunta_id')->orderBy('ordem');
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(TreinamentoPerguntaResposta::class, 'treinamento_pergunta_id');
    }
}
