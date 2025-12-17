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
            // PostgreSQL: Alterar o tipo ENUM para adicionar 'finalizada'
            DB::statement("ALTER TABLE ordens_servico DROP CONSTRAINT IF EXISTS ordens_servico_status_check");
            DB::statement("
                ALTER TABLE ordens_servico 
                ADD CONSTRAINT ordens_servico_status_check 
                CHECK (status IN ('aberta', 'em_andamento', 'concluida', 'finalizada', 'cancelada'))
            ");
        } elseif ($driver === 'mysql') {
            // MySQL: Alterar o tipo ENUM para adicionar 'finalizada'
            DB::statement("ALTER TABLE ordens_servico MODIFY COLUMN status ENUM('aberta', 'em_andamento', 'concluida', 'finalizada', 'cancelada') NOT NULL DEFAULT 'aberta'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            //
        });
    }
};
