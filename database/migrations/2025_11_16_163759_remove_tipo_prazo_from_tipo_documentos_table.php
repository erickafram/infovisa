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
            // Remove o campo tipo_prazo da tabela tipo_documentos
            // Esse campo não é mais necessário pois o tipo de prazo (corridos/úteis)
            // é definido apenas na criação do documento, não no tipo de documento
            $table->dropColumn('tipo_prazo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            // Restaura o campo caso precise fazer rollback
            $table->enum('tipo_prazo', ['corridos', 'uteis'])->default('corridos')->after('prazo_padrao_dias');
        });
    }
};
