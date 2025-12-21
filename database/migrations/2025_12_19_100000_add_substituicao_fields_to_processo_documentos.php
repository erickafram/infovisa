<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona campos para permitir substituição de documentos rejeitados
     * e manter histórico de rejeições.
     */
    public function up(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            // ID do documento que este substitui (quando for uma correção de documento rejeitado)
            $table->foreignId('documento_substituido_id')
                ->nullable()
                ->after('processo_id')
                ->constrained('processo_documentos')
                ->onDelete('set null');
            
            // Contador de quantas vezes foi rejeitado (para histórico)
            $table->integer('tentativas_envio')->default(1)->after('motivo_rejeicao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->dropForeign(['documento_substituido_id']);
            $table->dropColumn(['documento_substituido_id', 'tentativas_envio']);
        });
    }
};
