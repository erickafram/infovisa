<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreinamentoPerguntaResposta extends Model
{
    use HasFactory;

    protected $table = 'treinamento_pergunta_respostas';

    protected $fillable = [
        'treinamento_pergunta_id',
        'treinamento_pergunta_opcao_id',
        'treinamento_inscricao_id',
        'token_sessao',
        'participante_nome',
        'participante_email',
        'participante_telefone',
    ];

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(TreinamentoPergunta::class, 'treinamento_pergunta_id');
    }

    public function opcao(): BelongsTo
    {
        return $this->belongsTo(TreinamentoPerguntaOpcao::class, 'treinamento_pergunta_opcao_id');
    }

    public function inscricao(): BelongsTo
    {
        return $this->belongsTo(TreinamentoInscricao::class, 'treinamento_inscricao_id');
    }
}
