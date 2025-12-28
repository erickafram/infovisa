<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela pivot que vincula uma lista de documentos aos tipos de documento obrigatório
     * Define quais documentos são exigidos em cada lista
     */
    public function up(): void
    {
        Schema::create('lista_documento_tipo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lista_documento_id')->constrained('listas_documento')->onDelete('cascade');
            $table->foreignId('tipo_documento_obrigatorio_id')->constrained('tipos_documento_obrigatorio')->onDelete('cascade');
            $table->boolean('obrigatorio')->default(true); // Se é obrigatório ou opcional
            $table->text('observacao')->nullable(); // Observações específicas para este documento nesta lista
            $table->integer('ordem')->default(0); // Ordem de exibição
            $table->timestamps();
            
            $table->unique(['lista_documento_id', 'tipo_documento_obrigatorio_id'], 'lista_tipo_doc_unique');
            $table->index('lista_documento_id');
            $table->index('tipo_documento_obrigatorio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lista_documento_tipo');
    }
};
