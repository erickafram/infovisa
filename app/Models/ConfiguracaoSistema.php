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
        return self::normalizarCaminhoLogomarca(self::obter('logomarca_estadual'));
    }

    /**
     * Normaliza caminhos de logomarca para formato público esperado (storage/...)
     */
    public static function normalizarCaminhoLogomarca(?string $valor): ?string
    {
        if (empty($valor)) {
            return null;
        }

        $valor = trim($valor);

        if (str_starts_with($valor, 'http://') || str_starts_with($valor, 'https://') || str_starts_with($valor, 'data:')) {
            return $valor;
        }

        if (str_starts_with($valor, '/storage/')) {
            return ltrim($valor, '/');
        }

        if (str_starts_with($valor, 'storage/')) {
            return $valor;
        }

        if (str_starts_with($valor, 'sistema/logomarcas/')) {
            return 'storage/' . $valor;
        }

        if (!str_contains($valor, '/')) {
            return 'storage/sistema/logomarcas/' . $valor;
        }

        return 'storage/' . ltrim($valor, '/');
    }
}
