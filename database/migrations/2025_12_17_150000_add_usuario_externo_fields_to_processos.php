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
            // Torna usuario_id nullable (processos podem ser criados por usuários externos)
            $table->foreignId('usuario_id')->nullable()->change();
            
            // Adiciona campo para usuário externo que criou o processo
            $table->foreignId('usuario_externo_id')->nullable()->after('usuario_id')
                ->constrained('usuarios_externos')->onDelete('set null');
            
            // Flag para indicar se foi aberto por usuário externo
            $table->boolean('aberto_por_externo')->default(false)->after('usuario_externo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropForeign(['usuario_externo_id']);
            $table->dropColumn(['usuario_externo_id', 'aberto_por_externo']);
        });
    }
};
