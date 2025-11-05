<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\NivelAcesso;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado no guard 'interno'
        if (!auth('interno')->check()) {
            abort(403, 'Acesso não autorizado.');
        }

        $user = auth('interno')->user();

        // Verifica se o usuário é administrador
        if ($user->nivel_acesso !== NivelAcesso::Administrador) {
            abort(403, 'Acesso restrito a administradores.');
        }

        return $next($request);
    }
}
