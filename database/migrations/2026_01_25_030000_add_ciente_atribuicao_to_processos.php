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
            $table->timestamp('responsavel_ciente_em')->nullable()->after('prazo_atribuicao');
            $table->text('motivo_atribuicao')->nullable()->after('responsavel_ciente_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn(['responsavel_ciente_em', 'motivo_atribuicao']);
        });
    }
};
