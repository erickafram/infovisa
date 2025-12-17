<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            // PostgreSQL
            DB::statement('ALTER TABLE processos DROP CONSTRAINT IF EXISTS processos_tipo_check');
            DB::statement("
                ALTER TABLE processos 
                ADD CONSTRAINT processos_tipo_check 
                CHECK (tipo IN (
                    'licenciamento',
                    'analise_rotulagem',
                    'projeto_arquitetonico',
                    'administrativo',
                    'descentralizacao',
                    'receituario'
                ))
            ");
        } elseif ($driver === 'mysql') {
            // MySQL: Alterar o tipo ENUM
            DB::statement("ALTER TABLE processos MODIFY COLUMN tipo ENUM('licenciamento', 'analise_rotulagem', 'projeto_arquitetonico', 'administrativo', 'descentralizacao', 'receituario') NOT NULL DEFAULT 'licenciamento'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE processos DROP CONSTRAINT IF EXISTS processos_tipo_check');
            DB::statement("
                ALTER TABLE processos 
                ADD CONSTRAINT processos_tipo_check 
                CHECK (tipo IN (
                    'licenciamento',
                    'analise_rotulagem',
                    'projeto_arquitetonico',
                    'administrativo',
                    'descentralizacao'
                ))
            ");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE processos MODIFY COLUMN tipo ENUM('licenciamento', 'analise_rotulagem', 'projeto_arquitetonico', 'administrativo', 'descentralizacao') NOT NULL DEFAULT 'licenciamento'");
        }
    }
};
