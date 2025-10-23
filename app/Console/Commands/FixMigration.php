<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:mark-as-run {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark a migration as run without executing it';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $migrationName = $this->argument('name');
        
        // Verificar se já existe
        $exists = \DB::table('migrations')
            ->where('migration', $migrationName)
            ->exists();
            
        if ($exists) {
            $this->error("Migration '{$migrationName}' already marked as run!");
            return 1;
        }
        
        // Pegar o último batch
        $lastBatch = \DB::table('migrations')->max('batch') ?? 0;
        
        // Inserir o registro
        \DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $lastBatch + 1
        ]);
        
        $this->info("Migration '{$migrationName}' marked as run!");
        return 0;
    }
}
