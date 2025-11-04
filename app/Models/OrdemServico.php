<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdemServico extends Model
{
    use SoftDeletes;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'numero',
        'estabelecimento_id',
        'processo_id',
        'tipos_acao_ids',
        'acoes_executadas_ids',
        'tecnicos_ids',
        'municipio_id',
        'observacoes',
        'documento_anexo_path',
        'documento_anexo_nome',
        'data_abertura',
        'data_inicio',
        'data_fim',
        'data_conclusao',
        'status',
        'competencia',
        'atividades_realizadas',
        'observacoes_finalizacao',
        'finalizada_por',
        'finalizada_em',
        'motivo_cancelamento',
        'cancelada_em',
        'cancelada_por',
    ];

    protected $casts = [
        'data_abertura' => 'date',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'data_conclusao' => 'date',
        'finalizada_em' => 'datetime',
        'cancelada_em' => 'datetime',
        'tipos_acao_ids' => 'array',
        'acoes_executadas_ids' => 'array',
        'tecnicos_ids' => 'array',
    ];

    /**
     * Relacionamento com Estabelecimento
     */
    public function estabelecimento()
    {
        return $this->belongsTo(Estabelecimento::class);
    }

    /**
     * Relacionamento com Processo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    /**
     * Relacionamento com Tipos de Ação (múltiplos)
     */
    public function tiposAcao()
    {
        if (!$this->tipos_acao_ids) {
            return collect([]);
        }
        return TipoAcao::whereIn('id', $this->tipos_acao_ids)->get();
    }

    /**
     * Relacionamento com Ações Executadas (múltiplos)
     */
    public function acoesExecutadas()
    {
        if (!$this->acoes_executadas_ids) {
            return collect([]);
        }
        return TipoAcao::whereIn('id', $this->acoes_executadas_ids)->get();
    }

    /**
     * Relacionamento com Técnicos (múltiplos)
     */
    public function tecnicos()
    {
        if (!$this->tecnicos_ids) {
            return collect([]);
        }
        return UsuarioInterno::whereIn('id', $this->tecnicos_ids)->get();
    }

    /**
     * Relacionamento com Município
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Relacionamento com usuário que finalizou
     */
    public function finalizadaPor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'finalizada_por');
    }

    /**
     * Scope para filtrar por competência
     */
    public function scopeCompetencia($query, $competencia)
    {
        return $query->where('competencia', $competencia);
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por município
     */
    public function scopeMunicipio($query, $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }

    /**
     * Scope para ordens em andamento
     */
    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    /**
     * Scope para ordens finalizadas
     */
    public function scopeFinalizadas($query)
    {
        return $query->where('status', 'finalizada');
    }

    /**
     * Scope para ordens canceladas
     */
    public function scopeCanceladas($query)
    {
        return $query->where('status', 'cancelada');
    }

    /**
     * Gera número único para a OS no formato 000013.2025
     * O sequencial continua mesmo quando o ano muda
     */
    public static function gerarNumero()
    {
        $ano = date('Y');
        
        // Busca a última OS criada (independente do ano)
        $ultimaOS = self::orderBy('id', 'desc')->first();
        
        if ($ultimaOS) {
            // Extrai o número sequencial do formato 000013.2025
            $partes = explode('.', $ultimaOS->numero);
            $sequencial = (int) $partes[0] + 1;
        } else {
            $sequencial = 1;
        }
        
        // Retorna no formato 000013.2025
        return str_pad($sequencial, 6, '0', STR_PAD_LEFT) . '.' . $ano;
    }

    /**
     * Retorna label formatado do status
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'em_andamento' => 'Em Andamento',
            'finalizada' => 'Finalizada',
            'cancelada' => 'Cancelada',
            default => $this->status
        };
    }

    /**
     * Retorna badge HTML para status
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'em_andamento' => 'bg-blue-100 text-blue-800',
            'finalizada' => 'bg-green-100 text-green-800',
            'cancelada' => 'bg-red-100 text-red-800',
        ];

        $color = $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
        
        return "<span class='px-2 py-1 text-xs font-medium rounded {$color}'>{$this->status_label}</span>";
    }


    /**
     * Retorna label formatado da competência
     */
    public function getCompetenciaLabelAttribute()
    {
        return match($this->competencia) {
            'estadual' => 'Estadual',
            'municipal' => 'Municipal',
            default => $this->competencia
        };
    }

    /**
     * Retorna badge HTML para competência
     */
    public function getCompetenciaBadgeAttribute()
    {
        $colors = [
            'estadual' => 'bg-blue-100 text-blue-800',
            'municipal' => 'bg-green-100 text-green-800',
        ];

        $color = $colors[$this->competencia] ?? 'bg-gray-100 text-gray-800';
        
        return "<span class='px-2 py-1 text-xs font-medium rounded {$color}'>{$this->competencia_label}</span>";
    }
}
