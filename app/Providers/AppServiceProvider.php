<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use App\Models\OrdemServico;
use App\Observers\OrdemServicoObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Garante que o diretório de processos existe
        if (!Storage::disk('local')->exists('processos')) {
            Storage::disk('local')->makeDirectory('processos');
        }
        
        // Registra Observer de OrdemServico
        OrdemServico::observe(OrdemServicoObserver::class);
    }
}
