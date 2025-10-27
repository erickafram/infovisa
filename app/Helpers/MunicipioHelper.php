<?php

namespace App\Helpers;

use App\Models\Municipio;
use Illuminate\Support\Str;

class MunicipioHelper
{
    /**
     * Normaliza o nome do município vindo de APIs externas
     * e retorna o ID do município correspondente
     */
    public static function normalizarEObterIdPorNome(string $nome, ?string $codigoIbge = null): ?int
    {
        if (empty($nome)) {
            return null;
        }

        // Remove acentos e caracteres especiais para comparação
        $slug = Str::slug($nome);
        
        // Tenta buscar pelo código IBGE primeiro (mais confiável)
        if ($codigoIbge) {
            $municipio = Municipio::where('codigo_ibge', $codigoIbge)->first();
            if ($municipio) {
                return $municipio->id;
            }
        }

        // Tenta buscar pelo slug
        $municipio = Municipio::where('slug', $slug)->first();
        if ($municipio) {
            return $municipio->id;
        }

        // Se não encontrou, cria um novo município
        $municipio = Municipio::create([
            'nome' => mb_strtoupper(trim($nome)),
            'codigo_ibge' => $codigoIbge ?? '0000000',
            'uf' => 'TO',
            'slug' => $slug,
            'ativo' => true
        ]);

        return $municipio->id;
    }

    /**
     * Normaliza o nome do município vindo de APIs externas
     * e retorna o objeto Municipio correspondente
     */
    public static function normalizarEObterPorNome(string $nome, ?string $codigoIbge = null): ?Municipio
    {
        if (empty($nome)) {
            return null;
        }

        return Municipio::buscarOuCriarPorNome($nome, $codigoIbge);
    }

    /**
     * Obtém o ID do município a partir do código IBGE
     */
    public static function obterIdPorCodigoIbge(string $codigoIbge): ?int
    {
        $municipio = Municipio::where('codigo_ibge', $codigoIbge)->first();
        return $municipio?->id;
    }

    /**
     * Obtém o nome normalizado do município a partir do ID
     */
    public static function obterNomePorId(int $municipioId): ?string
    {
        $municipio = Municipio::find($municipioId);
        return $municipio?->nome;
    }

    /**
     * Mapeia variações comuns de nomes de municípios
     * para o nome oficial
     */
    public static function mapearVariacoes(string $nome): string
    {
        $mapeamento = [
            'PARAISO DO TOCANTINS' => 'PARAÍSO DO TOCANTINS',
            'PARAISO DO TO' => 'PARAÍSO DO TOCANTINS',
            'COLINAS DO TO' => 'COLINAS DO TOCANTINS',
            'AUGUSTINOPOLIS' => 'AUGUSTINÓPOLIS',
            'DIANOPOLIS' => 'DIANÓPOLIS',
            'GURUPI/TO' => 'GURUPI',
            'PALMAS/TO' => 'PALMAS',
            'ARAGUAINA' => 'ARAGUAÍNA',
            'TOCANTINOPOLIS' => 'TOCANTINÓPOLIS',
        ];

        $nomeUpper = mb_strtoupper(trim($nome));
        
        return $mapeamento[$nomeUpper] ?? $nomeUpper;
    }
}
