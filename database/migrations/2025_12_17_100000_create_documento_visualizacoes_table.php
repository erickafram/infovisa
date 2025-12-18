<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para registrar visualizações de documentos pelos usuários externos (estabelecimentos)
     * Conforme §1º: O estabelecimento é considerado notificado oficialmente quando o INFOVISA 
     * for acessado por um dos colaboradores da empresa, independente da visualização ou não do documento,
     * ou após 5 (cinco) dias de sua disponibilidade no INFOVISA.
     */
    public function up(): void
    {
        Schema::create('documento_visualizacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_digital_id')->constrained('documentos_digitais')->onDelete('cascade');
            $table->foreignId('usuario_externo_id')->nullable()->constrained('usuarios_externos')->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Índice para busca rápida
            $table->index(['documento_digital_id', 'created_at']);
        });

        // Adicionar campos no documento digital para controle de prazo
        Schema::table('documentos_digitais', function (Blueprint $table) {
            // Data em que o prazo começou a contar (primeira visualização ou 5 dias após disponibilização)
            $table->timestamp('prazo_iniciado_em')->nullable()->after('data_vencimento');
            // Motivo do início do prazo: 'visualizacao' ou 'tempo_disponibilidade'
            $table->string('prazo_iniciado_por', 30)->nullable()->after('prazo_iniciado_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->dropColumn(['prazo_iniciado_em', 'prazo_iniciado_por']);
        });
        
        Schema::dropIfExists('documento_visualizacoes');
    }
};
