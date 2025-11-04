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
        // 1. Atualizar OSs existentes com status antigos para os novos
        DB::table('ordens_servico')
            ->whereIn('status', ['aberta', 'concluida'])
            ->update(['status' => 'em_andamento']);

        // 2. Remover constraint antiga
        DB::statement("ALTER TABLE ordens_servico DROP CONSTRAINT IF EXISTS ordens_servico_status_check");
        
        // 3. Adicionar nova constraint com apenas 3 status
        DB::statement("
            ALTER TABLE ordens_servico 
            ADD CONSTRAINT ordens_servico_status_check 
            CHECK (status IN ('em_andamento', 'finalizada', 'cancelada'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover constraint nova
        DB::statement("ALTER TABLE ordens_servico DROP CONSTRAINT IF EXISTS ordens_servico_status_check");
        
        // Restaurar constraint antiga
        DB::statement("
            ALTER TABLE ordens_servico 
            ADD CONSTRAINT ordens_servico_status_check 
            CHECK (status IN ('aberta', 'em_andamento', 'concluida', 'finalizada', 'cancelada'))
        ");
    }
};
