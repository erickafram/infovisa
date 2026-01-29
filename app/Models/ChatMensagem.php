<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChatMensagem extends Model
{
    protected $table = 'chat_mensagens';

    protected $fillable = [
        'conversa_id',
        'remetente_id',
        'conteudo',
        'tipo',
        'arquivo_path',
        'arquivo_nome',
        'arquivo_mime',
        'arquivo_tamanho',
        'lida_em',
    ];

    protected $casts = [
        'lida_em' => 'datetime',
    ];

    public function conversa(): BelongsTo
    {
        return $this->belongsTo(ChatConversa::class, 'conversa_id');
    }

    public function remetente(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'remetente_id');
    }

    /**
     * Retorna a URL do arquivo
     */
    public function getArquivoUrlAttribute(): ?string
    {
        if (!$this->arquivo_path) {
            return null;
        }
        return Storage::url($this->arquivo_path);
    }

    /**
     * Verifica se é uma imagem
     */
    public function isImagem(): bool
    {
        return $this->tipo === 'imagem';
    }

    /**
     * Verifica se é um áudio
     */
    public function isAudio(): bool
    {
        return $this->tipo === 'audio';
    }

    /**
     * Formata o tamanho do arquivo
     */
    public function getTamanhoFormatadoAttribute(): string
    {
        if (!$this->arquivo_tamanho) {
            return '';
        }

        $bytes = $this->arquivo_tamanho;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
