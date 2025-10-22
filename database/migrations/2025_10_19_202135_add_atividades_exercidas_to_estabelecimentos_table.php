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
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->json('atividades_exercidas')->nullable()->after('atividade_principal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropColumn('atividades_exercidas');
        });
    }
};
