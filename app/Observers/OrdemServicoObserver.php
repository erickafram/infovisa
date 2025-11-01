<?php

namespace App\Observers;

use App\Models\OrdemServico;
use App\Models\Notificacao;

class OrdemServicoObserver
{
    /**
     * Handle the OrdemServico "created" event.
     */
    public function created(OrdemServico $ordemServico): void
    {
        // Envia notificações para técnicos atribuídos
        $this->notificarTecnicos($ordemServico);
    }

    /**
     * Handle the OrdemServico "updated" event.
     */
    public function updated(OrdemServico $ordemServico): void
    {
        // Verifica se houve mudança nos técnicos
        if ($ordemServico->wasChanged('tecnicos_ids')) {
            $tecnicosAntigos = $ordemServico->getOriginal('tecnicos_ids') ?? [];
            $tecnicosNovos = $ordemServico->tecnicos_ids ?? [];
            
            // Encontra novos técnicos adicionados
            $tecnicosAdicionados = array_diff($tecnicosNovos, $tecnicosAntigos);
            
            if (!empty($tecnicosAdicionados)) {
                $this->notificarTecnicos($ordemServico, $tecnicosAdicionados);
            }
        }
    }

    /**
     * Notifica técnicos sobre atribuição de OS
     */
    private function notificarTecnicos(OrdemServico $ordemServico, array $tecnicosIds = null)
    {
        $tecnicosIds = $tecnicosIds ?? $ordemServico->tecnicos_ids ?? [];
        
        if (empty($tecnicosIds)) {
            return;
        }
        
        foreach ($tecnicosIds as $tecnicoId) {
            // Calcula prioridade baseado na data de término
            $prioridade = 'normal';
            if ($ordemServico->data_fim) {
                $diasRestantes = now()->diffInDays($ordemServico->data_fim, false);
                if ($diasRestantes < 0) {
                    $prioridade = 'urgente'; // Prazo vencido
                } elseif ($diasRestantes <= 3) {
                    $prioridade = 'alta'; // Menos de 3 dias
                } elseif ($diasRestantes <= 7) {
                    $prioridade = 'normal'; // Menos de 7 dias
                }
            }
            
            // Monta mensagem com ou sem estabelecimento
            $estabelecimentoInfo = $ordemServico->estabelecimento 
                ? ' do estabelecimento ' . $ordemServico->estabelecimento->nome_fantasia 
                : ' (sem estabelecimento vinculado)';
            
            $mensagem = 'Você foi atribuído à OS #' . $ordemServico->numero . $estabelecimentoInfo . '. Prazo: ' . ($ordemServico->data_fim ? $ordemServico->data_fim->format('d/m/Y') : 'Não definido');
            
            Notificacao::create([
                'usuario_interno_id' => $tecnicoId,
                'tipo' => 'ordem_servico_atribuida',
                'titulo' => 'Nova OS Atribuída: #' . $ordemServico->numero,
                'mensagem' => $mensagem,
                'link' => route('admin.ordens-servico.show', $ordemServico),
                'ordem_servico_id' => $ordemServico->id,
                'prioridade' => $prioridade,
            ]);
        }
    }

    /**
     * Handle the OrdemServico "deleted" event.
     */
    public function deleted(OrdemServico $ordemServico): void
    {
        //
    }

    /**
     * Handle the OrdemServico "restored" event.
     */
    public function restored(OrdemServico $ordemServico): void
    {
        //
    }

    /**
     * Handle the OrdemServico "force deleted" event.
     */
    public function forceDeleted(OrdemServico $ordemServico): void
    {
        //
    }
}
