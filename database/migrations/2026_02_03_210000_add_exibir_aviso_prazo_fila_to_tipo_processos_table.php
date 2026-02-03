<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adiciona campo para exibir aviso de prazo no processo
     */
    public function up(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->boolean('exibir_aviso_prazo_fila')->default(false)->after('prazo_fila_publica')
                ->comment('Exibir aviso no processo sobre prazo restante quando documentação estiver completa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->dropColumn('exibir_aviso_prazo_fila');
        });
    }
};
