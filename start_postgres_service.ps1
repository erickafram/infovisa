# Tenta iniciar o serviço do PostgreSQL
$serviceName = "postgresql-x64-18"

# Verifica se o serviço existe
$service = Get-Service -Name $serviceName -ErrorAction SilentlyContinue

if ($service) {
    if ($service.Status -eq "Running") {
        Write-Host "PostgreSQL já está rodando!" -ForegroundColor Green
    } else {
        Write-Host "Iniciando PostgreSQL..." -ForegroundColor Yellow
        Start-Service -Name $serviceName
        Write-Host "PostgreSQL iniciado com sucesso!" -ForegroundColor Green
    }
} else {
    Write-Host "Serviço PostgreSQL não encontrado. Tentando iniciar manualmente..." -ForegroundColor Yellow
    Write-Host "Abra o pgAdmin ou Services.msc e inicie o PostgreSQL manualmente." -ForegroundColor Red
}
