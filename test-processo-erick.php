<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ID do usuário ERICK (conforme URL fornecida)
$usuarioId = 2;

// ID do processo que foi tramitado
$processoId = 18;

echo "=== TESTE DE PROCESSO TRAMITADO ===\n\n";

// Buscar o usuário
$usuario = App\Models\UsuarioInterno::find($usuarioId);
if (!$usuario) {
    echo "ERRO: Usuário ID {$usuarioId} não encontrado!\n";
    exit(1);
}

echo "Usuário: {$usuario->nome} (ID: {$usuario->id})\n";
echo "Setor: " . ($usuario->setor ?? 'NULL') . "\n";
echo "Nível: " . ($usuario->nivel_acesso->value ?? 'NULL') . "\n";
echo "Município ID: " . ($usuario->municipio_id ?? 'NULL') . "\n\n";

// Buscar o processo
$processo = App\Models\Processo::find($processoId);
if (!$processo) {
    echo "ERRO: Processo ID {$processoId} não encontrado!\n";
    exit(1);
}

echo "=== DADOS DO PROCESSO ===\n";
echo "Número: {$processo->numero_processo}\n";
echo "Status: {$processo->status}\n";
echo "Setor Atual: " . ($processo->setor_atual ?? 'NULL') . "\n";
echo "Responsável Atual ID: " . ($processo->responsavel_atual_id ?? 'NULL') . "\n";

if ($processo->responsavelAtual) {
    echo "Responsável Atual Nome: {$processo->responsavelAtual->nome}\n";
}

echo "Responsável Desde: " . ($processo->responsavel_desde ? $processo->responsavel_desde->format('d/m/Y H:i') : 'NULL') . "\n";
echo "Estabelecimento ID: {$processo->estabelecimento_id}\n\n";

// Verificar se o processo deveria aparecer para o usuário
echo "=== VERIFICAÇÕES ===\n";

$deveAparecerDireto = $processo->responsavel_atual_id == $usuario->id;
echo "1. Processo atribuído diretamente ao usuário? " . ($deveAparecerDireto ? 'SIM ✓' : 'NÃO ✗') . "\n";

$deveAparecerSetor = $usuario->setor && $processo->setor_atual === $usuario->setor;
echo "2. Processo no setor do usuário? " . ($deveAparecerSetor ? 'SIM ✓' : 'NÃO ✗') . "\n";

$statusValido = !in_array($processo->status, ['arquivado', 'concluido']);
echo "3. Status válido (não arquivado/concluído)? " . ($statusValido ? 'SIM ✓' : 'NÃO ✗') . "\n\n";

// Testar a query do dashboard
echo "=== TESTE DA QUERY DO DASHBOARD ===\n";

$query = App\Models\Processo::with(['estabelecimento', 'tipoProcesso', 'responsavelAtual'])
    ->whereNotIn('status', ['arquivado', 'concluido']);

$query->where(function($q) use ($usuario) {
    // Processos diretamente atribuídos ao usuário
    $q->where('responsavel_atual_id', $usuario->id);
    
    // Processos do setor
    if ($usuario->setor) {
        $q->orWhere(function($subQ) use ($usuario) {
            $subQ->where('setor_atual', $usuario->setor);
            
            // Filtro de competência para processos do setor
            if ($usuario->isEstadual()) {
                $subQ->whereHas('estabelecimento', fn($estQ) => 
                    $estQ->where('competencia_manual', 'estadual')->orWhereNull('competencia_manual'));
            } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
                $subQ->whereHas('estabelecimento', fn($estQ) => 
                    $estQ->where('municipio_id', $usuario->municipio_id));
            }
        });
    }
});

$processos = $query->get();

echo "Total de processos encontrados: {$processos->count()}\n";

$processoEncontrado = $processos->firstWhere('id', $processoId);
if ($processoEncontrado) {
    echo "✓ Processo {$processo->numero_processo} FOI ENCONTRADO na query!\n";
} else {
    echo "✗ Processo {$processo->numero_processo} NÃO FOI ENCONTRADO na query!\n";
    echo "\nDEBUG: Vamos testar cada condição separadamente...\n\n";
    
    // Teste 1: Processo existe e não está arquivado?
    $teste1 = App\Models\Processo::where('id', $processoId)
        ->whereNotIn('status', ['arquivado', 'concluido'])
        ->exists();
    echo "Teste 1 - Processo existe e não está arquivado? " . ($teste1 ? 'SIM ✓' : 'NÃO ✗') . "\n";
    
    // Teste 2: Processo atribuído ao usuário?
    $teste2 = App\Models\Processo::where('id', $processoId)
        ->where('responsavel_atual_id', $usuario->id)
        ->exists();
    echo "Teste 2 - Processo com responsavel_atual_id = {$usuario->id}? " . ($teste2 ? 'SIM ✓' : 'NÃO ✗') . "\n";
    
    // Teste 3: Processo no setor do usuário?
    if ($usuario->setor) {
        $teste3 = App\Models\Processo::where('id', $processoId)
            ->where('setor_atual', $usuario->setor)
            ->exists();
        echo "Teste 3 - Processo com setor_atual = '{$usuario->setor}'? " . ($teste3 ? 'SIM ✓' : 'NÃO ✗') . "\n";
    }
}

echo "\n=== LISTA DE TODOS OS PROCESSOS DO USUÁRIO ===\n";
foreach ($processos->take(10) as $p) {
    $tipo = $p->responsavel_atual_id == $usuario->id ? '[DIRETO]' : '[SETOR]';
    echo "- {$p->numero_processo} {$tipo} Status: {$p->status}\n";
}

echo "\n=== FIM DO TESTE ===\n";
