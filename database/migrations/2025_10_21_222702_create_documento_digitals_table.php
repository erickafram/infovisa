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
        Schema::create('documentos_digitais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_documento_id')->constrained('tipo_documentos')->onDelete('cascade');
            $table->foreignId('processo_id')->nullable()->constrained('processos')->onDelete('cascade');
            $table->foreignId('usuario_criador_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->string('numero_documento')->unique();
            $table->text('conteudo');
            $table->boolean('sigiloso')->default(false);
            $table->enum('status', ['rascunho', 'aguardando_assinatura', 'assinado', 'cancelado'])->default('rascunho');
            $table->string('arquivo_pdf')->nullable();
            $table->timestamp('finalizado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_digitals');
    }
};
