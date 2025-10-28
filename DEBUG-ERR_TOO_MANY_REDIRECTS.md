# 🔍 Debug: ERR_TOO_MANY_REDIRECTS

## 📋 Problema Identificado

Erro `ERR_TOO_MANY_REDIRECTS` ao acessar `http://localhost:8001/admin/documentos/{id}/edit`

Este erro ocorre quando há um **loop infinito de redirecionamentos** entre middlewares, controllers ou rotas.

---

## 🎯 Análise do Código Atual

### ✅ Rota Configurada Corretamente
```php
// routes/web.php - Linha 113
Route::get('/documentos/{id}/edit', [DocumentoDigitalController::class, 'edit'])
    ->name('documentos.edit');
```

**Status:** ✅ Rota está dentro do grupo `auth:interno` (linha 68)

### ✅ Controller Edit Method
```php
// DocumentoDigitalController.php - Linha 158
public function edit($id)
{
    $documento = DocumentoDigital::with([...])->findOrFail($id);
    
    // Validação: apenas rascunhos podem ser editados
    if ($documento->status !== 'rascunho') {
        return back()->with('error', 'Apenas documentos em rascunho podem ser editados.');
    }
    
    // ... código ...
    
    return view('documentos.edit', compact(...));
}
```

**Status:** ✅ Controller retorna view corretamente, sem redirects

---

## 🔴 Possíveis Causas do Loop

### 1. **Middleware de Autenticação Sem Configuração de Redirect**

Laravel 11 não possui mais `app/Http/Middleware/Authenticate.php` customizável. A configuração de redirect é feita no `bootstrap/app.php`.

**Problema:** Se o middleware `auth:interno` não souber para onde redirecionar usuários não autenticados, pode causar loop.

### 2. **Usuário Não Autenticado no Guard Correto**

Se o usuário está autenticado no guard `web` mas a rota exige `auth:interno`, haverá redirecionamento.

### 3. **Redirect em Blade ou JavaScript**

Pode haver redirect automático na view `documentos.edit.blade.php` ou em JavaScript.

### 4. **Session/Cookie Corrompida**

Sessão ou cookie corrompido pode causar falha na autenticação repetida.

---

## 🛠️ Passos para Debug

### **Passo 1: Verificar Autenticação**

Execute no terminal:

```bash
php artisan tinker
```

Depois teste:

```php
// Verificar se existe usuário interno
$usuario = App\Models\UsuarioInterno::first();
dd($usuario);

// Verificar guard
Auth::guard('interno')->check();
```

---

### **Passo 2: Adicionar Logging no Controller**

Edite `DocumentoDigitalController.php` método `edit`:

```php
public function edit($id)
{
    \Log::info('=== EDIT DOCUMENTO CHAMADO ===', [
        'documento_id' => $id,
        'usuario_autenticado' => auth('interno')->check(),
        'usuario_id' => auth('interno')->id(),
        'url_atual' => request()->url(),
        'url_anterior' => request()->header('referer'),
    ]);
    
    $documento = DocumentoDigital::with(['tipoDocumento', 'processo', 'assinaturas', 'versoes.usuarioInterno'])
        ->findOrFail($id);

    \Log::info('Documento encontrado', [
        'status' => $documento->status,
        'pode_editar' => $documento->status === 'rascunho'
    ]);

    if ($documento->status !== 'rascunho') {
        \Log::warning('Tentativa de editar documento não-rascunho');
        return back()->with('error', 'Apenas documentos em rascunho podem ser editados.');
    }

    // ... resto do código ...
    
    \Log::info('Retornando view documentos.edit');
    return view('documentos.edit', compact('documento', 'tiposDocumento', 'usuariosInternos', 'processo'));
}
```

Depois acesse a rota e verifique o log:

```bash
tail -f storage/logs/laravel.log
```

---

### **Passo 3: Configurar Redirect do Middleware Auth**

Edite `bootstrap/app.php`:

