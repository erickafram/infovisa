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
        // Tabela para cadastrar quais atividades econômicas exigem equipamentos de radiação ionizante
        Schema::create('atividades_equipamento_radiacao', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_atividade', 20); // Código CNAE da atividade
            $table->string('descricao_atividade', 500); // Descrição da atividade
            $table->text('observacoes')->nullable(); // Observações adicionais
            $table->boolean('ativo')->default(true);
            $table->foreignId('criado_por')->nullable()->constrained('usuarios_internos')->nullOnDelete();
            $table->timestamps();
            
            $table->unique('codigo_atividade');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividades_equipamento_radiacao');
    }
};
