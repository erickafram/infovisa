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
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->text('motivo_cancelamento')->nullable()->after('observacoes_finalizacao');
            $table->timestamp('cancelada_em')->nullable()->after('motivo_cancelamento');
            $table->foreignId('cancelada_por')->nullable()->constrained('usuarios_internos')->after('cancelada_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropForeign(['cancelada_por']);
            $table->dropColumn(['motivo_cancelamento', 'cancelada_em', 'cancelada_por']);
        });
    }
};
