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
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->enum('tipo_prazo', ['corridos', 'uteis'])->default('corridos')->after('prazo_padrao_dias');
        });

        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->enum('tipo_prazo', ['corridos', 'uteis'])->default('corridos')->after('prazo_dias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->dropColumn('tipo_prazo');
        });

        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->dropColumn('tipo_prazo');
        });
    }
};
