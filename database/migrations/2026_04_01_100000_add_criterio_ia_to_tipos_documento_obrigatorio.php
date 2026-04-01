<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->text('criterio_ia')->nullable()->after('instrucoes')
                ->comment('Critérios para análise automática por IA');
            $table->string('ia_modelo_visao')->nullable()->after('criterio_ia')
                ->comment('Modelo de visão específico para este documento (substitui o padrão)');
        });
    }

    public function down(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->dropColumn(['criterio_ia', 'ia_modelo_visao']);
        });
    }
};