```php
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
        ]);
        
        // ADICIONE ESTA CONFIGURAÇÃO
        $middleware->redirectGuestsTo(function ($request) {
            // Se a rota começa com /admin, redireciona para login
            if ($request->is('admin/*')) {
                return route('login');
            }
            // Se a rota começa com /company, redireciona para login
            if ($request->is('company/*')) {
                return route('login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

---

### **Passo 4: Verificar View Blade**

Verifique se existe a view e se não há redirects:

```bash
# Verificar se a view existe
ls resources/views/documentos/edit.blade.php
```

Se existir, abra e procure por:
- `<meta http-equiv="refresh"`
- `window.location`
- `header('Location:`
- Qualquer código JavaScript que faça redirect

---

### **Passo 5: Limpar Cache e Sessões**

```bash
# Limpar cache de rotas
php artisan route:clear

# Limpar cache de configuração
php artisan config:clear

# Limpar cache de views
php artisan view:clear

# Limpar cache geral
php artisan cache:clear

# Limpar sessões antigas
php artisan session:clear
```

Depois, **limpe os cookies do navegador** para `localhost:8001`.

---

### **Passo 6: Testar Autenticação Manualmente**

Crie uma rota de teste em `routes/web.php`:

```php
// ROTA DE TESTE - REMOVER DEPOIS
Route::get('/test-auth', function () {
    return [
        'interno_autenticado' => auth('interno')->check(),
        'interno_usuario_id' => auth('interno')->id(),
        'interno_usuario' => auth('interno')->user()?->nome,
        'externo_autenticado' => auth('externo')->check(),
        'web_autenticado' => auth('web')->check(),
        'guards_disponiveis' => array_keys(config('auth.guards')),
    ];
})->middleware('auth:interno');
```

Acesse: `http://localhost:8001/test-auth`

Se der erro de redirect, o problema é no middleware de autenticação.

---

### **Passo 7: Verificar Documento Status**

Pode ser que o documento não seja rascunho e o `back()` esteja causando loop:

```php
// Substitua o back() por redirect específico
if ($documento->status !== 'rascunho') {
    return redirect()->route('admin.documentos.show', $id)
        ->with('error', 'Apenas documentos em rascunho podem ser editados.');
}
```

---

## 🔧 Correções Recomendadas

### **Correção 1: Configurar Redirect do Auth (PRINCIPAL)**

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'api/consultar-cnpj',
        'api/verificar-cnpj/*',
    ]);
    
    // Configurar redirect para usuários não autenticados
    $middleware->redirectGuestsTo(fn () => route('login'));
    
    // Configurar redirect após autenticação
    $middleware->redirectUsersTo(function ($request) {
        // Detecta qual guard está autenticado
        if (auth('interno')->check()) {
            return route('admin.dashboard');
        }
        if (auth('externo')->check()) {
            return route('company.dashboard');
        }
        return '/';
    });
})
```

---

### **Correção 2: Adicionar Logging Temporário**

```php
// DocumentoDigitalController.php - método edit
public function edit($id)
{
    // ADICIONE ISTO NO INÍCIO
    \Log::channel('daily')->info('DocumentoDigitalController@edit chamado', [
        'id' => $id,
        'auth_check' => auth('interno')->check(),
        'user_id' => auth('interno')->id(),
    ]);
    
    // ... resto do código ...
}
```

---

### **Correção 3: Substituir back() por Redirect Específico**

```php
// DocumentoDigitalController.php - linha 165
if ($documento->status !== 'rascunho') {
    // ANTES:
    // return back()->with('error', 'Apenas documentos em rascunho podem ser editados.');
    
    // DEPOIS:
    return redirect()->route('admin.documentos.show', $documento->id)
        ->with('error', 'Apenas documentos em rascunho podem ser editados.');
}
```

---

### **Correção 4: Criar Middleware Customizado (Opcional)**

Se o problema persistir, crie um middleware específico:

```bash
php artisan make:middleware EnsureInternalAuth
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureInternalAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('interno')->check()) {
            \Log::warning('Usuário não autenticado tentou acessar área admin', [
                'url' => $request->url(),
                'ip' => $request->ip(),
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Você precisa estar autenticado como usuário interno.');
        }

        return $next($request);
    }
}
```

Registre em `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'internal.auth' => \App\Http\Middleware\EnsureInternalAuth::class,
    ]);
})
```

Use nas rotas:

```php
Route::middleware('internal.auth')->prefix('admin')->name('admin.')->group(function () {
    // rotas admin
});
```

---

## 📊 Checklist de Debug

- [ ] Verificar se usuário está autenticado no guard correto (`auth:interno`)
- [ ] Verificar logs em `storage/logs/laravel.log`
- [ ] Limpar cache (rotas, config, views, sessões)
- [ ] Limpar cookies do navegador
- [ ] Verificar se view `documentos/edit.blade.php` existe
- [ ] Verificar se há redirects na view (meta refresh, JavaScript)
- [ ] Testar rota `/test-auth` para confirmar autenticação
- [ ] Verificar status do documento (deve ser 'rascunho')
- [ ] Adicionar logging no controller
- [ ] Configurar `redirectGuestsTo` no `bootstrap/app.php`
- [ ] Substituir `back()` por redirect específico

---

## 🎯 Solução Mais Provável

**O problema mais comum é a falta de configuração do `redirectGuestsTo` no Laravel 11.**

Adicione isto em `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'api/consultar-cnpj',
        'api/verificar-cnpj/*',
    ]);
    
    // ADICIONE ESTA LINHA
    $middleware->redirectGuestsTo(fn () => route('login'));
})
```

Depois limpe o cache:

```bash
php artisan config:clear
php artisan route:clear
```

E limpe os cookies do navegador.

---

## 📞 Próximos Passos

1. **Aplique a Correção 1** (configurar redirectGuestsTo)
2. **Limpe cache e cookies**
3. **Teste novamente**
4. Se persistir, **adicione logging** (Correção 2)
5. Verifique os logs e me envie o resultado

---

## 🔗 Referências

- [Laravel 11 Authentication](https://laravel.com/docs/11.x/authentication)
- [Laravel 11 Middleware](https://laravel.com/docs/11.x/middleware)
- [Debugging Redirects](https://laravel.com/docs/11.x/responses#redirects)
