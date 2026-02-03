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
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->foreignId('tipo_setor_id')->nullable()->after('ordem')->constrained('tipo_setores')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->dropForeign(['tipo_setor_id']);
            $table->dropColumn('tipo_setor_id');
        });
    }
};
