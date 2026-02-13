-- MIGRAR OUTRO PC (NAO TIVER INSTALADO NADA)
git clone https://github.com/erickafram/infovisa
cd infovisa
composer install
npm install
cp .env.example .env
php artisan key:generate

-- Configurar .env com dados do banco PostgreSQL
-- DB_CONNECTION=pgsql
-- DB_HOST=127.0.0.1
-- DB_PORT=5432
-- DB_DATABASE=infovisa
-- DB_USERNAME=postgres
-- DB_PASSWORD=sua_senha

php artisan migrate
php artisan db:seed


-- ⚠️ PASSO A PASSO APÓS GIT PULL (SIGA SEMPRE ESTA ORDEM!)
-- ============================================================

-- 1. PUXAR ATUALIZAÇÕES DO GITHUB
cd /home/erick/htdocs/erickdev.online
git pull origin main

-- 2. ATUALIZAR DEPENDÊNCIAS (se houver novos pacotes)
composer install --no-dev --optimize-autoloader
npm install

-- 3. VERIFICAR STATUS DAS MIGRATIONS
php artisan migrate --force

php artisan optimize:clear
php artisan optimize
php artisan view:clear
php artisan config:clear

-- 7. LIMPAR CACHE (SEMPRE!)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

-- 8. COMPILAR ASSETS (se houver mudanças no frontend)
npm run build

-- ✅ PRONTO! Sistema atualizado!


-- SUBIR PARA GIT
git add .
git commit -m "Implementação de questionários dinâmicos e override de competência"
git push -u origin main

-- 1. PUXAR ATUALIZAÇÕES DO GITHUB
git pull origin main


-- RODAR SEEDERS INDIVIDUALMENTE (se necessário)
php artisan db:seed --class=MunicipioSeeder
php artisan db:seed --class=TipoDocumentoSeeder
php artisan db:seed --class=TipoProcessoSeeder
php artisan db:seed --class=PactuacaoSeeder
php artisan db:seed --class=UsuarioInternoSeeder
php artisan db:seed --class=EstabelecimentoSeeder


-- RODAR TODOS OS SEEDERS DE UMA VEZ
php artisan db:seed



para servidor da visa
php artisan serve --port=8001






SERVIDOR INFOVISA
sudo chown -R $USER:$USER /var/www/html/infovisa/
sudo chmod -R 755 /var/www/html/infovisa/
sudo chown -R $USER:$USER .git/
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
sudo systemctl restart httpd
sudo systemctl restart php-fpm

SE DER ERRO TENTE ESSE
cd /var/www/html/infovisa
sudo chown -R $USER:$USER /var/www/html/infovisa/ .git/
git pull origin main
php composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs
php artisan migrate --force
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear
sudo php artisan config:cache && sudo chown apache:apache bootstrap/cache/config.php
npm run build
sudo chown -R apache:apache /var/www/html/infovisa/ && sudo chmod -R 775 /var/www/html/infovisa/storage/ /var/www/html/infovisa/bootstrap/cache/
sudo systemctl restart httpd php-fpm


-- SUBIR PARA GIT
git add .
git commit -m "Implementação de questionários dinâmicos e override de competência"
git push -u origin main


se der erro
composer install --no-dev --optimize-autoloader



# 1. Instalar Node.js (se não tiver)
curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash -
sudo yum install -y nodejs

# 2. Copiar e instalar
sudo mkdir -p /opt/infovisa-whatsapp
sudo cp -r /var/www/html/infovisa/whatsapp-server/* /opt/infovisa-whatsapp/
cd /opt/infovisa-whatsapp
npm install --production

# 3. Criar serviço systemd
sudo nano /etc/systemd/system/infovisa-whatsapp.service


[Unit]
Description=INFOVISA WhatsApp Server
After=network.target

[Service]
Type=simple
WorkingDirectory=/opt/infovisa-whatsapp
ExecStart=/usr/bin/node src/server.js
Restart=on-failure
Environment=PORT=3000
Environment=API_KEY=sua_chave_secreta

[Install]
WantedBy=multi-user.target

# 4. Ativar e iniciar
sudo systemctl daemon-reload
sudo systemctl enable infovisa-whatsapp
sudo systemctl start infovisa-whatsapp