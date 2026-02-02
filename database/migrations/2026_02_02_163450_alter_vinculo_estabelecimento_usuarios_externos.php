<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Torna o campo vinculo_estabelecimento nullable e adiciona novos valores ao enum.
     * O vínculo agora é definido por estabelecimento (na tabela pivot), não por usuário.
     */
    public function up(): void
    {
        // Para PostgreSQL, precisamos alterar a coluna para aceitar null e converter para varchar
        // já que enum no PostgreSQL é mais complexo de alterar
        DB::statement("ALTER TABLE usuarios_externos ALTER COLUMN vinculo_estabelecimento DROP NOT NULL");
        DB::statement("ALTER TABLE usuarios_externos ALTER COLUMN vinculo_estabelecimento DROP DEFAULT");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE usuarios_externos ALTER COLUMN vinculo_estabelecimento SET NOT NULL");
        DB::statement("ALTER TABLE usuarios_externos ALTER COLUMN vinculo_estabelecimento SET DEFAULT 'proprietario'");
    }
};
