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
        'setor_atual',
        'responsavel_atual_id',
        'responsavel_desde',
        'prazo_atribuicao',
        'responsavel_ciente_em',
        'motivo_atribuicao',
        'setor_antes_arquivar',
        'responsavel_antes_arquivar_id',
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
        'responsavel_desde' => 'datetime',
        'prazo_atribuicao' => 'date',
        'responsavel_ciente_em' => 'datetime',
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
            'parado' => 'Parado',
            'arquivado' => 'Arquivado',
        ];
    }

    /**
     * Gera o próximo número de processo para o ano atual
     * IMPORTANTE: Esta função DEVE ser chamada dentro de uma DB::transaction()
     */
    public static function gerarNumeroProcesso(int $ano = null): array
    {
        $ano = $ano ?? date('Y');
        
        // Busca o último número sequencial do ano com lock para evitar duplicação
        // O lockForUpdate() garante que nenhuma outra transação leia este registro até o commit
        $ultimoProcesso = self::withTrashed()
            ->where('ano', $ano)
            ->orderBy('numero_sequencial', 'desc')
            ->lockForUpdate()
            ->first();
        
        $numeroSequencial = $ultimoProcesso ? $ultimoProcesso->numero_sequencial + 1 : 1;
        
        // Formata com 5 dígitos: 2025/00001
        $numeroProcesso = sprintf('%d/%05d', $ano, $numeroSequencial);
        
        // Verifica se o número já existe (segurança extra)
        $tentativas = 0;
        while (self::withTrashed()->where('numero_processo', $numeroProcesso)->exists() && $tentativas < 100) {
            $numeroSequencial++;
            $numeroProcesso = sprintf('%d/%05d', $ano, $numeroSequencial);
            $tentativas++;
        }
        
        if ($tentativas >= 100) {
            throw new \Exception('Não foi possível gerar um número de processo único após 100 tentativas.');
        }
        
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
     * Relacionamento com responsável atual do processo
     */
    public function responsavelAtual()
    {
        return $this->belongsTo(UsuarioInterno::class, 'responsavel_atual_id');
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

    /**
     * Scope para filtrar processos do setor do usuário
     */
    public function scopeDoMeuSetor($query, $setor)
    {
        return $query->where('setor_atual', $setor);
    }

    /**
     * Scope para filtrar processos sob minha responsabilidade
     */
    public function scopeMeusProcessos($query, $usuarioId)
    {
        return $query->where('responsavel_atual_id', $usuarioId);
    }

    /**
     * Atribui o processo a um setor e/ou responsável
     */
    public function atribuirPara($setor = null, $responsavelId = null): void
    {
        $this->update([
            'setor_atual' => $setor,
            'responsavel_atual_id' => $responsavelId,
            'responsavel_desde' => now(),
        ]);
    }

    /**
     * Retorna texto formatado de quem está com o processo
     */
    public function getComQuemAttribute(): string
    {
        $partes = [];
        
        if ($this->setor_atual) {
            $partes[] = $this->setor_atual_nome ?? $this->setor_atual;
        }
        
        if ($this->responsavelAtual) {
            $partes[] = $this->responsavelAtual->nome;
        }
        
        if (empty($partes)) {
            return 'Não atribuído';
        }
        
        return implode(' - ', $partes);
    }

    /**
     * Retorna o nome do setor atual (busca do TipoSetor)
     */
    public function getSetorAtualNomeAttribute(): ?string
    {
        if (!$this->setor_atual) {
            return null;
        }
        
        $tipoSetor = \App\Models\TipoSetor::where('codigo', $this->setor_atual)->first();
        return $tipoSetor ? $tipoSetor->nome : $this->setor_atual;
    }

    /**
     * Verifica se o processo está com determinado setor
     */
    public function estaComSetor($setor): bool
    {
        return $this->setor_atual === $setor;
    }

    /**
     * Verifica se o processo está com determinado usuário
     */
    public function estaComUsuario($usuarioId): bool
    {
        return $this->responsavel_atual_id === $usuarioId;
    }
}
