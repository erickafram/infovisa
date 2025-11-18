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
        Schema::table('diario_busca_salvas', function (Blueprint $table) {
            $table->boolean('executar_diariamente')->default(false)->after('data_final');
            $table->timestamp('ultima_execucao')->nullable()->after('executar_diariamente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diario_busca_salvas', function (Blueprint $table) {
            $table->dropColumn(['executar_diariamente', 'ultima_execucao']);
        });
    }
};
