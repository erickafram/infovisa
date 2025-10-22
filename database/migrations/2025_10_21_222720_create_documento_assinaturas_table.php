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
        Schema::create('documento_assinaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_digital_id')->constrained('documentos_digitais')->onDelete('cascade');
            $table->foreignId('usuario_interno_id')->constrained('usuarios_internos')->onDelete('cascade');
            $table->integer('ordem')->default(1);
            $table->boolean('obrigatoria')->default(true);
            $table->enum('status', ['pendente', 'assinado', 'recusado'])->default('pendente');
            $table->timestamp('assinado_em')->nullable();
            $table->text('observacao')->nullable();
            $table->string('hash_assinatura')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_assinaturas');
    }
};
