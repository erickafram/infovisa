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
        Schema::table('municipios', function (Blueprint $table) {
            $table->boolean('usa_infovisa')->default(false)->after('ativo')
                ->comment('Indica se o município já utiliza o InfoVISA. Se false, estabelecimentos de competência municipal não podem se cadastrar.');
            $table->date('data_adesao_infovisa')->nullable()->after('usa_infovisa')
                ->comment('Data em que o município aderiu ao InfoVISA');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            $table->dropColumn(['usa_infovisa', 'data_adesao_infovisa']);
        });
    }
};
