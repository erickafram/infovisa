<?php
// Script para marcar migrations antigas como executadas
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$migrations = [
    '2025_10_28_145600_create_processo_eventos_table',
    '2025_10_28_151200_add_motivo_arquivamento_to_processos_table',
    '2025_10_28_151300_add_processo_arquivado_to_evento_enum',
    '2025_10_28_152100_add_processo_desarquivado_to_evento_enum',
    '2025_10_28_153100_add_parada_fields_to_processos_table',
    '2025_10_28_153200_add_processo_parado_to_evento_enum',
    '2025_10_28_153300_add_parado_to_processos_status_check',
    '2025_10_28_163700_create_documento_edicoes_table',
    '2025_10_28_163800_add_ultimo_editor_to_documentos_digitais',
];

$batch = 24;

foreach ($migrations as $migration) {
    // Verifica se já existe
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    
    if (!$exists) {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch
        ]);
        echo "✓ Marcada: {$migration}\n";
    } else {
        echo "- Já existe: {$migration}\n";
    }
}

echo "\n✅ Migrations marcadas como executadas!\n";
echo "Agora você pode executar: php artisan migrate\n";
