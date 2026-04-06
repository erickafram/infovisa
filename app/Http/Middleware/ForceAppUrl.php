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
        // Só força em produção
        if (config('app.env') === 'production') {
            $appUrl = config('app.url');
            if ($appUrl) {
                URL::forceScheme('https');
                URL::forceRootUrl($appUrl);

                // Corrige a URL intended para incluir o prefixo do APP_URL
                // O proxy reverso faz strip do /infovisacore, então o Laravel
                // salva a URL sem o prefixo. Precisamos corrigir isso.
                $parsedUrl = parse_url($appUrl);
                $prefix = rtrim($parsedUrl['path'] ?? '', '/');

                if ($prefix && $request->session()) {
                    $intended = $request->session()->get('url.intended');
                    if ($intended) {
                        $parsedIntended = parse_url($intended);
                        $intendedPath = $parsedIntended['path'] ?? '';

                        // Se a URL intended não tem o prefixo, adiciona
                        if ($prefix && !str_starts_with($intendedPath, $prefix)) {
                            $corrected = $parsedIntended['scheme'] . '://' . $parsedIntended['host']
                                . $prefix . $intendedPath
                                . (isset($parsedIntended['query']) ? '?' . $parsedIntended['query'] : '');
                            $request->session()->put('url.intended', $corrected);
                        }
                    }
                }
            }
        }
        
        return $next($request);
    }
}
