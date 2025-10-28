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
        // PostgreSQL: Recriar a constraint check com o novo status
        DB::statement("
            ALTER TABLE processos 
            DROP CONSTRAINT IF EXISTS processos_status_check
        ");
        
        DB::statement("
            ALTER TABLE processos 
            ADD CONSTRAINT processos_status_check 
            CHECK (status IN (
                'aberto',
                'em_analise',
                'pendente',
                'aprovado',
                'indeferido',
                'parado',
                'arquivado'
            ))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não é necessário reverter
    }
};
