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

        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            // PostgreSQL
            DB::statement("ALTER TABLE ordens_servico DROP CONSTRAINT IF EXISTS ordens_servico_status_check");
            DB::statement("
                ALTER TABLE ordens_servico 
                ADD CONSTRAINT ordens_servico_status_check 
                CHECK (status IN ('em_andamento', 'finalizada', 'cancelada'))
            ");
        } elseif ($driver === 'mysql') {
            // MySQL: Alterar o tipo ENUM
            DB::statement("ALTER TABLE ordens_servico MODIFY COLUMN status ENUM('em_andamento', 'finalizada', 'cancelada') NOT NULL DEFAULT 'em_andamento'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE ordens_servico DROP CONSTRAINT IF EXISTS ordens_servico_status_check");
            DB::statement("
                ALTER TABLE ordens_servico 
                ADD CONSTRAINT ordens_servico_status_check 
                CHECK (status IN ('aberta', 'em_andamento', 'concluida', 'finalizada', 'cancelada'))
            ");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE ordens_servico MODIFY COLUMN status ENUM('aberta', 'em_andamento', 'concluida', 'finalizada', 'cancelada') NOT NULL DEFAULT 'aberta'");
        }
    }
};
