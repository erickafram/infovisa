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
     * Adiciona suporte para Tabela VI - Atividades de Processo
     * Essas são atividades especiais que não vêm do CNPJ, mas permitem
     * que estabelecimentos abram processos específicos (Projeto Arquitetônico, Análise de Rotulagem)
     */
    public function up(): void
    {
        // Adiciona campo para vincular atividade a tipo de processo específico
        Schema::table('pactuacoes', function (Blueprint $table) {
            // Tipo de processo vinculado (para Tabela VI)
            $table->string('tipo_processo_codigo')->nullable()->after('tabela');
            // Se é uma atividade especial (não vem do CNPJ)
            $table->boolean('atividade_especial')->default(false)->after('tipo_processo_codigo');
        });

        // Insere as atividades especiais da Tabela VI
        $atividadesEspeciais = [
            [
                'tipo' => 'estadual',
                'tabela' => 'VI',
                'cnae_codigo' => 'PROJ_ARQ',
                'cnae_descricao' => 'Projeto Arquitetônico - Análise de projeto arquitetônico para adequação sanitária',
                'tipo_processo_codigo' => 'projeto_arquitetonico',
                'atividade_especial' => true,
                'competencia_base' => 'estadual',
                'classificacao_risco' => 'medio',
                'requer_questionario' => false,
                'observacao' => 'Atividade especial para estabelecimentos que desejam apenas análise de projeto arquitetônico',
                'ativo' => true,
                'municipios_excecao' => json_encode([]),
                'municipios_excecao_ids' => json_encode([]),
            ],
            [
                'tipo' => 'estadual',
                'tabela' => 'VI',
                'cnae_codigo' => 'ANAL_ROT',
                'cnae_descricao' => 'Análise de Rotulagem - Análise e aprovação de rótulos de produtos',
                'tipo_processo_codigo' => 'analise_rotulagem',
                'atividade_especial' => true,
                'competencia_base' => 'estadual',
                'classificacao_risco' => 'baixo',
                'requer_questionario' => false,
                'observacao' => 'Atividade especial para estabelecimentos que desejam apenas análise de rotulagem',
                'ativo' => true,
                'municipios_excecao' => json_encode([]),
                'municipios_excecao_ids' => json_encode([]),
            ],
        ];

        foreach ($atividadesEspeciais as $atividade) {
            // Verifica se já existe
            $existe = DB::table('pactuacoes')
                ->where('cnae_codigo', $atividade['cnae_codigo'])
                ->exists();
            
            if (!$existe) {
                DB::table('pactuacoes')->insert(array_merge($atividade, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove as atividades especiais
        DB::table('pactuacoes')
            ->where('tabela', 'VI')
            ->where('atividade_especial', true)
            ->delete();

        Schema::table('pactuacoes', function (Blueprint $table) {
            $table->dropColumn(['tipo_processo_codigo', 'atividade_especial']);
        });
    }
};
