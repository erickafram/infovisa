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
        Schema::table('tipo_processos', function (Blueprint $table) {
            // Competência: 'estadual' ou 'municipal'
            $table->enum('competencia', ['estadual', 'municipal'])->default('municipal')->after('ativo');
            
            // Municípios descentralizados (que podem usar tipos estaduais)
            $table->json('municipios_descentralizados')->nullable()->after('competencia');
            $table->json('municipios_descentralizados_ids')->nullable()->after('municipios_descentralizados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->dropColumn(['competencia', 'municipios_descentralizados', 'municipios_descentralizados_ids']);
        });
    }
};
