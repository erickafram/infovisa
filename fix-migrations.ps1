# Script para corrigir ordem das migrations e executar migrate:fresh
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Configurando PHP 8.3.14..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

$env:Path = "C:\wamp\bin\php\php8.3.14;" + $env:Path

Write-Host "`nVersao do PHP:" -ForegroundColor Yellow
php --version

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Renomeando migrations de 2024 para 2025..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Renomear migrations de 2024 para 2025
$migrations = @(
    @{Old="2024_10_28_145600_create_processo_eventos_table.php"; New="2025_10_23_145600_create_processo_eventos_table.php"},
    @{Old="2024_10_28_151200_add_motivo_arquivamento_to_processos_table.php"; New="2025_10_23_151200_add_motivo_arquivamento_to_processos_table.php"},
    @{Old="2024_10_28_151300_add_processo_arquivado_to_evento_enum.php"; New="2025_10_23_151300_add_processo_arquivado_to_evento_enum.php"},
    @{Old="2024_10_28_152100_add_processo_desarquivado_to_evento_enum.php"; New="2025_10_23_152100_add_processo_desarquivado_to_evento_enum.php"},
    @{Old="2024_10_28_153100_add_parada_fields_to_processos_table.php"; New="2025_10_23_153100_add_parada_fields_to_processos_table.php"},
    @{Old="2024_10_28_153200_add_processo_parado_to_evento_enum.php"; New="2025_10_23_153200_add_processo_parado_to_evento_enum.php"},
    @{Old="2024_10_28_153300_add_parado_to_processos_status_check.php"; New="2025_10_23_153300_add_parado_to_processos_status_check.php"},
    @{Old="2024_10_28_163700_create_documento_edicoes_table.php"; New="2025_10_23_163700_create_documento_edicoes_table.php"},
    @{Old="2024_10_28_163800_add_ultimo_editor_to_documentos_digitais.php"; New="2025_10_23_163800_add_ultimo_editor_to_documentos_digitais.php"}
)

foreach ($migration in $migrations) {
    $oldPath = "database\migrations\$($migration.Old)"
    $newPath = "database\migrations\$($migration.New)"
    
    if (Test-Path $oldPath) {
        Move-Item -Path $oldPath -Destination $newPath -Force
        Write-Host "✓ Renomeado: $($migration.Old)" -ForegroundColor Green
    } else {
        Write-Host "○ Ja renomeado: $($migration.Old)" -ForegroundColor Gray
    }
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Executando migrate:fresh..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

php artisan migrate:fresh

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Executando seeders..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

php artisan db:seed

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "Processo concluido!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
