@echo off
echo ========================================
echo Executando Migrations: Ordem de Servico
echo ========================================
echo.

echo [1/2] Adicionando campo processo_id...
php artisan migrate --path=database/migrations/2025_10_30_222300_add_processo_id_to_ordens_servico_table.php

echo.
echo [2/2] Removendo campo prioridade...
php artisan migrate --path=database/migrations/2025_10_30_232600_remove_prioridade_from_ordens_servico.php

echo.
echo ========================================
echo Migrations executadas com sucesso!
echo ========================================
pause
