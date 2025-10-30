<?php
// Script para verificar extração de PDF
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$doc = \App\Models\DocumentoPop::where('titulo', 'LIKE', '%887%')->first();

if ($doc) {
    echo "=== DOCUMENTO ENCONTRADO ===\n";
    echo "Título: " . $doc->titulo . "\n";
    echo "Tamanho: " . strlen($doc->conteudo_extraido) . " caracteres\n\n";
    
    // Busca por Art. 4º
    if (stripos($doc->conteudo_extraido, 'Art. 4') !== false) {
        echo "✓ Art. 4º ENCONTRADO!\n";
        $pos = stripos($doc->conteudo_extraido, 'Art. 4');
        echo "Trecho:\n" . substr($doc->conteudo_extraido, $pos, 500) . "\n\n";
    } else {
        echo "✗ Art. 4º NÃO ENCONTRADO\n\n";
    }
    
    // Busca por Art. 6º
    if (stripos($doc->conteudo_extraido, 'Art. 6') !== false) {
        echo "✓ Art. 6º ENCONTRADO!\n";
        $pos = stripos($doc->conteudo_extraido, 'Art. 6');
        echo "Trecho:\n" . substr($doc->conteudo_extraido, $pos, 500) . "\n\n";
    } else {
        echo "✗ Art. 6º NÃO ENCONTRADO\n\n";
    }
    
    // Busca por Art. 11
    if (stripos($doc->conteudo_extraido, 'Art. 11') !== false) {
        echo "✓ Art. 11 ENCONTRADO!\n";
        $pos = stripos($doc->conteudo_extraido, 'Art. 11');
        echo "Trecho:\n" . substr($doc->conteudo_extraido, $pos, 500) . "\n\n";
    } else {
        echo "✗ Art. 11 NÃO ENCONTRADO\n\n";
    }
    
    // Busca pela frase específica
    if (stripos($doc->conteudo_extraido, 'gases medicinais enquadrados como medicamentos somente podem ser produzidos') !== false) {
        echo "✓ FRASE DO ART. 6º ENCONTRADA!\n";
        $pos = stripos($doc->conteudo_extraido, 'gases medicinais enquadrados como medicamentos somente podem ser produzidos');
        echo "Trecho:\n" . substr($doc->conteudo_extraido, max(0, $pos - 100), 700) . "\n\n";
    } else {
        echo "✗ FRASE DO ART. 6º NÃO ENCONTRADA\n\n";
    }
    
    // Mostra primeiros 1000 caracteres
    echo "=== PRIMEIROS 1000 CARACTERES ===\n";
    echo substr($doc->conteudo_extraido, 0, 1000) . "\n";
} else {
    echo "Documento RDC 887 não encontrado!\n";
}
