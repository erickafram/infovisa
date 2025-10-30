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
        'tipos_acao_ids',
        'tecnicos_ids',
        'municipio_id',
        'observacoes',
        'data_abertura',
        'data_inicio',
        'data_fim',
        'data_conclusao',
        'status',
        'prioridade',
        'competencia',
    ];

    protected $casts = [
        'data_abertura' => 'date',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'data_conclusao' => 'date',
        'tipos_acao_ids' => 'array',
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
     * Scope para ordens abertas
     */
    public function scopeAbertas($query)
    {
        return $query->where('status', 'aberta');
    }

    /**
     * Scope para ordens em andamento
     */
    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
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
            'aberta' => 'Aberta',
            'em_andamento' => 'Em Andamento',
            'concluida' => 'Concluída',
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
            'aberta' => 'bg-blue-100 text-blue-800',
            'em_andamento' => 'bg-yellow-100 text-yellow-800',
            'concluida' => 'bg-green-100 text-green-800',
            'cancelada' => 'bg-red-100 text-red-800',
        ];

        $color = $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
        
        return "<span class='px-2 py-1 text-xs font-medium rounded {$color}'>{$this->status_label}</span>";
    }

    /**
     * Retorna label formatado da prioridade
     */
    public function getPrioridadeLabelAttribute()
    {
        return match($this->prioridade) {
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
            default => $this->prioridade
        };
    }

    /**
     * Retorna badge HTML para prioridade
     */
    public function getPrioridadeBadgeAttribute()
    {
        $colors = [
            'baixa' => 'bg-gray-100 text-gray-800',
            'media' => 'bg-blue-100 text-blue-800',
            'alta' => 'bg-orange-100 text-orange-800',
            'urgente' => 'bg-red-100 text-red-800',
        ];

        $color = $colors[$this->prioridade] ?? 'bg-gray-100 text-gray-800';
        
        return "<span class='px-2 py-1 text-xs font-medium rounded {$color}'>{$this->prioridade_label}</span>";
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
