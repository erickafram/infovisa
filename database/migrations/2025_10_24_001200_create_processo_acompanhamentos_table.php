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
        Schema::create('processo_acompanhamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->foreignId('usuario_interno_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->timestamps();
            
            // Índice único para evitar duplicação
            $table->unique(['processo_id', 'usuario_interno_id']);
            
            // Índices para performance
            $table->index('usuario_interno_id');
            $table->index('processo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processo_acompanhamentos');
    }
};
