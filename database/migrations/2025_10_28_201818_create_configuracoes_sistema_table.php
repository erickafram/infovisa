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
        Schema::create('configuracoes_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('chave', 100)->unique()->comment('Chave única da configuração');
            $table->text('valor')->nullable()->comment('Valor da configuração');
            $table->string('tipo', 50)->default('texto')->comment('Tipo: texto, imagem, json, boolean');
            $table->text('descricao')->nullable()->comment('Descrição da configuração');
            $table->timestamps();
            
            $table->index('chave');
        });
        
        // Insere configuração padrão para logomarca estadual
        DB::table('configuracoes_sistema')->insert([
            'chave' => 'logomarca_estadual',
            'valor' => null,
            'tipo' => 'imagem',
            'descricao' => 'Logomarca do Estado do Tocantins (usada em documentos de usuários estaduais)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracoes_sistema');
    }
};
