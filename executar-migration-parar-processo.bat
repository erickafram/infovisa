@echo off
echo ========================================
echo Executando Migrations: Parar Processo
echo ========================================
echo.

cd /d C:\wamp64\www\infovisa

echo Executando migrations...
php artisan migrate

echo.
echo ========================================
echo Migrations concluidas!
echo ========================================
pause
