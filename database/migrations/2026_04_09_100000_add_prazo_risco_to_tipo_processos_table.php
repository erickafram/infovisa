<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->integer('prazo_fila_publica_alto')->nullable()->after('prazo_fila_publica');
            $table->integer('prazo_fila_publica_medio')->nullable()->after('prazo_fila_publica_alto');
            $table->integer('prazo_fila_publica_baixo')->nullable()->after('prazo_fila_publica_medio');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->dropColumn(['prazo_fila_publica_alto', 'prazo_fila_publica_medio', 'prazo_fila_publica_baixo']);
        });
    }
};
