<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pactuacao extends Model
{
    protected $table = 'pactuacoes';
    
    protected $fillable = [
        'tipo',
        'municipio',
        'cnae_codigo',
        'cnae_descricao',
        'ativo'
    ];
    
    protected $casts = [
        'ativo' => 'boolean',
    ];
    
    /**
     * Verifica se uma atividade é de competência estadual
     */
    public static function isAtividadeEstadual($cnaeCodigo)
    {
        return self::where('tipo', 'estadual')
            ->where('cnae_codigo', $cnaeCodigo)
            ->where('ativo', true)
            ->exists();
    }
    
    /**
     * Verifica se uma atividade é de competência municipal
     */
    public static function isAtividadeMunicipal($municipio, $cnaeCodigo)
    {
        return self::where('tipo', 'municipal')
            ->where('municipio', $municipio)
            ->where('cnae_codigo', $cnaeCodigo)
            ->where('ativo', true)
            ->exists();
    }
    
    /**
     * Retorna todas as atividades de um município
     */
    public static function getAtividadesMunicipio($municipio)
    {
        return self::where('tipo', 'municipal')
            ->where('municipio', $municipio)
            ->where('ativo', true)
            ->pluck('cnae_codigo')
            ->toArray();
    }
    
    /**
     * Retorna todas as atividades estaduais
     */
    public static function getAtividadesEstaduais()
    {
        return self::where('tipo', 'estadual')
            ->where('ativo', true)
            ->pluck('cnae_codigo')
            ->toArray();
    }
}
