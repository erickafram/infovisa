<?php

namespace App\Listeners;

use App\Models\UsuarioInterno;
use Illuminate\Auth\Events\Login;

class RegistrarUltimoLogin
{
    public function handle(Login $event): void
    {
        if ($event->user instanceof UsuarioInterno) {
            $event->user->forceFill([
                'ultimo_login_em' => now(),
            ])->saveQuietly();
        }
    }
}
