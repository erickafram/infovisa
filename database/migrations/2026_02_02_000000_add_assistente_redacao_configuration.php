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
        // Insere configuração para ativar/desativar assistente de redação se não existir
        DB::table('configuracoes_sistema')->updateOrInsert(
            ['chave' => 'assistente_redacao_ativo'],
            [
                'valor' => 'true',
                'tipo' => 'boolean',
                'descricao' => 'Ativa ou desativa o assistente de IA para redação de documentos',
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
            ->where('chave', 'assistente_redacao_ativo')
            ->delete();
    }
};
