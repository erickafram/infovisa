<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('tipo_processo_unidade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_processo_id')->constrained('tipo_processos')->onDelete('cascade');
            $table->foreignId('unidade_id')->constrained('unidades')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['tipo_processo_id', 'unidade_id']);
        });

        // Unidades selecionadas pelo estabelecimento ao abrir o processo
        Schema::create('processo_unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->foreignId('unidade_id')->constrained('unidades')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['processo_id', 'unidade_id']);
        });

        // Adiciona campo unidade_id na tabela de documentos do processo
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->foreignId('unidade_id')->nullable()->after('pasta_id')->constrained('unidades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidade_id');
        });
        Schema::dropIfExists('processo_unidades');
        Schema::dropIfExists('tipo_processo_unidade');
        Schema::dropIfExists('unidades');
    }
};
