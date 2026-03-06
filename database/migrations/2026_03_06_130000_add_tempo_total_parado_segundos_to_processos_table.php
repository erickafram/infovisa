<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->unsignedBigInteger('tempo_total_parado_segundos')
                ->default(0)
                ->after('usuario_parada_id');
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn('tempo_total_parado_segundos');
        });
    }
};