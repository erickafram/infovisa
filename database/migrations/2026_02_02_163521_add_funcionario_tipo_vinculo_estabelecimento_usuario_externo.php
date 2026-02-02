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
     * Esta migration não precisa fazer nada pois o campo tipo_vinculo é varchar
     * e aceita qualquer string. O valor 'funcionario' será aceito automaticamente.
     */
    public function up(): void
    {
        // Campo tipo_vinculo é varchar, não precisa de alteração para aceitar 'funcionario'
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nada a reverter
    }
};
