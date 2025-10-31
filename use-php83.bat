@echo off
set PATH=C:\wamp\bin\php\php8.3.14;%PATH%
echo.
echo ========================================
echo PHP 8.3.14 configurado para esta sessao
echo Extensoes PostgreSQL habilitadas
echo ========================================
echo.
php --version
echo.
echo Agora voce pode executar:
echo   - composer install
echo   - php artisan migrate
echo   - php artisan db:seed
echo.
cmd /k
