<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela pivot que vincula atividades diretamente aos tipos de documento obrigatório.
     * Esta é a nova estrutura simplificada que substitui a entidade ListaDocumento.
     */
    public function up(): void
    {
        Schema::create('atividade_documento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atividade_id')->constrained('atividades')->onDelete('cascade');
            $table->foreignId('tipo_documento_obrigatorio_id')->constrained('tipos_documento_obrigatorio')->onDelete('cascade');
            $table->boolean('obrigatorio')->default(true);
            $table->text('observacao')->nullable(); // Observação específica para esta atividade
            $table->integer('ordem')->default(0);
            $table->timestamps();

            // Índices
            $table->unique(['atividade_id', 'tipo_documento_obrigatorio_id'], 'atividade_documento_unique');
            $table->index('atividade_id');
            $table->index('tipo_documento_obrigatorio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividade_documento');
    }
};
