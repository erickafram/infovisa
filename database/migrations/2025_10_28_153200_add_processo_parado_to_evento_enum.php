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
        // MySQL: Modificar a coluna ENUM para adicionar os novos valores
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
                'processo_desarquivado',
                'processo_parado',
                'processo_reiniciado',
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
        // Não é necessário reverter
    }
};
