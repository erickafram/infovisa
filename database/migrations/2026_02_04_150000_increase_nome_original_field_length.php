<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Aumenta o tamanho do campo nome_original para 1000 caracteres
     * para suportar nomes de documentos obrigatórios muito longos
     */
    public function up(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->string('nome_original', 1000)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->string('nome_original', 500)->change();
        });
    }
};
