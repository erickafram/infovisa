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
        // Adiciona configuração para habilitar busca na internet
        DB::table('configuracoes_sistema')->insert([
            'chave' => 'ia_busca_web',
            'valor' => 'false',
            'descricao' => 'Habilitar busca complementar na internet quando não houver documentos POPs relevantes',
            'tipo' => 'boolean',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configuracoes_sistema')->where('chave', 'ia_busca_web')->delete();
    }
};
