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
        Schema::table('tipo_documentos', function (Blueprint $table) {
            // Adiciona campo para identificar se é tipo de documento de notificação/fiscalização
            // Se true: documentos deste tipo terão prazo contado da visualização OU 5º dia útil
            // Se false: documentos deste tipo terão prazo fixo/anual
            $table->boolean('prazo_notificacao')->default(false)->after('prazo_padrao_dias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->dropColumn('prazo_notificacao');
        });
    }
};
