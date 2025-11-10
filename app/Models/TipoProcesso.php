<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoProcesso extends Model
{
    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'anual',
        'usuario_externo_pode_abrir',
        'usuario_externo_pode_visualizar',
        'ativo',
        'ordem',
        'competencia',
        'municipios_descentralizados',
        'municipios_descentralizados_ids',
    ];

    protected $casts = [
        'anual' => 'boolean',
        'usuario_externo_pode_abrir' => 'boolean',
        'usuario_externo_pode_visualizar' => 'boolean',
        'ativo' => 'boolean',
        'ordem' => 'integer',
        'municipios_descentralizados' => 'array',
        'municipios_descentralizados_ids' => 'array',
    ];

    /**
     * Scope para tipos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para tipos que usuário externo pode abrir
     */
    public function scopeParaUsuarioExterno($query)
    {
        return $query->where('usuario_externo_pode_abrir', true)->where('ativo', true);
    }

    /**
     * Scope ordenado
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }
    
    /**
     * Scope para tipos disponíveis para um usuário específico
     */
    public function scopeParaUsuario($query, $usuario)
    {
        // Administradores veem todos
        if ($usuario->isAdmin()) {
            return $query;
        }
        
        // Usuários estaduais veem todos (estaduais e municipais)
        if ($usuario->isEstadual()) {
            return $query;
        }
        
        // Usuários municipais veem apenas:
        // 1. Tipos municipais
        // 2. Tipos estaduais descentralizados para seu município
        if ($usuario->isMunicipal()) {
            $municipioUsuario = strtoupper(self::removerAcentosStatic(trim($usuario->municipio)));
            
            // Busca todos os tipos para filtrar
            $todosTipos = self::all();
            $idsPermitidos = [];
            
            foreach ($todosTipos as $tipo) {
                // Sempre inclui tipos municipais
                if ($tipo->competencia === 'municipal') {
                    $idsPermitidos[] = $tipo->id;
                    continue;
                }
                
                // Para tipos estaduais (estadual ou estadual_exclusivo), verifica descentralização
                if (in_array($tipo->competencia, ['estadual', 'estadual_exclusivo']) && $tipo->municipios_descentralizados) {
                    foreach ($tipo->municipios_descentralizados as $municipioDesc) {
                        $municipioDescNorm = strtoupper(self::removerAcentosStatic(trim($municipioDesc)));
                        
                        if ($municipioDescNorm === $municipioUsuario) {
                            $idsPermitidos[] = $tipo->id;
                            break;
                        }
                    }
                }
            }
            
            return $query->whereIn('id', $idsPermitidos);
        }
        
        return $query;
    }
    
    /**
     * Remove acentos de uma string (método estático)
     */
    private static function removerAcentosStatic($string)
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
     * Remove acentos de uma string (método de instância)
     */
    private function removerAcentos($string)
    {
        return self::removerAcentosStatic($string);
    }
    
    /**
     * Verifica se um município tem acesso a este tipo de processo
     */
    public function municipioTemAcesso($municipio)
    {
        // Se for municipal, todos os municípios têm acesso
        if ($this->competencia === 'municipal') {
            return true;
        }
        
        // Se for estadual, verifica se o município está descentralizado
        if ($this->competencia === 'estadual') {
            if (!$this->municipios_descentralizados) {
                return false;
            }
            
            return in_array($municipio, $this->municipios_descentralizados);
        }
        
        return false;
    }
    
    /**
     * Adiciona um município à lista de descentralizados
     */
    public function adicionarMunicipioDescentralizado($municipio, $municipioId = null)
    {
        $municipios = $this->municipios_descentralizados ?? [];
        $municipiosIds = $this->municipios_descentralizados_ids ?? [];
        
        if (!in_array($municipio, $municipios)) {
            $municipios[] = $municipio;
            if ($municipioId) {
                $municipiosIds[] = $municipioId;
            }
            
            $this->municipios_descentralizados = $municipios;
            $this->municipios_descentralizados_ids = $municipiosIds;
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * Remove um município da lista de descentralizados
     */
    public function removerMunicipioDescentralizado($municipio)
    {
        $municipios = $this->municipios_descentralizados ?? [];
        $municipios = array_values(array_filter($municipios, fn($m) => $m !== $municipio));
        
        $this->municipios_descentralizados = $municipios;
        $this->save();
        
        return $this;
    }
}
