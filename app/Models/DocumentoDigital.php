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
        'prazo_iniciado_em',
        'prazo_iniciado_por',
    ];

    protected $casts = [
        'sigiloso' => 'boolean',
        'prazo_notificacao' => 'boolean',
        'finalizado_em' => 'datetime',
        'ultima_edicao_em' => 'datetime',
        'prazo_dias' => 'integer',
        'data_vencimento' => 'date',
        'prazo_iniciado_em' => 'datetime',
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
     * Relacionamento com visualizações
     */
    public function visualizacoes()
    {
        return $this->hasMany(DocumentoVisualizacao::class);
    }

    /**
     * Primeira visualização do documento (para mostrar quem visualizou primeiro)
     */
    public function primeiraVisualizacao()
    {
        return $this->hasOne(DocumentoVisualizacao::class)->oldestOfMany();
    }

    /**
     * Relacionamento com pasta
     */
    public function pasta()
    {
        return $this->belongsTo(ProcessoPasta::class, 'pasta_id');
    }

    /**
     * Registra uma visualização do documento por usuário externo
     * e inicia a contagem do prazo se for documento de notificação
     */
    public function registrarVisualizacao($usuarioExternoId, $ip = null, $userAgent = null): void
    {
        // Registra a visualização
        $this->visualizacoes()->create([
            'usuario_externo_id' => $usuarioExternoId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        // Se for documento de notificação e o prazo ainda não foi iniciado, inicia agora
        if ($this->prazo_notificacao && !$this->prazo_iniciado_em && $this->todasAssinaturasCompletas()) {
            $this->iniciarPrazoPorVisualizacao();
        }
    }

    /**
     * Inicia o prazo por visualização do estabelecimento
     */
    public function iniciarPrazoPorVisualizacao(): void
    {
        if ($this->prazo_iniciado_em) {
            return; // Prazo já foi iniciado
        }

        $this->prazo_iniciado_em = now();
        $this->prazo_iniciado_por = 'visualizacao';
        
        // Recalcula a data de vencimento baseada na data de início do prazo
        if ($this->prazo_dias) {
            $this->data_vencimento = $this->calcularDataVencimento($this->prazo_iniciado_em, $this->prazo_dias, $this->tipo_prazo);
        }
        
        $this->save();
    }

    /**
     * Inicia o prazo por tempo de disponibilidade (5 dias)
     * Deve ser chamado por um job/scheduler
     */
    public function iniciarPrazoPorDisponibilidade(): void
    {
        if ($this->prazo_iniciado_em) {
            return; // Prazo já foi iniciado
        }

        $this->prazo_iniciado_em = now();
        $this->prazo_iniciado_por = 'tempo_disponibilidade';
        
        // Recalcula a data de vencimento baseada na data de início do prazo
        if ($this->prazo_dias) {
            $this->data_vencimento = $this->calcularDataVencimento($this->prazo_iniciado_em, $this->prazo_dias, $this->tipo_prazo);
        }
        
        $this->save();
    }

    /**
     * Calcula a data de vencimento baseada no tipo de prazo
     */
    private function calcularDataVencimento($dataInicio, $dias, $tipoPrazo): \Carbon\Carbon
    {
        $data = \Carbon\Carbon::parse($dataInicio);
        
        if ($tipoPrazo === 'uteis') {
            // Dias úteis - exclui finais de semana
            $diasAdicionados = 0;
            while ($diasAdicionados < $dias) {
                $data->addDay();
                if (!$data->isWeekend()) {
                    $diasAdicionados++;
                }
            }
        } else {
            // Dias corridos
            $data->addDays($dias);
        }
        
        return $data;
    }

    /**
     * Verifica se o documento está disponível há mais de 5 dias úteis
     * e o prazo ainda não foi iniciado
     */
    public function verificarInicioAutomaticoPrazo(): bool
    {
        // Só aplica para documentos de notificação com prazo não iniciado
        if (!$this->prazo_notificacao || $this->prazo_iniciado_em || !$this->todasAssinaturasCompletas()) {
            return false;
        }

        // Calcula 5 dias úteis após a finalização
        $dataFinalizacao = $this->finalizado_em ?? $this->created_at;
        $diasUteis = 0;
        $dataVerificacao = \Carbon\Carbon::parse($dataFinalizacao);
        
        while ($diasUteis < 5) {
            $dataVerificacao->addDay();
            if (!$dataVerificacao->isWeekend()) {
                $diasUteis++;
            }
        }

        // Se já passou os 5 dias úteis, inicia o prazo automaticamente
        if (now()->gte($dataVerificacao)) {
            $this->iniciarPrazoPorDisponibilidade();
            return true;
        }

        return false;
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
     * Retorna o texto do status do prazo
     * Só mostra os dias restantes após todas as assinaturas serem concluídas
     */
    public function getTextoStatusPrazoAttribute(): string
    {
        if (!$this->data_vencimento && !$this->prazo_dias) {
            return 'Sem prazo';
        }

        // Verifica se todas as assinaturas obrigatórias foram feitas
        if (!$this->todasAssinaturasCompletas()) {
            $pendentes = $this->assinaturas()
                ->where('obrigatoria', true)
                ->where('status', '!=', 'assinado')
                ->count();
            $total = $this->assinaturas()->where('obrigatoria', true)->count();
            return "Aguardando {$pendentes}/{$total} assinatura(s)";
        }

        // Para documentos de notificação, verifica se o prazo já foi iniciado
        if ($this->prazo_notificacao && !$this->prazo_iniciado_em) {
            // Calcula quantos dias faltam para iniciar automaticamente (5 dias úteis)
            $dataFinalizacao = $this->finalizado_em ?? $this->created_at;
            $diasUteis = 0;
            $dataLimite = \Carbon\Carbon::parse($dataFinalizacao);
            
            while ($diasUteis < 5) {
                $dataLimite->addDay();
                if (!$dataLimite->isWeekend()) {
                    $diasUteis++;
                }
            }
            
            $diasRestantes = now()->startOfDay()->diffInDays($dataLimite, false);
            
            if ($diasRestantes > 0) {
                return "Aguardando visualização ({$diasRestantes}d)";
            } else {
                return "Prazo iniciará automaticamente";
            }
        }

        $diasFaltando = $this->dias_faltando;

        if ($diasFaltando === null) {
            return 'Sem prazo';
        }

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

    /**
     * Retorna a cor do badge de status do prazo
     * Considera se as assinaturas estão pendentes
     */
    public function getCorStatusPrazoAttribute(): string
    {
        if (!$this->data_vencimento && !$this->prazo_dias) {
            return 'gray';
        }

        // Se ainda tem assinaturas pendentes, mostra em cinza/neutro
        if (!$this->todasAssinaturasCompletas()) {
            return 'gray';
        }

        // Para documentos de notificação sem prazo iniciado
        if ($this->prazo_notificacao && !$this->prazo_iniciado_em) {
            return 'blue'; // Azul para indicar aguardando visualização
        }

        if ($this->vencido) {
            return 'red';
        }

        if ($this->proximo_vencimento) {
            return 'yellow';
        }

        return 'green';
    }
}
