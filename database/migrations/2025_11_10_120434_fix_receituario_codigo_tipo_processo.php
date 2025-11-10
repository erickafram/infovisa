<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Corrige o código do tipo de processo "Receituário" para minúsculo
        // O código estava com "R" maiúsculo causando erro na validação
        DB::table('tipo_processos')
            ->where('codigo', 'Receituário')
            ->update(['codigo' => 'receituario']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverte para o código original (não recomendado)
        DB::table('tipo_processos')
            ->where('codigo', 'receituario')
            ->update(['codigo' => 'Receituário']);
    }
};
