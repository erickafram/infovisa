<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        // SEMPRE força o domínio correto em produção
        // Isso resolve o problema de paginação gerando URLs com IP interno
        $host = request()->getHost();
        $isProduction = config('app.env') === 'production' || 
                        app()->environment('production') ||
                        str_contains($host, 'sistemas.saude.to.gov.br') ||
                        str_contains(config('app.url', ''), 'sistemas.saude.to.gov.br');
        
        // Se é produção OU se o APP_URL está configurado para o domínio correto
        if ($isProduction || str_contains(config('app.url', ''), 'infovisacore')) {
            URL::forceScheme('https');
            URL::forceRootUrl('https://sistemas.saude.to.gov.br/infovisacore');
            
            // Força o Paginator a usar a URL correta
            \Illuminate\Pagination\Paginator::currentPathResolver(function () {
                $path = request()->path();
                return 'https://sistemas.saude.to.gov.br/infovisacore/' . ltrim($path, '/');
            });
        }
        
        // Configura tamanho padrão de strings para MySQL
        Schema::defaultStringLength(191);
        
        // Garante que o diretório de processos existe
        if (!Storage::disk('local')->exists('processos')) {
            Storage::disk('local')->makeDirectory('processos');
        }
        
        // Registra Observer de OrdemServico
        OrdemServico::observe(OrdemServicoObserver::class);
    }
}
