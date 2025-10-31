<?php

// Teste simples de requisição HTTP
$cnpj = '33683111000280';
$url = "https://minhareceita.org/{$cnpj}";

echo "Testando requisição para: {$url}\n\n";

// Teste 1: file_get_contents
echo "=== Teste 1: file_get_contents ===\n";
$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'user_agent' => 'InfoVISA/3.0'
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

try {
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        echo "✓ Sucesso!\n";
        echo "Tamanho da resposta: " . strlen($response) . " bytes\n";
        $data = json_decode($response, true);
        if ($data && isset($data['razao_social'])) {
            echo "Razão Social: " . $data['razao_social'] . "\n";
        }
    } else {
        echo "✗ Falhou\n";
    }
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
}

echo "\n";

// Teste 2: cURL
echo "=== Teste 2: cURL ===\n";
if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'InfoVISA/3.0',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: {$httpCode}\n";
    
    if ($response !== false && $httpCode == 200) {
        echo "✓ Sucesso!\n";
        echo "Tamanho da resposta: " . strlen($response) . " bytes\n";
        $data = json_decode($response, true);
        if ($data && isset($data['razao_social'])) {
            echo "Razão Social: " . $data['razao_social'] . "\n";
        }
    } else {
        echo "✗ Falhou\n";
        if ($error) {
            echo "Erro cURL: {$error}\n";
        }
    }
} else {
    echo "✗ cURL não está disponível\n";
}

echo "\n";
echo "=== Extensões PHP carregadas ===\n";
echo "curl: " . (extension_loaded('curl') ? '✓' : '✗') . "\n";
echo "openssl: " . (extension_loaded('openssl') ? '✓' : '✗') . "\n";
