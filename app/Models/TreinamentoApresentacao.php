<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreinamentoApresentacao extends Model
{
    use HasFactory;

    protected $table = 'treinamento_apresentacoes';

    protected $fillable = [
        'treinamento_evento_id',
        'titulo',
        'descricao',
        'status',
        'criado_por',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(TreinamentoEvento::class, 'treinamento_evento_id');
    }

    public function slides(): HasMany
    {
        return $this->hasMany(TreinamentoSlide::class, 'treinamento_apresentacao_id')->orderBy('ordem');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'criado_por');
    }
}
