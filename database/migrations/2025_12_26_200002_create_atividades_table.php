<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para cadastrar as atividades que pertencem a um tipo de serviço
     * Ex: Restaurante, Lanchonete, Bar (dentro de Serviço de Alimentação)
     */
    public function up(): void
    {
        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_servico_id')->constrained('tipos_servico')->onDelete('cascade');
            $table->string('nome'); // Ex: Restaurante, Lanchonete
            $table->string('codigo_cnae')->nullable(); // Código CNAE relacionado (opcional)
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('tipo_servico_id');
            $table->index('codigo_cnae');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividades');
    }
};
