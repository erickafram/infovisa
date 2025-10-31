<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Estabelecimento;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Atualiza a competência das OSs baseado na competência do estabelecimento
        $ordensServico = DB::table('ordens_servico')->get();
        
        foreach ($ordensServico as $os) {
            $estabelecimento = Estabelecimento::find($os->estabelecimento_id);
            
            if ($estabelecimento) {
                $competencia = $estabelecimento->isCompetenciaEstadual() ? 'estadual' : 'municipal';
                
                DB::table('ordens_servico')
                    ->where('id', $os->id)
                    ->update(['competencia' => $competencia]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não há como reverter sem saber os valores originais
    }
};
