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
        // PostgreSQL: Recriar a constraint check com o novo valor
        DB::statement("
            ALTER TABLE processo_eventos 
            DROP CONSTRAINT IF EXISTS processo_eventos_tipo_evento_check
        ");
        
        DB::statement("
            ALTER TABLE processo_eventos 
            ADD CONSTRAINT processo_eventos_tipo_evento_check 
            CHECK (tipo_evento IN (
                'processo_criado',
                'documento_anexado',
                'documento_digital_criado',
                'documento_excluido',
                'documento_digital_excluido',
                'status_alterado',
                'processo_arquivado',
                'movimentacao',
                'observacao_adicionada'
            ))
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
