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
        Schema::create('diario_busca_salvas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_interno_id')
                ->constrained('usuarios_internos')
                ->onDelete('cascade');
            $table->string('nome');
            $table->string('texto');
            $table->date('data_inicial');
            $table->date('data_final');
            $table->timestamps();
            
            $table->index(['usuario_interno_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diario_busca_salvas');
    }
};
