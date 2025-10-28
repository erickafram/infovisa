# ✅ Solução Aplicada: ERR_TOO_MANY_REDIRECTS

## 🎯 Problema
Erro `ERR_TOO_MANY_REDIRECTS` ao acessar `/admin/documentos/{id}/edit`

## 🔧 Correções Aplicadas

### 1. ✅ Configuração de Middleware (bootstrap/app.php)
**Problema:** Laravel 11 não tinha configuração de redirect para usuários não autenticados.

**Solução:**
```php
$middleware->redirectGuestsTo(function ($request) {
    if ($request->is('admin/*') || $request->is('company/*')) {
        return route('login');
    }
    return route('login');
});

$middleware->redirectUsersTo(function () {
    if (auth('interno')->check()) {
        return route('admin.dashboard');
    }
    if (auth('externo')->check()) {
        return route('company.dashboard');
    }
    return '/';
});
```

### 2. ✅ Logging no Controller (DocumentoDigitalController.php)
**Problema:** Sem visibilidade do que estava acontecendo.

**Solução:** Adicionado logging detalhado:
```php
\Log::info('DocumentoDigitalController@edit chamado', [
    'documento_id' => $id,
    'usuario_autenticado' => auth('interno')->check(),
    'usuario_id' => auth('interno')->id(),
    'url_atual' => request()->url(),
]);
```

### 3. ✅ Redirect Específico ao Invés de back()
**Problema:** `back()` pode causar loop se a URL anterior também redirecionar.

**Solução:**
```php
// ANTES:
return back()->with('error', 'Apenas documentos em rascunho podem ser editados.');

// DEPOIS:
return redirect()->route('admin.documentos.show', $documento->id)
    ->with('error', 'Apenas documentos em rascunho podem ser editados.');
```

### 4. ✅ Rota de Debug Criada
**Rota:** `/test-auth-debug`

Retorna JSON com informações de autenticação:
- Status de cada guard (interno, externo, web)
- ID e nome do usuário autenticado
- Informações de sessão
- Guards disponíveis

## 📋 Próximos Passos

### Passo 1: Limpar Cache
Execute o script:
```bash
limpar-cache-debug.bat
```

Ou manualmente:
```bash
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### Passo 2: Limpar Cookies do Navegador
1. Abra DevTools (F12)
2. Application > Cookies
3. Delete todos os cookies de `localhost:8001`
4. Ou use modo anônimo

### Passo 3: Testar Autenticação
Acesse: `http://localhost:8001/test-auth-debug`

**Resultado esperado:**
```json
{
  "interno": {
    "autenticado": true,
    "usuario_id": 1,
    "usuario_nome": "Administrador do Sistema",
    "usuario_email": "admin@infovisa.gov.br"
  },
  "externo": {
    "autenticado": false,
    "usuario_id": null
  },
  "web": {
    "autenticado": false
  }
}
```

### Passo 4: Testar Rota Original
Acesse: `http://localhost:8001/admin/documentos/{id}/edit`

Substitua `{id}` por um ID de documento válido em status "rascunho".

### Passo 5: Verificar Logs
```bash
tail -f storage/logs/laravel.log
```

Procure por:
- `DocumentoDigitalController@edit chamado`
- Informações de autenticação
- Possíveis erros

## 🔍 Diagnóstico

### Se ainda houver erro, verifique:

#### 1. Usuário não está autenticado
```bash
php artisan tinker
```
```php
auth('interno')->check(); // Deve retornar true
auth('interno')->user(); // Deve retornar o usuário
```

#### 2. Documento não é rascunho
```bash
php artisan tinker
```
```php
$doc = App\Models\DocumentoDigital::find(ID_AQUI);
$doc->status; // Deve ser 'rascunho'
```

#### 3. View não existe
```bash
ls resources/views/documentos/edit.blade.php
```

#### 4. Redirect na View
Abra `resources/views/documentos/edit.blade.php` e procure por:
- `<meta http-equiv="refresh"`
- `window.location`
- `header('Location:`

## 🎯 Causas Mais Comuns

### 1. ❌ Middleware sem configuração de redirect (CORRIGIDO)
Laravel 11 precisa de `redirectGuestsTo()` configurado.

### 2. ❌ Usuário autenticado no guard errado
Usuário logado como `web` mas rota exige `interno`.

### 3. ❌ Documento não é rascunho (CORRIGIDO)
O `back()` causava loop. Agora usa redirect específico.

### 4. ❌ Sessão corrompida
Limpar cookies resolve.

### 5. ❌ Cache desatualizado
Limpar cache resolve.

## 📊 Checklist de Verificação

- [x] Configurar `redirectGuestsTo` no middleware
- [x] Adicionar logging no controller
- [x] Substituir `back()` por redirect específico
- [x] Criar rota de debug `/test-auth-debug`
- [ ] Limpar cache do Laravel
- [ ] Limpar cookies do navegador
- [ ] Testar rota `/test-auth-debug`
- [ ] Verificar logs em `storage/logs/laravel.log`
- [ ] Testar rota `/admin/documentos/{id}/edit`

## 🚀 Comandos Rápidos

```bash
# Limpar tudo
php artisan route:clear && php artisan config:clear && php artisan view:clear && php artisan cache:clear

# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Testar autenticação
curl http://localhost:8001/test-auth-debug

# Verificar documento no banco
php artisan tinker
>>> App\Models\DocumentoDigital::find(1)->status
```

## 📞 Suporte

Se o problema persistir após seguir todos os passos:

1. Verifique os logs: `storage/logs/laravel.log`
2. Acesse `/test-auth-debug` e envie o resultado
3. Verifique se o documento existe e é rascunho
4. Teste em modo anônimo do navegador

## 📚 Documentação Completa

Veja `DEBUG-ERR_TOO_MANY_REDIRECTS.md` para análise detalhada e mais opções de debug.
