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
            $table->string('descricao_situacao_cadastral')->nullable()->after('situacao_cadastral');
            $table->string('descricao_motivo_situacao_cadastral')->nullable()->after('motivo_situacao_cadastral');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            $table->dropColumn(['descricao_situacao_cadastral', 'descricao_motivo_situacao_cadastral']);
        });
    }
};
