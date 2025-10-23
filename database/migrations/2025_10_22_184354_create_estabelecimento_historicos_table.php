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
        Schema::create('estabelecimento_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estabelecimento_id')->constrained('estabelecimentos')->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios_internos')->nullOnDelete();
            $table->enum('acao', [
                'criado',
                'atualizado',
                'aprovado',
                'rejeitado',
                'arquivado',
                'reiniciado'
            ]);
            $table->string('status_anterior')->nullable();
            $table->string('status_novo')->nullable();
            $table->text('observacao')->nullable();
            $table->json('dados_alterados')->nullable(); // Armazena campos que foram alterados
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('estabelecimento_id');
            $table->index('usuario_id');
            $table->index('acao');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estabelecimento_historicos');
    }
};
