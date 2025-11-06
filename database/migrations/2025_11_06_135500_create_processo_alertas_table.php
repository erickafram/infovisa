<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processo_alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->foreignId('usuario_criador_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->text('descricao');
            $table->date('data_alerta');
            $table->enum('status', ['pendente', 'visualizado', 'concluido'])->default('pendente');
            $table->timestamp('visualizado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();
            
            $table->index(['processo_id', 'status']);
            $table->index('data_alerta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processo_alertas');
    }
};
