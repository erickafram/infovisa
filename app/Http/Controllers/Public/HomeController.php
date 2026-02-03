<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoProcesso;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\ListaDocumento;
use App\Models\Atividade;

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

        // Para cada tipo, busca os processos que têm todos os documentos obrigatórios aprovados
        $filaProcessos = [];
        foreach ($tiposComFilaPublica as $tipo) {
            $processos = Processo::where('tipo', $tipo->codigo)
                ->whereIn('status', ['aberto', 'em_analise', 'pendente', 'parado'])
                ->with(['estabelecimento:id,nome_fantasia,razao_social,cnpj,cpf,nome_completo,atividades_exercidas'])
                ->orderBy('created_at', 'asc') // Mais antigo primeiro
                ->get();

            $processosAptos = [];
            foreach ($processos as $processo) {
                // Verifica se todos os documentos obrigatórios estão aprovados
                $statusDocs = $this->verificarDocumentosObrigatorios($processo, $tipo);
                
                if ($statusDocs['completo']) {
                    $dataReferencia = $statusDocs['data_ultimo_aprovado'] ?? $processo->created_at;
                    $dataRef = \Carbon\Carbon::parse($dataReferencia);
                    $hoje = \Carbon\Carbon::now();
                    
                    // Calcula dias decorridos desde a aprovação do último documento
                    $totalHoras = $dataRef->diffInHours($hoje);
                    $dias = floor($totalHoras / 24);
                    $horas = $totalHoras % 24;
                    
                    // Calcula prazo restante
                    $prazo = $tipo->prazo_fila_publica ?? null;
                    $diasRestantes = $prazo ? ($prazo - $dias) : null;
                    $atrasado = $prazo && $dias > $prazo;
                    
                    $processosAptos[] = [
                        'numero_processo' => $processo->numero_processo,
                        'estabelecimento' => $processo->estabelecimento ? 
                            ($processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->nome_completo ?? 'Não informado') : 
                            'Não vinculado',
                        'status' => $processo->status,
                        'data_abertura' => \Carbon\Carbon::parse($processo->created_at)->format('d/m/Y H:i'),
                        'data_documentos_completos' => $dataRef->format('d/m/Y H:i'),
                        'dias_decorridos' => $dias,
                        'horas_decorridas' => $horas,
                        'tempo_formatado' => $dias > 0 ? "{$dias}d {$horas}h" : "{$horas}h",
                        'prazo' => $prazo,
                        'dias_restantes' => $diasRestantes,
                        'atrasado' => $atrasado,
                    ];
                }
            }

            // Ordena por data de documentos completos (mais antigo primeiro) e adiciona posição
            usort($processosAptos, function($a, $b) {
                return strcmp($a['data_documentos_completos'], $b['data_documentos_completos']);
            });
            
            foreach ($processosAptos as $index => &$proc) {
                $proc['posicao'] = $index + 1;
            }

            if (count($processosAptos) > 0) {
                $filaProcessos[] = [
                    'tipo' => $tipo->nome,
                    'codigo' => $tipo->codigo,
                    'prazo_analise' => $tipo->prazo_fila_publica,
                    'processos' => $processosAptos
                ];
            }
        }

        return view('public.fila-processos', compact('filaProcessos'));
    }

    /**
     * Verifica se todos os documentos obrigatórios de um processo estão aprovados
     * Retorna array com status e data do último documento aprovado
     */
    private function verificarDocumentosObrigatorios($processo, $tipoProcesso)
    {
        $estabelecimento = $processo->estabelecimento;
        $tipoProcessoId = $tipoProcesso->id ?? null;
        
        if (!$tipoProcessoId || !$estabelecimento) {
            return ['completo' => false, 'data_ultimo_aprovado' => null];
        }

        // Verifica se é um processo especial (Projeto Arquitetônico ou Análise de Rotulagem)
        $isProcessoEspecial = in_array($tipoProcesso->codigo, ['projeto_arquitetonico', 'analise_rotulagem']);

        // Pega as atividades exercidas do estabelecimento
        $atividadesExercidas = $estabelecimento->atividades_exercidas ?? [];
        
        // Para processos especiais, não precisa de atividades
        if (!$isProcessoEspecial && empty($atividadesExercidas)) {
            return ['completo' => false, 'data_ultimo_aprovado' => null];
        }

        $atividadeIds = collect();
        
        // Só busca atividades se não for processo especial e tiver atividades exercidas
        if (!$isProcessoEspecial && !empty($atividadesExercidas)) {
            $codigosCnae = collect($atividadesExercidas)->map(function($atividade) {
                $codigo = is_array($atividade) ? ($atividade['codigo'] ?? null) : $atividade;
                return $codigo ? preg_replace('/[^0-9]/', '', $codigo) : null;
            })->filter()->values()->toArray();

            if (!empty($codigosCnae)) {
                $atividadeIds = Atividade::where('ativo', true)
                    ->where(function($query) use ($codigosCnae) {
                        foreach ($codigosCnae as $codigo) {
                            $query->orWhere('codigo_cnae', $codigo);
                        }
                    })
                    ->pluck('id');
            }
        }

        // Busca listas de documentos aplicáveis
        $listasQuery = ListaDocumento::where('ativo', true)
            ->where('tipo_processo_id', $tipoProcessoId)
            ->with(['tiposDocumentoObrigatorio' => function($q) {
                $q->orderBy('lista_documento_tipo.ordem');
            }]);
            
        // Para processos especiais: busca listas SEM atividades vinculadas
        // Para processos normais: busca listas COM atividades que correspondem às do estabelecimento
        if ($isProcessoEspecial) {
            $listasQuery->whereDoesntHave('atividades');
        } else {
            if ($atividadeIds->isEmpty()) {
                return ['completo' => false, 'data_ultimo_aprovado' => null];
            }
            $listasQuery->whereHas('atividades', function($q) use ($atividadeIds) {
                $q->whereIn('atividades.id', $atividadeIds);
            });
        }

        // Filtra por escopo (estadual ou do município do estabelecimento)
        $listasQuery->where(function($q) use ($estabelecimento) {
            $q->where('escopo', 'estadual');
            if ($estabelecimento->municipio_id) {
                $q->orWhere(function($q2) use ($estabelecimento) {
                    $q2->where('escopo', 'municipal')
                       ->where('municipio_id', $estabelecimento->municipio_id);
                });
            }
        });

        $listas = $listasQuery->get();

        // Coleta todos os tipos de documento obrigatório das listas
        $docsObrigatorios = collect();
        foreach ($listas as $lista) {
            foreach ($lista->tiposDocumentoObrigatorio as $tipoDoc) {
                // Só adiciona se for obrigatório (pivot)
                if ($tipoDoc->pivot->obrigatorio && !$docsObrigatorios->contains('id', $tipoDoc->id)) {
                    $docsObrigatorios->push($tipoDoc);
                }
            }
        }

        if ($docsObrigatorios->isEmpty()) {
            // Se não tem documentos obrigatórios, considera como completo pela data de abertura
            return ['completo' => true, 'data_ultimo_aprovado' => $processo->created_at];
        }

        // Verifica o status de cada documento obrigatório no processo
        $todosAprovados = true;
        $dataUltimoAprovado = null;
        
        foreach ($docsObrigatorios as $docObrigatorio) {
            $documento = ProcessoDocumento::where('processo_id', $processo->id)
                ->where('tipo_documento_obrigatorio_id', $docObrigatorio->id)
                ->where('status_aprovacao', 'aprovado')
                ->orderBy('aprovado_em', 'desc')
                ->first();
            
            if (!$documento) {
                $todosAprovados = false;
                break;
            }
            
            // Guarda a data mais recente de aprovação
            if ($documento->aprovado_em && (!$dataUltimoAprovado || $documento->aprovado_em > $dataUltimoAprovado)) {
                $dataUltimoAprovado = $documento->aprovado_em;
            }
        }

        return [
            'completo' => $todosAprovados,
            'data_ultimo_aprovado' => $dataUltimoAprovado
        ];
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

