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
        // Remove a constraint antiga
        DB::statement('ALTER TABLE tipo_processos DROP CONSTRAINT IF EXISTS tipo_processos_competencia_check');
        
        // Adiciona a nova constraint com estadual_exclusivo
        DB::statement("ALTER TABLE tipo_processos ADD CONSTRAINT tipo_processos_competencia_check CHECK (competencia IN ('estadual', 'municipal', 'estadual_exclusivo'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove a constraint nova
        DB::statement('ALTER TABLE tipo_processos DROP CONSTRAINT IF EXISTS tipo_processos_competencia_check');
        
        // Restaura a constraint antiga (apenas estadual e municipal)
        DB::statement("ALTER TABLE tipo_processos ADD CONSTRAINT tipo_processos_competencia_check CHECK (competencia IN ('estadual', 'municipal'))");
    }
};
