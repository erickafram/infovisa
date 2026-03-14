<?php

namespace App\Http\Middleware;

use App\Enums\NivelAcesso;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdminOrGestor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('interno')->check()) {
            abort(403, 'Acesso não autorizado.');
        }

        $user = auth('interno')->user();

        if (!in_array($user->nivel_acesso, [
            NivelAcesso::Administrador,
            NivelAcesso::GestorEstadual,
            NivelAcesso::GestorMunicipal,
        ], true)) {
            abort(403, 'Acesso restrito a administradores e gestores.');
        }

        return $next($request);
    }
}