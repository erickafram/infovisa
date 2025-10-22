<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoDigital extends Model
{
    use SoftDeletes;

    protected $table = 'documentos_digitais';

    protected $fillable = [
        'tipo_documento_id',
        'processo_id',
        'pasta_id',
        'usuario_criador_id',
        'numero_documento',
        'conteudo',
        'sigiloso',
        'status',
        'arquivo_pdf',
        'finalizado_em',
    ];

    protected $casts = [
        'sigiloso' => 'boolean',
        'finalizado_em' => 'datetime',
    ];

    /**
     * Gera o próximo número de documento
     */
    public static function gerarNumeroDocumento(): string
    {
        $ano = date('Y');
        $ultimo = self::whereYear('created_at', $ano)
            ->orderBy('id', 'desc')
            ->first();

        $sequencial = $ultimo ? (int) substr($ultimo->numero_documento, -5) + 1 : 1;

        return sprintf('DOC-%s-%05d', $ano, $sequencial);
    }

    /**
     * Relacionamento com tipo de documento
     */
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    /**
     * Relacionamento com processo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    /**
     * Relacionamento com usuário criador
     */
    public function usuarioCriador()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_criador_id');
    }

    /**
     * Relacionamento com assinaturas
     */
    public function assinaturas()
    {
        return $this->hasMany(DocumentoAssinatura::class);
    }

    /**
     * Relacionamento com pasta
     */
    public function pasta()
    {
        return $this->belongsTo(ProcessoPasta::class, 'pasta_id');
    }

    /**
     * Verifica se todas assinaturas obrigatórias foram feitas
     */
    public function todasAssinaturasCompletas(): bool
    {
        return !$this->assinaturas()
            ->where('obrigatoria', true)
            ->where('status', '!=', 'assinado')
            ->exists();
    }
}
