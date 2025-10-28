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
            $table->enum('competencia_manual', ['estadual', 'municipal'])->nullable()->after('respostas_questionario')
                ->comment('Override manual da competência (decisão administrativa/judicial)');
            $table->text('motivo_alteracao_competencia')->nullable()->after('competencia_manual')
                ->comment('Justificativa para alteração manual da competência');
            $table->unsignedBigInteger('alterado_por')->nullable()->after('motivo_alteracao_competencia')
                ->comment('ID do usuário que alterou a competência');
            $table->timestamp('alterado_em')->nullable()->after('alterado_por')
                ->comment('Data/hora da alteração de competência');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropColumn(['competencia_manual', 'motivo_alteracao_competencia', 'alterado_por', 'alterado_em']);
        });
    }
};
