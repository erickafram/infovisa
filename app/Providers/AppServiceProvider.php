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
        // Configura paginação para usar Tailwind CSS
        \Illuminate\Pagination\Paginator::useTailwind();
        
        // Só força o domínio se APP_URL contém o domínio de produção
        $appUrl = config('app.url', '');
        $host = request()->getHost();
        
        $isLocalhost = str_contains($host, 'localhost') || 
                       str_contains($host, '127.0.0.1') ||
                       $host === '::1' ||
                       str_contains($host, '.test') ||
                       str_contains($host, '.local');
        
        $isProductionUrl = str_contains($appUrl, 'sistemas.saude.to.gov.br');
        
        // Detecta se está acessando via IP (ex: 10.48.208.42)
        $isAccessingViaIP = filter_var($host, FILTER_VALIDATE_IP) !== false;
        
        // Aplica se APP_URL é de produção E (não está acessando via localhost OU está acessando via IP)
        if ($isProductionUrl && (!$isLocalhost || $isAccessingViaIP)) {
            URL::forceScheme('https');
            URL::forceRootUrl($appUrl);
            
            // Força o Paginator a usar a URL correta
            \Illuminate\Pagination\Paginator::currentPathResolver(function () use ($appUrl) {
                $path = request()->path();
                return rtrim($appUrl, '/') . '/' . ltrim($path, '/');
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
