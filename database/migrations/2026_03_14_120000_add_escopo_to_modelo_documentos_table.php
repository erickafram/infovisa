<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modelo_documentos', function (Blueprint $table) {
            $table->string('escopo')->default('estadual')->after('variaveis');
            $table->foreignId('municipio_id')->nullable()->after('escopo')->constrained('municipios')->nullOnDelete();

            $table->index('escopo');
            $table->index('municipio_id');
        });
    }

    public function down(): void
    {
        Schema::table('modelo_documentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('municipio_id');
            $table->dropIndex(['escopo']);
            $table->dropColumn('escopo');
        });
    }
};