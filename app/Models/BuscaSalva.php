<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuscaSalva extends Model
{
    use HasFactory;

    protected $table = 'buscas_salvas';
    
    protected $fillable = [
        'nome',
        'texto', 
        'data_inicial',
        'data_final',
        'usuario_interno_id',
        'usuario_ip'
    ];
    
    protected $casts = [
        'data_inicial' => 'date',
        'data_final' => 'date',
    ];
    
    /**
     * Relacionamento com UsuarioInterno
     */
    public function usuarioInterno(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_interno_id');
    }
    
    /**
     * Scope para buscar por usuário logado
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('usuario_interno_id', $userId);
    }
    
    /**
     * Scope para buscar por IP do usuário
     */
    public function scopeByUserIp($query, $ip)
    {
        return $query->where('usuario_ip', $ip);
    }
}
