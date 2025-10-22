# 🤖 Sistema de Abertura Automática de Processos de Licenciamento

## 📌 Resumo Rápido

Todo **1º de janeiro às 00:01**, o sistema abre automaticamente processos de licenciamento sanitário para estabelecimentos que:

1. ✅ Estão **ativos**
2. ✅ Tiveram processo de licenciamento no **ano anterior**
3. ✅ Ainda **não têm** processo no ano atual

## 🎯 Por Quê?

Estabelecimentos licenciados em novembro/2025 (alvará vence em novembro/2026) precisam entregar documentação até **março/2026**. O sistema garante que o processo esteja aberto desde janeiro.

## 🚀 Comandos Disponíveis

### 1️⃣ Listar Estabelecimentos Elegíveis

```bash
# Ver quais estabelecimentos precisam de processo
php artisan processos:listar-elegiveis

# Ver para ano específico
php artisan processos:listar-elegiveis --ano=2026
```

### 2️⃣ Simular Criação (Dry Run)

```bash
# Simular sem criar de fato
php artisan processos:licenciamento-anual --dry-run

# Simular para ano específico
php artisan processos:licenciamento-anual --ano=2026 --dry-run
```

### 3️⃣ Criar Processos

```bash
# Criar para ano atual
php artisan processos:licenciamento-anual

# Criar para ano específico
php artisan processos:licenciamento-anual --ano=2026
```

## ⚙️ Configuração Automática

O sistema já está configurado para executar automaticamente em:
- **Data:** 1º de janeiro
- **Hora:** 00:01 (horário de Brasília)
- **Arquivo:** `routes/console.php`

## ⚠️ Importante

### ✅ Aplica-se APENAS a:
- Processos de **Licenciamento Sanitário**

### ❌ NÃO aplica-se a:
- Análise de Rotulagem
- Projeto Arquitetônico
- Administrativo
- Descentralização

### 🆕 Estabelecimentos Novos
Estabelecimentos que **nunca tiveram** licenciamento devem abrir o primeiro processo **manualmente**.

## 🛠️ Configurar Cron (Servidor)

Para funcionar automaticamente, configure o cron:

### Linux
```bash
* * * * * cd /caminho/para/laravel-infovisa && php artisan schedule:run >> /dev/null 2>&1
```

### Windows (Task Scheduler)
```
Programa: php
Argumentos: artisan schedule:run
Diretório: C:\wamp64\www\infovisa\laravel-infovisa
Executar: A cada 1 minuto
```

## 📊 Exemplo de Uso

```bash
# 1. Ver quem precisa de processo
php artisan processos:listar-elegiveis

# Saída:
# Total de estabelecimentos elegíveis: 150
# Já possuem processo em 2025: 5
# Precisam de processo em 2025: 145

# 2. Simular criação
php artisan processos:licenciamento-anual --dry-run

# 3. Criar de fato
php artisan processos:licenciamento-anual

# Saída:
# Processos criados com sucesso: 145
# Processos já existentes: 5
# Erros: 0
```

## 📝 Logs

Todos os processos criados são registrados em:
- `storage/logs/laravel.log`

## 🧪 Testar Agora

```bash
# Ver se tem estabelecimentos elegíveis
php artisan processos:listar-elegiveis

# Simular criação
php artisan processos:licenciamento-anual --dry-run
```

## 📚 Documentação Completa

Ver: `docs/PROCESSOS_AUTOMATICOS.md`
