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
        'usuario_externo_id',
        'aberto_por_externo',
        'tipo',
        'ano',
        'numero_sequencial',
        'numero_processo',
        'status',
        'observacoes',
        'motivo_arquivamento',
        'data_arquivamento',
        'usuario_arquivamento_id',
        'motivo_parada',
        'data_parada',
        'usuario_parada_id',
    ];

    protected $casts = [
        'ano' => 'integer',
        'numero_sequencial' => 'integer',
        'data_arquivamento' => 'datetime',
        'data_parada' => 'datetime',
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
            'parado' => 'Parado',
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
     * Relacionamento com usuário interno que criou
     */
    public function usuario()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_id');
    }

    /**
     * Relacionamento com usuário externo que criou
     */
    public function usuarioExterno()
    {
        return $this->belongsTo(UsuarioExterno::class, 'usuario_externo_id');
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
     * Relacionamento com pastas do processo
     */
    public function pastas()
    {
        return $this->hasMany(ProcessoPasta::class);
    }

    /**
     * Relacionamento com acompanhamentos
     */
    public function acompanhamentos()
    {
        return $this->hasMany(ProcessoAcompanhamento::class);
    }

    /**
     * Relacionamento com usuários que acompanham
     */
    public function usuariosAcompanhando()
    {
        return $this->belongsToMany(UsuarioInterno::class, 'processo_acompanhamentos', 'processo_id', 'usuario_interno_id')
            ->withTimestamps();
    }

    /**
     * Relacionamento com eventos do processo (histórico)
     */
    public function eventos()
    {
        return $this->hasMany(ProcessoEvento::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relacionamento com usuário que arquivou o processo
     */
    public function usuarioArquivamento()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_arquivamento_id');
    }

    /**
     * Relacionamento com usuário que parou o processo
     */
    public function usuarioParada()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_parada_id');
    }

    /**
     * Relacionamento com designações do processo
     */
    public function designacoes()
    {
        return $this->hasMany(ProcessoDesignacao::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relacionamento com designações pendentes
     */
    public function designacoesPendentes()
    {
        return $this->hasMany(ProcessoDesignacao::class)->where('status', 'pendente')->orderBy('created_at', 'desc');
    }

    /**
     * Relacionamento com alertas do processo
     */
    public function alertas()
    {
        return $this->hasMany(ProcessoAlerta::class)->orderBy('data_alerta', 'asc');
    }

    /**
     * Relacionamento com alertas pendentes
     */
    public function alertasPendentes()
    {
        return $this->hasMany(ProcessoAlerta::class)->where('status', 'pendente')->orderBy('data_alerta', 'asc');
    }

    /**
     * Verifica se um usuário está acompanhando o processo
     */
    public function estaAcompanhadoPor($usuarioId): bool
    {
        return $this->acompanhamentos()
            ->where('usuario_interno_id', $usuarioId)
            ->exists();
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
            'parado' => 'red',
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
