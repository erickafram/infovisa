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
            // Campos para guardar setor/responsável antes de arquivar
            $table->string('setor_antes_arquivar')->nullable()->after('setor_atual');
            $table->unsignedBigInteger('responsavel_antes_arquivar_id')->nullable()->after('responsavel_atual_id');
            
            $table->foreign('responsavel_antes_arquivar_id')
                  ->references('id')
                  ->on('usuarios_internos')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropForeign(['responsavel_antes_arquivar_id']);
            $table->dropColumn(['setor_antes_arquivar', 'responsavel_antes_arquivar_id']);
        });
    }
};
