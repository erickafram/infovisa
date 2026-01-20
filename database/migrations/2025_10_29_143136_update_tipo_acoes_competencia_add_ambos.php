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
        // PostgreSQL: Adicionar novo valor ao ENUM
        \DB::statement("ALTER TYPE competencia_enum ADD VALUE IF NOT EXISTS 'ambos'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PostgreSQL: Não é possível remover valores de ENUM diretamente
        // Seria necessário recriar o tipo e a coluna
        \DB::statement("-- Rollback não implementado para PostgreSQL ENUM");
    }
};
