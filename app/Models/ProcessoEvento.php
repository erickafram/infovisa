<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessoEvento extends Model
{
    use HasFactory;

    protected $fillable = [
        'processo_id',
        'usuario_interno_id',
        'tipo_evento',
        'titulo',
        'descricao',
        'dados_adicionais',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'dados_adicionais' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com Processo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    /**
     * Relacionamento com Usuário Interno
     */
    public function usuario()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_interno_id');
    }

    /**
     * Registrar evento de criação do processo
     */
    public static function registrarCriacaoProcesso(Processo $processo, $usuario = null)
    {
        return self::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => $usuario?->id ?? auth('interno')->id(),
            'tipo_evento' => 'processo_criado',
            'titulo' => 'Processo Criado',
            'descricao' => 'Processo iniciado no sistema',
            'dados_adicionais' => [
                'numero_processo' => $processo->numero_processo,
                'tipo_processo' => $processo->tipo_nome,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar evento de anexação de documento
     */
    public static function registrarDocumentoAnexado(Processo $processo, $documento, $usuario = null)
    {
        return self::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => $usuario?->id ?? auth('interno')->id(),
            'tipo_evento' => 'documento_anexado',
            'titulo' => 'Documento Anexado',
            'descricao' => $documento->nome_arquivo ?? 'Arquivo',
            'dados_adicionais' => [
                'documento_id' => $documento->id,
                'nome_arquivo' => $documento->nome_arquivo,
                'tipo_documento' => $documento->tipo_documento,
                'tamanho' => $documento->tamanho ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar evento de criação de documento digital
     */
    public static function registrarDocumentoDigitalCriado(Processo $processo, $documentoDigital, $usuario = null)
    {
        return self::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => $usuario?->id ?? auth('interno')->id(),
            'tipo_evento' => 'documento_digital_criado',
            'titulo' => 'Documento Digital Criado',
            'descricao' => $documentoDigital->tipoDocumento->nome ?? 'Documento',
            'dados_adicionais' => [
                'documento_digital_id' => $documentoDigital->id,
                'tipo_documento' => $documentoDigital->tipoDocumento->nome ?? null,
                'status' => $documentoDigital->status,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar evento de exclusão de documento
     */
    public static function registrarDocumentoExcluido(Processo $processo, $documento, $usuario = null)
    {
        return self::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => $usuario?->id ?? auth('interno')->id(),
            'tipo_evento' => 'documento_excluido',
            'titulo' => 'Documento Excluído',
            'descricao' => $documento->nome_arquivo ?? 'Arquivo',
            'dados_adicionais' => [
                'documento_id' => $documento->id,
                'nome_arquivo' => $documento->nome_arquivo,
                'tipo_documento' => $documento->tipo_documento,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar evento de exclusão de documento digital
     */
    public static function registrarDocumentoDigitalExcluido(Processo $processo, $documentoDigital, $usuario = null)
    {
        return self::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => $usuario?->id ?? auth('interno')->id(),
            'tipo_evento' => 'documento_digital_excluido',
            'titulo' => 'Documento Digital Excluído',
            'descricao' => $documentoDigital->tipoDocumento->nome ?? 'Documento',
            'dados_adicionais' => [
                'documento_digital_id' => $documentoDigital->id,
                'tipo_documento' => $documentoDigital->tipoDocumento->nome ?? null,
                'status' => $documentoDigital->status,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar evento de alteração de status
     */
    public static function registrarAlteracaoStatus(Processo $processo, $statusAntigo, $statusNovo, $usuario = null)
    {
        return self::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => $usuario?->id ?? auth('interno')->id(),
            'tipo_evento' => 'status_alterado',
            'titulo' => 'Status Alterado',
            'descricao' => "Status alterado de '{$statusAntigo}' para '{$statusNovo}'",
            'dados_adicionais' => [
                'status_antigo' => $statusAntigo,
                'status_novo' => $statusNovo,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar evento de arquivamento do processo
     */
    public static function registrarArquivamento(Processo $processo, $motivo, $usuario = null)
    {
        return self::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => $usuario?->id ?? auth('interno')->id(),
            'tipo_evento' => 'processo_arquivado',
            'titulo' => 'Processo Arquivado',
            'descricao' => $motivo,
            'dados_adicionais' => [
                'motivo' => $motivo,
                'data_arquivamento' => now()->toDateTimeString(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar evento de parada do processo
     */
    public static function registrarParada(Processo $processo, $motivo, $usuario = null)
    {
        return self::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => $usuario?->id ?? auth('interno')->id(),
            'tipo_evento' => 'processo_parado',
            'titulo' => 'Processo Parado',
            'descricao' => $motivo,
            'dados_adicionais' => [
                'motivo' => $motivo,
                'data_parada' => now()->toDateTimeString(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar evento de reinício do processo
     */
    public static function registrarReinicio(Processo $processo, $usuario = null)
    {
        return self::create([
            'processo_id' => $processo->id,
            'usuario_interno_id' => $usuario?->id ?? auth('interno')->id(),
            'tipo_evento' => 'processo_reiniciado',
            'titulo' => 'Processo Reiniciado',
            'descricao' => 'Processo foi reiniciado e voltou ao status aberto',
            'dados_adicionais' => [
                'motivo_parada_anterior' => $processo->motivo_parada,
                'data_parada_anterior' => $processo->data_parada?->toDateTimeString(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Obter ícone do evento
     */
    public function getIconeAttribute(): string
    {
        return match($this->tipo_evento) {
            'processo_criado' => 'plus',
            'documento_anexado' => 'upload',
            'documento_digital_criado' => 'document',
            'documento_excluido' => 'trash',
            'documento_digital_excluido' => 'trash',
            'status_alterado' => 'refresh',
            'processo_arquivado' => 'archive',
            'processo_desarquivado' => 'check',
            'processo_parado' => 'pause',
            'processo_reiniciado' => 'play',
            'movimentacao' => 'arrow-right',
            'observacao_adicionada' => 'chat',
            default => 'info',
        };
    }

    /**
     * Obter cor do evento
     */
    public function getCorAttribute(): string
    {
        return match($this->tipo_evento) {
            'processo_criado' => 'blue',
            'documento_anexado' => 'purple',
            'documento_digital_criado' => 'green',
            'documento_excluido' => 'red',
            'documento_digital_excluido' => 'red',
            'status_alterado' => 'yellow',
            'processo_arquivado' => 'orange',
            'processo_desarquivado' => 'green',
            'processo_parado' => 'red',
            'processo_reiniciado' => 'green',
            'movimentacao' => 'indigo',
            'observacao_adicionada' => 'gray',
            default => 'gray',
        };
    }
}
