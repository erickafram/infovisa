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
            // Declaração de que não possui equipamentos de imagem mesmo tendo atividade que exige
            $table->boolean('declaracao_sem_equipamentos_imagem')->default(false)->after('atividades_exercidas');
            $table->timestamp('declaracao_sem_equipamentos_imagem_data')->nullable()->after('declaracao_sem_equipamentos_imagem');
            $table->text('declaracao_sem_equipamentos_imagem_justificativa')->nullable()->after('declaracao_sem_equipamentos_imagem_data');
            $table->unsignedBigInteger('declaracao_sem_equipamentos_imagem_usuario_id')->nullable()->after('declaracao_sem_equipamentos_imagem_justificativa');
            
            $table->index('declaracao_sem_equipamentos_imagem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropIndex(['declaracao_sem_equipamentos_imagem']);
            $table->dropColumn([
                'declaracao_sem_equipamentos_imagem',
                'declaracao_sem_equipamentos_imagem_data',
                'declaracao_sem_equipamentos_imagem_justificativa',
                'declaracao_sem_equipamentos_imagem_usuario_id',
            ]);
        });
    }
};
