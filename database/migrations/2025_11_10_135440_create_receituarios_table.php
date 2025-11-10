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
        Schema::create('receituarios', function (Blueprint $table) {
            $table->id();
            
            // Tipo de receituário
            $table->enum('tipo', ['medico', 'instituicao', 'secretaria', 'talidomida'])->comment('Tipo de receituário');
            
            // Dados Pessoais (para médicos e talidomida)
            $table->string('nome')->nullable();
            $table->string('cpf', 14)->nullable();
            $table->string('especialidade')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('telefone2', 20)->nullable();
            $table->string('numero_conselho_classe')->nullable(); // Nº Cons. de Classe / CRM
            $table->string('numero_crm')->nullable(); // Para talidomida
            $table->string('endereco')->nullable();
            $table->string('endereco_residencial')->nullable(); // Para talidomida
            $table->string('cep', 10)->nullable();
            $table->string('municipio')->nullable();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onDelete('set null');
            $table->string('email')->nullable();
            
            // Dados da Instituição (para instituição e secretaria)
            $table->string('razao_social')->nullable();
            $table->string('cnpj', 18)->nullable();
            
            // Responsável Técnico (para instituição)
            $table->string('responsavel_nome')->nullable();
            $table->string('responsavel_cpf', 14)->nullable();
            $table->string('responsavel_crm')->nullable();
            $table->string('responsavel_especialidade')->nullable();
            $table->string('responsavel_telefone', 20)->nullable();
            $table->string('responsavel_telefone2', 20)->nullable();
            
            // Locais de Trabalho (para médicos e talidomida)
            $table->json('locais_trabalho')->nullable()->comment('Array de locais de trabalho');
            
            // Status e controle
            $table->enum('status', ['ativo', 'inativo', 'pendente'])->default('pendente');
            $table->text('observacoes')->nullable();
            
            // Relacionamento com processo (se houver)
            $table->foreignId('processo_id')->nullable()->constrained('processos')->onDelete('set null');
            
            // Auditoria
            $table->foreignId('usuario_criacao_id')->nullable()->constrained('usuarios_internos')->onDelete('set null');
            $table->foreignId('usuario_atualizacao_id')->nullable()->constrained('usuarios_internos')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('tipo');
            $table->index('cpf');
            $table->index('cnpj');
            $table->index('status');
            $table->index('municipio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receituarios');
    }
};
