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
        'respostas_questionario',
        'competencia_manual',
        'motivo_alteracao_competencia',
        'alterado_por',
        'alterado_em',
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
        'municipio_id',
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
        'respostas_questionario' => 'array',
        'tipo_setor' => TipoSetor::class,
        'aprovado_em' => 'datetime',
        'alterado_em' => 'datetime',
    ];

    /**
     * Relacionamento com usuário externo
     */
    public function usuarioExterno()
    {
        return $this->belongsTo(UsuarioExterno::class);
    }

    /**
     * Relacionamento com município
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Alias para relacionamento com município (consistência com outros models)
     */
    public function municipioRelacionado()
    {
        return $this->municipio();
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

    /**
     * Determina se o estabelecimento é de competência estadual
     * Um estabelecimento é estadual se PELO MENOS UMA de suas atividades for estadual
     * Considera exceções de descentralização para o município do estabelecimento
     */
    public function isCompetenciaEstadual()
    {
        // PRIORIDADE 1: Se há competência manual definida, usa ela (override administrativo/judicial)
        if ($this->competencia_manual) {
            return $this->competencia_manual === 'estadual';
        }
        
        // PRIORIDADE 2: Verifica pela pactuação (lógica normal)
        // Pega todas as atividades do estabelecimento
        $atividades = $this->getTodasAtividades();
        
        // Normaliza o nome do município (remove " - TO" ou "/TO")
        $municipio = $this->cidade;
        if ($municipio) {
            $municipio = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', $municipio);
            $municipio = trim($municipio);
        }
        
        // Se pelo menos uma atividade for estadual (considerando exceções), o estabelecimento é estadual
        foreach ($atividades as $cnae) {
            if (Pactuacao::isAtividadeEstadual($cnae, $municipio)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Determina se o estabelecimento é de competência municipal
     * Um estabelecimento é municipal se TODAS as suas atividades forem municipais
     * e NENHUMA for estadual
     */
    public function isCompetenciaMunicipal()
    {
        return !$this->isCompetenciaEstadual();
    }
    
    /**
     * Retorna APENAS as atividades que o estabelecimento REALMENTE EXERCE
     * Essas são as atividades marcadas pelo estabelecimento no cadastro
     * 
     * IMPORTANTE: A competência (estadual/municipal) é determinada pelas atividades
     * que o estabelecimento EXERCE, não por todas as atividades cadastradas na Receita.
     */
    public function getTodasAtividades()
    {
        $atividades = [];
        
        // Retorna APENAS as atividades exercidas (marcadas pelo estabelecimento)
        if ($this->atividades_exercidas && is_array($this->atividades_exercidas)) {
            foreach ($this->atividades_exercidas as $atividade) {
                $codigo = null;
                
                // Se for um array (com código e descrição), pega apenas o código
                if (is_array($atividade) && isset($atividade['codigo'])) {
                    $codigo = $atividade['codigo'];
                } 
                // Se for uma string (apenas o código CNAE), usa diretamente
                elseif (is_string($atividade)) {
                    $codigo = $atividade;
                }
                
                // Normaliza o código removendo formatação (pontos, traços, etc)
                if ($codigo) {
                    $codigo = preg_replace('/[^0-9]/', '', $codigo);
                    if (!empty($codigo)) {
                        $atividades[] = $codigo;
                    }
                }
            }
        }
        
        // Se não houver atividades exercidas marcadas, considera o CNAE principal como fallback
        // (para estabelecimentos antigos que não têm atividades exercidas cadastradas)
        if (empty($atividades) && $this->cnae_fiscal) {
            $cnae = preg_replace('/[^0-9]/', '', $this->cnae_fiscal);
            if (!empty($cnae)) {
                $atividades[] = $cnae;
            }
        }
        
        return array_unique(array_filter($atividades));
    }
    
    /**
     * Retorna TODAS as atividades cadastradas (incluindo as não exercidas)
     * Usado apenas para exibição/informação, NÃO para determinar competência
     */
    public function getTodasAtividadesCadastradas()
    {
        $atividades = [];
        
        // Adiciona CNAE principal
        if ($this->cnae_fiscal) {
            $atividades[] = $this->cnae_fiscal;
        }
        
        // Adiciona CNAEs secundários
        if ($this->cnaes_secundarios && is_array($this->cnaes_secundarios)) {
            foreach ($this->cnaes_secundarios as $cnae) {
                if (isset($cnae['codigo'])) {
                    $atividades[] = $cnae['codigo'];
                }
            }
        }
        
        return array_unique(array_filter($atividades));
    }
    
    /**
     * Scope para filtrar estabelecimentos por competência do usuário
     */
    public function scopeParaUsuario($query, $usuario)
    {
        // Administrador vê tudo
        if ($usuario->isAdmin()) {
            return $query;
        }
        
        // Gestor/Técnico Municipal - vê apenas do seu município
        if ($usuario->isMunicipal()) {
            if (!$usuario->municipio_id) {
                return $query->whereRaw('1=0'); // Sem município vinculado, não vê nada
            }
            return $query->where('municipio_id', $usuario->municipio_id);
        }
        
        // Gestor/Técnico Estadual - vê estabelecimentos de competência estadual de qualquer município
        // O filtro de competência será aplicado no controller
        if ($usuario->isEstadual()) {
            return $query;
        }
        
        return $query->whereRaw('1=0'); // Nenhum acesso por padrão
    }
    
    /**
     * Scope para filtrar apenas estabelecimentos de um município específico
     */
    public function scopeDoMunicipio($query, $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }

    /**
     * Calcula o Grupo de Risco do estabelecimento baseado nas atividades pactuadas
     * 
     * Lógica:
     * - Alto Risco: Estabelecimentos com atividades de classificação_risco "alto" na pactuação
     * - Médio Risco: Estabelecimentos com atividades de classificação_risco "medio"
     * - Baixo Risco: Estabelecimentos sem atividades de risco ou classificação "baixo"
     * 
     * @return string 'alto', 'medio', 'baixo', ou 'indefinido'
     */
    public function getGrupoRisco(): string
    {
        $atividades = $this->getTodasAtividades();
        
        if (empty($atividades)) {
            return 'indefinido';
        }
        
        $temAltoRisco = false;
        $temMedioRisco = false;
        
        foreach ($atividades as $cnae) {
            $pactuacao = Pactuacao::where('cnae_codigo', $cnae)
                ->where('ativo', true)
                ->first();
            
            if ($pactuacao && $pactuacao->classificacao_risco) {
                $risco = strtolower($pactuacao->classificacao_risco);
                
                if ($risco === 'alto') {
                    $temAltoRisco = true;
                    break; // Se tem alto risco, já retorna
                } elseif ($risco === 'medio' || $risco === 'médio') {
                    $temMedioRisco = true;
                }
            }
        }
        
        if ($temAltoRisco) {
            return 'alto';
        } elseif ($temMedioRisco) {
            return 'medio';
        }
        
        return 'baixo';
    }
    
    /**
     * Retorna o label curto do grupo de risco (versão clean)
     */
    public function getGrupoRiscoLabelAttribute(): string
    {
        $risco = $this->getGrupoRisco();
        
        return match($risco) {
            'alto' => 'Alto',
            'medio' => 'Médio',
            'baixo' => 'Baixo',
            'indefinido' => 'N/A',
            default => 'N/A',
        };
    }
    
    /**
     * Retorna o label completo do grupo de risco (para tooltip)
     */
    public function getGrupoRiscoTooltipAttribute(): string
    {
        $risco = $this->getGrupoRisco();
        
        return match($risco) {
            'alto' => 'Alto Risco - Estabelecimento com atividades de alto risco sanitário',
            'medio' => 'Médio Risco - Estabelecimento com atividades de risco moderado',
            'baixo' => 'Baixo Risco - Estabelecimento com atividades de baixo risco',
            'indefinido' => 'Não Classificado - Sem atividades cadastradas',
            default => 'Não Classificado',
        };
    }
    
    /**
     * Retorna o estilo inline do grupo de risco (cores exatas)
     */
    public function getGrupoRiscoStyleAttribute(): string
    {
        $risco = $this->getGrupoRisco();
        
        return match($risco) {
            'alto' => 'background-color: #ef4444; color: white;',      // Vermelho
            'medio' => 'background-color: #fbbf24; color: white;',     // Laranja
            'baixo' => 'background-color: #34d399; color: white;',     // Verde
            'indefinido' => 'background-color: #9ca3af; color: white;',
            default => 'background-color: #9ca3af; color: white;',
        };
    }
    
    /**
     * Retorna as classes CSS do grupo de risco (versão compacta com cores vibrantes)
     */
    public function getGrupoRiscoCorAttribute(): string
    {
        $risco = $this->getGrupoRisco();
        
        return match($risco) {
            'alto' => 'bg-red-500 text-white',
            'medio' => 'bg-yellow-500 text-white',
            'baixo' => 'bg-green-500 text-white',
            'indefinido' => 'bg-gray-400 text-white',
            default => 'bg-gray-400 text-white',
        };
    }
    
    /**
     * Retorna a cor de fundo mais escura para hover
     */
    public function getGrupoRiscoCorHoverAttribute(): string
    {
        $risco = $this->getGrupoRisco();
        
        return match($risco) {
            'alto' => 'hover:bg-red-600',
            'medio' => 'hover:bg-yellow-600',
            'baixo' => 'hover:bg-green-600',
            'indefinido' => 'hover:bg-gray-500',
            default => 'hover:bg-gray-500',
        };
    }

}
