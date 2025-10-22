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
        Schema::create('processos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estabelecimento_id')->constrained('estabelecimentos')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios_internos')->onDelete('restrict'); // Quem criou
            
            // Tipo de processo
            $table->enum('tipo', [
                'licenciamento',
                'analise_rotulagem',
                'projeto_arquitetonico',
                'administrativo',
                'descentralizacao'
            ]);
            
            // Número do processo (ano/número sequencial)
            $table->year('ano');
            $table->integer('numero_sequencial');
            $table->string('numero_processo', 20)->unique(); // 2025/01319
            
            // Status do processo
            $table->enum('status', [
                'aberto',
                'em_analise',
                'pendente',
                'aprovado',
                'indeferido',
                'arquivado'
            ])->default('aberto');
            
            // Observações
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['estabelecimento_id', 'tipo']);
            $table->index('numero_processo');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processos');
    }
};
