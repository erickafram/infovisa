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
