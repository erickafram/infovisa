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
            // PostgreSQL: Atualiza o municipio_id das OSs baseado no município do estabelecimento
            DB::statement("
                UPDATE ordens_servico 
                SET municipio_id = estabelecimentos.municipio_id
                FROM estabelecimentos
                WHERE ordens_servico.estabelecimento_id = estabelecimentos.id
                AND ordens_servico.municipio_id != estabelecimentos.municipio_id
            ");
        } elseif ($driver === 'mysql') {
            // MySQL: Sintaxe diferente para UPDATE com JOIN
            DB::statement("
                UPDATE ordens_servico 
                INNER JOIN estabelecimentos ON ordens_servico.estabelecimento_id = estabelecimentos.id
                SET ordens_servico.municipio_id = estabelecimentos.municipio_id
                WHERE ordens_servico.municipio_id != estabelecimentos.municipio_id
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não há como reverter sem saber os valores originais
    }
};
