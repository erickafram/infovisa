<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PesquisaSatisfacao extends Model
{
    use HasFactory;

    protected $table = 'pesquisas_satisfacao';

    protected $fillable = [
        'titulo',
        'descricao',
        'tipo_publico',
        'tipo_setores_ids',
        'ativo',
        'slug',
    ];

    protected $casts = [
        'tipo_setores_ids' => 'array',
        'ativo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->titulo) . '-' . Str::random(8);
            }
        });
    }

    public function perguntas()
    {
        return $this->hasMany(PesquisaSatisfacaoPergunta::class, 'pesquisa_id')->orderBy('ordem');
    }

    public function respostas()
    {
        return $this->hasMany(PesquisaSatisfacaoResposta::class, 'pesquisa_id');
    }

    public function getTipoPublicoLabelAttribute(): string
    {
        return match ($this->tipo_publico) {
            'interno' => 'Usuário Interno (Técnico)',
            'externo' => 'Usuário Externo (Empresa)',
            default   => $this->tipo_publico,
        };
    }

    public function getLinkRespostaAttribute(): string
    {
        return url('/pesquisa/' . $this->slug);
    }
}
