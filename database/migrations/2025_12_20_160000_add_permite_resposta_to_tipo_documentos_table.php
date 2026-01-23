<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona campo para permitir que a empresa responda documentos deste tipo
     */
    public function up(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->boolean('permite_resposta')->default(false)->after('prazo_notificacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->dropColumn('permite_resposta');
        });
    }
};




