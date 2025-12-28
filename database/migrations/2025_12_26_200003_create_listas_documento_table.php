<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela principal que define uma lista de documentos obrigatórios
     * vinculada a atividades e com escopo de competência (Estado ou Município específico)
     */
    public function up(): void
    {
        Schema::create('listas_documento', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Nome da lista para identificação
            $table->text('descricao')->nullable();
            
            // Escopo: 'estadual' ou 'municipal'
            $table->string('escopo', 20)->default('estadual');
            
            // Se escopo = 'municipal', qual município
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onDelete('set null');
            
            $table->boolean('ativo')->default(true);
            $table->foreignId('criado_por')->nullable()->constrained('usuarios_internos')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('escopo');
            $table->index('municipio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listas_documento');
    }
};
