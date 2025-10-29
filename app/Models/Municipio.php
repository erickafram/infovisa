<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Municipio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'codigo_ibge',
        'uf',
        'slug',
        'logomarca',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento com estabelecimentos
     */
    public function estabelecimentos()
    {
        return $this->hasMany(Estabelecimento::class);
    }

    /**
     * Relacionamento com pactuações municipais
     */
    public function pactuacoes()
    {
        return $this->hasMany(Pactuacao::class);
    }

    /**
     * Relacionamento com usuários internos vinculados ao município
     */
    public function usuariosInternos()
    {
        return $this->hasMany(UsuarioInterno::class, 'municipio_id');
    }

    /**
     * Busca ou cria um município baseado no nome
     * Normaliza o nome para evitar duplicatas
     */
    public static function buscarOuCriarPorNome($nome, $codigoIbge = null)
    {
        if (empty($nome)) {
            return null;
        }

        $slug = Str::slug($nome);
        
        // Tenta buscar pelo slug primeiro
        $municipio = self::where('slug', $slug)->first();
        
        if ($municipio) {
            return $municipio;
        }

        // Se não encontrou, tenta buscar por código IBGE
        if ($codigoIbge) {
            $municipio = self::where('codigo_ibge', $codigoIbge)->first();
            if ($municipio) {
                return $municipio;
            }
        }

        // Se não encontrou, cria um novo
        return self::create([
            'nome' => mb_strtoupper(trim($nome)),
            'codigo_ibge' => $codigoIbge ?? '0000000',
            'uf' => 'TO',
            'slug' => $slug,
            'ativo' => true
        ]);
    }

    /**
     * Busca município por código IBGE
     */
    public static function buscarPorCodigoIbge($codigoIbge)
    {
        return self::where('codigo_ibge', $codigoIbge)->first();
    }

    /**
     * Busca município por nome (case-insensitive)
     */
    public static function buscarPorNome($nome)
    {
        $slug = Str::slug($nome);
        return self::where('slug', $slug)->first();
    }

    /**
     * Scope para municípios ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para municípios do Tocantins
     */
    public function scopeDoTocantins($query)
    {
        return $query->where('uf', 'TO');
    }
}
