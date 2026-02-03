<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adiciona campo de prazo para análise após documentos completos
     */
    public function up(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->integer('prazo_fila_publica')->nullable()->after('exibir_fila_publica')
                ->comment('Prazo em dias para análise após todos os documentos obrigatórios serem aprovados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->dropColumn('prazo_fila_publica');
        });
    }
};
