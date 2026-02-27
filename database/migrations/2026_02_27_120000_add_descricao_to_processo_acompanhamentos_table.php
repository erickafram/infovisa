<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_acompanhamentos', function (Blueprint $table) {
            $table->string('descricao', 255)->nullable()->after('usuario_interno_id');
        });
    }

    public function down(): void
    {
        Schema::table('processo_acompanhamentos', function (Blueprint $table) {
            $table->dropColumn('descricao');
        });
    }
};
