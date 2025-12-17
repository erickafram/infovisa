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
        if (Schema::hasTable('sugestoes')) {
            return;
        }
        
        Schema::create('sugestoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_interno_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->string('pagina_url')->comment('URL da página onde a sugestão foi feita');
            $table->string('titulo');
            $table->text('descricao');
            $table->enum('tipo', ['funcionalidade', 'melhoria', 'modulo', 'correcao', 'outro'])->default('melhoria');
            $table->enum('status', ['pendente', 'em_analise', 'em_desenvolvimento', 'concluido', 'cancelado'])->default('pendente');
            $table->text('resposta_admin')->nullable()->comment('Resposta/feedback do administrador');
            $table->json('checklist')->nullable()->comment('Lista de itens a serem feitos');
            $table->foreignId('admin_responsavel_id')->nullable()->constrained('usuarios_internos')->onDelete('set null');
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();
            
            $table->index('pagina_url');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sugestoes');
    }
};
