@echo off
echo ========================================
echo Executando Migration: processo_eventos
echo ========================================
echo.

cd /d C:\wamp64\www\infovisa

echo Executando migration...
php artisan migrate --path=database/migrations/2024_10_28_145600_create_processo_eventos_table.php

echo.
echo ========================================
echo Migration concluida!
echo ========================================
pause
