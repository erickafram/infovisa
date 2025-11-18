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
        Schema::table('estabelecimentos', function (Blueprint $table) {
            // Remove a restrição de unicidade simples do CNPJ
            // Verifica se o índice existe antes de dropar (para evitar erros em re-runs manuais)
            try {
                $table->dropUnique('estabelecimentos_cnpj_unique');
            } catch (\Exception $e) {
                // Ignora se não existir
            }
        });
        
        // Adiciona constraint parcial para PostgreSQL
        // CNPJ único APENAS para estabelecimentos PRIVADOS
        try {
            DB::statement('CREATE UNIQUE INDEX estabelecimentos_cnpj_privado_unique ON estabelecimentos (cnpj) WHERE tipo_setor = \'privado\'');
        } catch (\Exception $e) {
            // Ignora se já existir
        }
        
        // Adiciona constraint composta para setor PUBLICO (CNPJ + Nome Fantasia únicos)
        try {
            DB::statement('CREATE UNIQUE INDEX estabelecimentos_publico_composto_unique ON estabelecimentos (cnpj, nome_fantasia) WHERE tipo_setor = \'publico\'');
        } catch (\Exception $e) {
            // Ignora se já existir
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            // Remove os índices criados
            DB::statement('DROP INDEX IF EXISTS estabelecimentos_cnpj_privado_unique');
            DB::statement('DROP INDEX IF EXISTS estabelecimentos_publico_composto_unique');
        } catch (\Exception $e) {}

        Schema::table('estabelecimentos', function (Blueprint $table) {
            // Restaura a unicidade global (pode falhar se já existirem duplicados)
            try {
                $table->unique('cnpj');
            } catch (\Exception $e) {}
        });
    }
};
