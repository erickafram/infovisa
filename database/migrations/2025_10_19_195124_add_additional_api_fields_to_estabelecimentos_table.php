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
        Schema::table('estabelecimentos', function (Blueprint $table) {
            // Campos adicionais da API Minha Receita
            $table->string('ddd_telefone_1')->nullable()->after('telefone');
            $table->string('ddd_telefone_2')->nullable()->after('ddd_telefone_1');
            $table->string('ddd_fax')->nullable()->after('ddd_telefone_2');
            $table->boolean('opcao_pelo_mei')->nullable()->after('ddd_fax');
            $table->boolean('opcao_pelo_simples')->nullable()->after('opcao_pelo_mei');
            $table->json('regime_tributario')->nullable()->after('opcao_pelo_simples');
            $table->string('situacao_especial')->nullable()->after('regime_tributario');
            $table->string('motivo_situacao_cadastral')->nullable()->after('situacao_especial');
            $table->string('identificador_matriz_filial')->nullable()->after('motivo_situacao_cadastral');
            $table->string('qualificacao_do_responsavel')->nullable()->after('identificador_matriz_filial');
            
            // Índices para campos importantes
            $table->index('opcao_pelo_mei');
            $table->index('opcao_pelo_simples');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropColumn([
                'ddd_telefone_1',
                'ddd_telefone_2',
                'ddd_fax',
                'opcao_pelo_mei',
                'opcao_pelo_simples',
                'regime_tributario',
                'situacao_especial',
                'motivo_situacao_cadastral',
                'identificador_matriz_filial',
                'qualificacao_do_responsavel'
            ]);
            
            $table->dropIndex(['opcao_pelo_mei']);
            $table->dropIndex(['opcao_pelo_simples']);
        });
    }
};
