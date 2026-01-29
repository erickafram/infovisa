<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de conversas (entre dois usuários)
        Schema::create('chat_conversas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario1_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->foreignId('usuario2_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->timestamp('ultima_mensagem_at')->nullable();
            $table->timestamps();
            
            // Índice único para evitar conversas duplicadas
            $table->unique(['usuario1_id', 'usuario2_id']);
        });

        // Tabela de mensagens
        Schema::create('chat_mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversa_id')->constrained('chat_conversas')->onDelete('cascade');
            $table->foreignId('remetente_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->text('conteudo')->nullable(); // Texto da mensagem
            $table->enum('tipo', ['texto', 'imagem', 'audio', 'arquivo'])->default('texto');
            $table->string('arquivo_path')->nullable(); // Caminho do arquivo
            $table->string('arquivo_nome')->nullable(); // Nome original do arquivo
            $table->string('arquivo_mime')->nullable(); // Tipo MIME
            $table->integer('arquivo_tamanho')->nullable(); // Tamanho em bytes
            $table->timestamp('lida_em')->nullable();
            $table->timestamps();
            
            $table->index(['conversa_id', 'created_at']);
        });

        // Tabela de status online
        Schema::create('chat_usuarios_online', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->timestamp('ultimo_acesso')->useCurrent();
            $table->boolean('digitando')->default(false);
            $table->foreignId('digitando_para')->nullable()->constrained('usuarios_internos')->onDelete('set null');
            
            $table->unique('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_usuarios_online');
        Schema::dropIfExists('chat_mensagens');
        Schema::dropIfExists('chat_conversas');
    }
};
