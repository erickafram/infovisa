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
        Schema::create('processo_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->foreignId('usuario_interno_id')->nullable()->constrained('usuarios_internos')->onDelete('set null');
            
            // Tipo de evento
            $table->enum('tipo_evento', [
                'processo_criado',
                'documento_anexado',
                'documento_digital_criado',
                'documento_excluido',
                'documento_digital_excluido',
                'status_alterado',
                'processo_arquivado',
                'movimentacao',
                'observacao_adicionada',
            ]);
            
            // Dados do evento
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->json('dados_adicionais')->nullable(); // Para armazenar dados extras (ID do documento, nome, etc)
            
            // Metadados
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('processo_id');
            $table->index('tipo_evento');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processo_eventos');
    }
};
