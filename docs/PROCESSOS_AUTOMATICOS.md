# Sistema de Abertura Automática de Processos de Licenciamento

## 📋 Visão Geral

O sistema abre automaticamente processos de licenciamento sanitário para estabelecimentos ativos no início de cada ano (1º de janeiro).

## 🎯 Objetivo

Garantir que estabelecimentos licenciados tenham seus processos de renovação abertos automaticamente, permitindo que entreguem a documentação completa até março, mesmo que o alvará vença apenas em novembro.

## ⚙️ Como Funciona

### Critérios de Elegibilidade

Um estabelecimento é elegível para abertura automática de processo se:

1. ✅ **Estabelecimento está ATIVO** (`ativo = true`)
2. ✅ **Teve processo de licenciamento no ano anterior** (ano - 1)
3. ✅ **Ainda não tem processo de licenciamento no ano atual**

### Exemplo Prático

**Cenário:**
- Estabelecimento licenciado em novembro/2025
- Alvará vence em novembro/2026
- Em 1º de janeiro de 2026, o sistema abre automaticamente o processo
- Estabelecimento tem até março/2026 para entregar documentação completa

## 🚀 Execução

### Automática (Agendada)

O sistema executa automaticamente todo **1º de janeiro às 00:01** (horário de Brasília).

```php
// Configurado em: routes/console.php
Schedule::command('processos:licenciamento-anual')
    ->yearlyOn(1, 1, '00:01')
    ->timezone('America/Sao_Paulo');
```

### Manual (Linha de Comando)

#### Criar processos para o ano atual
```bash
php artisan processos:licenciamento-anual
```

#### Criar processos para um ano específico
```bash
php artisan processos:licenciamento-anual --ano=2026
```

#### Simular sem criar (Dry Run)
```bash
php artisan processos:licenciamento-anual --dry-run
```

#### Simular para ano específico
```bash
php artisan processos:licenciamento-anual --ano=2026 --dry-run
```

## 📊 Relatório de Execução

Ao executar o comando, você verá um relatório detalhado:

```
===========================================
Abertura Automática de Processos de Licenciamento
Ano: 2026
Modo: PRODUÇÃO
===========================================

Estabelecimentos elegíveis: 150

[████████████████████████████] 100%

===========================================
RELATÓRIO FINAL
===========================================
Total de estabelecimentos elegíveis: 150
Processos criados com sucesso: 145
Processos já existentes: 5
Erros: 0
===========================================
```

## 📝 Dados do Processo Criado

Cada processo criado automaticamente contém:

- **Estabelecimento:** ID do estabelecimento
- **Usuário:** `null` (criado pelo sistema)
- **Tipo:** `licenciamento`
- **Ano:** Ano atual
- **Número Sequencial:** Gerado automaticamente
- **Número do Processo:** Formato `ANO/XXXXX` (ex: 2026/00001)
- **Status:** `aberto`
- **Observações:** "Processo aberto automaticamente pelo sistema para renovação anual de licenciamento sanitário."

## 🔍 Logs

Todos os processos criados são registrados em logs:

```php
// Log de sucesso
Log::info("Processo de licenciamento criado automaticamente", [
    'estabelecimento_id' => 123,
    'estabelecimento_nome' => 'Restaurante ABC',
    'ano' => 2026,
]);

// Log de erro
Log::error("Erro ao criar processo automático de licenciamento", [
    'estabelecimento_id' => 456,
    'erro' => 'Mensagem do erro',
]);
```

## ⚠️ Importante

### Apenas Licenciamento

Este sistema automático aplica-se **APENAS** a processos de **licenciamento sanitário**.

Outros tipos de processo não são afetados:
- ❌ Análise de Rotulagem
- ❌ Projeto Arquitetônico
- ❌ Administrativo
- ❌ Descentralização

### Estabelecimentos Novos

Estabelecimentos que **nunca tiveram** processo de licenciamento não terão processo criado automaticamente. Eles devem abrir o primeiro processo manualmente.

### Duplicação

O sistema verifica se já existe processo de licenciamento para o ano antes de criar, evitando duplicações.

## 🛠️ Configuração do Servidor

Para que o agendamento funcione, você precisa configurar o **Cron** no servidor:

### Linux/Ubuntu

Adicione ao crontab:

```bash
* * * * * cd /caminho/para/laravel-infovisa && php artisan schedule:run >> /dev/null 2>&1
```

### Windows (Task Scheduler)

Crie uma tarefa agendada que execute a cada minuto:

```
Programa: php
Argumentos: artisan schedule:run
Diretório: C:\wamp64\www\infovisa\laravel-infovisa
```

## 🧪 Testes

### Testar Antes de 1º de Janeiro

```bash
# Simular criação para próximo ano
php artisan processos:licenciamento-anual --ano=2026 --dry-run

# Ver quais estabelecimentos seriam afetados
php artisan processos:licenciamento-anual --dry-run
```

### Verificar Agendamento

```bash
# Listar tarefas agendadas
php artisan schedule:list
```

## 📞 Suporte

Em caso de dúvidas ou problemas:
1. Verificar logs em `storage/logs/laravel.log`
2. Executar em modo `--dry-run` para simular
3. Verificar se o cron está configurado corretamente
