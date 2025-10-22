<?php

namespace App\Models;

use App\Enums\TipoSetor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estabelecimento extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Os atributos que podem ser atribuídos em massa
     */
    protected $fillable = [
        'nome_fantasia',
        'razao_social',
        'cnpj',
        'cpf',
        'nome_completo',
        'inscricao_estadual',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'telefone',
        'email',
        'tipo_estabelecimento',
        'atividade_principal',
        'ativo',
        'usuario_externo_id',
        // Campos da API
        'natureza_juridica',
        'porte',
        'situacao_cadastral',
        'data_situacao_cadastral',
        'data_inicio_atividade',
        'cnae_fiscal',
        'cnae_fiscal_descricao',
        'cnaes_secundarios',
        'qsa',
        'capital_social',
        'logradouro',
        'codigo_municipio_ibge',
        'tipo_pessoa',
        'tipo_setor',
        'atividades_exercidas',
        // Campos adicionais da API
        'ddd_telefone_1',
        'ddd_telefone_2',
        'ddd_fax',
        'opcao_pelo_mei',
        'opcao_pelo_simples',
        'regime_tributario',
        'situacao_especial',
        'motivo_situacao_cadastral',
        'descricao_situacao_cadastral',
        'descricao_motivo_situacao_cadastral',
        'identificador_matriz_filial',
        'qualificacao_do_responsavel',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos
     */
    protected $casts = [
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'data_situacao_cadastral' => 'date',
        'data_inicio_atividade' => 'date',
        'cnaes_secundarios' => 'array',
        'qsa' => 'array',
        'capital_social' => 'decimal:2',
        'opcao_pelo_mei' => 'boolean',
        'opcao_pelo_simples' => 'boolean',
        'regime_tributario' => 'array',
        'atividades_exercidas' => 'array',
        'tipo_setor' => TipoSetor::class,
    ];

    /**
     * Relacionamento com usuário externo
     */
    public function usuarioExterno()
    {
        return $this->belongsTo(UsuarioExterno::class);
    }

    /**
     * Relacionamento com responsáveis
     */
    public function responsaveis()
    {
        return $this->belongsToMany(Responsavel::class, 'estabelecimento_responsavel')
                    ->withPivot('tipo_vinculo', 'ativo')
                    ->withTimestamps();
    }

    /**
     * Responsáveis legais ativos
     */
    public function responsaveisLegais()
    {
        return $this->responsaveis()->wherePivot('tipo_vinculo', 'legal')->wherePivot('ativo', true);
    }

    /**
     * Responsáveis técnicos ativos
     */
    public function responsaveisTecnicos()
    {
        return $this->responsaveis()->wherePivot('tipo_vinculo', 'tecnico')->wherePivot('ativo', true);
    }

    /**
     * Relacionamento com processos
     */
    public function processos()
    {
        return $this->hasMany(Processo::class);
    }

    /**
     * Accessor para CNPJ formatado
     */
    public function getCnpjFormatadoAttribute(): string
    {
        if (empty($this->cnpj)) {
            return '';
        }
        
        $cnpj = preg_replace('/[^0-9]/', '', $this->cnpj);
        
        if (strlen($cnpj) === 14) {
            return substr($cnpj, 0, 2) . '.' . 
                   substr($cnpj, 2, 3) . '.' . 
                   substr($cnpj, 5, 3) . '/' . 
                   substr($cnpj, 8, 4) . '-' . 
                   substr($cnpj, 12, 2);
        }
        
        return $this->cnpj;
    }

    /**
     * Accessor para CPF formatado
     */
    public function getCpfFormatadoAttribute(): string
    {
        if (empty($this->cpf)) {
            return '';
        }
        
        $cpf = preg_replace('/[^0-9]/', '', $this->cpf);
        
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . 
                   substr($cpf, 3, 3) . '.' . 
                   substr($cpf, 6, 3) . '-' . 
                   substr($cpf, 9, 2);
        }
        
        return $this->cpf;
    }

    /**
     * Accessor para documento formatado (CNPJ ou CPF)
     */
    public function getDocumentoFormatadoAttribute(): string
    {
        if ($this->tipo_pessoa === 'juridica' && !empty($this->cnpj)) {
            return $this->cnpj_formatado;
        } elseif ($this->tipo_pessoa === 'fisica' && !empty($this->cpf)) {
            return $this->cpf_formatado;
        }
        
        return '';
    }

    /**
     * Accessor para nome/razão social
     */
    public function getNomeRazaoSocialAttribute(): string
    {
        if ($this->tipo_pessoa === 'juridica') {
            return $this->razao_social ?? '';
        } else {
            return $this->nome_completo ?? '';
        }
    }

    /**
     * Accessor para CEP formatado
     */
    public function getCepFormatadoAttribute(): string
    {
        $cep = $this->cep;
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }

    /**
     * Accessor para telefone formatado
     */
    public function getTelefoneFormatadoAttribute(): ?string
    {
        if (!$this->telefone) {
            return null;
        }

        $telefone = preg_replace('/[^0-9]/', '', $this->telefone);

        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' .
                   substr($telefone, 2, 5) . '-' .
                   substr($telefone, 7);
        }

        if (strlen($telefone) === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' .
                   substr($telefone, 2, 4) . '-' .
                   substr($telefone, 6);
        }

        return $this->telefone;
    }

    /**
     * Accessor para endereço completo
     */
    public function getEnderecoCompletoAttribute(): string
    {
        return $this->endereco . ', ' . $this->numero .
               ($this->complemento ? ', ' . $this->complemento : '') .
               ' - ' . $this->bairro . ', ' . $this->cidade . ' - ' . $this->estado .
               ', ' . $this->cep_formatado;
    }

    /**
     * Retorna o tipo de estabelecimento legível
     */
    public function getTipoEstabelecimentoLabelAttribute(): string
    {
        return match($this->tipo_estabelecimento) {
            'restaurante' => 'Restaurante',
            'bar' => 'Bar',
            'lanchonete' => 'Lanchonete',
            'supermercado' => 'Supermercado',
            'mercearia' => 'Mercearia',
            'padaria' => 'Padaria',
            'acougue' => 'Açougue',
            'farmacia' => 'Farmácia',
            'hospital' => 'Hospital',
            'clinica' => 'Clínica',
            'laboratorio' => 'Laboratório',
            'pet_shop' => 'Pet Shop',
            'outros' => 'Outros',
            default => $this->tipo_estabelecimento,
        };
    }

    /**
     * Verifica se o estabelecimento está ativo
     */
    public function isAtivo(): bool
    {
        return $this->ativo === true;
    }

    /**
     * Scope para filtrar estabelecimentos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para filtrar por tipo de estabelecimento
     */
    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo_estabelecimento', $tipo);
    }

    /**
     * Scope para buscar estabelecimentos do usuário logado
     */
    public function scopeDoUsuario($query, $usuarioId = null)
    {
        $usuarioId = $usuarioId ?? auth('externo')->id();
        return $query->where('usuario_externo_id', $usuarioId);
    }
}
