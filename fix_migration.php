<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Inserir registro da migration
DB::table('migrations')->insert([
    'migration' => '2025_10_21_173500_create_modelo_documentos_table',
    'batch' => 7
]);

echo "Migration marcada como executada!\n";

// Agora executar as migrations pendentes
Artisan::call('migrate');
echo Artisan::output();
