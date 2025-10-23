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
            // Adicionar campo motivo_desativacao
            $table->text('motivo_desativacao')->nullable()->after('ativo');
            
            // Remover o status 'arquivado' se existir
            // Atualizar estabelecimentos arquivados para aprovados e inativos
            DB::statement("UPDATE estabelecimentos SET status = 'aprovado', ativo = false WHERE status = 'arquivado'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropColumn('motivo_desativacao');
        });
    }
};
