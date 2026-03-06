<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            if (!Schema::hasColumn('processos', 'prazo_fila_publica_reiniciado_em')) {
                $table->timestamp('prazo_fila_publica_reiniciado_em')
                    ->nullable()
                    ->after('tempo_total_parado_segundos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            if (Schema::hasColumn('processos', 'prazo_fila_publica_reiniciado_em')) {
                $table->dropColumn('prazo_fila_publica_reiniciado_em');
            }
        });
    }
};
