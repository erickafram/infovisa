<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Agendamento de tarefas automáticas
 */
Schedule::command('processos:licenciamento-anual')
    ->yearlyOn(1, 1, '00:01') // 1º de janeiro às 00:01
    ->timezone('America/Sao_Paulo')
    ->onSuccess(function () {
        \Log::info('Processos de licenciamento anual criados com sucesso');
    })
    ->onFailure(function () {
        \Log::error('Falha ao criar processos de licenciamento anual');
    });
