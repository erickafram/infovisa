<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treinamento_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('local')->nullable();
            $table->dateTime('data_inicio')->nullable();
            $table->dateTime('data_fim')->nullable();
            $table->string('status')->default('planejado');
            $table->boolean('inscricoes_ativas')->default(true);
            $table->string('link_inscricao_token')->unique();
            $table->foreignId('criado_por')->constrained('usuarios_internos')->cascadeOnDelete();
            $table->foreignId('atualizado_por')->nullable()->constrained('usuarios_internos')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('treinamento_inscricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treinamento_evento_id')->constrained('treinamento_eventos')->cascadeOnDelete();
            $table->string('nome');
            $table->string('email');
            $table->string('telefone')->nullable();
            $table->string('instituicao')->nullable();
            $table->string('cargo')->nullable();
            $table->string('cidade')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('token')->unique();
            $table->timestamps();

            $table->unique(['treinamento_evento_id', 'email'], 'treinamento_inscricoes_evento_email_unique');
        });

        Schema::create('treinamento_apresentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treinamento_evento_id')->constrained('treinamento_eventos')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('status')->default('rascunho');
            $table->foreignId('criado_por')->constrained('usuarios_internos')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('treinamento_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treinamento_apresentacao_id')->constrained('treinamento_apresentacoes')->cascadeOnDelete();
            $table->string('titulo');
            $table->longText('conteudo')->nullable();
            $table->unsignedInteger('ordem')->default(1);
            $table->timestamps();
        });

        Schema::create('treinamento_perguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treinamento_slide_id')->constrained('treinamento_slides')->cascadeOnDelete();
            $table->text('enunciado');
            $table->string('token')->unique();
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });

        Schema::create('treinamento_pergunta_opcoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treinamento_pergunta_id')->constrained('treinamento_perguntas')->cascadeOnDelete();
            $table->string('texto');
            $table->unsignedInteger('ordem')->default(1);
            $table->timestamps();
        });

        Schema::create('treinamento_pergunta_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treinamento_pergunta_id')->constrained('treinamento_perguntas')->cascadeOnDelete();
            $table->foreignId('treinamento_pergunta_opcao_id')->constrained('treinamento_pergunta_opcoes')->cascadeOnDelete();
            $table->foreignId('treinamento_inscricao_id')->nullable()->constrained('treinamento_inscricoes')->nullOnDelete();
            $table->string('token_sessao')->nullable();
            $table->string('participante_nome')->nullable();
            $table->string('participante_email')->nullable();
            $table->string('participante_telefone')->nullable();
            $table->timestamps();

            $table->index(['treinamento_pergunta_id', 'participante_email'], 'treinamento_respostas_pergunta_email_idx');
            $table->index(['treinamento_pergunta_id', 'token_sessao'], 'treinamento_respostas_pergunta_sessao_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treinamento_pergunta_respostas');
        Schema::dropIfExists('treinamento_pergunta_opcoes');
        Schema::dropIfExists('treinamento_perguntas');
        Schema::dropIfExists('treinamento_slides');
        Schema::dropIfExists('treinamento_apresentacoes');
        Schema::dropIfExists('treinamento_inscricoes');
        Schema::dropIfExists('treinamento_eventos');
    }
};
