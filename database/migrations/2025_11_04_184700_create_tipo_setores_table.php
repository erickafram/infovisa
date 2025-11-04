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
        Schema::create('tipo_setores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('codigo', 50)->unique();
            $table->text('descricao')->nullable();
            $table->json('niveis_acesso')->nullable()->comment('Array de níveis de acesso que podem usar este setor');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('codigo');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_setores');
    }
};
