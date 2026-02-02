<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Aumenta o tamanho dos campos de nome_arquivo, nome_original e caminho
     * para suportar nomes de arquivos e caminhos mais longos.
     */
    public function up(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->string('nome_arquivo', 500)->change();
            $table->string('nome_original', 500)->change();
            $table->string('caminho', 500)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->string('nome_arquivo', 255)->change();
            $table->string('nome_original', 255)->change();
            $table->string('caminho', 255)->change();
        });
    }
};