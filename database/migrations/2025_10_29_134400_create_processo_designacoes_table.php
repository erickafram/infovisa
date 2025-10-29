<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processo_designacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->foreignId('usuario_designado_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->foreignId('usuario_designador_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->text('descricao_tarefa');
            $table->date('data_limite')->nullable();
            $table->enum('status', ['pendente', 'em_andamento', 'concluida', 'cancelada'])->default('pendente');
            $table->text('observacoes_conclusao')->nullable();
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('usuario_designado_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processo_designacoes');
    }
};
