<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreinamentoPerguntaOpcao extends Model
{
    use HasFactory;

    protected $table = 'treinamento_pergunta_opcoes';

    protected $fillable = [
        'treinamento_pergunta_id',
        'texto',
        'ordem',
    ];

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(TreinamentoPergunta::class, 'treinamento_pergunta_id');
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(TreinamentoPerguntaResposta::class, 'treinamento_pergunta_opcao_id');
    }
}
