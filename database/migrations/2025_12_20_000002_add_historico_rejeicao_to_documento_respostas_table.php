<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_respostas', function (Blueprint $table) {
            $table->json('historico_rejeicao')->nullable()->after('motivo_rejeicao');
        });
    }

    public function down(): void
    {
        Schema::table('documento_respostas', function (Blueprint $table) {
            $table->dropColumn('historico_rejeicao');
        });
    }
};
