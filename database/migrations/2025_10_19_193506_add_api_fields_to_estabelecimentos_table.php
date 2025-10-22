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
            $table->string('natureza_juridica')->nullable()->after('ativo');
            $table->string('porte')->nullable()->after('natureza_juridica');
            $table->string('situacao_cadastral')->nullable()->after('porte');
            $table->date('data_situacao_cadastral')->nullable()->after('situacao_cadastral');
            $table->date('data_inicio_atividade')->nullable()->after('data_situacao_cadastral');
            $table->string('cnae_fiscal')->nullable()->after('data_inicio_atividade');
            $table->string('cnae_fiscal_descricao')->nullable()->after('cnae_fiscal');
            $table->json('cnaes_secundarios')->nullable()->after('cnae_fiscal_descricao');
            $table->json('qsa')->nullable()->after('cnaes_secundarios'); // Quadro Societário e Administração
            $table->decimal('capital_social', 15, 2)->nullable()->after('qsa');
            $table->string('logradouro')->nullable()->after('capital_social');
            $table->string('codigo_municipio_ibge')->nullable()->after('logradouro');
            $table->enum('tipo_pessoa', ['juridica', 'fisica'])->default('juridica')->after('codigo_municipio_ibge');
            
            // Campos específicos para pessoa física
            $table->string('cpf', 11)->nullable()->after('cnpj');
            $table->string('nome_completo')->nullable()->after('cpf'); // Para pessoa física
            
            // Tornar CNPJ nullable para permitir pessoa física
            $table->string('cnpj', 14)->nullable()->change();
            
            // Índices
            $table->index('cpf');
            $table->index('tipo_pessoa');
            $table->index('situacao_cadastral');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropColumn([
                'natureza_juridica',
                'porte',
                'situacao_cadastral',
                'data_situacao_cadastral',
                'data_inicio_atividade',
                'cnae_fiscal',
                'cnae_fiscal_descricao',
                'cnaes_secundarios',
                'qsa',
                'capital_social',
                'logradouro',
                'codigo_municipio_ibge',
                'tipo_pessoa',
                'cpf',
                'nome_completo'
            ]);
            
            $table->dropIndex(['cpf']);
            $table->dropIndex(['tipo_pessoa']);
            $table->dropIndex(['situacao_cadastral']);
            
            // Reverter CNPJ para não nullable
            $table->string('cnpj', 14)->nullable(false)->change();
        });
    }
};
