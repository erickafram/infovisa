# Deploy para Produção - Sistema de Pactuação Corrigido

## Alterações Realizadas

### 1. Controller Atualizado
- `app/Http/Controllers/Admin/PactuacaoController.php`
- Método `storeMultiple` agora processa corretamente os campos `tabela`, `classificacao_risco` e `pergunta`
- Validação ajustada para aceitar `municipio` como nullable

### 2. View Atualizada
- `resources/views/admin/pactuacoes/index.blade.php`
- Modal aumentado de `max-w-4xl` para `max-w-7xl`
- Modal não fecha mais ao clicar fora
- Todas as rotas AJAX corrigidas para usar `url()` em vez de `route()`
- Adicionado debug melhorado para erros
- Removido código duplicado

### 3. Funcionalidades Corrigidas
✅ Adicionar atividades (múltiplas de uma vez)
✅ Remover atividades
✅ Ativar/Desativar atividades
✅ Editar observações
✅ Adicionar/Remover exceções (municípios descentralizados)
✅ Pesquisa de atividades
✅ Suporte para Tabelas I, II, III, IV e V

## Comandos para Deploy em Produção

```bash
# 1. Conectar ao servidor
ssh root@sistemas.saude.to.gov.br

# 2. Navegar até o diretório do projeto
cd /var/www/html/infovisacore

# 3. Fazer backup do banco (IMPORTANTE!)
pg_dump -U postgres -d infovisa > backup_antes_deploy_$(date +%Y%m%d_%H%M%S).sql

# 4. Fazer pull das alterações
git pull origin main

# 5. Limpar todos os caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# 6. Ajustar permissões
sudo chown -R apache:apache storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 7. Reiniciar serviços (se necessário)
sudo systemctl restart php-fpm
sudo systemctl restart httpd
```

## Verificação Pós-Deploy

1. Acesse: https://sistemas.saude.to.gov.br/infovisacore/admin/configuracoes/pactuacao
2. Teste adicionar uma atividade
3. Teste remover uma atividade
4. Teste ativar/desativar uma atividade
5. Verifique se o modal está grande e não fecha ao clicar fora

## Troubleshooting

### Se ainda der erro 404:
```bash
# Verificar se as rotas estão carregadas
php artisan route:list --name=pactuacao

# Limpar cache do OPcache (se estiver usando)
sudo systemctl restart php-fpm
```

### Se der erro de permissão:
```bash
sudo chown -R apache:apache /var/www/html/infovisacore
sudo chmod -R 755 /var/www/html/infovisacore
sudo chmod -R 775 /var/www/html/infovisacore/storage
sudo chmod -R 775 /var/www/html/infovisacore/bootstrap/cache
```

### Se o JavaScript não atualizar:
- Limpe o cache do navegador (Ctrl+Shift+Delete)
- Ou force refresh (Ctrl+F5)

## Notas Importantes

- ⚠️ **SEMPRE faça backup do banco antes de fazer deploy**
- ⚠️ As alterações são apenas em código, não há migrations novas
- ⚠️ A tabela `pactuacoes` já tem todas as colunas necessárias
- ✅ Todas as funcionalidades foram testadas localmente
- ✅ O sistema está funcionando corretamente no ambiente local

## Contato

Se houver algum problema após o deploy, verifique:
1. Logs do Laravel: `storage/logs/laravel.log`
2. Logs do Apache: `/var/log/httpd/error_log`
3. Console do navegador (F12)
