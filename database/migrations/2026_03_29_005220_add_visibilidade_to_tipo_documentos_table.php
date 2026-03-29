<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            // 'todos' = visível para todos, 'estadual' = só estadual, 'municipal' = só municipal
            $table->string('visibilidade', 20)->default('todos')->after('ativo');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->dropColumn('visibilidade');
        });
    }
};
