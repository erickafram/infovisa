<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('baileys_server_url')->comment('URL do servidor Baileys (ex: http://localhost:3000)');
            $table->string('api_key')->nullable()->comment('Chave de API para autenticação no Baileys');
            $table->string('session_name')->default('infovisa')->comment('Nome da sessão do WhatsApp');
            $table->boolean('ativo')->default(false)->comment('Se o envio de WhatsApp está ativo');
            $table->boolean('enviar_ao_assinar')->default(true)->comment('Enviar quando documento for totalmente assinado');
            $table->text('mensagem_template')->nullable()->comment('Template da mensagem (variáveis: {nome_usuario}, {nome_documento}, {numero_documento}, {nome_estabelecimento}, {link_documento})');
            $table->string('status_conexao')->default('desconectado')->comment('Status atual da conexão: conectado, desconectado, aguardando_qr');
            $table->text('qr_code')->nullable()->comment('QR Code atual para conexão');
            $table->timestamp('ultima_verificacao')->nullable();
            $table->unsignedBigInteger('configurado_por')->nullable();
            $table->foreign('configurado_por')->references('id')->on('usuarios_internos')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_configuracoes');
    }
};
