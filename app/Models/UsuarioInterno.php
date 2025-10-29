<?php

namespace App\Models;

use App\Enums\NivelAcesso;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UsuarioInterno extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * O nome da tabela
     */
    protected $table = 'usuarios_internos';

    /**
     * Os atributos que podem ser atribuídos em massa
     */
    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'telefone',
        'matricula',
        'cargo',
        'nivel_acesso',
        'municipio',
        'municipio_id',
        'password',
        'senha_assinatura_digital',
        'ativo',
        'email_verified_at',
    ];

    /**
     * Os atributos que devem ser escondidos na serialização
     */
    protected $hidden = [
        'password',
        'senha_assinatura_digital',
        'remember_token',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'senha_assinatura_digital' => 'hashed',
        'nivel_acesso' => NivelAcesso::class,
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relacionamento com município
     */
    public function municipioRelacionado()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    /**
     * Accessor para nome do município
     * Se o campo municipio estiver vazio, busca do relacionamento
     */
    public function getMunicipioAttribute($value)
    {
        // Se já tem valor no campo, retorna
        if ($value) {
            return $value;
        }
        
        // Se não tem, busca do relacionamento
        if ($this->municipio_id && $this->relationLoaded('municipioRelacionado')) {
            return $this->municipioRelacionado?->nome;
        }
        
        // Se não tem relacionamento carregado, carrega agora
        if ($this->municipio_id) {
            $municipio = Municipio::find($this->municipio_id);
            return $municipio?->nome;
        }
        
        return null;
    }

    /**
     * Accessor para CPF formatado
     */
    public function getCpfFormatadoAttribute(): string
    {
        $cpf = $this->cpf;
        return substr($cpf, 0, 3) . '.' . 
               substr($cpf, 3, 3) . '.' . 
               substr($cpf, 6, 3) . '-' . 
               substr($cpf, 9, 2);
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
     * Verifica se o usuário é administrador
     */
    public function isAdmin(): bool
    {
        return $this->nivel_acesso === NivelAcesso::Administrador;
    }

    /**
     * Verifica se o usuário é gestor
     */
    public function isGestor(): bool
    {
        return $this->nivel_acesso->isGestor();
    }

    /**
     * Verifica se o usuário é técnico
     */
    public function isTecnico(): bool
    {
        return in_array($this->nivel_acesso, [
            NivelAcesso::TecnicoEstadual,
            NivelAcesso::TecnicoMunicipal
        ]);
    }

    /**
     * Verifica se o usuário tem acesso estadual
     */
    public function isEstadual(): bool
    {
        return $this->nivel_acesso->isEstadual();
    }

    /**
     * Verifica se o usuário tem acesso municipal
     */
    public function isMunicipal(): bool
    {
        return $this->nivel_acesso->isMunicipal();
    }

    /**
     * Verifica se o usuário está ativo
     */
    public function isAtivo(): bool
    {
        return $this->ativo === true;
    }

    /**
     * Verifica se o usuário tem senha de assinatura digital cadastrada
     */
    public function temSenhaAssinatura(): bool
    {
        return !empty($this->senha_assinatura_digital);
    }

    /**
     * Scope para filtrar apenas usuários ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para filtrar apenas usuários ativos (singular)
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenar por nome
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('nome', 'asc');
    }

    /**
     * Scope para filtrar por nível de acesso
     */
    public function scopeNivelAcesso($query, NivelAcesso|string $nivel)
    {
        $valor = $nivel instanceof NivelAcesso ? $nivel->value : $nivel;
        return $query->where('nivel_acesso', $valor);
    }

    /**
     * Scope para filtrar administradores
     */
    public function scopeAdministradores($query)
    {
        return $query->where('nivel_acesso', NivelAcesso::Administrador->value);
    }

    /**
     * Obtém a logomarca para documentos digitais
     * - Se for usuário municipal: retorna logomarca do município
     * - Se for usuário estadual: retorna logomarca estadual (configuração do sistema)
     * - Se não houver logomarca: retorna null
     */
    public function getLogomarcaDocumento()
    {
        // Se for usuário municipal e tiver município vinculado
        if ($this->isMunicipal() && $this->municipio_id) {
            $municipio = $this->municipioRelacionado;
            return $municipio?->logomarca;
        }
        
        // Se for usuário estadual ou não tiver município
        if ($this->isEstadual() || !$this->municipio_id) {
            return \App\Models\ConfiguracaoSistema::logomarcaEstadual();
        }
        
        return null;
    }

    /**
     * Verifica se o usuário tem logomarca configurada
     */
    public function temLogomarca(): bool
    {
        return !empty($this->getLogomarcaDocumento());
    }
}

