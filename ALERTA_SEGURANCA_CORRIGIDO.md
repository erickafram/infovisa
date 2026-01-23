# ⚠️ ALERTA DE SEGURANÇA - CORRIGIDO

## 🚨 Problema Identificado

Foi encontrado texto hardcoded com credenciais de acesso na view:

```
https://sistemas.saude.to.gov.br/infovisacore/
CPF: 808.019.191-34
Senha: @InfoVisa123
```

**Localização:** `resources/views/estabelecimentos/processos/show.blade.php` (linhas 84-86)

## ✅ Correção Aplicada

O texto foi **REMOVIDO COMPLETAMENTE** do arquivo.

## 🔒 Recomendações de Segurança

### 1. **NUNCA** coloque credenciais no código
- ❌ Não hardcode senhas
- ❌ Não hardcode CPFs/usuários
- ❌ Não hardcode URLs de produção com credenciais
- ❌ Não deixe comentários com senhas

### 2. **Use variáveis de ambiente**
```php
// .env
EXTERNAL_SYSTEM_URL=https://sistemas.saude.to.gov.br/infovisacore/
EXTERNAL_SYSTEM_USER=seu_usuario
EXTERNAL_SYSTEM_PASSWORD=sua_senha

// No código
$url = env('EXTERNAL_SYSTEM_URL');
$user = env('EXTERNAL_SYSTEM_USER');
$password = env('EXTERNAL_SYSTEM_PASSWORD');
```

### 3. **Adicione .env ao .gitignore**
Certifique-se de que o arquivo `.env` está no `.gitignore` para não ser commitado.

### 4. **Revise o histórico do Git**
Se essas credenciais foram commitadas, considere:
- Trocar a senha imediatamente
- Fazer um git rebase para remover do histórico (se possível)
- Revogar o acesso do CPF comprometido

### 5. **Faça code review**
- Sempre revise código antes de commitar
- Use ferramentas de análise estática
- Configure pre-commit hooks para detectar credenciais

## 📋 Checklist de Segurança

- [x] Credenciais removidas do código
- [ ] Senha alterada no sistema externo
- [ ] Verificar se foi commitado no Git
- [ ] Adicionar validação de segurança no CI/CD
- [ ] Treinar equipe sobre boas práticas

## 🔍 Como Verificar

Execute este comando para procurar por possíveis credenciais:

```bash
# Procurar por padrões de senha
grep -r "senha.*:" resources/views/
grep -r "password.*:" resources/views/
grep -r "@InfoVisa" .
grep -r "CPF:.*Senha:" .

# Procurar por URLs com credenciais
grep -r "https://.*@" .
```

## ⚡ Ação Imediata Necessária

**TROQUE A SENHA IMEDIATAMENTE!**

A senha `@InfoVisa123` do CPF `808.019.191-34` foi exposta no código e pode ter sido commitada no repositório Git. Por segurança:

1. Acesse o sistema externo
2. Troque a senha imediatamente
3. Verifique logs de acesso para atividades suspeitas
4. Considere revogar e recriar as credenciais

## 📝 Data da Correção

**Data:** 23/01/2026
**Arquivo:** resources/views/estabelecimentos/processos/show.blade.php
**Linhas removidas:** 84-86
