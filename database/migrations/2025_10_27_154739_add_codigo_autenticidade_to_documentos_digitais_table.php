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
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->string('codigo_autenticidade', 64)->unique()->nullable()->after('arquivo_pdf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->dropColumn('codigo_autenticidade');
        });
    }
};
