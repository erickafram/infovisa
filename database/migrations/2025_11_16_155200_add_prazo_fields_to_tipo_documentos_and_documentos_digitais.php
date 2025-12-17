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
        // Adiciona campos de prazo na tabela tipo_documentos
        Schema::table('tipo_documentos', function (Blueprint $table) {
            if (!Schema::hasColumn('tipo_documentos', 'tem_prazo')) {
                $table->boolean('tem_prazo')->default(false)->after('ativo');
            }
            if (!Schema::hasColumn('tipo_documentos', 'prazo_padrao_dias')) {
                $table->integer('prazo_padrao_dias')->nullable()->after('tem_prazo')->comment('Prazo padrão em dias (opcional)');
            }
        });

        // Adiciona campos de prazo na tabela documentos_digitais
        Schema::table('documentos_digitais', function (Blueprint $table) {
            if (!Schema::hasColumn('documentos_digitais', 'prazo_dias')) {
                $table->integer('prazo_dias')->nullable()->after('finalizado_em')->comment('Prazo em dias para este documento');
            }
            if (!Schema::hasColumn('documentos_digitais', 'data_vencimento')) {
                $table->date('data_vencimento')->nullable()->after('prazo_dias')->comment('Data de vencimento calculada');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            if (Schema::hasColumn('tipo_documentos', 'tem_prazo')) {
                $table->dropColumn('tem_prazo');
            }
            if (Schema::hasColumn('tipo_documentos', 'prazo_padrao_dias')) {
                $table->dropColumn('prazo_padrao_dias');
            }
        });

        Schema::table('documentos_digitais', function (Blueprint $table) {
            if (Schema::hasColumn('documentos_digitais', 'prazo_dias')) {
                $table->dropColumn('prazo_dias');
            }
            if (Schema::hasColumn('documentos_digitais', 'data_vencimento')) {
                $table->dropColumn('data_vencimento');
            }
        });
    }
};
