<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Processo extends Model
{
    use SoftDeletes;

    protected $table = 'processos';

    protected $fillable = [
        'estabelecimento_id',
        'usuario_id',
        'tipo',
        'ano',
        'numero_sequencial',
        'numero_processo',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'ano' => 'integer',
        'numero_sequencial' => 'integer',
    ];

    /**
     * Tipos de processo disponíveis
     */
    public static function tipos(): array
    {
        return [
            'licenciamento' => 'Licenciamento',
            'analise_rotulagem' => 'Análise de Rotulagem',
            'projeto_arquitetonico' => 'Projeto Arquitetônico',
            'administrativo' => 'Administrativo',
            'descentralizacao' => 'Descentralização',
        ];
    }

    /**
     * Status disponíveis
     */
    public static function statusDisponiveis(): array
    {
        return [
            'aberto' => 'Aberto',
            'em_analise' => 'Em Análise',
            'pendente' => 'Pendente',
            'aprovado' => 'Aprovado',
            'indeferido' => 'Indeferido',
            'arquivado' => 'Arquivado',
        ];
    }

    /**
     * Gera o próximo número de processo para o ano atual
     */
    public static function gerarNumeroProcesso(int $ano = null): array
    {
        $ano = $ano ?? date('Y');
        
        // Busca o último número sequencial do ano com lock para evitar duplicação
        $ultimoProcesso = self::where('ano', $ano)
            ->orderBy('numero_sequencial', 'desc')
            ->lockForUpdate()
            ->first();
        
        $numeroSequencial = $ultimoProcesso ? $ultimoProcesso->numero_sequencial + 1 : 1;
        
        // Formata com 5 dígitos: 2025/00001
        $numeroProcesso = sprintf('%d/%05d', $ano, $numeroSequencial);
        
        return [
            'ano' => $ano,
            'numero_sequencial' => $numeroSequencial,
            'numero_processo' => $numeroProcesso,
        ];
    }

    /**
     * Relacionamento com estabelecimento
     */
    public function estabelecimento()
    {
        return $this->belongsTo(Estabelecimento::class);
    }

    /**
     * Relacionamento com usuário que criou
     */
    public function usuario()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_id');
    }

    /**
     * Relacionamento com tipo de processo
     */
    public function tipoProcesso()
    {
        return $this->belongsTo(TipoProcesso::class, 'tipo', 'codigo');
    }

    /**
     * Relacionamento com documentos
     */
    public function documentos()
    {
        return $this->hasMany(ProcessoDocumento::class);
    }

    /**
     * Accessor para nome do tipo formatado
     */
    public function getTipoNomeAttribute(): string
    {
        // Tenta buscar da tabela tipo_processos
        if ($this->tipoProcesso) {
            return $this->tipoProcesso->nome;
        }
        
        // Fallback para array estático (compatibilidade)
        return self::tipos()[$this->tipo] ?? $this->tipo;
    }

    /**
     * Accessor para nome do status formatado
     */
    public function getStatusNomeAttribute(): string
    {
        return self::statusDisponiveis()[$this->status] ?? $this->status;
    }

    /**
     * Accessor para cor do status (para badges)
     */
    public function getStatusCorAttribute(): string
    {
        return match($this->status) {
            'aberto' => 'blue',
            'em_analise' => 'yellow',
            'pendente' => 'orange',
            'aprovado' => 'green',
            'indeferido' => 'red',
            'arquivado' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Scope para filtrar por estabelecimento
     */
    public function scopeDoEstabelecimento($query, $estabelecimentoId)
    {
        return $query->where('estabelecimento_id', $estabelecimentoId);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para filtrar por status
     */
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
