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
        'password',
        'ativo',
        'email_verified_at',
    ];

    /**
     * Os atributos que devem ser escondidos na serialização
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'nivel_acesso' => NivelAcesso::class,
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

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
}

