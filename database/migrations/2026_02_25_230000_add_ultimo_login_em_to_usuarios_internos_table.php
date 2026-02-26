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
            $table->timestamp('ultimo_login_em')->nullable()->after('email_verified_at');
            $table->index('ultimo_login_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios_internos', function (Blueprint $table) {
            $table->dropIndex(['ultimo_login_em']);
            $table->dropColumn('ultimo_login_em');
        });
    }
};
