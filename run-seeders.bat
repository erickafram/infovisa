@echo off
echo Configurando ambiente...
set PATH=C:\wamp\bin\php\php8.3.14;%PATH%

echo.
echo ========================================
echo Executando seeders...
echo ========================================
echo.

php artisan db:seed --class=TipoAcoesTableSeeder

echo.
echo ========================================
echo Processo concluido!
echo ========================================
echo.

pause
