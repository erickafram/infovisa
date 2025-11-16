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
        Schema::table('documentos_digitais', function (Blueprint $table) {
            // Adiciona campo para identificar se é documento de notificação/fiscalização
            // Se true: prazo conta da visualização OU 5º dia útil (o que ocorrer primeiro)
            // Se false: prazo é fixo/anual (ex: Alvará Sanitário)
            $table->boolean('prazo_notificacao')->default(false)->after('tipo_prazo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->dropColumn('prazo_notificacao');
        });
    }
};
