<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->timestamp('prazo_finalizado_em')->nullable()->after('prazo_iniciado_por');
            $table->unsignedBigInteger('prazo_finalizado_por')->nullable()->after('prazo_finalizado_em');
            $table->string('prazo_finalizado_motivo')->nullable()->after('prazo_finalizado_por');
            
            $table->foreign('prazo_finalizado_por')->references('id')->on('usuarios_internos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->dropForeign(['prazo_finalizado_por']);
            $table->dropColumn(['prazo_finalizado_em', 'prazo_finalizado_por', 'prazo_finalizado_motivo']);
        });
    }
};
