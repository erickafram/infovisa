<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoProcesso;
use App\Models\Processo;

class HomeController extends Controller
{
    /**
     * Exibe a página inicial pública
     */
    public function index()
    {
        return view('public.home');
    }

    /**
     * Exibe a página de fila de processos
     */
    public function filaProcessos()
    {
        // Busca tipos de processo com fila pública ativada
        $tiposComFilaPublica = TipoProcesso::where('exibir_fila_publica', true)
            ->where('ativo', true)
            ->ordenado()
            ->get();

        // Para cada tipo, busca os processos (exceto fechados/deferidos/indeferidos/cancelados)
        $filaProcessos = [];
        foreach ($tiposComFilaPublica as $tipo) {
            $processos = Processo::where('tipo', $tipo->codigo)
                ->whereIn('status', ['aberto', 'em_analise', 'pendente', 'parado', 'arquivado'])
                ->with(['estabelecimento:id,nome_fantasia,razao_social,cnpj,cpf,nome_completo'])
                ->orderBy('created_at', 'asc') // Mais antigo primeiro
                ->get()
                ->map(function($processo, $index) {
                    $dataAbertura = \Carbon\Carbon::parse($processo->created_at);
                    $hoje = \Carbon\Carbon::now();
                    
                    // Calcula dias e horas
                    $totalHoras = $dataAbertura->diffInHours($hoje);
                    $dias = floor($totalHoras / 24);
                    $horas = $totalHoras % 24;
                    
                    return [
                        'posicao' => $index + 1,
                        'numero_processo' => $processo->numero_processo,
                        'estabelecimento' => $processo->estabelecimento ? 
                            ($processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->nome_completo ?? 'Não informado') : 
                            'Não vinculado',
                        'status' => $processo->status,
                        'data_abertura' => $dataAbertura->format('d/m/Y H:i'),
                        'dias_decorridos' => $dias,
                        'horas_decorridas' => $horas,
                        'tempo_formatado' => $dias > 0 ? "{$dias}d {$horas}h" : "{$horas}h",
                    ];
                });

            if ($processos->isNotEmpty()) {
                $filaProcessos[] = [
                    'tipo' => $tipo->nome,
                    'codigo' => $tipo->codigo,
                    'processos' => $processos
                ];
            }
        }

        return view('public.fila-processos', compact('filaProcessos'));
    }

    /**
     * Consulta processo por CNPJ
     */
    public function consultarProcesso(Request $request)
    {
        $request->validate([
            'cnpj' => 'required|string|min:14|max:18',
        ]);

        // TODO: Implementar lógica de consulta
        return redirect()->back()->with('success', 'Consulta realizada com sucesso!');
    }

    /**
     * Verifica autenticidade de documento
     */
    public function verificarDocumento(Request $request)
    {
        $request->validate([
            'codigo_verificador' => 'required|string|min:6',
        ]);

        // TODO: Implementar lógica de verificação
        return redirect()->back()->with('success', 'Documento verificado com sucesso!');
    }
}

