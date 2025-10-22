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
        Schema::create('modelo_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_documento_id')->constrained('tipo_documentos')->onDelete('cascade');
            $table->string('codigo')->unique(); // Código único gerado automaticamente
            $table->text('descricao')->nullable(); // Descrição do modelo
            $table->longText('conteudo'); // Conteúdo HTML do modelo
            $table->json('variaveis')->nullable(); // Variáveis disponíveis no modelo (ex: {estabelecimento_nome})
            $table->boolean('ativo')->default(true); // Se está ativo
            $table->integer('ordem')->default(0); // Ordem de exibição
            $table->timestamps();
            
            $table->index('tipo_documento_id');
            $table->index('ativo');
            $table->index('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modelo_documentos');
    }
};
