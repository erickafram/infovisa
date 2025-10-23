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
        'rg',
        'orgao_emissor',
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
        'motivo_desativacao',
        'situacao',
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
        // Campos de aprovação
        'status',
        'municipio',
        'motivo_rejeicao',
        'aprovado_por',
        'aprovado_em',
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
        'aprovado_em' => 'datetime',
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
     * Relacionamento com histórico
     */
    public function historicos()
    {
        return $this->hasMany(EstabelecimentoHistorico::class);
    }

    /**
     * Relacionamento com usuário que aprovou
     */
    public function aprovadoPor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'aprovado_por');
    }

    /**
     * Relacionamento com usuários externos vinculados
     */
    public function usuariosVinculados()
    {
        return $this->belongsToMany(UsuarioExterno::class, 'estabelecimento_usuario_externo')
                    ->withPivot('tipo_vinculo', 'observacao', 'vinculado_por')
                    ->withTimestamps();
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
     * Retorna o label da situação cadastral (vem da API)
     */
    public function getSituacaoLabelAttribute(): string
    {
        // A API da Receita Federal retorna códigos numéricos
        $situacao = $this->situacao_cadastral ?? $this->descricao_situacao_cadastral ?? '2';
        
        // Se for texto, usa direto
        if (!is_numeric($situacao)) {
            $situacao = strtoupper($situacao);
            return match($situacao) {
                'ATIVA' => 'Ativa',
                'BAIXADA' => 'Baixada',
                'SUSPENSA' => 'Suspensa',
                'INAPTA' => 'Inapta',
                'NULA' => 'Nula',
                default => ucfirst(strtolower($situacao)),
            };
        }
        
        // Mapeamento dos códigos da Receita Federal
        return match((string)$situacao) {
            '1' => 'Nula',
            '2' => 'Ativa',
            '3' => 'Suspensa',
            '4' => 'Inapta',
            '8' => 'Baixada',
            default => 'Ativa',
        };
    }

    /**
     * Retorna a cor da badge da situação cadastral
     */
    public function getSituacaoCorAttribute(): string
    {
        $situacao = $this->situacao_cadastral ?? $this->descricao_situacao_cadastral ?? '2';
        
        // Se for texto, converte
        if (!is_numeric($situacao)) {
            $situacao = strtoupper($situacao);
            return match($situacao) {
                'ATIVA' => 'bg-green-100 text-green-800',
                'BAIXADA' => 'bg-gray-100 text-gray-800',
                'SUSPENSA' => 'bg-yellow-100 text-yellow-800',
                'INAPTA' => 'bg-red-100 text-red-800',
                'NULA' => 'bg-red-100 text-red-800',
                default => 'bg-gray-100 text-gray-800',
            };
        }
        
        // Mapeamento dos códigos da Receita Federal
        return match((string)$situacao) {
            '1' => 'bg-red-100 text-red-800',      // Nula
            '2' => 'bg-green-100 text-green-800',  // Ativa
            '3' => 'bg-yellow-100 text-yellow-800', // Suspensa
            '4' => 'bg-red-100 text-red-800',      // Inapta
            '8' => 'bg-gray-100 text-gray-800',    // Baixada
            default => 'bg-green-100 text-green-800',
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

    /**
     * Scope para filtrar por município
     */
    public function scopePorMunicipio($query, $municipio)
    {
        return $query->where('municipio', $municipio);
    }

    /**
     * Scope para filtrar por status
     */
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para estabelecimentos pendentes
     */
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    /**
     * Scope para estabelecimentos aprovados
     */
    public function scopeAprovados($query)
    {
        return $query->where('status', 'aprovado');
    }

    /**
     * Scope para estabelecimentos rejeitados
     */
    public function scopeRejeitados($query)
    {
        return $query->where('status', 'rejeitado');
    }

    /**
     * Scope para filtrar estabelecimentos do município do usuário logado
     */
    public function scopeDoMunicipioUsuario($query)
    {
        if (auth('interno')->check()) {
            $usuario = auth('interno')->user();
            // Se não for administrador, filtra por município
            if (!$usuario->nivel_acesso->isAdmin()) {
                return $query->where('municipio', $usuario->municipio);
            }
        }
        return $query;
    }

    /**
     * Verifica se o estabelecimento está aprovado
     */
    public function isAprovado(): bool
    {
        return $this->status === 'aprovado';
    }

    /**
     * Verifica se o estabelecimento está pendente
     */
    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    /**
     * Verifica se o estabelecimento está rejeitado
     */
    public function isRejeitado(): bool
    {
        return $this->status === 'rejeitado';
    }

    /**
     * Aprova o estabelecimento
     */
    public function aprovar(?string $observacao = null)
    {
        $statusAnterior = $this->status;
        
        $this->update([
            'status' => 'aprovado',
            'aprovado_por' => auth('interno')->id(),
            'aprovado_em' => now(),
            'motivo_rejeicao' => null,
        ]);

        EstabelecimentoHistorico::registrar(
            $this->id,
            'aprovado',
            $statusAnterior,
            'aprovado',
            $observacao
        );

        return $this;
    }

    /**
     * Rejeita o estabelecimento
     */
    public function rejeitar(string $motivo, ?string $observacao = null)
    {
        $statusAnterior = $this->status;
        
        $this->update([
            'status' => 'rejeitado',
            'aprovado_por' => auth('interno')->id(),
            'aprovado_em' => now(),
            'motivo_rejeicao' => $motivo,
        ]);

        EstabelecimentoHistorico::registrar(
            $this->id,
            'rejeitado',
            $statusAnterior,
            'rejeitado',
            $observacao ?? $motivo
        );

        return $this;
    }

    /**
     * Reinicia o estabelecimento (volta para pendente)
     */
    public function reiniciar(?string $observacao = null)
    {
        $statusAnterior = $this->status;
        
        $this->update([
            'status' => 'pendente',
            'aprovado_por' => null,
            'aprovado_em' => null,
            'motivo_rejeicao' => null,
        ]);

        EstabelecimentoHistorico::registrar(
            $this->id,
            'reiniciado',
            $statusAnterior,
            'pendente',
            $observacao
        );

        return $this;
    }

}
