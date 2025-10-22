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
        Schema::create('estabelecimentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome_fantasia');
            $table->string('razao_social');
            $table->string('cnpj', 14)->unique();
            $table->string('inscricao_estadual')->nullable();
            $table->string('endereco');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cidade');
            $table->string('estado', 2);
            $table->string('cep', 8);
            $table->string('telefone', 15)->nullable();
            $table->string('email')->nullable();
            $table->enum('tipo_estabelecimento', [
                'restaurante',
                'bar',
                'lanchonete',
                'supermercado',
                'mercearia',
                'padaria',
                'acougue',
                'farmacia',
                'hospital',
                'clinica',
                'laboratorio',
                'pet_shop',
                'outros'
            ]);
            $table->text('atividade_principal')->nullable();
            $table->boolean('ativo')->default(true);
            $table->foreignId('usuario_externo_id')->constrained('usuarios_externos')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('cnpj');
            $table->index('tipo_estabelecimento');
            $table->index('ativo');
            $table->index('usuario_externo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estabelecimentos');
    }
};
