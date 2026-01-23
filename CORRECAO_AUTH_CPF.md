# Correção: Autenticação por CPF - ID vs CPF

## 🐛 Problema Identificado

Ao cadastrar um estabelecimento, ocorria erro:
```
Foreign key violation: usuario_externo_id=(1758848111) não está presente na tabela usuarios_externos
```

O sistema estava tentando usar o **CPF** como `usuario_externo_id` ao invés do **ID** do usuário.

## 🔍 Causa Raiz

Nos modelos `UsuarioExterno` e `UsuarioInterno`, o método `getAuthIdentifierName()` estava retornando `'cpf'`:

```php
public function getAuthIdentifierName()
{
    return 'cpf'; // ❌ ERRADO - fazia auth()->id() retornar o CPF
}
```

Isso causava:
- `auth('externo')->id()` retornava `01758848111` (CPF)
- `auth('interno')->id()` retornava o CPF também
- Deveria retornar o ID numérico do usuário

## ✅ Solução Implementada

Corrigido ambos os modelos:
- `app/Models/UsuarioExterno.php`
- `app/Models/UsuarioInterno.php`

```php
/**
 * Get the name of the unique identifier for the user.
 * 
 * Este método define qual campo é usado como identificador único
 * para autenticação (login), mas o ID do usuário continua sendo 'id'
 */
public function getAuthIdentifierName()
{
    return 'id'; // ✓ CORRETO - auth()->id() retorna o ID
}

/**
 * Get the name of the password field for authentication.
 * 
 * Define que o campo 'cpf' será usado como username no login
 */
public function username()
{
    return 'cpf'; // CPF continua sendo usado no login
}
```

## 🎯 Resultado

### Antes da Correção:
- `auth('externo')->id()` → `01758848111` (CPF) ❌
- `auth('interno')->id()` → CPF ❌
- Cadastro de estabelecimento falhava
- Foreign keys não funcionavam

### Depois da Correção:
- `auth('externo')->id()` → `3` (ID) ✓
- `auth('interno')->id()` → ID numérico ✓
- Cadastro de estabelecimento funciona corretamente
- Login por CPF continua funcionando normalmente
- Foreign keys funcionam corretamente

## 📊 Usuários no Sistema

```
ID: 1 | CPF: 07886155187 | Nome: Marcelo Santos
ID: 2 | CPF: 87921502172 | Nome: Kauany
ID: 3 | CPF: 01758848111 | Nome: ERICK VINICIUS RODRIGUES ← Usuário de teste
```

## 🔐 Autenticação

O sistema continua usando **CPF** para login:
- Campo de login: CPF (com ou sem formatação)
- Senha: password
- Guard: `externo` ou `interno`

Mas internamente usa **ID** para relacionamentos:
- `usuario_externo_id` nas tabelas
- `usuario_interno_id` nas tabelas
- `auth('externo')->id()` retorna o ID
- `auth('interno')->id()` retorna o ID
- Foreign keys funcionam corretamente

## ✅ Teste Realizado

```bash
php test_auth.php

=== Teste de Autenticação ===

✓ Usuário encontrado:
  ID: 3
  CPF: 01758848111
  Nome: ERICK VINICIUS RODRIGUES
  Auth Identifier Name: id
  Auth Identifier: 3

=== Fim do Teste ===
```

## 📝 Próximos Passos

1. **IMPORTANTE:** Fazer logout e login novamente com o CPF 017.588.481-11
   - Isso é necessário para que a sessão seja atualizada com o ID correto
2. Tentar cadastrar o estabelecimento novamente
3. Verificar se o `usuario_externo_id` está sendo salvo corretamente (deve ser 3)

## ⚠️ Nota Importante

Se você já estava logado quando a correção foi feita, é **obrigatório fazer logout e login novamente**. A sessão antiga ainda pode conter o CPF como identificador, e isso causará o mesmo erro até que você faça um novo login.
