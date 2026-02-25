<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->jsonb('processos_ids')->nullable()->after('processo_id');
            $table->unsignedBigInteger('os_id')->nullable()->after('processos_ids');

            $table->foreign('os_id')->references('id')->on('ordens_servico')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->dropForeign(['os_id']);
            $table->dropColumn(['processos_ids', 'os_id']);
        });
    }
};
