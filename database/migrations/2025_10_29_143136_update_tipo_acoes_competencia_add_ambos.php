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
        // No PostgreSQL, precisamos usar DB::statement para alterar ENUM
        \DB::statement("ALTER TABLE tipo_acoes DROP CONSTRAINT IF EXISTS tipo_acoes_competencia_check");
        \DB::statement("ALTER TABLE tipo_acoes ADD CONSTRAINT tipo_acoes_competencia_check CHECK (competencia IN ('estadual', 'municipal', 'ambos'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE tipo_acoes DROP CONSTRAINT IF EXISTS tipo_acoes_competencia_check");
        \DB::statement("ALTER TABLE tipo_acoes ADD CONSTRAINT tipo_acoes_competencia_check CHECK (competencia IN ('estadual', 'municipal'))");
    }
};
