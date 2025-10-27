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
            $table->json('municipios_excecao')->nullable()->after('cnae_descricao')
                ->comment('Municípios que têm competência descentralizada (exceção à regra estadual)');
            $table->text('observacao')->nullable()->after('municipios_excecao')
                ->comment('Observações sobre a atividade (ex: condições especiais)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pactuacoes', function (Blueprint $table) {
            $table->dropColumn(['municipios_excecao', 'observacao']);
        });
    }
};
