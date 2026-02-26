<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesquisas_satisfacao_respostas', function (Blueprint $table) {
            $table->foreignId('ordem_servico_id')->nullable()->after('pesquisa_id')
                  ->constrained('ordens_servico')->nullOnDelete();
            $table->foreignId('estabelecimento_id')->nullable()->after('ordem_servico_id')
                  ->constrained('estabelecimentos')->nullOnDelete();
            $table->foreignId('usuario_interno_id')->nullable()->after('estabelecimento_id')
                  ->constrained('usuarios_internos')->nullOnDelete();
            $table->foreignId('usuario_externo_id')->nullable()->after('usuario_interno_id')
                  ->constrained('usuarios_externos')->nullOnDelete();
            $table->string('tipo_respondente', 20)->nullable()->after('usuario_externo_id')
                  ->comment('interno ou externo');

            $table->index('ordem_servico_id');
            $table->index('estabelecimento_id');
        });
    }

    public function down(): void
    {
        Schema::table('pesquisas_satisfacao_respostas', function (Blueprint $table) {
            $table->dropForeign(['ordem_servico_id']);
            $table->dropForeign(['estabelecimento_id']);
            $table->dropForeign(['usuario_interno_id']);
            $table->dropForeign(['usuario_externo_id']);
            $table->dropColumn([
                'ordem_servico_id',
                'estabelecimento_id',
                'usuario_interno_id',
                'usuario_externo_id',
                'tipo_respondente',
            ]);
        });
    }
};
