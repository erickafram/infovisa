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
        Schema::create('documentos_pops', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('arquivo_nome');
            $table->string('arquivo_path');
            $table->string('arquivo_mime_type');
            $table->integer('arquivo_tamanho'); // em bytes
            $table->boolean('disponivel_ia')->default(false); // Se será usado pelo Assistente IA
            $table->text('conteudo_extraido')->nullable(); // Conteúdo extraído do PDF/DOC para indexação
            $table->timestamp('indexado_em')->nullable(); // Quando foi indexado pela IA
            $table->foreignId('criado_por')->constrained('usuarios_internos')->onDelete('cascade');
            $table->foreignId('atualizado_por')->nullable()->constrained('usuarios_internos')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para busca
            $table->index('disponivel_ia');
            $table->index('criado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_pops');
    }
};
