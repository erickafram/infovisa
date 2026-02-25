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
        Schema::create('atividades_responsavel_tecnico', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_atividade', 20);
            $table->string('descricao_atividade', 500);
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->foreignId('criado_por')->nullable()->constrained('usuarios_internos')->nullOnDelete();
            $table->timestamps();

            $table->unique('codigo_atividade');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividades_responsavel_tecnico');
    }
};
