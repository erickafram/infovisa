<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Atividade extends Model
{
    use SoftDeletes;

    protected $table = 'atividades';

    protected $fillable = [
        'tipo_servico_id',
        'nome',
        'codigo_cnae',
        'descricao',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * Relacionamento com tipo de serviço
     */
    public function tipoServico()
    {
        return $this->belongsTo(TipoServico::class);
    }

    /**
     * Relacionamento com listas de documento (estrutura antiga - mantida para compatibilidade)
     */
    public function listasDocumento()
    {
        return $this->belongsToMany(ListaDocumento::class, 'lista_documento_atividade')
            ->withTimestamps();
    }

    /**
     * Relacionamento direto com tipos de documento obrigatório (nova estrutura)
     */
    public function documentosObrigatorios()
    {
        return $this->belongsToMany(TipoDocumentoObrigatorio::class, 'atividade_documento')
            ->withPivot(['obrigatorio', 'observacao', 'ordem'])
            ->withTimestamps()
            ->orderBy('atividade_documento.ordem');
    }

    /**
     * Documentos obrigatórios desta atividade
     */
    public function documentosObrigatoriosAtivos()
    {
        return $this->documentosObrigatorios()
            ->where('tipos_documento_obrigatorio.ativo', true)
            ->wherePivot('obrigatorio', true);
    }

    /**
     * Documentos opcionais desta atividade
     */
    public function documentosOpcionais()
    {
        return $this->documentosObrigatorios()
            ->where('tipos_documento_obrigatorio.ativo', true)
            ->wherePivot('obrigatorio', false);
    }

    /**
     * Scope para atividades ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenação
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    /**
     * Scope para buscar por código CNAE
     */
    public function scopePorCnae($query, string $cnae)
    {
        $cnaeNumerico = preg_replace('/[^0-9]/', '', $cnae);
        return $query->where('codigo_cnae', 'like', '%' . $cnaeNumerico . '%');
    }

    /**
     * Retorna nome completo com tipo de serviço
     */
    public function getNomeCompletoAttribute(): string
    {
        return $this->tipoServico ? "{$this->tipoServico->nome} - {$this->nome}" : $this->nome;
    }

    /**
     * Retorna o código CNAE formatado
     */
    public function getCodigoCnaeFormatadoAttribute(): string
    {
        if (!$this->codigo_cnae) {
            return '';
        }
        
        $cnae = preg_replace('/[^0-9]/', '', $this->codigo_cnae);
        if (strlen($cnae) === 7) {
            return substr($cnae, 0, 4) . '-' . substr($cnae, 4, 1) . '/' . substr($cnae, 5, 2);
        }
        
        return $this->codigo_cnae;
    }

    /**
     * Busca atividades por lista de códigos CNAE
     */
    public static function buscarPorCnaes(array $cnaes): Collection
    {
        if (empty($cnaes)) {
            return collect();
        }

        $query = self::where('ativo', true);
        
        $query->where(function($q) use ($cnaes) {
            foreach ($cnaes as $cnae) {
                $cnaeNumerico = preg_replace('/[^0-9]/', '', $cnae);
                if ($cnaeNumerico) {
                    $q->orWhere('codigo_cnae', 'like', '%' . $cnaeNumerico . '%');
                }
            }
        });

        return $query->get();
    }
}
