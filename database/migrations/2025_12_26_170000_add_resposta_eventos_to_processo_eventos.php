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
        // PostgreSQL: Atualizar a constraint de check para incluir os novos tipos
        DB::statement("
            ALTER TABLE processo_eventos 
            DROP CONSTRAINT IF EXISTS processo_eventos_tipo_evento_check
        ");
        
        DB::statement("
            ALTER TABLE processo_eventos 
            ADD CONSTRAINT processo_eventos_tipo_evento_check 
            CHECK (tipo_evento::text = ANY (ARRAY[
                'processo_criado'::text,
                'documento_anexado'::text,
                'documento_digital_criado'::text,
                'documento_excluido'::text,
                'documento_digital_excluido'::text,
                'status_alterado'::text,
                'processo_arquivado'::text,
                'processo_desarquivado'::text,
                'processo_parado'::text,
                'processo_reiniciado'::text,
                'movimentacao'::text,
                'observacao_adicionada'::text,
                'resposta_aprovada'::text,
                'resposta_rejeitada'::text
            ]))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para a constraint anterior (sem os novos tipos)
        DB::statement("
            ALTER TABLE processo_eventos 
            DROP CONSTRAINT IF EXISTS processo_eventos_tipo_evento_check
        ");
        
        DB::statement("
            ALTER TABLE processo_eventos 
            ADD CONSTRAINT processo_eventos_tipo_evento_check 
            CHECK (tipo_evento::text = ANY (ARRAY[
                'processo_criado'::text,
                'documento_anexado'::text,
                'documento_digital_criado'::text,
                'documento_excluido'::text,
                'documento_digital_excluido'::text,
                'status_alterado'::text,
                'processo_arquivado'::text,
                'processo_desarquivado'::text,
                'processo_parado'::text,
                'processo_reiniciado'::text,
                'movimentacao'::text,
                'observacao_adicionada'::text
            ]))
        ");
    }
};
