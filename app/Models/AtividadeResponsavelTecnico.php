<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtividadeResponsavelTecnico extends Model
{
    use HasFactory;

    protected $table = 'atividades_responsavel_tecnico';

    protected $fillable = [
        'codigo_atividade',
        'descricao_atividade',
        'observacoes',
        'ativo',
        'criado_por',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function criadoPor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'criado_por');
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public static function normalizarCodigo(string $codigo): string
    {
        return preg_replace('/[^0-9]/', '', $codigo);
    }

    public static function atividadeExigeResponsavelTecnico(string $codigoAtividade): bool
    {
        $codigoNormalizado = self::normalizarCodigo($codigoAtividade);

        return self::where('ativo', true)
            ->get()
            ->contains(function ($atividade) use ($codigoNormalizado) {
                return self::normalizarCodigo($atividade->codigo_atividade) === $codigoNormalizado;
            });
    }

    public static function estabelecimentoExigeResponsavelTecnico(Estabelecimento $estabelecimento): bool
    {
        if (empty($estabelecimento->atividades_exercidas)) {
            return false;
        }

        $codigosAtividades = collect($estabelecimento->atividades_exercidas)
            ->map(function ($atividade) {
                if (is_array($atividade)) {
                    return $atividade['codigo'] ?? null;
                }

                return is_string($atividade) ? $atividade : null;
            })
            ->filter()
            ->map(fn($codigo) => self::normalizarCodigo($codigo))
            ->toArray();

        if (empty($codigosAtividades)) {
            return false;
        }

        $atividadesConfiguradas = self::where('ativo', true)->get();

        foreach ($atividadesConfiguradas as $atividade) {
            if (in_array(self::normalizarCodigo($atividade->codigo_atividade), $codigosAtividades)) {
                return true;
            }
        }

        return false;
    }
}
