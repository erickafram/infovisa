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
        Schema::create('sub_acoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_acao_id')->constrained('tipo_acoes')->onDelete('cascade');
            $table->string('descricao'); // Nome/descrição da subação
            $table->string('codigo_procedimento')->nullable(); // Código específico da subação (opcional)
            $table->integer('ordem')->default(0); // Ordem de exibição
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('tipo_acao_id');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_acoes');
    }
};
