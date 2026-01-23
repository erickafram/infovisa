<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona campos para nomenclatura padrão do arquivo e instruções
     * de como obter/preencher o documento.
     */
    public function up(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            // Nomenclatura padrão para o arquivo (ex: "CNPJ", "CONTRATO SOCIAL")
            $table->string('nomenclatura_arquivo', 100)->nullable()->after('nome');
            
            // Instruções detalhadas de como obter/preencher o documento
            $table->text('instrucoes')->nullable()->after('descricao');
            
            // URL de referência para obter o documento (ex: site da Receita Federal)
            $table->string('url_referencia', 500)->nullable()->after('instrucoes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->dropColumn(['nomenclatura_arquivo', 'instrucoes', 'url_referencia']);
        });
    }
};
