@echo off
echo ========================================
echo  ATUALIZANDO SISTEMA INFOVISA
echo ========================================
echo.

echo [1/8] Puxando atualizacoes do GitHub...
git pull origin main
if %errorlevel% neq 0 (
    echo ERRO ao fazer git pull!
    pause
    exit /b 1
)
echo.

echo [2/8] Atualizando dependencias PHP...
call composer install
echo.

echo [3/8] Atualizando dependencias Node...
call npm install
echo.

echo [4/8] Verificando status das migrations...
php artisan migrate:status
echo.

echo [5/8] Executando migrations...
php artisan migrate
if %errorlevel% neq 0 (
    echo.
    echo ⚠️ ERRO nas migrations!
    echo Executando script de correcao...
    php fix-migrations-2025.php
    echo.
    echo Tentando migrations novamente...
    php artisan migrate
)
echo.

echo [6/8] Executando seeders...
php artisan db:seed
echo.

echo [7/8] Limpando cache...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo.

echo [8/8] Compilando assets...
call npm run build
echo.

echo ========================================
echo  ✅ SISTEMA ATUALIZADO COM SUCESSO!
echo ========================================
echo.
pause
