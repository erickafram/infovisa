<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesquisaSatisfacaoOpcao extends Model
{
    use HasFactory;

    protected $table = 'pesquisas_satisfacao_opcoes';

    protected $fillable = [
        'pergunta_id',
        'texto',
        'ordem',
    ];

    protected $casts = [
        'ordem' => 'integer',
    ];

    public function pergunta()
    {
        return $this->belongsTo(PesquisaSatisfacaoPergunta::class, 'pergunta_id');
    }
}
