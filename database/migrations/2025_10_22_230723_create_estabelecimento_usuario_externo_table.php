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
        Schema::create('estabelecimento_usuario_externo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estabelecimento_id')->constrained('estabelecimentos')->onDelete('cascade');
            $table->foreignId('usuario_externo_id')->constrained('usuarios_externos')->onDelete('cascade');
            $table->enum('tipo_vinculo', [
                'proprietario',
                'responsavel_legal',
                'responsavel_tecnico',
                'contador',
                'procurador',
                'outro'
            ])->default('outro');
            $table->text('observacao')->nullable();
            $table->foreignId('vinculado_por')->nullable()->constrained('usuarios_internos')->nullOnDelete();
            $table->timestamps();

            // Índices
            $table->unique(['estabelecimento_id', 'usuario_externo_id'], 'estabelecimento_usuario_unique');
            $table->index('estabelecimento_id');
            $table->index('usuario_externo_id');
            $table->index('tipo_vinculo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estabelecimento_usuario_externo');
    }
};
