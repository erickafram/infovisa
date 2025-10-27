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
        Schema::table('estabelecimentos', function (Blueprint $table) {
            // Adiciona FK para municípios (nullable para permitir migração gradual)
            $table->foreignId('municipio_id')
                  ->nullable()
                  ->after('cidade')
                  ->constrained('municipios')
                  ->nullOnDelete();
            
            // Índice para melhor performance
            $table->index('municipio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropForeign(['municipio_id']);
            $table->dropIndex(['municipio_id']);
            $table->dropColumn('municipio_id');
        });
    }
};
