@echo off
echo ========================================
echo   EXECUTAR MIGRATION - TIPO DE PRAZO
echo ========================================
echo.
echo Esta migration adiciona o campo 'tipo_prazo' nas tabelas:
echo - tipo_documentos
echo - documentos_digitais
echo.
echo O campo permite escolher entre:
echo - corridos: Conta todos os dias
echo - uteis: Conta apenas dias uteis (segunda a sexta)
echo.
pause
echo.
echo Executando migration...
php artisan migrate --path=database/migrations/2025_11_16_162400_add_tipo_prazo_to_tables.php
echo.
echo ========================================
echo   MIGRATION CONCLUIDA!
echo ========================================
pause
