<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\NivelAcesso;

class EnsureUserIsAdminOrGestorEstadual
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

        // Verifica se o usuário é administrador ou gestor estadual
        if (!in_array($user->nivel_acesso, [NivelAcesso::Administrador, NivelAcesso::GestorEstadual])) {
            abort(403, 'Acesso restrito a administradores e gestores estaduais.');
        }

        return $next($request);
    }
}
