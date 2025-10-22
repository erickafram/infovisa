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
        Schema::create('tipo_processos', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Nome do tipo de processo (ex: Licenciamento)
            $table->string('codigo')->unique(); // Código único (ex: licenciamento)
            $table->text('descricao')->nullable(); // Descrição do tipo de processo
            $table->boolean('anual')->default(false); // Se é processo anual (apenas 1 por ano)
            $table->boolean('usuario_externo_pode_abrir')->default(false); // Se usuário externo pode abrir
            $table->boolean('ativo')->default(true); // Se está ativo no sistema
            $table->integer('ordem')->default(0); // Ordem de exibição
            $table->timestamps();
            
            // Índices
            $table->index('codigo');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_processos');
    }
};
