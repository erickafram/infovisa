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
        Schema::create('estabelecimento_responsavel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estabelecimento_id')->constrained('estabelecimentos')->onDelete('cascade');
            $table->foreignId('responsavel_id')->constrained('responsaveis')->onDelete('cascade');
            $table->enum('tipo_vinculo', ['legal', 'tecnico']); // Tipo do vínculo
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            // Garantir que não haja duplicatas
            $table->unique(['estabelecimento_id', 'responsavel_id', 'tipo_vinculo']);
            
            // Índices para consultas rápidas
            $table->index('estabelecimento_id');
            $table->index('responsavel_id');
            $table->index('tipo_vinculo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estabelecimento_responsavel');
    }
};
