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
            // Setor atual que está com o processo
            $table->string('setor_atual')->nullable()->after('status');
            
            // Usuário responsável atual pelo processo
            $table->foreignId('responsavel_atual_id')->nullable()->after('setor_atual')
                  ->constrained('usuarios_internos')->nullOnDelete();
            
            // Data/hora que foi atribuído ao responsável atual
            $table->timestamp('responsavel_desde')->nullable()->after('responsavel_atual_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropForeign(['responsavel_atual_id']);
            $table->dropColumn(['setor_atual', 'responsavel_atual_id', 'responsavel_desde']);
        });
    }
};
