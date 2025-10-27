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
        Schema::table('usuarios_internos', function (Blueprint $table) {
            // Adiciona FK para municípios (obrigatório para gestores/técnicos municipais)
            $table->foreignId('municipio_id')
                  ->nullable()
                  ->after('nivel_acesso')
                  ->constrained('municipios')
                  ->nullOnDelete()
                  ->comment('Município vinculado (obrigatório para gestores/técnicos municipais)');
            
            $table->index('municipio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios_internos', function (Blueprint $table) {
            $table->dropForeign(['municipio_id']);
            $table->dropIndex(['municipio_id']);
            $table->dropColumn('municipio_id');
        });
    }
};
