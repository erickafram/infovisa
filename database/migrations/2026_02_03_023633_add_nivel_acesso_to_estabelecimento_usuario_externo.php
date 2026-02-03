<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona o campo nivel_acesso na tabela pivot de vínculo entre estabelecimento e usuário externo.
     * 
     * Níveis de acesso:
     * - 'gestor': Acesso total (criar processo, editar cadastro, anexar documento, etc.)
     * - 'visualizador': Apenas visualização (não pode editar, anexar, abrir processo)
     */
    public function up(): void
    {
        Schema::table('estabelecimento_usuario_externo', function (Blueprint $table) {
            $table->string('nivel_acesso', 20)->default('gestor')->after('observacao');
            $table->index('nivel_acesso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimento_usuario_externo', function (Blueprint $table) {
            $table->dropIndex(['nivel_acesso']);
            $table->dropColumn('nivel_acesso');
        });
    }
};
