# Correções Aplicadas - Sistema de Pactuação

## Problema Resolvido
O sistema estava retornando erro "Unexpected token '<', "<!DOCTYPE "... is not valid JSON" ao tentar salvar pactuações.

## Causa
O controller `PactuacaoController` não estava processando os novos campos (`tabela`, `classificacao_risco`, `pergunta`) que o formulário estava enviando.

## Correções Aplicadas

### 1. Controller Atualizado
**Arquivo:** `app/Http/Controllers/Admin/PactuacaoController.php`

O método `storeMultiple` foi atualizado para:
- Validar os campos `tabela`, `classificacao_risco` e `pergunta`
- Salvar esses campos no banco de dados junto com as atividades

### 2. Modal Melhorado
**Arquivo:** `resources/views/admin/pactuacoes/index.blade.php`

- Largura aumentada de `max-w-4xl` para `max-w-7xl`
- Modal não fecha mais ao clicar fora (apenas com botão X ou Cancelar)
- Removido código duplicado que causava erros no Alpine.js
- Adicionadas verificações de segurança para evitar erros de `trim()`

### 3. Estrutura do Banco
A tabela `pactuacoes` já possui todas as colunas necessárias:
- `tabela` (varchar) - Classificação I, II, III, IV ou V
- `classificacao_risco` (varchar) - baixo, medio ou alto
- `pergunta` (text) - Pergunta do questionário
- `municipios_excecao` (json) - Municípios descentralizados
- `observacao` (text) - Observações adicionais

## Funcionalidades Implementadas

### Tabela I - Municipal (139 municípios)
Atividades de competência dos 139 municípios do Tocantins

### Tabela II - Estadual Exclusiva
Atividades que são SEMPRE de competência estadual (não descentralizadas)

### Tabela III - Alto Risco Pactuado
Atividades estaduais descentralizadas para municípios específicos

### Tabela IV - Com Questionário (Estadual/Municipal)
- Competência definida por questionário
- Resposta SIM = Estadual | NÃO = Municipal
- Permite definir municípios descentralizados (se resposta for SIM)

### Tabela V - Definir se é VISA
- Questionário define se a atividade é sujeita à vigilância sanitária
- Resposta SIM = Sujeito à VISA | NÃO = Não sujeito
- Se SIM e sujeito à VISA, verifica se município é descentralizado

## Status
✅ **SISTEMA FUNCIONANDO** - Todas as correções foram aplicadas e testadas.

## Comandos Executados (Local)
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Para Aplicar em Produção
```bash
ssh root@sistemas.saude.to.gov.br
cd /var/www/html/infovisacore
git pull origin main
php artisan config:clear
php artisan cache:clear
php artisan view:clear
sudo chown -R apache:apache storage bootstrap/cache
```
