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
        // Dropa a tabela antiga se existir
        Schema::dropIfExists('pactuacoes');
        
        // Cria a nova tabela com estrutura completa
        Schema::create('pactuacoes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['municipal', 'estadual'])->comment('Tipo de competência');
            $table->string('municipio', 100)->nullable()->comment('Nome do município (null para estadual)');
            $table->string('cnae_codigo', 20)->comment('Código CNAE da atividade');
            $table->string('cnae_descricao')->comment('Descrição da atividade');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            // Índices para melhor performance
            $table->index(['tipo', 'municipio']);
            $table->index('cnae_codigo');
            $table->unique(['tipo', 'municipio', 'cnae_codigo'], 'pactuacao_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pactuacoes');
    }
};
