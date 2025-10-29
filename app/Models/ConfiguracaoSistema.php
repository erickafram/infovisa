<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoSistema extends Model
{
    protected $table = 'configuracoes_sistema';

    protected $fillable = [
        'chave',
        'valor',
        'tipo',
        'descricao',
    ];

    /**
     * Obtém o valor de uma configuração pela chave
     */
    public static function obter(string $chave, $padrao = null)
    {
        $config = self::where('chave', $chave)->first();
        return $config ? $config->valor : $padrao;
    }

    /**
     * Define o valor de uma configuração
     */
    public static function definir(string $chave, $valor, string $tipo = 'texto', string $descricao = null)
    {
        return self::updateOrCreate(
            ['chave' => $chave],
            [
                'valor' => $valor,
                'tipo' => $tipo,
                'descricao' => $descricao,
            ]
        );
    }

    /**
     * Obtém a logomarca estadual
     */
    public static function logomarcaEstadual()
    {
        return self::obter('logomarca_estadual');
    }
}
