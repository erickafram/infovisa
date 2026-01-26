# Correção do Frontend - Deploy de Produção

## Problema Identificado
O deploy atual não está compilando os assets do frontend (CSS/JS) com o Vite/Node.js.

## Solução Imediata (Execute no servidor)

```bash
# 1. Navegue para o diretório do projeto
cd /var/www/html/infovisa

# 2. Instale as dependências do Node.js (se ainda não instaladas)
npm install

# 3. Compile os assets do frontend
npm run build

# 4. Ajuste as permissões dos arquivos compilados
sudo chown -R apache:apache public/build
sudo chmod -R 755 public/build

# 5. Limpe o cache do Laravel
php artisan view:clear
php artisan cache:clear

# 6. Reinicie os serviços
sudo systemctl restart httpd php-fpm
```

## Verificação

Após executar os comandos acima, verifique se:
- A pasta `public/build` foi criada com os arquivos compilados
- O site carrega corretamente com os estilos CSS
- O JavaScript está funcionando (Alpine.js, etc)

## Para Próximos Deploys

O script `deploy.sh` foi atualizado para incluir automaticamente:
1. Instalação de dependências do Composer
2. Instalação de dependências do Node.js
3. Compilação dos assets com Vite
4. Execução de migrations
5. Cache de configurações

Execute o deploy completo com:
```bash
bash deploy.sh
```

## Troubleshooting

### Se o Node.js não estiver instalado no servidor:
```bash
# Instalar Node.js (versão LTS recomendada)
curl -fsSL https://rpm.nodesource.com/setup_lts.x | sudo bash -
sudo yum install -y nodejs
```

### Se houver erro de permissões:
```bash
sudo chown -R $USER:$USER /var/www/html/infovisa/node_modules
sudo chown -R apache:apache /var/www/html/infovisa/public
```

### Se o build falhar por falta de memória:
```bash
# Aumentar limite de memória do Node.js
NODE_OPTIONS="--max-old-space-size=4096" npm run build
```
