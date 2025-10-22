<?php

namespace App\Models;

use App\Enums\VinculoEstabelecimento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UsuarioExterno extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'usuarios_externos';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'telefone',
        'vinculo_estabelecimento',
        'password',
        'aceite_termos_em',
        'ip_aceite_termos',
        'ativo',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'aceite_termos_em' => 'datetime',
            'password' => 'hashed',
            'ativo' => 'boolean',
            'vinculo_estabelecimento' => VinculoEstabelecimento::class,
        ];
    }

    /**
     * Acessor para formatar o CPF
     */
    public function getCpfFormatadoAttribute(): string
    {
        $cpf = preg_replace('/\D/', '', $this->cpf);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    /**
     * Acessor para formatar o telefone
     */
    public function getTelefoneFormatadoAttribute(): string
    {
        $telefone = preg_replace('/\D/', '', $this->telefone);
        
        if (strlen($telefone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        }
        
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }

    /**
     * Verifica se o usuário aceitou os termos
     */
    public function aceitouTermos(): bool
    {
        return !is_null($this->aceite_termos_em);
    }

    /**
     * Registra o aceite dos termos
     */
    public function registrarAceiteTermos(string $ip): void
    {
        $this->update([
            'aceite_termos_em' => now(),
            'ip_aceite_termos' => $ip,
        ]);
    }

    /**
     * Relacionamento com estabelecimentos (será implementado depois)
     */
    // public function estabelecimentos()
    // {
    //     return $this->belongsToMany(Estabelecimento::class, 'usuarios_estabelecimentos');
    // }
}

