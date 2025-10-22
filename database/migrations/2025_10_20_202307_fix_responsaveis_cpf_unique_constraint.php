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
        Schema::table('responsaveis', function (Blueprint $table) {
            // Remover constraint unique do CPF
            $table->dropUnique('responsaveis_cpf_unique');
            
            // Adicionar constraint unique composto (CPF + TIPO)
            $table->unique(['cpf', 'tipo'], 'responsaveis_cpf_tipo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responsaveis', function (Blueprint $table) {
            // Remover constraint unique composto
            $table->dropUnique('responsaveis_cpf_tipo_unique');
            
            // Restaurar constraint unique do CPF
            $table->unique('cpf');
        });
    }
};
