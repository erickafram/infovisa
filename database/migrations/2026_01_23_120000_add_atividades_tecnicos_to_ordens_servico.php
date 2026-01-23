<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            // Nova estrutura: atividades com técnicos
            $table->json('atividades_tecnicos')->nullable()->after('tipos_acao_ids');
            // Estrutura: [
            //   {
            //     "tipo_acao_id": 1,
            //     "tecnicos": [2, 3, 5],
            //     "responsavel_id": 2,
            //     "status": "pendente", // pendente, em_andamento, finalizada
            //     "finalizada_por": null,
            //     "finalizada_em": null,
            //     "observacoes": null
            //   }
            // ]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropColumn('atividades_tecnicos');
        });
    }
};