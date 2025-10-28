@echo off
echo ========================================
echo Executando Migrations: Edicao Colaborativa
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
