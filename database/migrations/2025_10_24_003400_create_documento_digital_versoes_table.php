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
        Schema::create('documento_digital_versoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_digital_id')->constrained('documentos_digitais')->onDelete('cascade');
            $table->foreignId('usuario_interno_id')->constrained('usuarios_internos')->onDelete('restrict');
            $table->integer('versao')->default(1);
            $table->text('conteudo');
            $table->text('alteracoes')->nullable(); // Descrição das alterações
            $table->timestamps();
            
            // Índices
            $table->index(['documento_digital_id', 'versao']);
            $table->index('usuario_interno_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_digital_versoes');
    }
};
