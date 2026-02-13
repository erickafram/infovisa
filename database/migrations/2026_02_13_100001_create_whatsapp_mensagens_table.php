<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_mensagens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documento_digital_id');
            $table->unsignedBigInteger('estabelecimento_id');
            $table->unsignedBigInteger('usuario_externo_id');
            $table->string('telefone', 20)->comment('Telefone para o qual foi enviado');
            $table->string('nome_destinatario')->comment('Nome do usuário externo no momento do envio');
            $table->text('mensagem')->comment('Conteúdo da mensagem enviada');
            $table->string('status')->default('pendente')->comment('pendente, enviado, erro, entregue, lido');
            $table->text('erro_mensagem')->nullable()->comment('Descrição do erro caso falhe');
            $table->string('whatsapp_message_id')->nullable()->comment('ID da mensagem retornado pelo Baileys');
            $table->timestamp('enviado_em')->nullable();
            $table->timestamp('entregue_em')->nullable();
            $table->timestamp('lido_em')->nullable();
            $table->unsignedInteger('tentativas')->default(0);
            $table->timestamp('proxima_tentativa')->nullable();
            $table->timestamps();

            $table->foreign('documento_digital_id')->references('id')->on('documentos_digitais')->cascadeOnDelete();
            $table->foreign('estabelecimento_id')->references('id')->on('estabelecimentos')->cascadeOnDelete();
            $table->foreign('usuario_externo_id')->references('id')->on('usuarios_externos')->cascadeOnDelete();

            $table->index(['status', 'created_at']);
            $table->index('documento_digital_id');
            $table->index('estabelecimento_id');
            $table->index('usuario_externo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_mensagens');
    }
};
