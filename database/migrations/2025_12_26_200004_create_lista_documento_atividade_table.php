<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela pivot que vincula uma lista de documentos às atividades
     * Uma lista pode ter várias atividades e uma atividade pode estar em várias listas
     */
    public function up(): void
    {
        Schema::create('lista_documento_atividade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lista_documento_id')->constrained('listas_documento')->onDelete('cascade');
            $table->foreignId('atividade_id')->constrained('atividades')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['lista_documento_id', 'atividade_id'], 'lista_atividade_unique');
            $table->index('lista_documento_id');
            $table->index('atividade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lista_documento_atividade');
    }
};
