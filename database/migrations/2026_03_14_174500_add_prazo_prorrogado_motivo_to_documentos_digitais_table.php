<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            if (!Schema::hasColumn('documentos_digitais', 'prazo_prorrogado_motivo')) {
                $table->text('prazo_prorrogado_motivo')->nullable()->after('prazo_prorrogado_por');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            if (Schema::hasColumn('documentos_digitais', 'prazo_prorrogado_motivo')) {
                $table->dropColumn('prazo_prorrogado_motivo');
            }
        });
    }
};