<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstabelecimentoHistorico extends Model
{
    protected $table = 'estabelecimento_historicos';

    protected $fillable = [
        'estabelecimento_id',
        'usuario_id',
        'acao',
        'status_anterior',
        'status_novo',
        'observacao',
        'dados_alterados',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'dados_alterados' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com estabelecimento
     */
    public function estabelecimento()
    {
        return $this->belongsTo(Estabelecimento::class);
    }

    /**
     * Relacionamento com usuário interno que realizou a ação
     */
    public function usuario()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_id');
    }

    /**
     * Scope para filtrar por estabelecimento
     */
    public function scopeDoEstabelecimento($query, $estabelecimentoId)
    {
        return $query->where('estabelecimento_id', $estabelecimentoId);
    }

    /**
     * Scope para filtrar por ação
     */
    public function scopePorAcao($query, $acao)
    {
        return $query->where('acao', $acao);
    }

    /**
     * Registra uma ação no histórico
     */
    public static function registrar(
        int $estabelecimentoId,
        string $acao,
        ?string $statusAnterior = null,
        ?string $statusNovo = null,
        ?string $observacao = null,
        ?array $dadosAlterados = null
    ) {
        return self::create([
            'estabelecimento_id' => $estabelecimentoId,
            'usuario_id' => auth('interno')->id(),
            'acao' => $acao,
            'status_anterior' => $statusAnterior,
            'status_novo' => $statusNovo,
            'observacao' => $observacao,
            'dados_alterados' => $dadosAlterados,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
