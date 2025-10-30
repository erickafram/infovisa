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
        Schema::table('ordens_servico', function (Blueprint $table) {
            // Remove campos desnecessários
            $table->dropForeign(['usuario_responsavel_id']);
            $table->dropColumn(['usuario_responsavel_id', 'descricao', 'data_prevista']);
            
            // Altera tipo_acao_id para permitir múltiplas ações (será JSON)
            $table->dropForeign(['tipo_acao_id']);
            $table->dropColumn('tipo_acao_id');
            
            // Adiciona novos campos (nullable para permitir migração de dados existentes)
            $table->json('tipos_acao_ids')->nullable()->after('estabelecimento_id'); // Array de IDs de tipos de ação
            $table->json('tecnicos_ids')->nullable()->after('tipos_acao_ids'); // Array de IDs de técnicos
            $table->date('data_inicio')->nullable()->after('data_abertura'); // Data de início da execução
            $table->date('data_fim')->nullable()->after('data_inicio'); // Data fim da execução
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            // Reverte as alterações
            $table->dropColumn(['tipos_acao_ids', 'tecnicos_ids', 'data_inicio', 'data_fim']);
            
            // Restaura campos antigos
            $table->foreignId('tipo_acao_id')->after('estabelecimento_id')->constrained('tipo_acoes')->onDelete('restrict');
            $table->foreignId('usuario_responsavel_id')->after('tipo_acao_id')->constrained('usuarios_internos')->onDelete('restrict');
            $table->text('descricao')->nullable()->after('observacoes');
            $table->date('data_prevista')->nullable()->after('data_abertura');
        });
    }
};
