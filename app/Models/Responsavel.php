<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responsavel extends Model
{
    use SoftDeletes;

    protected $table = 'responsaveis';

    protected $fillable = [
        'tipo',
        'cpf',
        'nome',
        'email',
        'telefone',
        'tipo_documento',
        'documento_identificacao',
        'conselho',
        'numero_registro_conselho',
        'carteirinha_conselho',
    ];

    /**
     * Relacionamento com estabelecimentos
     */
    public function estabelecimentos()
    {
        return $this->belongsToMany(Estabelecimento::class, 'estabelecimento_responsavel')
                    ->withPivot('tipo_vinculo', 'ativo')
                    ->withTimestamps();
    }

    /**
     * Accessor para CPF formatado
     */
    public function getCpfFormatadoAttribute()
    {
        $cpf = $this->cpf;
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    /**
     * Accessor para telefone formatado
     */
    public function getTelefoneFormatadoAttribute()
    {
        $telefone = preg_replace('/[^0-9]/', '', $this->telefone);
        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
        }
        return $this->telefone;
    }

    /**
     * Verifica se é responsável legal
     */
    public function isLegal()
    {
        return $this->tipo === 'legal';
    }

    /**
     * Verifica se é responsável técnico
     */
    public function isTecnico()
    {
        return $this->tipo === 'tecnico';
    }
}
