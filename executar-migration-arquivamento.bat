@echo off
echo ========================================
echo Executando Migration: processo_arquivado
echo ========================================
echo.

cd /d C:\wamp64\www\infovisa

echo Executando migration...
php artisan migrate --path=database/migrations/2024_10_28_151300_add_processo_arquivado_to_evento_enum.php

echo.
echo ========================================
echo Migration concluida!
echo ========================================
pause
