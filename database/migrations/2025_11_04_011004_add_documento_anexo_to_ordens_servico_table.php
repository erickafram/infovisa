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
            // Campos para armazenar documento anexo (denúncia, solicitação MPE, etc)
            $table->string('documento_anexo_path')->nullable()->after('observacoes');
            $table->string('documento_anexo_nome')->nullable()->after('documento_anexo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropColumn(['documento_anexo_path', 'documento_anexo_nome']);
        });
    }
};
