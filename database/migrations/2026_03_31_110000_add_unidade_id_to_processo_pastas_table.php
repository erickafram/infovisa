<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_pastas', function (Blueprint $table) {
            $table->foreignId('unidade_id')->nullable()->after('ordem')->constrained('unidades')->nullOnDelete();
            $table->boolean('protegida')->default(false)->after('unidade_id');
        });
    }

    public function down(): void
    {
        Schema::table('processo_pastas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidade_id');
            $table->dropColumn('protegida');
        });
    }
};
