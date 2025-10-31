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
        Schema::table('processos', function (Blueprint $table) {
            $table->text('motivo_parada')->nullable()->after('motivo_arquivamento');
            $table->timestamp('data_parada')->nullable()->after('motivo_parada');
            $table->foreignId('usuario_parada_id')->nullable()->after('data_parada')
                ->constrained('usuarios_internos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropForeign(['usuario_parada_id']);
            $table->dropColumn(['motivo_parada', 'data_parada', 'usuario_parada_id']);
        });
    }
};
