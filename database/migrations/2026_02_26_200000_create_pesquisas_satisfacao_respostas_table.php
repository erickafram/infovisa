<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesquisas_satisfacao_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesquisa_id')->constrained('pesquisas_satisfacao')->cascadeOnDelete();
            $table->string('respondente_nome')->nullable();
            $table->string('respondente_email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('token', 64)->unique()->nullable();
            // JSON: [{pergunta_id, tipo, valor, opcao_id}]
            $table->jsonb('respostas')->default('[]');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesquisas_satisfacao_respostas');
    }
};
