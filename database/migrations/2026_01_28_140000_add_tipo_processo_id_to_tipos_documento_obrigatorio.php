<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->foreignId('tipo_processo_id')
                ->nullable()
                ->after('documento_comum')
                ->constrained('tipo_processos')
                ->onDelete('set null');
        });

        // Documentos comuns existentes ficam com tipo_processo_id = null (aplicável a todos)
        // O administrador pode configurar manualmente para qual tipo de processo cada documento comum se aplica
    }

    public function down(): void
    {
        Schema::table('tipos_documento_obrigatorio', function (Blueprint $table) {
            $table->dropForeign(['tipo_processo_id']);
            $table->dropColumn('tipo_processo_id');
        });
    }
};
