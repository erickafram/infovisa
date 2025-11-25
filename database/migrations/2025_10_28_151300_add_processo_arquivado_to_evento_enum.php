<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL: Modificar a coluna ENUM para adicionar o novo valor
        DB::statement("
            ALTER TABLE processo_eventos 
            MODIFY COLUMN tipo_evento ENUM(
                'processo_criado',
                'documento_anexado',
                'documento_digital_criado',
                'documento_excluido',
                'documento_digital_excluido',
                'status_alterado',
                'processo_arquivado',
                'movimentacao',
                'observacao_adicionada'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não é possível remover valores de um enum no PostgreSQL facilmente
        // Seria necessário recriar o tipo, o que é complexo
        // Por isso, deixamos vazio
    }
};
