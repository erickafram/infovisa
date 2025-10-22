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
        Schema::create('usuarios_internos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cpf', 11)->unique();
            $table->string('email')->unique();
            $table->string('telefone', 15)->nullable();
            $table->string('matricula', 20)->unique()->nullable();
            $table->string('cargo')->nullable();
            $table->enum('nivel_acesso', [
                'administrador',
                'gestor_estadual',
                'gestor_municipal',
                'tecnico_estadual',
                'tecnico_municipal'
            ]);
            $table->string('municipio')->nullable()->comment('Para usuários municipais');
            $table->string('password');
            $table->boolean('ativo')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('cpf');
            $table->index('email');
            $table->index('nivel_acesso');
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_internos');
    }
};
