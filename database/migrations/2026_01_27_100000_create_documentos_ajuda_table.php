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
        Schema::create('documentos_ajuda', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('arquivo'); // Caminho do arquivo PDF
            $table->string('nome_original'); // Nome original do arquivo
            $table->bigInteger('tamanho')->default(0); // Tamanho em bytes
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // Tabela pivot para vincular documentos de ajuda aos tipos de processo
        Schema::create('documento_ajuda_tipo_processo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_ajuda_id')->constrained('documentos_ajuda')->onDelete('cascade');
            $table->foreignId('tipo_processo_id')->constrained('tipo_processos')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['documento_ajuda_id', 'tipo_processo_id'], 'doc_ajuda_tipo_proc_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_ajuda_tipo_processo');
        Schema::dropIfExists('documentos_ajuda');
    }
};
