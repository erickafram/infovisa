<?php

use Illuminate\Database\Migrations\Migration;
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
            DB::statement('ALTER TABLE tipo_processos DROP CONSTRAINT IF EXISTS tipo_processos_competencia_check');
            DB::statement("ALTER TABLE tipo_processos ADD CONSTRAINT tipo_processos_competencia_check CHECK (competencia IN ('estadual', 'municipal', 'estadual_exclusivo'))");
        } elseif ($driver === 'mysql') {
            // MySQL: Alterar o tipo ENUM
            DB::statement("ALTER TABLE tipo_processos MODIFY COLUMN competencia ENUM('estadual', 'municipal', 'estadual_exclusivo') NOT NULL DEFAULT 'municipal'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tipo_processos DROP CONSTRAINT IF EXISTS tipo_processos_competencia_check');
            DB::statement("ALTER TABLE tipo_processos ADD CONSTRAINT tipo_processos_competencia_check CHECK (competencia IN ('estadual', 'municipal'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE tipo_processos MODIFY COLUMN competencia ENUM('estadual', 'municipal') NOT NULL DEFAULT 'municipal'");
        }
    }
};
