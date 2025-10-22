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
        Schema::create('usuarios_externos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cpf', 14)->unique();
            $table->string('email')->unique();
            $table->string('telefone', 20);
            $table->enum('vinculo_estabelecimento', [
                'proprietario',
                'responsavel_tecnico',
                'responsavel_legal',
                'gerente',
                'outro'
            ])->default('proprietario');
            $table->string('password');
            $table->timestamp('aceite_termos_em')->nullable();
            $table->string('ip_aceite_termos')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Índices para melhorar performance
            $table->index('cpf');
            $table->index('email');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_externos');
    }
};
