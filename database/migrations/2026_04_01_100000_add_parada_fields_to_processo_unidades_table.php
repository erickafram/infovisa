<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_unidades', function (Blueprint $table) {
            $table->string('status')->default('ativo')->after('unidade_id'); // ativo, parado
            $table->text('motivo_parada')->nullable()->after('status');
            $table->timestamp('data_parada')->nullable()->after('motivo_parada');
            $table->unsignedBigInteger('usuario_parada_id')->nullable()->after('data_parada');
            $table->integer('tempo_total_parado_segundos')->default(0)->after('usuario_parada_id');
        });
    }

    public function down(): void
    {
        Schema::table('processo_unidades', function (Blueprint $table) {
            $table->dropColumn(['status', 'motivo_parada', 'data_parada', 'usuario_parada_id', 'tempo_total_parado_segundos']);
        });
    }
};
