<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesquisaSatisfacaoPergunta extends Model
{
    use HasFactory;

    protected $table = 'pesquisas_satisfacao_perguntas';

    protected $fillable = [
        'pesquisa_id',
        'texto',
        'tipo',
        'obrigatoria',
        'ordem',
    ];

    protected $casts = [
        'obrigatoria' => 'boolean',
        'ordem'       => 'integer',
    ];

    public function pesquisa()
    {
        return $this->belongsTo(PesquisaSatisfacao::class, 'pesquisa_id');
    }

    public function opcoes()
    {
        return $this->hasMany(PesquisaSatisfacaoOpcao::class, 'pergunta_id')->orderBy('ordem');
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'escala_1_5'       => 'Nota de 1 a 5',
            'multipla_escolha' => 'Múltipla Escolha',
            'texto_livre'      => 'Texto Livre',
            default            => $this->tipo,
        };
    }
}
