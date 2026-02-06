<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simular autenticação do usuário ERICK
$usuario = App\Models\UsuarioInterno::find(2);
if (!$usuario) {
    echo "ERRO: Usuário não encontrado!\n";
    exit(1);
}

// Fazer login
Auth::guard('interno')->login($usuario);

echo "=== TESTE DA ROTA AJAX DE PROCESSOS ===\n\n";
echo "Usuário autenticado: {$usuario->nome}\n\n";

// Criar request simulado
$request = Illuminate\Http\Request::create('/admin/dashboard/processos-atribuidos', 'GET', ['page' => 1]);

// Chamar o controller
$controller = new App\Http\Controllers\Admin\DashboardController();
$response = $controller->processosAtribuidosPaginados($request);

$data = json_decode($response->getContent(), true);

echo "Status da resposta: {$response->getStatusCode()}\n";
echo "Total de processos: {$data['total']}\n";
echo "Página atual: {$data['current_page']}\n";
echo "Última página: {$data['last_page']}\n";
echo "Por página: {$data['per_page']}\n\n";

echo "=== PROCESSOS RETORNADOS ===\n";
foreach ($data['data'] as $proc) {
    $tipo = $proc['is_meu_direto'] ? '[DIRETO]' : '[SETOR]';
    echo "- {$proc['numero_processo']} {$tipo}\n";
    echo "  Estabelecimento: {$proc['estabelecimento']}\n";
    echo "  Status: {$proc['status_nome']}\n";
    echo "  URL: {$proc['url']}\n";
    if ($proc['id'] == 18) {
        echo "  *** ESTE É O PROCESSO 2026/00018 ***\n";
    }
    echo "\n";
}

// Verificar especificamente o processo 18
$processo18 = collect($data['data'])->firstWhere('id', 18);
if ($processo18) {
    echo "✓ PROCESSO 18 (2026/00018) ENCONTRADO NA RESPOSTA AJAX!\n";
    echo "  is_meu_direto: " . ($processo18['is_meu_direto'] ? 'true' : 'false') . "\n";
    echo "  is_do_setor: " . ($processo18['is_do_setor'] ? 'true' : 'false') . "\n";
} else {
    echo "✗ PROCESSO 18 (2026/00018) NÃO ENCONTRADO NA RESPOSTA AJAX!\n";
}

echo "\n=== JSON COMPLETO ===\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
