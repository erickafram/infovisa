<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tipos de documento que o estabelecimento deve enviar como resposta
        Schema::create('tipo_documento_respostas', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Ex: "ROI dos equipamentos", "Prancha do estabelecimento"
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // Pivot: quais tipos de resposta são exigidos por cada tipo de documento
        Schema::create('tipo_documento_tipo_resposta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_documento_id')->constrained('tipo_documentos')->cascadeOnDelete();
            $table->foreignId('tipo_documento_resposta_id')->constrained('tipo_documento_respostas')->cascadeOnDelete();
            $table->boolean('obrigatorio')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();

            $table->unique(['tipo_documento_id', 'tipo_documento_resposta_id'], 'tipo_doc_tipo_resp_unique');
        });

        // Adiciona referência ao tipo de resposta na tabela de respostas
        Schema::table('documento_respostas', function (Blueprint $table) {
            $table->foreignId('tipo_documento_resposta_id')->nullable()->after('documento_digital_id')
                  ->constrained('tipo_documento_respostas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documento_respostas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_documento_resposta_id');
        });
        Schema::dropIfExists('tipo_documento_tipo_resposta');
        Schema::dropIfExists('tipo_documento_respostas');
    }
};
