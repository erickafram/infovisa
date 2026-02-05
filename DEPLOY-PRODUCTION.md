# Guia de Deploy em Produção

## Problema Atual
O erro acontece porque os arquivos de build (`public/build/*`) estão sendo trackeados pelo Git, mas não deveriam estar.

## Solução Rápida (Execute no servidor)

### Opção 1: Usando o script automatizado

```bash
cd /var/www/html/infovisa
chmod +x fix-production-deploy.sh
./fix-production-deploy.sh
```

### Opção 2: Comandos manuais

Execute os comandos abaixo **no servidor de produção**:

```bash
# 1. Entrar no diretório
cd /var/www/html/infovisa

# 2. Fazer stash das alterações locais
git stash

# 3. Fazer pull
git pull origin main

# 4. Remover arquivos de build do Git (se necessário)
git rm -r --cached public/build 2>/dev/null || true

# 5. Se houver mudanças, commitar
git status
# Se aparecer mudanças para commit:
git commit -m "Remove arquivos de build do Git"
git push origin main

# 6. Instalar dependências PHP
php composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs

# 7. Rodar migrações
php artisan migrate --force

# 8. Limpar caches
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear

# 9. Instalar dependências Node e fazer build
npm install
npm run build

# 10. Configurar cache
sudo php artisan config:cache && sudo chown apache:apache bootstrap/cache/config.php

# 11. Ajustar permissões
sudo chown -R apache:apache /var/www/html/infovisa/
sudo chmod -R 775 /var/www/html/infovisa/storage/ /var/www/html/infovisa/bootstrap/cache/

# 12. Reiniciar serviços
sudo systemctl restart httpd php-fpm
```

## Prevenindo o Problema no Futuro

Os arquivos de build já estão no `.gitignore`, mas se eles foram commitados antes, precisam ser removidos do Git:

```bash
# No seu ambiente de desenvolvimento (Windows):
git rm -r --cached public/build
git commit -m "Remove arquivos de build do Git"
git push origin main
```

## Comandos de Deploy Simplificados (Para usar depois da correção)

Depois de resolver o problema acima, use este comando único para deploy:

```bash
cd /var/www/html/infovisa && \
git pull origin main && \
php composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs && \
php artisan migrate --force && \
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear && \
npm install && npm run build && \
sudo php artisan config:cache && sudo chown apache:apache bootstrap/cache/config.php && \
sudo chown -R apache:apache /var/www/html/infovisa/ && \
sudo chmod -R 775 /var/www/html/infovisa/storage/ /var/www/html/infovisa/bootstrap/cache/ && \
sudo systemctl restart httpd php-fpm
```

## Notas Importantes

1. **Arquivos de build não devem estar no Git**: Os arquivos em `public/build/` são gerados automaticamente pelo `npm run build` e não devem ser versionados.

2. **Sempre fazer build no servidor**: Após fazer pull, sempre execute `npm run build` para gerar os assets atualizados.

3. **Permissões**: Certifique-se de que o Apache tem permissão para ler os arquivos gerados.

## Troubleshooting

### Se o erro persistir:
```bash
# Forçar reset (CUIDADO: perde alterações locais)
cd /var/www/html/infovisa
git fetch origin
git reset --hard origin/main
# Depois continue com os comandos de deploy normais
```

### Verificar status do Git:
```bash
git status
git log --oneline -5
```

### Verificar permissões:
```bash
ls -la public/build/
ls -la storage/
```
