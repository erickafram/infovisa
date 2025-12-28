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
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->foreignId('tipo_documento_obrigatorio_id')
                ->nullable()
                ->after('tipo_documento')
                ->constrained('tipos_documento_obrigatorio')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->dropForeign(['tipo_documento_obrigatorio_id']);
            $table->dropColumn('tipo_documento_obrigatorio_id');
        });
    }
};
