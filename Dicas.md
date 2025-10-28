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


-- SE O PROJETO JÁ ESTIVER MIGRADO
git pull origin main
composer install
npm install
php artisan migrate
php artisan db:seed


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
