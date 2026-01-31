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
     * Normaliza um código CNAE removendo formatação (traços, barras, pontos)
     */
    public static function normalizarCodigo(string $codigo): string
    {
        return preg_replace('/[^0-9]/', '', $codigo);
    }

    /**
     * Verifica se um código de atividade está na lista de equipamentos obrigatórios
     */
    public static function atividadeExigeEquipamento(string $codigoAtividade): bool
    {
        $codigoNormalizado = self::normalizarCodigo($codigoAtividade);
        
        return self::where('ativo', true)
            ->get()
            ->contains(function ($atividade) use ($codigoNormalizado) {
                return self::normalizarCodigo($atividade->codigo_atividade) === $codigoNormalizado;
            });
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
            ->map(fn($codigo) => self::normalizarCodigo($codigo))
            ->toArray();

        if (empty($codigosAtividades)) {
            return false;
        }

        // Busca todas as atividades de equipamento ativas e normaliza os códigos
        $atividadesEquipamento = self::where('ativo', true)->get();
        
        foreach ($atividadesEquipamento as $atividade) {
            $codigoNormalizado = self::normalizarCodigo($atividade->codigo_atividade);
            if (in_array($codigoNormalizado, $codigosAtividades)) {
                return true;
            }
        }
        
        return false;
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
            ->map(fn($codigo) => self::normalizarCodigo($codigo))
            ->toArray();

        if (empty($codigosAtividades)) {
            return false;
        }

        // Busca o tipo de processo pelo código
        $tipoProcesso = TipoProcesso::where('codigo', $tipoProcessoCodigo)->first();
        if (!$tipoProcesso) {
            return false;
        }

        // Busca todas as atividades de equipamento para este tipo de processo
        $atividadesEquipamento = self::where('ativo', true)
            ->where('obrigatorio_processo', true)
            ->whereHas('tiposProcesso', function ($query) use ($tipoProcesso) {
                $query->where('tipo_processo_id', $tipoProcesso->id);
            })
            ->get();
        
        foreach ($atividadesEquipamento as $atividade) {
            $codigoNormalizado = self::normalizarCodigo($atividade->codigo_atividade);
            if (in_array($codigoNormalizado, $codigosAtividades)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Retorna as atividades do estabelecimento que exigem equipamentos
     */
    public static function getAtividadesQueExigemEquipamentos(Estabelecimento $estabelecimento): array
    {
        if (empty($estabelecimento->atividades_exercidas)) {
            return [];
        }

        $codigosAtividadesNormalizados = collect($estabelecimento->atividades_exercidas)
            ->pluck('codigo')
            ->filter()
            ->mapWithKeys(fn($codigo) => [self::normalizarCodigo($codigo) => $codigo])
            ->toArray();

        if (empty($codigosAtividadesNormalizados)) {
            return [];
        }

        // Busca todos os códigos de atividades de equipamento ativos
        $atividadesObrigatoriasNormalizadas = self::where('ativo', true)
            ->get()
            ->map(fn($a) => self::normalizarCodigo($a->codigo_atividade))
            ->toArray();

        return collect($estabelecimento->atividades_exercidas)
            ->filter(function ($atividade) use ($atividadesObrigatoriasNormalizadas) {
                $codigoNormalizado = self::normalizarCodigo($atividade['codigo'] ?? '');
                return in_array($codigoNormalizado, $atividadesObrigatoriasNormalizadas);
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

        $codigosAtividadesNormalizados = collect($estabelecimento->atividades_exercidas)
            ->pluck('codigo')
            ->filter()
            ->map(fn($codigo) => self::normalizarCodigo($codigo))
            ->toArray();

        if (empty($codigosAtividadesNormalizados)) {
            return [];
        }

        // Busca o tipo de processo pelo código
        $tipoProcesso = TipoProcesso::where('codigo', $tipoProcessoCodigo)->first();
        if (!$tipoProcesso) {
            return [];
        }

        $atividadesObrigatoriasNormalizadas = self::where('ativo', true)
            ->where('obrigatorio_processo', true)
            ->whereHas('tiposProcesso', function ($query) use ($tipoProcesso) {
                $query->where('tipo_processo_id', $tipoProcesso->id);
            })
            ->get()
            ->map(fn($a) => self::normalizarCodigo($a->codigo_atividade))
            ->toArray();

        return collect($estabelecimento->atividades_exercidas)
            ->filter(function ($atividade) use ($atividadesObrigatoriasNormalizadas) {
                $codigoNormalizado = self::normalizarCodigo($atividade['codigo'] ?? '');
                return in_array($codigoNormalizado, $atividadesObrigatoriasNormalizadas);
            })
            ->values()
            ->toArray();
    }
}
