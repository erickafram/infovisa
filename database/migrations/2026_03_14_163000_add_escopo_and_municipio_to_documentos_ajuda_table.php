<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_ajuda', function (Blueprint $table) {
            if (!Schema::hasColumn('documentos_ajuda', 'escopo_competencia')) {
                $table->string('escopo_competencia', 20)
                    ->default('todos')
                    ->after('ordem');

                $table->index('escopo_competencia');
            }

            if (!Schema::hasColumn('documentos_ajuda', 'municipio_id')) {
                $table->foreignId('municipio_id')
                    ->nullable()
                    ->after('escopo_competencia')
                    ->constrained('municipios')
                    ->nullOnDelete();

                $table->index('municipio_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documentos_ajuda', function (Blueprint $table) {
            if (Schema::hasColumn('documentos_ajuda', 'municipio_id')) {
                $table->dropConstrainedForeignId('municipio_id');
            }

            if (Schema::hasColumn('documentos_ajuda', 'escopo_competencia')) {
                $table->dropIndex(['escopo_competencia']);
                $table->dropColumn('escopo_competencia');
            }
        });
    }
};