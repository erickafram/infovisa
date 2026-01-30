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
        // Insere configuração para ativar/desativar chat interno se não existir
        DB::table('configuracoes_sistema')->updateOrInsert(
            ['chave' => 'chat_interno_ativo'],
            [
                'valor' => 'true',
                'tipo' => 'boolean',
                'descricao' => 'Ativa ou desativa o chat interno entre usuários',
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configuracoes_sistema')
            ->where('chave', 'chat_interno_ativo')
            ->delete();
    }
};
