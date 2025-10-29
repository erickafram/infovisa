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
        // Insere configurações da IA
        DB::table('configuracoes_sistema')->insert([
            [
                'chave' => 'ia_ativa',
                'valor' => 'false',
                'descricao' => 'Ativa ou desativa o assistente de IA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chave' => 'ia_api_key',
                'valor' => '8f2666a67bee6b36fbc09d507c0b2e4e4059ae3c3a78672448eefaf248cd673b',
                'descricao' => 'Chave de API do Together AI',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chave' => 'ia_api_url',
                'valor' => 'https://api.together.xyz/v1/chat/completions',
                'descricao' => 'URL da API do Together AI',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chave' => 'ia_model',
                'valor' => 'meta-llama/Llama-3-70b-chat-hf',
                'descricao' => 'Modelo de IA utilizado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configuracoes_sistema')
            ->whereIn('chave', ['ia_ativa', 'ia_api_key', 'ia_api_url', 'ia_model'])
            ->delete();
    }
};
