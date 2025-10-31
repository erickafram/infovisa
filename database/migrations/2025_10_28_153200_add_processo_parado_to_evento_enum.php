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
        // PostgreSQL: Recriar a constraint check com os novos valores
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
                'processo_desarquivado',
                'processo_parado',
                'processo_reiniciado',
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
        // Não é necessário reverter
    }
};
