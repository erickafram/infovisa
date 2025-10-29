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
        Schema::create('categoria_documento_pop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_pop_id')->constrained('categorias_pops')->onDelete('cascade');
            $table->foreignId('documento_pop_id')->constrained('documentos_pops')->onDelete('cascade');
            $table->timestamps();
            
            // Evita duplicatas
            $table->unique(['categoria_pop_id', 'documento_pop_id']);
            
            $table->index('categoria_pop_id');
            $table->index('documento_pop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_documento_pop');
    }
};
