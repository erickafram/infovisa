<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona campos para documentos comuns a todos os serviços,
     * com suporte a escopo de competência (estadual/municipal) e
     * tipo de setor (público/privado).
     */
    public function up(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            // Se true, este documento é comum a TODOS os serviços (não precisa vincular a atividades específicas)
            $table->boolean('documento_comum')->default(false)->after('ordem');
            
            // Escopo de competência: 'todos', 'estadual', 'municipal'
            // Define se o documento se aplica a processos de competência estadual, municipal ou ambos
            $table->string('escopo_competencia', 20)->default('todos')->after('documento_comum');
            
            // Tipo de setor: 'todos', 'publico', 'privado'
            // Define se o documento se aplica a estabelecimentos públicos, privados ou ambos
            $table->string('tipo_setor', 20)->default('todos')->after('escopo_competencia');
            
            // Observação específica para estabelecimentos públicos (ex: "Isento para estabelecimentos públicos")
            $table->text('observacao_publica')->nullable()->after('tipo_setor');
            
            // Observação específica para estabelecimentos privados
            $table->text('observacao_privada')->nullable()->after('observacao_publica');
            
            // Prazo de validade em dias (ex: CNPJ com data de impressão de até 30 dias)
            $table->integer('prazo_validade_dias')->nullable()->after('observacao_privada');
            
            // Índices para consultas
            $table->index('documento_comum');
            $table->index('escopo_competencia');
            $table->index('tipo_setor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->dropIndex(['documento_comum']);
            $table->dropIndex(['escopo_competencia']);
            $table->dropIndex(['tipo_setor']);
            
            $table->dropColumn([
                'documento_comum',
                'escopo_competencia',
                'tipo_setor',
                'observacao_publica',
                'observacao_privada',
                'prazo_validade_dias',
            ]);
        });
    }
};
