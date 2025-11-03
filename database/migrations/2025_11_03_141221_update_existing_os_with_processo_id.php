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
        // Atualiza OSs existentes que têm estabelecimento mas não têm processo vinculado
        // Vincula ao processo ativo mais recente do estabelecimento
        
        $ordensServico = DB::table('ordens_servico')
            ->whereNotNull('estabelecimento_id')
            ->whereNull('processo_id')
            ->get();
        
        foreach ($ordensServico as $os) {
            // Busca processo ativo do estabelecimento
            $processo = DB::table('processos')
                ->where('estabelecimento_id', $os->estabelecimento_id)
                ->whereIn('status', ['aberto', 'em_andamento'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($processo) {
                DB::table('ordens_servico')
                    ->where('id', $os->id)
                    ->update(['processo_id' => $processo->id]);
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
