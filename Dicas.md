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


-- SUBIR PARA GIT
git add .
git commit -m "Implementação de questionários dinâmicos e override de competência"
git push -u origin main


ESSE É FUNCIONANDO (SEM SOBRESCREVER .env)
cd /var/www/html/infovisa @@2025@@Ekb

# backup rápido do .env antes do pull
cp .env /tmp/infovisa.env.bak

# pull seguro
sudo chown -R $USER:$USER /var/www/html/infovisa .git
git pull --ff-only origin main

# se por algum motivo o .env mudar, restaura automaticamente
cmp -s .env /tmp/infovisa.env.bak || cp /tmp/infovisa.env.bak .env

composer install --no-dev --optimize-autoloader --ignore-platform-reqs
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
npm run build

# permissões só onde precisa
sudo chown -R apache:apache storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# garantir link da logo
[ -L public/storage ] || sudo ln -s ../storage/app/public public/storage
sudo chown -h apache:apache public/storage

sudo systemctl restart httpd php-fpm

# conferência opcional: .env NÃO deve ser versionado
git ls-files .env


-- SUBIR PARA GIT
git add .
git commit -m "Implementação de questionários dinâmicos e override de competência"
git push -u origin main



se der erro
composer install --no-dev --optimize-autoloader



NOTEBOOK
$env:Path = [Environment]::GetEnvironmentVariable('Path','Machine') + ';' + [Environment]::GetEnvironmentVariable('Path','User'); php -v | Select-Object -First 1 | Out-String; php artisan serve


git pull origin main
php artisan migrate
php artisan db:seed