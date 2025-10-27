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
        Schema::table('pactuacoes', function (Blueprint $table) {
            // Adiciona FK para municípios (nullable para permitir migração gradual)
            $table->foreignId('municipio_id')
                  ->nullable()
                  ->after('municipio')
                  ->constrained('municipios')
                  ->nullOnDelete()
                  ->comment('FK para tabela de municípios (pactuações municipais)');
            
            // Campo JSON para IDs de municípios de exceção
            $table->json('municipios_excecao_ids')
                  ->nullable()
                  ->after('municipios_excecao')
                  ->comment('Array de IDs de municípios descentralizados');
            
            // Índices
            $table->index('municipio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pactuacoes', function (Blueprint $table) {
            $table->dropForeign(['municipio_id']);
            $table->dropIndex(['municipio_id']);
            $table->dropColumn(['municipio_id', 'municipios_excecao_ids']);
        });
    }
};
