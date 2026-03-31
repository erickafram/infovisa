<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_servico', function (Blueprint $table) {
            $table->string('escopo', 20)->default('estadual')->after('descricao');
            $table->unsignedBigInteger('municipio_id')->nullable()->after('escopo');
            $table->foreign('municipio_id')->references('id')->on('municipios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tipos_servico', function (Blueprint $table) {
            $table->dropForeign(['municipio_id']);
            $table->dropColumn(['escopo', 'municipio_id']);
        });
    }
};
