<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesquisas_satisfacao', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->enum('tipo_publico', ['interno', 'externo']);
            // Para tipo_publico = 'interno': array de tipo_setores ids que devem responder
            $table->json('tipo_setores_ids')->nullable();
            $table->boolean('ativo')->default(true);
            // Slug único para gerar link público de resposta
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('pesquisas_satisfacao_perguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesquisa_id')->constrained('pesquisas_satisfacao')->cascadeOnDelete();
            $table->text('texto');
            $table->enum('tipo', ['escala_1_5', 'multipla_escolha', 'texto_livre'])->default('escala_1_5');
            $table->boolean('obrigatoria')->default(true);
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('pesquisas_satisfacao_opcoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pergunta_id')->constrained('pesquisas_satisfacao_perguntas')->cascadeOnDelete();
            $table->string('texto');
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesquisas_satisfacao_opcoes');
        Schema::dropIfExists('pesquisas_satisfacao_perguntas');
        Schema::dropIfExists('pesquisas_satisfacao');
    }
};
