<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_documento_respostas', function (Blueprint $table) {
            $table->string('tipo_setor', 20)->default('todos')->after('ativo'); // todos, publico, privado
        });
    }

    public function down(): void
    {
        Schema::table('tipo_documento_respostas', function (Blueprint $table) {
            $table->dropColumn('tipo_setor');
        });
    }
};
