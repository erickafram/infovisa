<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabela para cadastrar os tipos de documentos obrigatórios
     * que os estabelecimentos precisam apresentar (CNPJ, Contrato Social, etc.)
     */
    public function up(): void
    {
        Schema::create('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Ex: CNPJ, Contrato Social, Alvará de Funcionamento
            $table->text('descricao')->nullable(); // Descrição detalhada do documento
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0); // Para ordenação na listagem
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_documento_obrigatorio');
    }
};
