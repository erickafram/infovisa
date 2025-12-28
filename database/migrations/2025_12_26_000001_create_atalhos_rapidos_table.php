<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atalhos_rapidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_interno_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->string('titulo', 100);
            $table->string('url', 500);
            $table->string('icone', 50)->default('link');
            $table->string('cor', 20)->default('blue');
            $table->integer('ordem')->default(0);
            $table->timestamps();
            
            $table->index(['usuario_interno_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atalhos_rapidos');
    }
};
