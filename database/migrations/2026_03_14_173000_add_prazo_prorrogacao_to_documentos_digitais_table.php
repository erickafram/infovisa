<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            if (!Schema::hasColumn('documentos_digitais', 'prazo_prorrogado_dias')) {
                $table->unsignedInteger('prazo_prorrogado_dias')->default(0)->after('prazo_finalizado_motivo');
            }

            if (!Schema::hasColumn('documentos_digitais', 'prazo_prorrogado_em')) {
                $table->timestamp('prazo_prorrogado_em')->nullable()->after('prazo_prorrogado_dias');
            }

            if (!Schema::hasColumn('documentos_digitais', 'prazo_prorrogado_por')) {
                $table->unsignedBigInteger('prazo_prorrogado_por')->nullable()->after('prazo_prorrogado_em');
                $table->foreign('prazo_prorrogado_por')->references('id')->on('usuarios_internos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            if (Schema::hasColumn('documentos_digitais', 'prazo_prorrogado_por')) {
                $table->dropForeign(['prazo_prorrogado_por']);
            }

            $colunas = collect([
                'prazo_prorrogado_dias',
                'prazo_prorrogado_em',
                'prazo_prorrogado_por',
            ])->filter(fn ($coluna) => Schema::hasColumn('documentos_digitais', $coluna))->all();

            if (!empty($colunas)) {
                $table->dropColumn($colunas);
            }
        });
    }
};