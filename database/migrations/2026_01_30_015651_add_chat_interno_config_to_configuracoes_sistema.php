<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adiciona configuração para habilitar/desabilitar o chat interno
        DB::table('configuracoes_sistema')->insert([
            'chave' => 'chat_interno_ativo',
            'valor' => 'true',
            'tipo' => 'boolean',
            'descricao' => 'Habilita ou desabilita o chat interno para comunicação entre usuários',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configuracoes_sistema')->where('chave', 'chat_interno_ativo')->delete();
    }
};
