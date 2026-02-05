<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simular autenticação
Auth::guard('interno')->loginUsingId(3);

// Criar request
$request = new Illuminate\Http\Request(['page' => 1]);

// Chamar o controller
$controller = new App\Http\Controllers\Admin\DashboardController();
$response = $controller->processosAtribuidosPaginados($request);

$data = json_decode($response->getContent(), true);

echo "Total: {$data['total']}\n";
echo "Current Page: {$data['current_page']}\n";
echo "Last Page: {$data['last_page']}\n\n";

echo "Processos:\n";
foreach ($data['data'] as $p) {
    echo "{$p['numero_processo']} - Docs: {$p['docs_enviados']}/{$p['docs_total']} - Pend: {$p['docs_pendentes']}\n";
}
