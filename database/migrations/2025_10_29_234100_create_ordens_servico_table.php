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
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // Número da OS (gerado automaticamente)
            $table->foreignId('estabelecimento_id')->constrained('estabelecimentos')->onDelete('cascade');
            $table->foreignId('tipo_acao_id')->constrained('tipo_acoes')->onDelete('restrict');
            $table->foreignId('usuario_responsavel_id')->constrained('usuarios_internos')->onDelete('restrict');
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onDelete('restrict');
            
            $table->text('descricao')->nullable();
            $table->text('observacoes')->nullable();
            
            $table->date('data_abertura');
            $table->date('data_prevista')->nullable();
            $table->date('data_conclusao')->nullable();
            
            $table->enum('status', ['aberta', 'em_andamento', 'concluida', 'cancelada'])->default('aberta');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            $table->enum('competencia', ['estadual', 'municipal']); // Competência da OS
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('numero');
            $table->index('estabelecimento_id');
            $table->index('status');
            $table->index('competencia');
            $table->index('data_abertura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};
