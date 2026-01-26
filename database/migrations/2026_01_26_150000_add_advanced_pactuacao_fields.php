<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona campos para suportar cenários avançados de pactuação:
     * - Questionário para definir risco (não competência)
     * - Verificação de localização (dentro de Unidade Hospitalar)
     * - Múltiplas perguntas
     * - Municípios com exceção para localização hospitalar
     */
    public function up(): void
    {
        Schema::table('pactuacoes', function (Blueprint $table) {
            // Tipo de questionário: competencia, risco, localizacao, visa
            $table->string('tipo_questionario')->nullable()->after('requer_questionario')
                ->comment('Tipo: competencia, risco, localizacao, visa, risco_localizacao');
            
            // Segunda pergunta (para casos com múltiplas perguntas)
            $table->text('pergunta2')->nullable()->after('pergunta')
                ->comment('Segunda pergunta do questionário (ex: localização hospitalar)');
            
            // Tipo da segunda pergunta
            $table->string('tipo_pergunta2')->nullable()->after('pergunta2')
                ->comment('Tipo da pergunta 2: localizacao, competencia');
            
            // Municípios exceção para localização hospitalar (Palmas, Araguaína)
            $table->json('municipios_excecao_hospitalar')->nullable()->after('municipios_excecao_ids')
                ->comment('Municípios que mantêm competência municipal mesmo em hospital');
            
            // Risco quando resposta é SIM
            $table->string('risco_sim')->nullable()->after('classificacao_risco')
                ->comment('Classificação de risco quando resposta é SIM');
            
            // Risco quando resposta é NÃO
            $table->string('risco_nao')->nullable()->after('risco_sim')
                ->comment('Classificação de risco quando resposta é NÃO');
            
            // Competência base (para casos onde competência é fixa mas risco varia)
            $table->string('competencia_base')->nullable()->after('risco_nao')
                ->comment('Competência base: municipal, estadual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pactuacoes', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_questionario',
                'pergunta2',
                'tipo_pergunta2',
                'municipios_excecao_hospitalar',
                'risco_sim',
                'risco_nao',
                'competencia_base'
            ]);
        });
    }
};
