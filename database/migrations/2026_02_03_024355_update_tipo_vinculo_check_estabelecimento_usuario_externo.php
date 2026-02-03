<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Atualiza a constraint de tipo_vinculo para incluir 'funcionario'
     */
    public function up(): void
    {
        // Remove a constraint antiga
        DB::statement('ALTER TABLE estabelecimento_usuario_externo DROP CONSTRAINT IF EXISTS estabelecimento_usuario_externo_tipo_vinculo_check');
        
        // Altera a coluna para varchar sem restrição de enum
        DB::statement("ALTER TABLE estabelecimento_usuario_externo ALTER COLUMN tipo_vinculo TYPE VARCHAR(50)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaura a constraint original (se necessário)
        DB::statement("ALTER TABLE estabelecimento_usuario_externo ADD CONSTRAINT estabelecimento_usuario_externo_tipo_vinculo_check CHECK (tipo_vinculo IN ('proprietario', 'responsavel_legal', 'responsavel_tecnico', 'contador', 'procurador', 'outro'))");
    }
};
