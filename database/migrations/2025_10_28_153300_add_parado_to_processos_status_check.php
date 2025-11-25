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
        // MySQL: Modificar a coluna ENUM para adicionar o novo status
        DB::statement("
            ALTER TABLE processos 
            MODIFY COLUMN status ENUM(
                'aberto',
                'em_analise',
                'pendente',
                'aprovado',
                'indeferido',
                'parado',
                'arquivado'
            ) NOT NULL DEFAULT 'aberto'
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
