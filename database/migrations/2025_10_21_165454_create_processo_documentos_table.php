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
        Schema::create('processo_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios_internos')->onDelete('set null');
            $table->string('tipo_usuario')->default('interno'); // 'interno' ou 'externo'
            $table->string('nome_arquivo');
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('extensao');
            $table->integer('tamanho'); // em bytes
            $table->string('tipo_documento')->default('arquivo_externo'); // 'arquivo_externo', 'documento_digital', etc
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            $table->index('processo_id');
            $table->index('tipo_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processo_documentos');
    }
};
