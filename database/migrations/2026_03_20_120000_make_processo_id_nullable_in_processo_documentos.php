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
            $table->dropForeign(['processo_id']);
        });

        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->foreignId('processo_id')->nullable()->change();
        });

        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->foreign('processo_id')->references('id')->on('processos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->dropForeign(['processo_id']);
        });

        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->unsignedBigInteger('processo_id')->nullable(false)->change();
        });

        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->foreign('processo_id')->references('id')->on('processos')->onDelete('cascade');
        });
    }
};