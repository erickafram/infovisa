<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            if (!Schema::hasColumn('processo_documentos', 'os_id')) {
                $table->foreignId('os_id')->nullable()->after('processo_id')->constrained('ordens_servico')->nullOnDelete();
            }

            if (!Schema::hasColumn('processo_documentos', 'atividade_index')) {
                $table->unsignedInteger('atividade_index')->nullable()->after('os_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            if (Schema::hasColumn('processo_documentos', 'atividade_index')) {
                $table->dropColumn('atividade_index');
            }

            if (Schema::hasColumn('processo_documentos', 'os_id')) {
                $table->dropForeign(['os_id']);
                $table->dropColumn('os_id');
            }
        });
    }
};