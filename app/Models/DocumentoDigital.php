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
        'ultimo_editor_id',
        'ultima_edicao_em',
        'versao_atual',
        'numero_documento',
        'nome',
        'conteudo',
        'sigiloso',
        'status',
        'arquivo_pdf',
        'codigo_autenticidade',
        'finalizado_em',
        'prazo_dias',
        'tipo_prazo',
        'prazo_notificacao',
        'data_vencimento',
    ];

    protected $casts = [
        'sigiloso' => 'boolean',
        'prazo_notificacao' => 'boolean',
        'finalizado_em' => 'datetime',
        'ultima_edicao_em' => 'datetime',
        'prazo_dias' => 'integer',
        'data_vencimento' => 'date',
    ];

    /**
     * Gera código único de autenticidade
     */
    public static function gerarCodigoAutenticidade(): string
    {
        do {
            $codigo = md5(uniqid(rand(), true));
            $existe = self::where('codigo_autenticidade', $codigo)->exists();
        } while ($existe);

        return $codigo;
    }

    /**
     * Gera o próximo número de documento no formato 000001.2025
     */
    public static function gerarNumeroDocumento(): string
    {
        $ano = date('Y');
        
        // Busca o último número de documento do ano (incluindo soft deleted)
        $ultimo = self::withTrashed()
            ->where('numero_documento', 'like', "%.{$ano}")
            ->orderByRaw("CAST(SUBSTRING(numero_documento FROM 1 FOR 6) AS INTEGER) DESC")
            ->first();

        // Extrai o número sequencial (primeiros 6 dígitos)
        $sequencial = 1;
        if ($ultimo) {
            $partes = explode('.', $ultimo->numero_documento);
            $sequencial = (int) $partes[0] + 1;
        }

        // Garante que o número seja único tentando até encontrar um disponível
        $tentativas = 0;
        do {
            $numeroDocumento = sprintf('%06d.%s', $sequencial + $tentativas, $ano);
            $existe = self::withTrashed()->where('numero_documento', $numeroDocumento)->exists();
            $tentativas++;
        } while ($existe && $tentativas < 100);

        return $numeroDocumento;
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
     * Relacionamento com versões
     */
    public function versoes()
    {
        return $this->hasMany(DocumentoDigitalVersao::class);
    }

    /**
     * Relacionamento com último editor
     */
    public function ultimoEditor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'ultimo_editor_id');
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

    /**
     * Salva uma nova versão do documento
     */
    public function salvarVersao($usuarioId, $conteudo, $alteracoes = null)
    {
        $ultimaVersao = $this->versoes()->max('versao') ?? 0;
        
        return $this->versoes()->create([
            'usuario_interno_id' => $usuarioId,
            'versao' => $ultimaVersao + 1,
            'conteudo' => $conteudo,
            'alteracoes' => $alteracoes,
        ]);
    }

    /**
     * Calcula quantos dias faltam para o vencimento
     */
    public function getDiasFaltandoAttribute(): ?int
    {
        if (!$this->data_vencimento) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->data_vencimento, false);
    }

    /**
     * Verifica se o documento está vencido
     */
    public function getVencidoAttribute(): bool
    {
        if (!$this->data_vencimento) {
            return false;
        }

        return now()->startOfDay()->gt($this->data_vencimento);
    }

    /**
     * Verifica se o documento está próximo do vencimento (7 dias ou menos)
     */
    public function getProximoVencimentoAttribute(): bool
    {
        $diasFaltando = $this->dias_faltando;
        
        if ($diasFaltando === null) {
            return false;
        }

        return $diasFaltando >= 0 && $diasFaltando <= 7;
    }

    /**
     * Retorna a cor do badge de status do prazo
     */
    public function getCorStatusPrazoAttribute(): string
    {
        if (!$this->data_vencimento) {
            return 'gray';
        }

        if ($this->vencido) {
            return 'red';
        }

        if ($this->proximo_vencimento) {
            return 'yellow';
        }

        return 'green';
    }

    /**
     * Retorna o texto do status do prazo
     */
    public function getTextoStatusPrazoAttribute(): string
    {
        if (!$this->data_vencimento) {
            return 'Sem prazo';
        }

        $diasFaltando = $this->dias_faltando;

        if ($diasFaltando < 0) {
            $diasVencidos = abs($diasFaltando);
            return "Vencido há {$diasVencidos} " . ($diasVencidos === 1 ? 'dia' : 'dias');
        }

        if ($diasFaltando === 0) {
            return 'Vence hoje';
        }

        if ($diasFaltando === 1) {
            return 'Vence amanhã';
        }

        return "Faltam {$diasFaltando} dias";
    }
}
