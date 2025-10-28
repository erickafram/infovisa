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
        Schema::table('usuarios_internos', function (Blueprint $table) {
            // Senha específica para assinatura digital (diferente da senha de login)
            $table->string('senha_assinatura_digital')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios_internos', function (Blueprint $table) {
            $table->dropColumn('senha_assinatura_digital');
        });
    }
};
