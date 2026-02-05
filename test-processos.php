<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Buscar usuário
$usuario = App\Models\UsuarioInterno::find(3);

echo "Usuario: {$usuario->nome}\n";
echo "Setor do usuario: {$usuario->setor}\n";
echo "ID do usuario: {$usuario->id}\n\n";

// Ver todos os processos não arquivados
$todosProcessos = App\Models\Processo::whereNotIn('status', ['arquivado', 'concluido'])->get();
echo "Total de processos não arquivados/concluídos: {$todosProcessos->count()}\n\n";

// Ver processos com responsavel_atual_id do usuario
$processosResp = App\Models\Processo::where('responsavel_atual_id', $usuario->id)->count();
echo "Processos com responsavel_atual_id = {$usuario->id}: {$processosResp}\n";

// Ver processos com setor_atual igual ao setor do usuario
$processosSetor = App\Models\Processo::where('setor_atual', $usuario->setor)->count();
echo "Processos com setor_atual = '{$usuario->setor}': {$processosSetor}\n\n";

// Listar todos processos com setor_atual
echo "Lista de processos com setor_atual:\n";
$processosComSetor = App\Models\Processo::whereNotNull('setor_atual')->get();
foreach ($processosComSetor->take(10) as $p) {
    echo "{$p->numero_processo} - Status: {$p->status} - Setor: {$p->setor_atual}\n";
}
