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
            $table->text('motivo_arquivamento')->nullable()->after('status');
            $table->timestamp('data_arquivamento')->nullable()->after('motivo_arquivamento');
            $table->foreignId('usuario_arquivamento_id')->nullable()->after('data_arquivamento')
                ->constrained('usuarios_internos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropForeign(['usuario_arquivamento_id']);
            $table->dropColumn(['motivo_arquivamento', 'data_arquivamento', 'usuario_arquivamento_id']);
        });
    }
};
