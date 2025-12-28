<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para cadastrar os tipos de serviço
     * Ex: Serviço de Alimentação, Serviço de Saúde, etc.
     */
    public function up(): void
    {
        Schema::create('tipos_servico', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Ex: Serviço de Alimentação
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_servico');
    }
};
