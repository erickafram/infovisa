<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona campo de exclusão nas mensagens
        Schema::table('chat_mensagens', function (Blueprint $table) {
            $table->timestamp('deletada_em')->nullable()->after('lida_em');
            $table->boolean('deletada_para_todos')->default(false)->after('deletada_em');
        });

        // Tabela de mensagens broadcast (Suporte InfoVISA)
        Schema::create('chat_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enviado_por')->constrained('usuarios_internos')->onDelete('cascade');
            $table->text('conteudo');
            $table->json('niveis_acesso'); // Array de níveis que recebem a mensagem
            $table->string('tipo')->default('texto'); // texto, imagem, arquivo
            $table->string('arquivo_path')->nullable();
            $table->string('arquivo_nome')->nullable();
            $table->timestamps();
        });

        // Tabela de leitura de broadcasts por usuário
        Schema::create('chat_broadcast_leituras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained('chat_broadcasts')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->timestamp('lida_em')->nullable();
            $table->timestamps();

            $table->unique(['broadcast_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_broadcast_leituras');
        Schema::dropIfExists('chat_broadcasts');
        
        Schema::table('chat_mensagens', function (Blueprint $table) {
            $table->dropColumn(['deletada_em', 'deletada_para_todos']);
        });
    }
};
