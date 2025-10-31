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
            // Adiciona campo processo_id após estabelecimento_id
            $table->foreignId('processo_id')
                  ->nullable()
                  ->after('estabelecimento_id')
                  ->constrained('processos')
                  ->onDelete('restrict');
            
            // Adiciona índice
            $table->index('processo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropForeign(['processo_id']);
            $table->dropIndex(['processo_id']);
            $table->dropColumn('processo_id');
        });
    }
};
