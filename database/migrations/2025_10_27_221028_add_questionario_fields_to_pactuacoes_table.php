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
        Schema::table('pactuacoes', function (Blueprint $table) {
            $table->string('tabela')->nullable()->after('tipo')->comment('Tabela do documento: I, II, III, IV, V');
            $table->boolean('requer_questionario')->default(false)->after('tabela')->comment('Se requer questionário para definir competência');
            $table->text('pergunta')->nullable()->after('requer_questionario')->comment('Pergunta do questionário');
            $table->string('classificacao_risco')->nullable()->after('pergunta')->comment('baixo, medio, alto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pactuacoes', function (Blueprint $table) {
            $table->dropColumn(['tabela', 'requer_questionario', 'pergunta', 'classificacao_risco']);
        });
    }
};
