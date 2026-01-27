<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceAppUrl
{
    /**
     * Handle an incoming request.
     * Força o Laravel a usar o domínio correto para todas as URLs geradas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Força o domínio correto
        URL::forceScheme('https');
        URL::forceRootUrl('https://sistemas.saude.to.gov.br/infovisacore');
        
        return $next($request);
    }
}
