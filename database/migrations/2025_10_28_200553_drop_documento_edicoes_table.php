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
        Schema::dropIfExists('documento_edicoes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('documento_edicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_digital_id')->constrained('documentos_digitais')->onDelete('cascade');
            $table->foreignId('usuario_interno_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->text('conteudo');
            $table->text('diff')->nullable();
            $table->integer('caracteres_adicionados')->default(0);
            $table->integer('caracteres_removidos')->default(0);
            $table->timestamp('iniciado_em');
            $table->timestamp('finalizado_em')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['documento_digital_id', 'ativo']);
            $table->index(['usuario_interno_id', 'ativo']);
        });
    }
};
