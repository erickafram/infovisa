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
        Schema::create('processo_pastas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->onDelete('cascade');
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('cor')->default('#3B82F6'); // Cor da pasta (hex)
            $table->integer('ordem')->default(0); // Ordem de exibição
            $table->timestamps();
            
            $table->index('processo_id');
        });
        
        // Adicionar coluna pasta_id nas tabelas de documentos e arquivos
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->foreignId('pasta_id')->nullable()->after('processo_id')->constrained('processo_pastas')->onDelete('set null');
            $table->index('pasta_id');
        });
        
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->foreignId('pasta_id')->nullable()->after('processo_id')->constrained('processo_pastas')->onDelete('set null');
            $table->index('pasta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos_digitais', function (Blueprint $table) {
            $table->dropForeign(['pasta_id']);
            $table->dropColumn('pasta_id');
        });
        
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->dropForeign(['pasta_id']);
            $table->dropColumn('pasta_id');
        });
        
        Schema::dropIfExists('processo_pastas');
    }
};
