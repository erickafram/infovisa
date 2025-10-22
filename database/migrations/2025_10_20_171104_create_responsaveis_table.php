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
        Schema::create('responsaveis', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['legal', 'tecnico']); // Tipo de responsável
            $table->string('cpf', 11)->unique(); // CPF sem formatação
            $table->string('nome');
            $table->string('email');
            $table->string('telefone', 20);
            
            // Campos específicos para Responsável Legal
            $table->enum('tipo_documento', ['rg', 'cnh'])->nullable(); // RG ou CNH
            $table->string('documento_identificacao')->nullable(); // Path do PDF
            
            // Campos específicos para Responsável Técnico
            $table->string('conselho', 50)->nullable(); // Ex: CREA, CRF, CRM
            $table->string('numero_registro_conselho', 50)->nullable();
            $table->string('carteirinha_conselho')->nullable(); // Path do arquivo (PDF, JPG, PNG)
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('cpf');
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responsaveis');
    }
};
