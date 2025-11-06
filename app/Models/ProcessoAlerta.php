<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProcessoAlerta extends Model
{
    use HasFactory;

    protected $fillable = [
        'processo_id',
        'usuario_criador_id',
        'descricao',
        'data_alerta',
        'status',
        'visualizado_em',
        'concluido_em',
    ];

    protected $casts = [
        'data_alerta' => 'date',
        'visualizado_em' => 'datetime',
        'concluido_em' => 'datetime',
    ];

    // Relacionamentos
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    public function usuarioCriador()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_criador_id');
    }

    // Helpers
    public function isVencido()
    {
        return $this->status === 'pendente' && $this->data_alerta->isPast();
    }

    public function isProximo()
    {
        return $this->status === 'pendente' && 
               !$this->data_alerta->isPast() && 
               $this->data_alerta->diffInDays(now()) <= 3;
    }

    public function marcarComoVisualizado()
    {
        $this->update([
            'status' => 'visualizado',
            'visualizado_em' => now(),
        ]);
    }

    public function marcarComoConcluido()
    {
        $this->update([
            'status' => 'concluido',
            'concluido_em' => now(),
        ]);
    }
}
