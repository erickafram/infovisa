<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

$cnpj = '33683111000280';
$url = "https://minhareceita.org/{$cnpj}";

echo "Testando Laravel HTTP Client\n";
echo "URL: {$url}\n\n";

try {
    $response = Http::timeout(30)
        ->withHeaders([
            'Accept' => 'application/json',
            'User-Agent' => 'InfoVISA/3.0'
        ])
        ->get($url);
    
    echo "Status: " . $response->status() . "\n";
    echo "Successful: " . ($response->successful() ? 'SIM' : 'NÃO') . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "\n✓ Dados recebidos com sucesso!\n";
        echo "Razão Social: " . ($data['razao_social'] ?? 'N/A') . "\n";
        echo "CNPJ: " . ($data['cnpj'] ?? 'N/A') . "\n";
    } else {
        echo "\n✗ Requisição falhou\n";
        echo "Body: " . $response->body() . "\n";
    }
    
} catch (Exception $e) {
    echo "\n✗ Erro: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
