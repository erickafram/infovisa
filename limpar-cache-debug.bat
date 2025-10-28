@echo off
echo ========================================
echo Limpando Cache do Laravel
echo ========================================
echo.

echo [1/6] Limpando cache de rotas...
php artisan route:clear
echo.

echo [2/6] Limpando cache de configuracao...
php artisan config:clear
echo.

echo [3/6] Limpando cache de views...
php artisan view:clear
echo.

echo [4/6] Limpando cache geral...
php artisan cache:clear
echo.

echo [5/6] Limpando cache de eventos...
php artisan event:clear
echo.

echo [6/6] Otimizando autoload...
composer dump-autoload
echo.

echo ========================================
echo Cache limpo com sucesso!
echo ========================================
echo.
echo PROXIMOS PASSOS:
echo 1. Limpe os cookies do navegador para localhost:8001
echo 2. Faca login novamente
echo 3. Teste a rota: http://localhost:8001/test-auth-debug
echo 4. Teste a rota: http://localhost:8001/admin/documentos/{id}/edit
echo.
pause
