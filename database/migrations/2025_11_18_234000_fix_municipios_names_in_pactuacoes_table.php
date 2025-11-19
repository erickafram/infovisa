<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Pactuacao;
use App\Models\Municipio;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pega todas as pactuações que têm exceções
        $pactuacoes = Pactuacao::whereNotNull('municipios_excecao')->get();

        foreach ($pactuacoes as $pactuacao) {
            $excecoesAtuais = $pactuacao->municipios_excecao;
            
            // Verifica se é um array válido e não vazio
            if (!is_array($excecoesAtuais) || empty($excecoesAtuais)) {
                continue;
            }

            $novasExcecoes = [];
            $alterado = false;

            foreach ($excecoesAtuais as $nomeAtual) {
                // Tenta encontrar o município
                $municipio = $this->encontrarMunicipio($nomeAtual);

                if ($municipio) {
                    // Se encontrou e o nome é diferente (case, acentos, etc), atualiza
                    if ($municipio->nome !== $nomeAtual) {
                        $novasExcecoes[] = $municipio->nome;
                        $alterado = true;
                    } else {
                        $novasExcecoes[] = $nomeAtual;
                    }
                } else {
                    // Se não encontrar, mantém o original
                    $novasExcecoes[] = $nomeAtual;
                }
            }

            if ($alterado) {
                // Remove duplicatas e reindexa array
                $pactuacao->municipios_excecao = array_values(array_unique($novasExcecoes));
                $pactuacao->save();
            }
        }
    }

    /**
     * Tenta encontrar o município na base de dados com variações de nome
     */
    private function encontrarMunicipio($nome)
    {
        $nome = trim($nome);
        
        // 1. Tenta busca exata (case insensitive)
        $municipio = Municipio::whereRaw('LOWER(nome) = ?', [strtolower($nome)])->first();
        if ($municipio) return $municipio;

        // 2. Remove " - TO", "/TO"
        $nomeLimpo = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', $nome);
        $municipio = Municipio::whereRaw('LOWER(nome) = ?', [strtolower($nomeLimpo)])->first();
        if ($municipio) return $municipio;

        // 3. Tenta substituir "do TO" por "do Tocantins"
        if (stripos($nome, 'do TO') !== false) {
            $nomeExtenso = str_ireplace('do TO', 'do Tocantins', $nome);
            $municipio = Municipio::whereRaw('LOWER(nome) = ?', [strtolower($nomeExtenso)])->first();
            if ($municipio) return $municipio;
        }
        
        // 4. Tenta "Paraíso do TO" -> "Paraíso do Tocantins" (específico)
        if (stripos($nome, 'Paraíso do TO') !== false) {
            $municipio = Municipio::whereRaw('LOWER(nome) = ?', ['paraiso do tocantins'])->first();
            if ($municipio) return $municipio;
        }

        return null;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não há rollback para correção de dados
    }
};
