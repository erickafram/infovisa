<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pactuacao extends Model
{
    protected $table = 'pactuacoes';
    
    protected $fillable = [
        'tipo',
        'municipio',
        'municipio_id',
        'cnae_codigo',
        'cnae_descricao',
        'municipios_excecao',
        'municipios_excecao_ids',
        'observacao',
        'ativo',
        'tabela',
        'requer_questionario',
        'pergunta',
        'classificacao_risco'
    ];
    
    protected $casts = [
        'ativo' => 'boolean',
        'requer_questionario' => 'boolean',
        'municipios_excecao' => 'array',
        'municipios_excecao_ids' => 'array',
    ];
    
    /**
     * Relacionamento com município (para pactuações municipais)
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
    
    /**
     * Relacionamento com municípios de exceção
     */
    public function municipiosExcecao()
    {
        if (!$this->municipios_excecao_ids) {
            return collect();
        }
        
        return Municipio::whereIn('id', $this->municipios_excecao_ids)->get();
    }
    
    /**
     * Verifica se uma atividade é de competência estadual
     * Considera exceções municipais (descentralização) e resposta do questionário
     */
    public static function isAtividadeEstadual($cnaeCodigo, $municipio = null, $resposta = null)
    {
        // Se a resposta do questionário for "não", a competência é MUNICIPAL
        if ($resposta !== null) {
            $resp = strtolower(trim($resposta));
            if ($resp === 'nao' || $resp === 'não') {
                return false;
            }
        }

        $pactuacao = self::where('tipo', 'estadual')
            ->where('cnae_codigo', $cnaeCodigo)
            ->where('ativo', true)
            ->first();
        
        if (!$pactuacao) {
            return false;
        }
        
        // Se não foi informado município, retorna true (é estadual)
        if (!$municipio) {
            return true;
        }
        
        // Verifica se o município está na lista de exceções (descentralizado)
        if ($pactuacao->municipios_excecao && is_array($pactuacao->municipios_excecao)) {
            // Normaliza o município do estabelecimento
            $municipioNormalizado = strtoupper(self::removerAcentos(trim($municipio)));
            
            // Verifica se está na lista de exceções (comparando sem acentos)
            foreach ($pactuacao->municipios_excecao as $municipioExcecao) {
                $municipioExcecaoNorm = strtoupper(self::removerAcentos(trim($municipioExcecao)));
                
                if ($municipioExcecaoNorm === $municipioNormalizado) {
                    // Se o município está nas exceções, ele tem competência MUNICIPAL (não é estadual)
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Remove acentos de uma string
     */
    private static function removerAcentos($string)
    {
        $acentos = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ];
        
        return strtr($string, $acentos);
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
    
    /**
     * Verifica se um município tem exceção (descentralização) para uma atividade estadual
     */
    public static function municipioTemExcecao($cnaeCodigo, $municipio)
    {
        $pactuacao = self::where('tipo', 'estadual')
            ->where('cnae_codigo', $cnaeCodigo)
            ->where('ativo', true)
            ->first();
        
        if (!$pactuacao || !$pactuacao->municipios_excecao) {
            return false;
        }
        
        return in_array($municipio, $pactuacao->municipios_excecao);
    }
    
    /**
     * Adiciona um município à lista de exceções
     */
    public function adicionarMunicipioExcecao($municipio)
    {
        $excecoes = $this->municipios_excecao ?? [];
        
        if (!in_array($municipio, $excecoes)) {
            $excecoes[] = $municipio;
            $this->municipios_excecao = $excecoes;
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * Remove um município da lista de exceções
     */
    public function removerMunicipioExcecao($municipio)
    {
        $excecoes = $this->municipios_excecao ?? [];
        $excecoes = array_values(array_filter($excecoes, fn($m) => $m !== $municipio));
        
        $this->municipios_excecao = $excecoes;
        $this->save();
        
        return $this;
    }
}
