<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreinamentoSlide extends Model
{
    use HasFactory;

    protected $table = 'treinamento_slides';

    protected $fillable = [
        'treinamento_apresentacao_id',
        'titulo',
        'conteudo',
        'ordem',
    ];

    public function apresentacao(): BelongsTo
    {
        return $this->belongsTo(TreinamentoApresentacao::class, 'treinamento_apresentacao_id');
    }

    public function perguntas(): HasMany
    {
        return $this->hasMany(TreinamentoPergunta::class, 'treinamento_slide_id');
    }
}
