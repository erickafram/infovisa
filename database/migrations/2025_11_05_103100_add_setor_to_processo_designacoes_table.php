<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_designacoes', function (Blueprint $table) {
            // Tornar usuario_designado_id nullable para permitir designação apenas por setor
            $table->foreignId('usuario_designado_id')->nullable()->change();
            
            // Adicionar campo para setor designado
            $table->string('setor_designado')->nullable()->after('usuario_designado_id');
            $table->index('setor_designado');
        });
    }

    public function down(): void
    {
        Schema::table('processo_designacoes', function (Blueprint $table) {
            $table->dropIndex(['setor_designado']);
            $table->dropColumn('setor_designado');
            
            // Reverter usuario_designado_id para not null
            $table->foreignId('usuario_designado_id')->nullable(false)->change();
        });
    }
};
