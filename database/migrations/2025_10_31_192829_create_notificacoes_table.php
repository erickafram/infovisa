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
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_interno_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->string('tipo'); // 'ordem_servico_atribuida', 'ordem_servico_prazo', etc
            $table->string('titulo');
            $table->text('mensagem');
            $table->string('link')->nullable();
            $table->foreignId('ordem_servico_id')->nullable()->constrained('ordens_servico')->onDelete('cascade');
            $table->boolean('lida')->default(false);
            $table->timestamp('lida_em')->nullable();
            $table->string('prioridade')->default('normal'); // 'baixa', 'normal', 'alta', 'urgente'
            $table->timestamps();
            
            $table->index(['usuario_interno_id', 'lida']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};
