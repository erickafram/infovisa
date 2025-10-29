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
        Schema::create('processo_documento_anotacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_documento_id')->constrained('processo_documentos')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->integer('pagina');
            $table->string('tipo'); // highlight, text, drawing, area, comment
            $table->json('dados'); // Coordenadas e pontos da anotação
            $table->text('comentario')->nullable();
            $table->timestamps();
            
            $table->index(['processo_documento_id', 'pagina']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processo_documento_anotacoes');
    }
};
