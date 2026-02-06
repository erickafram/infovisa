<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simular autenticação do usuário ERICK
$usuario = App\Models\UsuarioInterno::find(2);
Auth::guard('interno')->login($usuario);

echo "=== VERIFICANDO TODAS AS PÁGINAS ===\n\n";

$controller = new App\Http\Controllers\Admin\DashboardController();

for ($page = 1; $page <= 3; $page++) {
    echo "--- PÁGINA {$page} ---\n";
    $request = Illuminate\Http\Request::create('/admin/dashboard/processos-atribuidos', 'GET', ['page' => $page]);
    $response = $controller->processosAtribuidosPaginados($request);
    $data = json_decode($response->getContent(), true);
    
    foreach ($data['data'] as $proc) {
        $tipo = $proc['is_meu_direto'] ? '[DIRETO]' : '[SETOR]';
        $destaque = $proc['id'] == 18 ? ' *** PROCESSO 18 AQUI! ***' : '';
        echo "  {$proc['numero_processo']} {$tipo} - Desde: " . ($proc['responsavel_desde'] ?? 'NULL') . $destaque . "\n";
    }
    echo "\n";
}

echo "=== VERIFICANDO ORDEM DOS 17 PROCESSOS ===\n\n";

$query = App\Models\Processo::with(['estabelecimento', 'tipoProcesso', 'responsavelAtual'])
    ->whereNotIn('status', ['arquivado', 'concluido']);

$query->where(function($q) use ($usuario) {
    $q->where('responsavel_atual_id', $usuario->id);
    if ($usuario->setor) {
        $q->orWhere(function($subQ) use ($usuario) {
            $subQ->where('setor_atual', $usuario->setor);
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

$processos = $query->orderBy('responsavel_desde', 'desc')->get();

echo "Total: {$processos->count()} processos\n\n";

$posicao = 0;
foreach ($processos as $p) {
    $posicao++;
    $tipo = $p->responsavel_atual_id == $usuario->id ? '[DIRETO]' : '[SETOR]';
    $destaque = $p->id == 18 ? ' *** PROCESSO 18 ***' : '';
    $desde = $p->responsavel_desde ? $p->responsavel_desde->format('d/m/Y H:i') : 'NULL';
    echo "{$posicao}. {$p->numero_processo} {$tipo} - Desde: {$desde}{$destaque}\n";
}
