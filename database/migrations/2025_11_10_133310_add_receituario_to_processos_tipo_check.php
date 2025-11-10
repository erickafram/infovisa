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
        // Remove a constraint antiga
        DB::statement('ALTER TABLE processos DROP CONSTRAINT IF EXISTS processos_tipo_check');
        
        // Adiciona a nova constraint com "receituario" incluído
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove a constraint com receituario
        DB::statement('ALTER TABLE processos DROP CONSTRAINT IF EXISTS processos_tipo_check');
        
        // Restaura a constraint antiga sem "receituario"
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
    }
};
