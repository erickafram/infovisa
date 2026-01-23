<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para armazenar respostas dos estabelecimentos aos documentos digitais
     * (ex: resposta a uma notificação sanitária)
     */
    public function up(): void
    {
        Schema::create('documento_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_digital_id')->constrained('documentos_digitais')->onDelete('cascade');
            $table->foreignId('usuario_externo_id')->constrained('usuarios_externos')->onDelete('cascade');
            $table->string('nome_arquivo');
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('extensao', 10);
            $table->bigInteger('tamanho'); // em bytes
            $table->text('observacoes')->nullable();
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente');
            $table->text('motivo_rejeicao')->nullable();
            $table->foreignId('avaliado_por')->nullable()->constrained('usuarios_internos')->onDelete('set null');
            $table->timestamp('avaliado_em')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['documento_digital_id', 'status']);
            $table->index('usuario_externo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_respostas');
    }
};




