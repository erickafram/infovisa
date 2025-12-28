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
        Schema::table('listas_documento', function (Blueprint $table) {
            $table->foreignId('tipo_processo_id')->nullable()->after('id')->constrained('tipo_processos')->onDelete('set null');
            $table->index('tipo_processo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listas_documento', function (Blueprint $table) {
            $table->dropForeign(['tipo_processo_id']);
            $table->dropColumn('tipo_processo_id');
        });
    }
};
