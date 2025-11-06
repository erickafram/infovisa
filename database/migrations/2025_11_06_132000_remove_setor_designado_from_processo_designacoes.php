<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primeiro, remove designações que não têm usuário (apenas setor)
        DB::table('processo_designacoes')
            ->whereNull('usuario_designado_id')
            ->delete();
        
        Schema::table('processo_designacoes', function (Blueprint $table) {
            // Remove índice e coluna setor_designado
            $table->dropIndex(['setor_designado']);
            $table->dropColumn('setor_designado');
            
            // Torna usuario_designado_id obrigatório novamente
            $table->foreignId('usuario_designado_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('processo_designacoes', function (Blueprint $table) {
            // Reverte: torna usuario_designado_id nullable
            $table->foreignId('usuario_designado_id')->nullable()->change();
            
            // Adiciona novamente setor_designado
            $table->string('setor_designado')->nullable()->after('usuario_designado_id');
            $table->index('setor_designado');
        });
    }
};
