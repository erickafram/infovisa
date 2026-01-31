<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtividadeEquipamentoRadiacao extends Model
{
    use HasFactory;

    protected $table = 'atividades_equipamento_radiacao';

    protected $fillable = [
        'codigo_atividade',
        'descricao_atividade',
        'observacoes',
        'ativo',
        'obrigatorio_processo',
        'criado_por',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'obrigatorio_processo' => 'boolean',
    ];

    /**
     * Relacionamento com o usuário que criou
     */
    public function criadoPor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'criado_por');
    }

    /**
     * Relacionamento com tipos de processo onde é obrigatório
     */
    public function tiposProcesso()
    {
        return $this->belongsToMany(TipoProcesso::class, 'atividade_equipamento_radiacao_tipo_processo', 'atividade_equipamento_radiacao_id', 'tipo_processo_id')
            ->withTimestamps();
    }

    /**
     * Scope para atividades ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Verifica se um código de atividade está na lista de equipamentos obrigatórios
     */
    public static function atividadeExigeEquipamento(string $codigoAtividade): bool
    {
        return self::where('codigo_atividade', $codigoAtividade)
            ->where('ativo', true)
            ->exists();
    }

    /**
     * Verifica se um estabelecimento possui alguma atividade que exige equipamentos
     */
    public static function estabelecimentoExigeEquipamentos(Estabelecimento $estabelecimento): bool
    {
        if (empty($estabelecimento->atividades_exercidas)) {
            return false;
        }

        $codigosAtividades = collect($estabelecimento->atividades_exercidas)
            ->pluck('codigo')
            ->filter()
            ->toArray();

        if (empty($codigosAtividades)) {
            return false;
        }

        return self::whereIn('codigo_atividade', $codigosAtividades)
            ->where('ativo', true)
            ->exists();
    }

    /**
     * Verifica se o estabelecimento precisa cadastrar equipamentos para um tipo de processo específico
     */
    public static function estabelecimentoExigeEquipamentosParaProcesso(Estabelecimento $estabelecimento, string $tipoProcessoCodigo): bool
    {
        if (empty($estabelecimento->atividades_exercidas)) {
            return false;
        }

        $codigosAtividades = collect($estabelecimento->atividades_exercidas)
            ->pluck('codigo')
            ->filter()
            ->toArray();

        if (empty($codigosAtividades)) {
            return false;
        }

        // Busca o tipo de processo pelo código
        $tipoProcesso = TipoProcesso::where('codigo', $tipoProcessoCodigo)->first();
        if (!$tipoProcesso) {
            return false;
        }

        // Verifica se há alguma atividade que exige equipamentos para este tipo de processo
        return self::whereIn('codigo_atividade', $codigosAtividades)
            ->where('ativo', true)
            ->where('obrigatorio_processo', true)
            ->whereHas('tiposProcesso', function ($query) use ($tipoProcesso) {
                $query->where('tipo_processo_id', $tipoProcesso->id);
            })
            ->exists();
    }

    /**
     * Retorna as atividades do estabelecimento que exigem equipamentos
     */
    public static function getAtividadesQueExigemEquipamentos(Estabelecimento $estabelecimento): array
    {
        if (empty($estabelecimento->atividades_exercidas)) {
            return [];
        }

        $codigosAtividades = collect($estabelecimento->atividades_exercidas)
            ->pluck('codigo')
            ->filter()
            ->toArray();

        if (empty($codigosAtividades)) {
            return [];
        }

        $atividadesObrigatorias = self::whereIn('codigo_atividade', $codigosAtividades)
            ->where('ativo', true)
            ->pluck('codigo_atividade')
            ->toArray();

        return collect($estabelecimento->atividades_exercidas)
            ->filter(function ($atividade) use ($atividadesObrigatorias) {
                return in_array($atividade['codigo'] ?? '', $atividadesObrigatorias);
            })
            ->values()
            ->toArray();
    }

    /**
     * Retorna as atividades que exigem equipamentos para um tipo de processo específico
     */
    public static function getAtividadesQueExigemEquipamentosParaProcesso(Estabelecimento $estabelecimento, string $tipoProcessoCodigo): array
    {
        if (empty($estabelecimento->atividades_exercidas)) {
            return [];
        }

        $codigosAtividades = collect($estabelecimento->atividades_exercidas)
            ->pluck('codigo')
            ->filter()
            ->toArray();

        if (empty($codigosAtividades)) {
            return [];
        }

        // Busca o tipo de processo pelo código
        $tipoProcesso = TipoProcesso::where('codigo', $tipoProcessoCodigo)->first();
        if (!$tipoProcesso) {
            return [];
        }

        $atividadesObrigatorias = self::whereIn('codigo_atividade', $codigosAtividades)
            ->where('ativo', true)
            ->where('obrigatorio_processo', true)
            ->whereHas('tiposProcesso', function ($query) use ($tipoProcesso) {
                $query->where('tipo_processo_id', $tipoProcesso->id);
            })
            ->pluck('codigo_atividade')
            ->toArray();

        return collect($estabelecimento->atividades_exercidas)
            ->filter(function ($atividade) use ($atividadesObrigatorias) {
                return in_array($atividade['codigo'] ?? '', $atividadesObrigatorias);
            })
            ->values()
            ->toArray();
    }
}
