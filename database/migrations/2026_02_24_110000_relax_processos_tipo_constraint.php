<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE processos DROP CONSTRAINT IF EXISTS processos_tipo_check');
            DB::statement('ALTER TABLE processos ALTER COLUMN tipo TYPE VARCHAR(100)');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE processos MODIFY COLUMN tipo VARCHAR(100) NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE processos MODIFY COLUMN tipo ENUM('licenciamento', 'analise_rotulagem', 'projeto_arquitetonico', 'administrativo', 'descentralizacao', 'receituario') NOT NULL DEFAULT 'licenciamento'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE processos DROP CONSTRAINT IF EXISTS processos_tipo_check');
            DB::statement("\n                ALTER TABLE processos\n                ADD CONSTRAINT processos_tipo_check\n                CHECK (tipo IN (\n                    'licenciamento',\n                    'analise_rotulagem',\n                    'projeto_arquitetonico',\n                    'administrativo',\n                    'descentralizacao',\n                    'receituario'\n                ))\n            ");
        }
    }
};
