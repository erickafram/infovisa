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
            // Remove a constraint foreign key atual
            $table->dropForeign(['estabelecimento_id']);
            
            // Altera a coluna para nullable
            $table->foreignId('estabelecimento_id')->nullable()->change();
            
            // Recria a constraint foreign key
            $table->foreign('estabelecimento_id')
                  ->references('id')
                  ->on('estabelecimentos')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            // Remove a constraint foreign key
            $table->dropForeign(['estabelecimento_id']);
            
            // Volta a coluna para NOT NULL
            $table->foreignId('estabelecimento_id')->nullable(false)->change();
            
            // Recria a constraint foreign key
            $table->foreign('estabelecimento_id')
                  ->references('id')
                  ->on('estabelecimentos')
                  ->onDelete('restrict');
        });
    }
};
