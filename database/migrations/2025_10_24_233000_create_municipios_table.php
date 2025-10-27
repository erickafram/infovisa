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
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100)->comment('Nome do município');
            $table->string('codigo_ibge', 7)->unique()->comment('Código IBGE do município');
            $table->string('uf', 2)->default('TO')->comment('Unidade Federativa');
            $table->string('slug', 100)->unique()->comment('Slug para URLs e comparações');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index('nome');
            $table->index('codigo_ibge');
            $table->index('slug');
            $table->index('uf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
