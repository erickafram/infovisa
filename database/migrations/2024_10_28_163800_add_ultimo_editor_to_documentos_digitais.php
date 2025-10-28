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
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->foreignId('ultimo_editor_id')->nullable()->after('usuario_criador_id')
                ->constrained('usuarios_internos')->onDelete('set null');
            $table->timestamp('ultima_edicao_em')->nullable()->after('ultimo_editor_id');
            $table->integer('versao_atual')->default(1)->after('ultima_edicao_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->dropForeign(['ultimo_editor_id']);
            $table->dropColumn(['ultimo_editor_id', 'ultima_edicao_em', 'versao_atual']);
        });
    }
};
