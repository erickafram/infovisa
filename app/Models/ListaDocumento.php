<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListaDocumento extends Model
{
    use SoftDeletes;

    protected $table = 'listas_documento';

    protected $fillable = [
        'tipo_processo_id',
        'nome',
        'descricao',
        'escopo',
        'municipio_id',
        'ativo',
        'criado_por',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento com tipo de processo
     */
    public function tipoProcesso()
    {
        return $this->belongsTo(\App\Models\TipoProcesso::class);
    }

    /**
     * Relacionamento com município
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Relacionamento com usuário criador
     */
    public function criadoPor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'criado_por');
    }

    /**
     * Relacionamento com atividades
     */
    public function atividades()
    {
        return $this->belongsToMany(Atividade::class, 'lista_documento_atividade')
            ->withTimestamps();
    }

    /**
     * Relacionamento com tipos de documento obrigatório
     */
    public function tiposDocumentoObrigatorio()
    {
        return $this->belongsToMany(TipoDocumentoObrigatorio::class, 'lista_documento_tipo', 'lista_documento_id', 'tipo_documento_obrigatorio_id')
            ->withPivot(['obrigatorio', 'observacao', 'ordem'])
            ->withTimestamps()
            ->orderBy('lista_documento_tipo.ordem');
    }

    /**
     * Documentos obrigatórios
     */
    public function documentosObrigatorios()
    {
        return $this->tiposDocumentoObrigatorio()->wherePivot('obrigatorio', true);
    }

    /**
     * Documentos opcionais
     */
    public function documentosOpcionais()
    {
        return $this->tiposDocumentoObrigatorio()->wherePivot('obrigatorio', false);
    }

    /**
     * Scope para listas ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para listas estaduais
     */
    public function scopeEstaduais($query)
    {
        return $query->where('escopo', 'estadual');
    }

    /**
     * Scope para listas municipais
     */
    public function scopeMunicipais($query)
    {
        return $query->where('escopo', 'municipal');
    }

    /**
     * Scope para listas de um município específico
     */
    public function scopeDoMunicipio($query, $municipioId)
    {
        return $query->where('escopo', 'municipal')->where('municipio_id', $municipioId);
    }

    /**
     * Verifica se é lista estadual
     */
    public function isEstadual(): bool
    {
        return $this->escopo === 'estadual';
    }

    /**
     * Verifica se é lista municipal
     */
    public function isMunicipal(): bool
    {
        return $this->escopo === 'municipal';
    }

    /**
     * Retorna o label do escopo
     */
    public function getEscopoLabelAttribute(): string
    {
        return $this->escopo === 'estadual' ? 'Estadual' : 'Municipal';
    }

    /**
     * Retorna a cor do badge do escopo
     */
    public function getEscopoCorAttribute(): string
    {
        return $this->escopo === 'estadual' 
            ? 'bg-blue-100 text-blue-800' 
            : 'bg-green-100 text-green-800';
    }

    /**
     * Busca listas aplicáveis para um estabelecimento baseado nas atividades exercidas
     */
    public static function buscarParaEstabelecimento(Estabelecimento $estabelecimento)
    {
        $atividadesExercidas = $estabelecimento->atividades_exercidas ?? [];
        
        if (empty($atividadesExercidas)) {
            return collect();
        }

        // Busca atividades cadastradas que correspondem às atividades exercidas
        $atividadeIds = Atividade::where('ativo', true)
            ->where(function($query) use ($atividadesExercidas) {
                foreach ($atividadesExercidas as $atividade) {
                    $codigo = is_array($atividade) ? ($atividade['codigo'] ?? null) : $atividade;
                    if ($codigo) {
                        $query->orWhere('codigo_cnae', 'like', '%' . preg_replace('/[^0-9]/', '', $codigo) . '%');
                    }
                }
            })
            ->pluck('id');

        if ($atividadeIds->isEmpty()) {
            return collect();
        }

        // Busca listas que contenham essas atividades
        $query = self::ativas()
            ->whereHas('atividades', function($q) use ($atividadeIds) {
                $q->whereIn('atividades.id', $atividadeIds);
            })
            ->with(['tiposDocumentoObrigatorio', 'atividades', 'municipio']);

        // Filtra por escopo (estadual ou do município do estabelecimento)
        $query->where(function($q) use ($estabelecimento) {
            $q->where('escopo', 'estadual');
            if ($estabelecimento->municipio_id) {
                $q->orWhere(function($q2) use ($estabelecimento) {
                    $q2->where('escopo', 'municipal')
                       ->where('municipio_id', $estabelecimento->municipio_id);
                });
            }
        });

        return $query->get();
    }
}
