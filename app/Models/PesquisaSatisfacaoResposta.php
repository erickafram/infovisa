<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesquisaSatisfacaoResposta extends Model
{
    use HasFactory;

    protected $table = 'pesquisas_satisfacao_respostas';

    protected $fillable = [
        'pesquisa_id',
        'ordem_servico_id',
        'estabelecimento_id',
        'usuario_interno_id',
        'usuario_externo_id',
        'tipo_respondente',
        'respondente_nome',
        'respondente_email',
        'ip_address',
        'token',
        'respostas',
    ];

    protected $casts = [
        'respostas' => 'array',
    ];

    public function pesquisa()
    {
        return $this->belongsTo(PesquisaSatisfacao::class, 'pesquisa_id');
    }

    public function ordemServico()
    {
        return $this->belongsTo(\App\Models\OrdemServico::class, 'ordem_servico_id');
    }

    public function estabelecimento()
    {
        return $this->belongsTo(\App\Models\Estabelecimento::class, 'estabelecimento_id');
    }

    public function usuarioInterno()
    {
        return $this->belongsTo(\App\Models\UsuarioInterno::class, 'usuario_interno_id');
    }

    public function usuarioExterno()
    {
        return $this->belongsTo(\App\Models\UsuarioExterno::class, 'usuario_externo_id');
    }

    public function getNomeRespondenteAttribute(): string
    {
        if ($this->usuario_interno_id) {
            return $this->usuarioInterno->nome ?? $this->respondente_nome ?? 'N/D';
        }
        if ($this->usuario_externo_id) {
            return $this->usuarioExterno->nome ?? $this->respondente_nome ?? 'N/D';
        }
        return $this->respondente_nome ?? 'Anônimo';
    }
}
