<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_processo_setor_municipio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_processo_id')->constrained('tipo_processos')->cascadeOnDelete();
            $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
            $table->foreignId('tipo_setor_id')->constrained('tipo_setores')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tipo_processo_id', 'municipio_id'], 'tp_setor_municipio_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_processo_setor_municipio');
    }
};