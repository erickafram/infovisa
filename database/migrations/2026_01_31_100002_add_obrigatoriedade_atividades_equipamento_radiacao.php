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
        // Adiciona campo de obrigatoriedade na tabela de atividades
        Schema::table('atividades_equipamento_radiacao', function (Blueprint $table) {
            $table->boolean('obrigatorio_processo')->default(false)->after('ativo');
        });

        // Tabela pivot para vincular atividades de equipamento de radiação aos tipos de processo
        Schema::create('atividade_equipamento_radiacao_tipo_processo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atividade_equipamento_radiacao_id')->constrained('atividades_equipamento_radiacao')->cascadeOnDelete();
            $table->foreignId('tipo_processo_id')->constrained('tipo_processos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['atividade_equipamento_radiacao_id', 'tipo_processo_id'], 'atividade_equip_rad_tipo_proc_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividade_equipamento_radiacao_tipo_processo');

        Schema::table('atividades_equipamento_radiacao', function (Blueprint $table) {
            $table->dropColumn('obrigatorio_processo');
        });
    }
};
