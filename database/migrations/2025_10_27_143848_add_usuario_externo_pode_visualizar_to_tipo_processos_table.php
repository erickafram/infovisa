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
        Schema::table('tipo_processos', function (Blueprint $table) {
            // Campo para controlar se usuário externo pode VISUALIZAR o processo
            // Diferente de 'usuario_externo_pode_abrir' que controla se pode CRIAR
            $table->boolean('usuario_externo_pode_visualizar')->default(true)->after('usuario_externo_pode_abrir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->dropColumn('usuario_externo_pode_visualizar');
        });
    }
};
