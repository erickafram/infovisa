<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('mensagem');
            $table->string('link')->nullable();
            $table->enum('tipo', ['info', 'aviso', 'urgente'])->default('info');
            $table->json('niveis_acesso'); // Array de níveis que podem ver o aviso
            $table->date('data_expiracao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->foreignId('criado_por')->constrained('usuarios_internos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos');
    }
};
