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
        // Tabela para armazenar os equipamentos de radiação ionizante cadastrados pelos estabelecimentos
        Schema::create('equipamentos_radiacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estabelecimento_id')->constrained('estabelecimentos')->cascadeOnDelete();
            
            // Dados do equipamento
            $table->string('tipo_equipamento', 200); // Ex: Raio-X, Tomógrafo, etc
            $table->string('fabricante', 200);
            $table->string('modelo', 200);
            $table->string('numero_serie', 100)->nullable();
            $table->string('ano_fabricacao', 4)->nullable();
            
            // Dados de registro
            $table->string('registro_anvisa', 100)->nullable(); // Registro na ANVISA
            $table->string('numero_patrimonio', 100)->nullable(); // Número de patrimônio interno
            
            // Localização
            $table->string('setor_localizacao', 200)->nullable(); // Setor onde está instalado
            $table->string('sala', 100)->nullable(); // Sala específica
            
            // Controle de radiação
            $table->date('data_ultima_calibracao')->nullable();
            $table->date('data_proxima_calibracao')->nullable();
            $table->string('responsavel_tecnico', 200)->nullable();
            $table->string('registro_cnen', 100)->nullable(); // Registro CNEN se aplicável
            
            // Status
            $table->enum('status', ['ativo', 'inativo', 'em_manutencao', 'descartado'])->default('ativo');
            $table->text('observacoes')->nullable();
            
            // Controle
            $table->foreignId('usuario_externo_id')->nullable()->constrained('usuarios_externos')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['estabelecimento_id', 'status']);
            $table->index('tipo_equipamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipamentos_radiacao');
    }
};
