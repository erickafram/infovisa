<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoEdicao extends Model
{
    protected $table = 'documento_edicoes';

    protected $fillable = [
        'documento_digital_id',
        'usuario_interno_id',
        'conteudo',
        'diff',
        'caracteres_adicionados',
        'caracteres_removidos',
        'iniciado_em',
        'finalizado_em',
        'ativo',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'iniciado_em' => 'datetime',
        'finalizado_em' => 'datetime',
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento com documento digital
     */
    public function documentoDigital()
    {
        return $this->belongsTo(DocumentoDigital::class);
    }

    /**
     * Relacionamento com usuário
     */
    public function usuarioInterno()
    {
        return $this->belongsTo(UsuarioInterno::class);
    }

    /**
     * Calcula diferença entre dois conteúdos
     */
    public static function calcularDiff($conteudoAntigo, $conteudoNovo)
    {
        // Remove tags HTML para comparação mais precisa
        $textoAntigo = strip_tags($conteudoAntigo);
        $textoNovo = strip_tags($conteudoNovo);
        
        $adicionados = strlen($textoNovo) - strlen($textoAntigo);
        $removidos = $adicionados < 0 ? abs($adicionados) : 0;
        $adicionados = $adicionados > 0 ? $adicionados : 0;
        
        return [
            'adicionados' => $adicionados,
            'removidos' => $removidos,
            'diff' => self::gerarDiffSimples($textoAntigo, $textoNovo)
        ];
    }

    /**
     * Gera diff simples entre dois textos
     */
    private static function gerarDiffSimples($antigo, $novo)
    {
        $palavrasAntigas = explode(' ', $antigo);
        $palavrasNovas = explode(' ', $novo);
        
        $diff = [
            'adicionadas' => array_diff($palavrasNovas, $palavrasAntigas),
            'removidas' => array_diff($palavrasAntigas, $palavrasNovas),
        ];
        
        return json_encode($diff);
    }

    /**
     * Marca edição como finalizada
     */
    public function finalizar()
    {
        $this->update([
            'ativo' => false,
            'finalizado_em' => now(),
        ]);
    }

    /**
     * Busca editores ativos de um documento
     * Retorna apenas o registro mais recente de cada usuário único
     */
    public static function editoresAtivos($documentoId)
    {
        return self::with('usuarioInterno')
            ->where('documento_digital_id', $documentoId)
            ->where('ativo', true)
            ->where('iniciado_em', '>=', now()->subMinutes(5)) // Considera ativo se editou nos últimos 5 min
            ->orderBy('iniciado_em', 'desc')
            ->get()
            ->unique('usuario_interno_id') // Remove duplicatas, mantém apenas o mais recente de cada usuário
            ->values(); // Reindexar a coleção
    }

    /**
     * Desativa edições antigas (mais de 5 minutos sem atualização)
     */
    public static function desativarEdicoesAntigas()
    {
        return self::where('ativo', true)
            ->where('iniciado_em', '<', now()->subMinutes(5))
            ->update([
                'ativo' => false,
                'finalizado_em' => now(),
            ]);
    }
}
