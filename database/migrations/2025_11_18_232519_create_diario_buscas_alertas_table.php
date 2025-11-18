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
        Schema::create('diario_busca_alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diario_busca_salva_id')->constrained('diario_busca_salvas')->onDelete('cascade');
            $table->foreignId('usuario_interno_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->string('titulo');
            $table->string('edicao')->nullable();
            $table->date('data_publicacao');
            $table->text('url_download')->nullable();
            $table->boolean('lido')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diario_busca_alertas');
    }
};
