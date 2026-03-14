<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoSistema extends Model
{
    public const RODAPE_TEXTO_PADRAO = "Diretoria de Vigilância Sanitária - Anexo I da Secretaria de Estado de Saúde - Qd. 104 Norte, Av. LO-02, Conj. 01, Lotes 20/30 - Ed. Lauro Knopp (3° Andar) - CEP 77.006-022 - Palmas-TO.\nContatos: (63) 3027-4486 - (63) 3027-4475 - (63) 3027-4432 - tocantins.visa@gmail.com";

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
     * Obtém a imagem de rodapé estadual
     */
    public static function rodapeEstadual()
    {
        $rodapeConfigurado = self::normalizarCaminhoArquivoPublico(self::obter('rodape_estadual'));

        if ($rodapeConfigurado) {
            return $rodapeConfigurado;
        }

        return file_exists(public_path('img/rodape.jpeg')) ? 'img/rodape.jpeg' : null;
    }

    /**
     * Obtém o texto padrão do rodapé usado pelo estado e como fallback dos municípios
     */
    public static function rodapeTextoPadrao(): string
    {
        $texto = trim((string) self::obter('rodape_texto_padrao', self::RODAPE_TEXTO_PADRAO));

        return $texto !== '' ? $texto : self::RODAPE_TEXTO_PADRAO;
    }

    /**
     * Normaliza caminhos de logomarca para formato público esperado (storage/...)
     */
    public static function normalizarCaminhoLogomarca(?string $valor): ?string
    {
        return self::normalizarCaminhoArquivoPublico($valor);
    }

    /**
     * Normaliza caminhos de imagens para formato público esperado
     */
    public static function normalizarCaminhoArquivoPublico(?string $valor): ?string
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

        if (str_starts_with($valor, '/img/')) {
            return ltrim($valor, '/');
        }

        if (str_starts_with($valor, 'storage/')) {
            return $valor;
        }

        if (str_starts_with($valor, 'img/')) {
            return $valor;
        }

        if (str_starts_with($valor, 'sistema/logomarcas/')) {
            return 'storage/' . $valor;
        }

        if (str_starts_with($valor, 'sistema/rodapes/')) {
            return 'storage/' . $valor;
        }

        if (str_starts_with($valor, 'municipios/logomarcas/')) {
            return 'storage/' . $valor;
        }

        if (str_starts_with($valor, 'municipios/rodapes/')) {
            return 'storage/' . $valor;
        }

        if (!str_contains($valor, '/')) {
            return 'storage/sistema/logomarcas/' . $valor;
        }

        return 'storage/' . ltrim($valor, '/');
    }
}
