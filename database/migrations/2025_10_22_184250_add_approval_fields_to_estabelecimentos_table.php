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
            // Status de aprovação
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado', 'arquivado'])
                  ->default('pendente')
                  ->after('ativo');
            
            // Município do estabelecimento (para filtros)
            $table->string('municipio', 100)->nullable()->after('cidade');
            
            // Motivo de rejeição
            $table->text('motivo_rejeicao')->nullable()->after('status');
            
            // Quem aprovou/rejeitou
            $table->foreignId('aprovado_por')->nullable()
                  ->constrained('usuarios_internos')
                  ->nullOnDelete()
                  ->after('motivo_rejeicao');
            
            // Quando foi aprovado/rejeitado
            $table->timestamp('aprovado_em')->nullable()->after('aprovado_por');
            
            // Índices
            $table->index('status');
            $table->index('municipio');
            $table->index('aprovado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropForeign(['aprovado_por']);
            $table->dropIndex(['status']);
            $table->dropIndex(['municipio']);
            $table->dropIndex(['aprovado_por']);
            $table->dropColumn([
                'status',
                'municipio',
                'motivo_rejeicao',
                'aprovado_por',
                'aprovado_em'
            ]);
        });
    }
};
