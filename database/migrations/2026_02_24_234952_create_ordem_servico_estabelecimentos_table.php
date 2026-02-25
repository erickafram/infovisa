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
        Schema::create('ordem_servico_estabelecimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordens_servico')->onDelete('cascade');
            $table->foreignId('estabelecimento_id')->constrained('estabelecimentos')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['ordem_servico_id', 'estabelecimento_id'], 'os_estab_unique');
            $table->index('estabelecimento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_servico_estabelecimentos');
    }
};
