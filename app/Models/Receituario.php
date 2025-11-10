<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receituario extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tipo',
        'nome',
        'cpf',
        'especialidade',
        'telefone',
        'telefone2',
        'numero_conselho_classe',
        'numero_crm',
        'endereco',
        'endereco_residencial',
        'cep',
        'municipio',
        'municipio_id',
        'email',
        'razao_social',
        'cnpj',
        'responsavel_nome',
        'responsavel_cpf',
        'responsavel_crm',
        'responsavel_especialidade',
        'responsavel_telefone',
        'responsavel_telefone2',
        'locais_trabalho',
        'status',
        'observacoes',
        'processo_id',
        'usuario_criacao_id',
        'usuario_atualizacao_id',
    ];

    protected $casts = [
        'locais_trabalho' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento com Município
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Relacionamento com Processo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    /**
     * Usuário que criou
     */
    public function usuarioCriacao()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_criacao_id');
    }

    /**
     * Usuário que atualizou
     */
    public function usuarioAtualizacao()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_atualizacao_id');
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    /**
     * Scope para pendentes
     */
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    /**
     * Retorna o nome formatado do tipo
     */
    public function getTipoNomeAttribute()
    {
        $tipos = [
            'medico' => 'Médico, Cirurgião Dentista e Médico Veterinário',
            'instituicao' => 'Instituição (Hospital, Clínica e Similares)',
            'secretaria' => 'Secretaria de Saúde e Vigilância Sanitária',
            'talidomida' => 'Prescritor de Talidomida',
        ];

        return $tipos[$this->tipo] ?? $this->tipo;
    }

    /**
     * Retorna o CPF formatado
     */
    public function getCpfFormatadoAttribute()
    {
        if (!$this->cpf) return null;
        
        $cpf = preg_replace('/[^0-9]/', '', $this->cpf);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    /**
     * Retorna o CNPJ formatado
     */
    public function getCnpjFormatadoAttribute()
    {
        if (!$this->cnpj) return null;
        
        $cnpj = preg_replace('/[^0-9]/', '', $this->cnpj);
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    /**
     * Retorna o identificador principal (nome ou razão social)
     */
    public function getIdentificadorAttribute()
    {
        return $this->nome ?? $this->razao_social;
    }
}
