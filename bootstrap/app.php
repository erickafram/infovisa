<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'api/consultar-cnpj',
            'api/verificar-cnpj/*',
            'admin/ia/chat',
            'admin/ia/chat-edicao-documento',
            'admin/ia/extrair-pdf',
        ]);
        
        // Registrar alias para middleware customizado
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'admin.gestor.estadual' => \App\Http\Middleware\EnsureUserIsAdminOrGestorEstadual::class,
        ]);
        
        // Configurar redirect para usuários não autenticados
        $middleware->redirectGuestsTo(function ($request) {
            // Se a rota começa com /admin ou /company, redireciona para login
            if ($request->is('admin/*') || $request->is('company/*')) {
                return route('login');
            }
            return route('login');
        });
        
        // Configurar redirect após autenticação bem-sucedida
        $middleware->redirectUsersTo(function () {
            // Detecta qual guard está autenticado e redireciona adequadamente
            if (auth('interno')->check()) {
                return route('admin.dashboard');
            }
            if (auth('externo')->check()) {
                return route('company.dashboard');
            }
            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
