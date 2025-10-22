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
        Schema::create('tipo_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Nome do tipo (ex: Alvará Sanitário)
            $table->string('codigo')->unique(); // Código único (ex: alvara_sanitario)
            $table->text('descricao')->nullable(); // Descrição do tipo
            $table->boolean('ativo')->default(true); // Se está ativo
            $table->integer('ordem')->default(0); // Ordem de exibição
            $table->timestamps();
            
            $table->index('ativo');
            $table->index('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_documentos');
    }
};
