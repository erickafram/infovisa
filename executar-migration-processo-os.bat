@echo off
echo ========================================
echo Executando Migration: Adicionar processo_id em ordens_servico
echo ========================================
echo.

php artisan migrate --path=database/migrations/2025_10_30_222300_add_processo_id_to_ordens_servico_table.php

echo.
echo ========================================
echo Migration executada!
echo ========================================
pause
