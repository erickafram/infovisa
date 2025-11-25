<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sugestao extends Model
{
    use HasFactory;

    protected $table = 'sugestoes';

    protected $fillable = [
        'usuario_interno_id',
        'pagina_url',
        'titulo',
        'descricao',
        'tipo',
        'status',
        'resposta_admin',
        'checklist',
        'admin_responsavel_id',
        'concluido_em',
    ];

    protected $casts = [
        'checklist' => 'array',
        'concluido_em' => 'datetime',
    ];

    const TIPOS = [
        'funcionalidade' => 'Nova Funcionalidade',
        'melhoria' => 'Melhoria',
        'modulo' => 'Novo Módulo',
        'correcao' => 'Correção de Bug',
        'outro' => 'Outro',
    ];

    const STATUS = [
        'pendente' => 'Pendente',
        'em_analise' => 'Em Análise',
        'em_desenvolvimento' => 'Em Desenvolvimento',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
    ];

    const STATUS_COLORS = [
        'pendente' => 'gray',
        'em_analise' => 'yellow',
        'em_desenvolvimento' => 'blue',
        'concluido' => 'green',
        'cancelado' => 'red',
    ];

    /**
     * Usuário que criou a sugestão
     */
    public function usuario()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_interno_id');
    }

    /**
     * Admin responsável pela sugestão
     */
    public function adminResponsavel()
    {
        return $this->belongsTo(UsuarioInterno::class, 'admin_responsavel_id');
    }

    /**
     * Retorna o label do tipo
     */
    public function getTipoLabelAttribute()
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /**
     * Retorna o label do status
     */
    public function getStatusLabelAttribute()
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * Retorna a cor do status
     */
    public function getStatusColorAttribute()
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    /**
     * Verifica se o usuário pode editar a sugestão
     */
    public function podeEditar($usuario)
    {
        if (!$usuario) return false;
        
        // Admin pode tudo
        if ($usuario->isAdmin()) return true;
        
        // Criador pode editar apenas se ainda estiver pendente
        if ($this->usuario_interno_id === $usuario->id && $this->status === 'pendente') {
            return true;
        }
        
        return false;
    }

    /**
     * Verifica se o usuário pode excluir a sugestão
     */
    public function podeExcluir($usuario)
    {
        if (!$usuario) return false;
        
        // Admin pode tudo
        if ($usuario->isAdmin()) return true;
        
        // Criador pode excluir apenas se ainda estiver pendente
        if ($this->usuario_interno_id === $usuario->id && $this->status === 'pendente') {
            return true;
        }
        
        return false;
    }

    /**
     * Verifica se o usuário pode gerenciar (admin)
     */
    public function podeGerenciar($usuario)
    {
        if (!$usuario) return false;
        return $usuario->isAdmin();
    }

    /**
     * Scope para filtrar por página
     */
    public function scopeDaPagina($query, $url)
    {
        return $query->where('pagina_url', $url);
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeComStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Retorna o progresso do checklist em porcentagem
     */
    public function getProgressoChecklistAttribute()
    {
        if (empty($this->checklist)) return 0;
        
        $total = count($this->checklist);
        $concluidos = collect($this->checklist)->where('concluido', true)->count();
        
        return $total > 0 ? round(($concluidos / $total) * 100) : 0;
    }
}
