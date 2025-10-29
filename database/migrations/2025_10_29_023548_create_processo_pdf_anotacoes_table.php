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
        Schema::create('processo_pdf_anotacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_documento_id')->constrained('processo_documentos')->onDelete('cascade');
            $table->foreignId('usuario_interno_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->integer('pagina')->comment('Número da página do PDF onde está a anotação');
            $table->string('tipo')->comment('Tipo de anotação: highlight, text, drawing, area, comment');
            $table->json('dados')->comment('Dados da anotação (coordenadas, cor, texto, etc)');
            $table->text('comentario')->nullable()->comment('Comentário/observação do usuário');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['processo_documento_id', 'pagina']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processo_pdf_anotacoes');
    }
};
