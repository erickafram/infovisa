<?php
/**
 * Script de debug para verificar por que os processos do setor não estão aparecendo
 * Execute no servidor: php debug-setor.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG PROCESSOS DO SETOR ===\n\n";

// Pegar o ID do usuário logado (ou altere para testar com um ID específico)
$usuarioId = $argv[1] ?? 3; // Passa o ID como argumento ou usa 3 como padrão

$usuario = App\Models\UsuarioInterno::find($usuarioId);

if (!$usuario) {
    echo "Usuário ID {$usuarioId} não encontrado!\n";
    exit(1);
}

echo "1. INFORMAÇÕES DO USUÁRIO\n";
echo "   ID: {$usuario->id}\n";
echo "   Nome: {$usuario->nome}\n";
echo "   Setor: " . ($usuario->setor ?: '(VAZIO/NULL)') . "\n";
echo "   Tipo: {$usuario->tipo}\n";
echo "   Municipio ID: " . ($usuario->municipio_id ?: '(NULL)') . "\n\n";

if (empty($usuario->setor)) {
    echo "⚠️  PROBLEMA: O usuário NÃO tem setor definido!\n";
    echo "   O campo 'setor' está vazio ou NULL.\n";
    echo "   Verifique no banco de dados: SELECT setor FROM usuarios_internos WHERE id = {$usuario->id}\n\n";
}

echo "2. PROCESSOS NO SISTEMA\n";
$totalProcessos = App\Models\Processo::count();
$processosAbertos = App\Models\Processo::whereNotIn('status', ['arquivado', 'concluido'])->count();
echo "   Total de processos: {$totalProcessos}\n";
echo "   Processos não arquivados/concluídos: {$processosAbertos}\n\n";

echo "3. PROCESSOS COM SETOR_ATUAL PREENCHIDO\n";
$processosComSetor = App\Models\Processo::whereNotNull('setor_atual')->where('setor_atual', '!=', '')->get();
echo "   Total: {$processosComSetor->count()}\n";

if ($processosComSetor->count() > 0) {
    echo "   Valores de setor_atual encontrados:\n";
    $setores = $processosComSetor->pluck('setor_atual')->unique();
    foreach ($setores as $setor) {
        $count = $processosComSetor->where('setor_atual', $setor)->count();
        echo "     - '{$setor}' ({$count} processos)\n";
    }
} else {
    echo "⚠️  PROBLEMA: Nenhum processo tem setor_atual preenchido!\n";
}
echo "\n";

echo "4. COMPARAÇÃO DE SETORES\n";
if (!empty($usuario->setor)) {
    $processosDoSetor = App\Models\Processo::where('setor_atual', $usuario->setor)->count();
    echo "   Processos com setor_atual = '{$usuario->setor}': {$processosDoSetor}\n";
    
    // Verificar case-sensitive
    $processosDoSetorLower = App\Models\Processo::whereRaw('LOWER(setor_atual) = ?', [strtolower($usuario->setor)])->count();
    echo "   Processos com setor_atual (case-insensitive): {$processosDoSetorLower}\n";
    
    if ($processosDoSetor != $processosDoSetorLower) {
        echo "⚠️  PROBLEMA: Diferença de maiúsculas/minúsculas detectada!\n";
    }
}
echo "\n";

echo "5. PROCESSOS DO USUÁRIO (responsavel_atual_id)\n";
$processosResponsavel = App\Models\Processo::where('responsavel_atual_id', $usuario->id)
    ->whereNotIn('status', ['arquivado', 'concluido'])
    ->count();
echo "   Processos onde é responsável direto: {$processosResponsavel}\n\n";

echo "6. QUERY FINAL (mesma do Dashboard)\n";
$query = App\Models\Processo::with(['estabelecimento', 'tipoProcesso'])
    ->whereNotIn('status', ['arquivado', 'concluido']);

$query->where(function($q) use ($usuario) {
    $q->where('responsavel_atual_id', $usuario->id);
    if ($usuario->setor) {
        $q->orWhere('setor_atual', $usuario->setor);
    }
});

// Aplicar filtro de competência
if ($usuario->isEstadual()) {
    echo "   Filtro: Usuário ESTADUAL\n";
    $query->whereHas('estabelecimento', fn($q) => 
        $q->where('competencia_manual', 'estadual')->orWhereNull('competencia_manual'));
} elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
    echo "   Filtro: Usuário MUNICIPAL (municipio_id: {$usuario->municipio_id})\n";
    $query->whereHas('estabelecimento', fn($q) => 
        $q->where('municipio_id', $usuario->municipio_id));
} else {
    echo "   Filtro: Nenhum (Admin ou outro)\n";
}

$processos = $query->get();
echo "   Total encontrado antes do filtro em memória: {$processos->count()}\n";

// Aplicar filtro em memória (igual ao controller)
if ($usuario->isEstadual()) {
    $processos = $processos->filter(fn($p) => $p->estabelecimento && $p->estabelecimento->isCompetenciaEstadual());
    echo "   Total após filtro estadual em memória: {$processos->count()}\n";
} elseif ($usuario->isMunicipal()) {
    $processos = $processos->filter(fn($p) => $p->estabelecimento && $p->estabelecimento->isCompetenciaMunicipal());
    echo "   Total após filtro municipal em memória: {$processos->count()}\n";
}

echo "\n7. RESULTADO FINAL\n";
echo "   Processos que apareceriam no Dashboard: {$processos->count()}\n\n";

if ($processos->count() > 0) {
    echo "   Lista:\n";
    foreach ($processos->take(10) as $p) {
        $tipo = $p->responsavel_atual_id == $usuario->id ? 'DIRETO' : 'SETOR';
        echo "   - {$p->numero_processo} [{$tipo}] Status: {$p->status} Setor: " . ($p->setor_atual ?: 'NULL') . "\n";
    }
} else {
    echo "⚠️  NENHUM PROCESSO ENCONTRADO!\n";
    echo "\n   Possíveis causas:\n";
    echo "   1. O usuário não tem 'setor' definido\n";
    echo "   2. Nenhum processo tem 'setor_atual' igual ao setor do usuário\n";
    echo "   3. O filtro de competência está removendo os processos\n";
    echo "   4. Todos os processos estão arquivados ou concluídos\n";
}

echo "\n=== FIM DO DEBUG ===\n";
