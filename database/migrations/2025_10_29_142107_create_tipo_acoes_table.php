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
        Schema::create('tipo_acoes', function (Blueprint $table) {
            $table->id();
            $table->string('descricao'); // Nome detalhado da ação
            $table->string('codigo_procedimento')->unique(); // Código único do procedimento
            $table->boolean('atividade_sia')->default(false); // Indica se faz parte do SIA
            $table->enum('competencia', ['estadual', 'municipal', 'ambos']); // Competência da ação
            $table->boolean('ativo')->default(true); // Status ativo/inativo
            $table->timestamps();
            $table->softDeletes(); // Soft delete para histórico
            
            // Índices para melhor performance
            $table->index('codigo_procedimento');
            $table->index('competencia');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_acoes');
    }
};
