<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordem_servico_estabelecimentos', function (Blueprint $table) {
            $table->foreignId('processo_id')->nullable()->after('estabelecimento_id')
                  ->constrained('processos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('ordem_servico_estabelecimentos', function (Blueprint $table) {
            $table->dropForeign(['processo_id']);
            $table->dropColumn('processo_id');
        });
    }
};
