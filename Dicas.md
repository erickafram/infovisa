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
git pull origin main

-- 2. ATUALIZAR DEPENDÊNCIAS (se houver novos pacotes)
composer install
npm install

-- 3. VERIFICAR STATUS DAS MIGRATIONS
php artisan migrate:status

-- 4. SE HOUVER ERRO DE "DUPLICATE TABLE" OU "TABELA JÁ EXISTE":
--    Execute o script de correção:
php fix-migrations-2025.php

-- 5. RODAR MIGRATIONS (novas tabelas/colunas)
php artisan migrate

-- 6. RODAR SEEDERS (novos dados)
php artisan db:seed

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


-- RODAR SEEDERS INDIVIDUALMENTE (se necessário)
php artisan db:seed --class=MunicipioSeeder
php artisan db:seed --class=TipoDocumentoSeeder
php artisan db:seed --class=TipoProcessoSeeder
php artisan db:seed --class=PactuacaoSeeder
php artisan db:seed --class=UsuarioInternoSeeder
php artisan db:seed --class=EstabelecimentoSeeder


-- RODAR TODOS OS SEEDERS DE UMA VEZ
php artisan db:seed
