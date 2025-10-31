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
            $table->string('atividades_realizadas')->nullable()->after('observacoes');
            $table->text('observacoes_finalizacao')->nullable()->after('atividades_realizadas');
            $table->foreignId('finalizada_por')->nullable()->constrained('usuarios_internos')->after('observacoes_finalizacao');
            $table->timestamp('finalizada_em')->nullable()->after('finalizada_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropForeign(['finalizada_por']);
            $table->dropColumn(['atividades_realizadas', 'observacoes_finalizacao', 'finalizada_por', 'finalizada_em']);
        });
    }
};
