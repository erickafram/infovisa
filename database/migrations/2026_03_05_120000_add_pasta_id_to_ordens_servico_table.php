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
        if (!Schema::hasColumn('ordens_servico', 'pasta_id')) {
            Schema::table('ordens_servico', function (Blueprint $table) {
                $table->foreignId('pasta_id')
                    ->nullable()
                    ->after('processo_id')
                    ->constrained('processo_pastas')
                    ->nullOnDelete();

                $table->index('pasta_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ordens_servico', 'pasta_id')) {
            Schema::table('ordens_servico', function (Blueprint $table) {
                $table->dropForeign(['pasta_id']);
                $table->dropIndex(['pasta_id']);
                $table->dropColumn('pasta_id');
            });
        }
    }
};
