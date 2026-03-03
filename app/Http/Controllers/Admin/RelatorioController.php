<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\OrdemServico;
use App\Models\DocumentoDigital;
use App\Models\TipoDocumento;
use App\Models\UsuarioInterno;
use App\Models\Atividade;
use App\Models\AtividadeEquipamentoRadiacao;
use App\Models\EquipamentoRadiacao;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PesquisaSatisfacao;
use App\Models\PesquisaSatisfacaoResposta;

class RelatorioController extends Controller
{
    /**
     * Exibe a página principal de relatórios
     */
    public function index()
    {
        return view('admin.relatorios.index');
    }

    /**
     * Relatório de Equipamentos de Imagem
     */
    public function equipamentosRadiacao()
    {
        $usuario = auth('interno')->user();

        // Códigos das atividades que exigem equipamentos de radiação (normalizados)
        $codigosAtividadesRadiacao = AtividadeEquipamentoRadiacao::where('ativo', true)
            ->pluck('codigo_atividade')
            ->map(fn($c) => preg_replace('/[^0-9]/', '', $c))
            ->unique()
            ->filter()
            ->toArray();

        // Buscar todos os estabelecimentos e filtrar por atividades em PHP
        $query = Estabelecimento::query()
            ->whereNotNull('atividades_exercidas')
            ->with('municipio') // Carregar relacionamento para o mapa
            ->withCount('equipamentosRadiacao as equipamentos_count');

        // Filtro por município se for usuário municipal
        if ($usuario->isMunicipal()) {
            $query->where('municipio_id', $usuario->municipio_id);
        }

        $todosEstabelecimentos = $query->orderBy('nome_fantasia')->get();

        // Filtrar estabelecimentos que têm atividades de radiação
        // E EXCLUIR os que declararam não ter equipamentos
        $estabelecimentos = $todosEstabelecimentos->filter(function($est) use ($codigosAtividadesRadiacao) {
            // Excluir estabelecimentos que declararam não ter equipamentos
            if ($est->declaracao_sem_equipamentos_imagem) {
                return false;
            }
            
            $atividadesEstabelecimento = $est->getTodasAtividades();
            foreach ($atividadesEstabelecimento as $codigo) {
                if (in_array($codigo, $codigosAtividadesRadiacao)) {
                    return true;
                }
            }
            return false;
        });

        // Adicionar as atividades de radiação encontradas em cada estabelecimento
        $estabelecimentos = $estabelecimentos->map(function($est) use ($codigosAtividadesRadiacao) {
            $atividadesEstabelecimento = $est->getTodasAtividades();
            $codigosRadiacaoDoEst = array_intersect($atividadesEstabelecimento, $codigosAtividadesRadiacao);
            
            // Buscar as atividades de radiação correspondentes
            $est->atividades_radiacao = AtividadeEquipamentoRadiacao::where('ativo', true)
                ->where(function($q) use ($codigosRadiacaoDoEst) {
                    foreach ($codigosRadiacaoDoEst as $codigo) {
                        $q->orWhereRaw("REPLACE(REPLACE(codigo_atividade, '.', ''), '-', '') = ?", [$codigo]);
                    }
                })
                ->get();
            
            return $est;
        })->values();

        // Calcular totais (exclui os que declararam não ter equipamentos)
        $totalDeclaracoesSemEquipamentos = $todosEstabelecimentos->where('declaracao_sem_equipamentos_imagem', true)->count();
        
        $totais = [
            'total' => $estabelecimentos->count(),
            'com_equipamentos' => $estabelecimentos->where('equipamentos_count', '>', 0)->count(),
            'sem_equipamentos' => $estabelecimentos->where('equipamentos_count', 0)->count(),
            'total_equipamentos' => $estabelecimentos->sum('equipamentos_count'),
            'declaracoes_sem_equipamentos' => $totalDeclaracoesSemEquipamentos,
        ];

        // Atividades que exigem equipamentos (para filtro)
        $atividades = AtividadeEquipamentoRadiacao::where('ativo', true)
            ->orderBy('descricao_atividade')
            ->get();

        return view('admin.relatorios.equipamentos-radiacao', compact(
            'estabelecimentos',
            'totais',
            'atividades'
        ));
    }

    /**
     * Exportar relatório de equipamentos de radiação para Excel
     */
    public function equipamentosRadiacaoExport(Request $request)
    {
        $usuario = auth('interno')->user();

        // Códigos das atividades que exigem equipamentos de radiação (normalizados)
        $codigosAtividadesRadiacao = AtividadeEquipamentoRadiacao::where('ativo', true)
            ->pluck('codigo_atividade')
            ->map(fn($c) => preg_replace('/[^0-9]/', '', $c))
            ->unique()
            ->filter()
            ->toArray();

        // Filtro por atividade específica
        if ($request->filled('atividade')) {
            $atividadeFiltro = AtividadeEquipamentoRadiacao::find($request->atividade);
            if ($atividadeFiltro) {
                $codigosAtividadesRadiacao = [preg_replace('/[^0-9]/', '', $atividadeFiltro->codigo_atividade)];
            }
        }

        // Buscar todos os estabelecimentos
        $query = Estabelecimento::query()
            ->whereNotNull('atividades_exercidas')
            ->with('equipamentosRadiacao')
            ->withCount('equipamentosRadiacao as equipamentos_count');

        // Filtro por município
        if ($usuario->isMunicipal()) {
            $query->where('municipio_id', $usuario->municipio_id);
        }

        $todosEstabelecimentos = $query->orderBy('nome_fantasia')->get();

        // Filtrar estabelecimentos que têm atividades de radiação
        $estabelecimentos = $todosEstabelecimentos->filter(function($est) use ($codigosAtividadesRadiacao) {
            $atividadesEstabelecimento = $est->getTodasAtividades();
            foreach ($atividadesEstabelecimento as $codigo) {
                if (in_array($codigo, $codigosAtividadesRadiacao)) {
                    return true;
                }
            }
            return false;
        });

        // Aplicar filtro de status
        if ($request->filled('status')) {
            if ($request->status === 'com') {
                $estabelecimentos = $estabelecimentos->where('equipamentos_count', '>', 0);
            } elseif ($request->status === 'sem') {
                $estabelecimentos = $estabelecimentos->where('equipamentos_count', '=', 0);
            }
        }

        // Adicionar as atividades de radiação encontradas
        $estabelecimentos = $estabelecimentos->map(function($est) use ($codigosAtividadesRadiacao) {
            $atividadesEstabelecimento = $est->getTodasAtividades();
            $codigosRadiacaoDoEst = array_intersect($atividadesEstabelecimento, $codigosAtividadesRadiacao);
            
            $est->atividades_radiacao_nomes = AtividadeEquipamentoRadiacao::where('ativo', true)
                ->where(function($q) use ($codigosRadiacaoDoEst) {
                    foreach ($codigosRadiacaoDoEst as $codigo) {
                        $q->orWhereRaw("REPLACE(REPLACE(codigo_atividade, '.', ''), '-', '') = ?", [$codigo]);
                    }
                })
                ->pluck('descricao_atividade')
                ->implode(', ');
            
            return $est;
        })->values();

        // Gerar CSV
        $filename = 'relatorio-equipamentos-radiacao-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($estabelecimentos) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'Estabelecimento',
                'Razão Social',
                'CNPJ',
                'Atividades com Radiação',
                'Qtd. Equipamentos',
                'Status',
            ], ';');

            foreach ($estabelecimentos as $est) {
                $cnpj = $est->cnpj ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $est->cnpj) : '';
                $atividades = $est->atividades_radiacao_nomes ?? '';
                $status = $est->equipamentos_count > 0 ? 'Cadastrado' : 'Pendente';

                fputcsv($file, [
                    $est->nome_fantasia ?? $est->razao_social,
                    $est->razao_social,
                    $cnpj,
                    $atividades,
                    $est->equipamentos_count,
                    $status,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Obtém estatísticas gerais do sistema
     */
    private function obterEstatisticasGerais($usuario)
    {
        $stats = [];
        
        // Total de estabelecimentos
        $queryEstabelecimentos = Estabelecimento::query();
        if ($usuario->isMunicipal()) {
            $queryEstabelecimentos->where('municipio_id', $usuario->municipio_id);
        }
        $stats['total_estabelecimentos'] = $queryEstabelecimentos->count();
        
        // Total de processos
        $queryProcessos = Processo::query();
        if ($usuario->isMunicipal()) {
            $queryProcessos->whereHas('estabelecimento', function($q) use ($usuario) {
                $q->where('municipio_id', $usuario->municipio_id);
            });
        }
        $stats['total_processos'] = $queryProcessos->count();
        $stats['processos_abertos'] = (clone $queryProcessos)->where('status', 'aberto')->count();
        
        // Total de ordens de serviço
        $stats['total_ordens_servico'] = OrdemServico::count();
        $stats['ordens_em_andamento'] = OrdemServico::where('status', 'em_andamento')->count();
        
        // Total de documentos digitais
        $stats['total_documentos'] = DocumentoDigital::count();
        
        return $stats;
    }

    /**
     * Listar estabelecimentos que declararam não ter equipamentos
     */
    public function declaracoesSemEquipamentos()
    {
        $usuario = auth('interno')->user();

        $query = Estabelecimento::query()
            ->where('declaracao_sem_equipamentos_imagem', true)
            ->with(['municipio', 'declaracaoSemEquipamentosUsuario'])
            ->orderBy('nome_fantasia');

        // Filtro por município se for usuário municipal
        if ($usuario->isMunicipal()) {
            $query->where('municipio_id', $usuario->municipio_id);
        }

        $declaracoes = $query->paginate(15);

        return view('admin.relatorios.declaracoes-sem-equipamentos', compact(
            'declaracoes'
        ));
    }

    /**
     * Relatório de documentos digitais gerados
     *
     * Regras de visibilidade:
     * - Admin, Gestor Estadual e Técnico Estadual: visualizam documentos do estado
     * - Gestor Municipal e Técnico Municipal: visualizam apenas documentos do seu município
     */
    public function documentosGerados(Request $request)
    {
        $usuario = auth('interno')->user();

        $tiposDocumento = TipoDocumento::orderBy('nome')->get(['id', 'nome']);

        $query = DocumentoDigital::query()
            ->with([
                'tipoDocumento:id,nome',
                'usuarioCriador:id,nome',
                'processo:id,numero_processo,estabelecimento_id',
                'processo.estabelecimento:id,nome_fantasia,razao_social,municipio_id',
                'processo.estabelecimento.municipio:id,nome',
            ])
            ->whereNotNull('numero_documento');

        // Usuários municipais veem apenas documentos do seu município
        if ($usuario->isMunicipal()) {
            $query->whereHas('processo.estabelecimento', function ($q) use ($usuario) {
                $q->where('municipio_id', $usuario->municipio_id);
            });
        }

        // Filtros opcionais
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tipo_documento_id')) {
            $query->where('tipo_documento_id', $request->tipo_documento_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        if ($request->filled('busca')) {
            $busca = trim($request->busca);

            $query->where(function ($q) use ($busca) {
                $q->where('numero_documento', 'like', "%{$busca}%")
                    ->orWhere('nome', 'like', "%{$busca}%")
                    ->orWhereHas('tipoDocumento', function ($tipoQ) use ($busca) {
                        $tipoQ->where('nome', 'like', "%{$busca}%");
                    })
                    ->orWhereHas('processo', function ($processoQ) use ($busca) {
                        $processoQ->where('numero_processo', 'like', "%{$busca}%");
                    })
                    ->orWhereHas('processo.estabelecimento', function ($estQ) use ($busca) {
                        $estQ->where('nome_fantasia', 'like', "%{$busca}%")
                            ->orWhere('razao_social', 'like', "%{$busca}%");
                    });
            });
        }

        $documentos = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $totais = [
            'total' => (clone $query)->count(),
            'assinados' => (clone $query)->where('status', 'assinado')->count(),
            'aguardando_assinatura' => (clone $query)->where('status', 'aguardando_assinatura')->count(),
            'rascunhos' => (clone $query)->where('status', 'rascunho')->count(),
        ];

        return view('admin.relatorios.documentos-gerados', compact('documentos', 'totais', 'tiposDocumento'));
    }

    /**
     * Relatório de Pesquisa de Satisfação com gráficos
     * Suporta seleção de múltiplas pesquisas (pesquisa_ids[])
     */
    public function pesquisaSatisfacao(Request $request)
    {
        $pesquisas = PesquisaSatisfacao::withCount('respostas')->orderBy('titulo')->get();

        // Suporta array de IDs (múltiplas) ou ID único (retrocompatível)
        $pesquisaIds = $request->input('pesquisa_ids', []);
        if (empty($pesquisaIds) && $request->filled('pesquisa_id')) {
            $pesquisaIds = [$request->input('pesquisa_id')];
        }
        $pesquisaIds = array_filter(array_map('intval', (array) $pesquisaIds));

        $pesquisasSelecionadas = collect();
        $dados = null;

        if (!empty($pesquisaIds)) {
            $pesquisasSelecionadas = PesquisaSatisfacao::with('perguntas.opcoes')
                ->whereIn('id', $pesquisaIds)
                ->get();

            if ($pesquisasSelecionadas->isEmpty()) {
                abort(404);
            }

            // Buscar respostas de TODAS as pesquisas selecionadas
            $queryRespostas = PesquisaSatisfacaoResposta::whereIn('pesquisa_id', $pesquisaIds);

            if ($request->filled('data_inicio')) {
                $queryRespostas->whereDate('created_at', '>=', $request->data_inicio);
            }
            if ($request->filled('data_fim')) {
                $queryRespostas->whereDate('created_at', '<=', $request->data_fim);
            }

            $respostas = $queryRespostas->orderByDesc('created_at')->get();

            $dados = [
                'total_respostas' => $respostas->count(),
                'por_tipo_respondente' => [
                    'interno' => $respostas->where('tipo_respondente', 'interno')->count(),
                    'externo' => $respostas->where('tipo_respondente', 'externo')->count(),
                    'anonimo' => $respostas->whereNull('tipo_respondente')->count(),
                ],
                'por_mes' => [],
                'por_pesquisa' => [],
                'perguntas' => [],
            ];

            // Respostas por mês (últimos 6 meses)
            for ($i = 5; $i >= 0; $i--) {
                $mes = now()->subMonths($i);
                $count = $respostas->filter(function ($r) use ($mes) {
                    return $r->created_at->format('Y-m') === $mes->format('Y-m');
                })->count();
                $dados['por_mes'][] = [
                    'label' => $mes->translatedFormat('M/Y'),
                    'count' => $count,
                ];
            }

            // Respostas por pesquisa (para gráfico comparativo)
            foreach ($pesquisasSelecionadas as $ps) {
                $dados['por_pesquisa'][] = [
                    'titulo' => \Str::limit($ps->titulo, 30),
                    'count' => $respostas->where('pesquisa_id', $ps->id)->count(),
                ];
            }

            // Análise por pergunta (de todas as pesquisas selecionadas)
            foreach ($pesquisasSelecionadas as $ps) {
                $respostasDaPesquisa = $respostas->where('pesquisa_id', $ps->id);
                $prefixo = count($pesquisaIds) > 1 ? '[' . \Str::limit($ps->titulo, 25) . '] ' : '';

                foreach ($ps->perguntas as $pergunta) {
                    $perguntaDados = [
                        'id' => $pergunta->id,
                        'texto' => $prefixo . $pergunta->texto,
                        'tipo' => $pergunta->tipo,
                        'pesquisa' => $ps->titulo,
                        'distribuicao' => [],
                        'media' => null,
                        'textos_livres' => [],
                    ];

                    if ($pergunta->tipo === 'escala_1_5') {
                        $contagem = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                        $soma = 0;
                        $total = 0;
                        foreach ($respostasDaPesquisa as $resp) {
                            $respostasJson = is_array($resp->respostas) ? $resp->respostas : [];
                            foreach ($respostasJson as $r) {
                                if (($r['pergunta_id'] ?? null) == $pergunta->id && isset($r['valor'])) {
                                    $val = (int) $r['valor'];
                                    if ($val >= 1 && $val <= 5) {
                                        $contagem[$val]++;
                                        $soma += $val;
                                        $total++;
                                    }
                                }
                            }
                        }
                        $perguntaDados['distribuicao'] = $contagem;
                        $perguntaDados['media'] = $total > 0 ? round($soma / $total, 1) : 0;
                        $perguntaDados['total'] = $total;
                    } elseif ($pergunta->tipo === 'multipla_escolha') {
                        $contagem = [];
                        foreach ($pergunta->opcoes as $opcao) {
                            $contagem[$opcao->id] = ['texto' => $opcao->texto, 'count' => 0];
                        }
                        foreach ($respostasDaPesquisa as $resp) {
                            $respostasJson = is_array($resp->respostas) ? $resp->respostas : [];
                            foreach ($respostasJson as $r) {
                                if (($r['pergunta_id'] ?? null) == $pergunta->id && isset($r['opcao_id'])) {
                                    $opcaoId = $r['opcao_id'];
                                    if (isset($contagem[$opcaoId])) {
                                        $contagem[$opcaoId]['count']++;
                                    }
                                }
                            }
                        }
                        $perguntaDados['distribuicao'] = array_values($contagem);
                    } elseif ($pergunta->tipo === 'texto_livre') {
                        foreach ($respostasDaPesquisa as $resp) {
                            $respostasJson = is_array($resp->respostas) ? $resp->respostas : [];
                            foreach ($respostasJson as $r) {
                                if (($r['pergunta_id'] ?? null) == $pergunta->id && !empty($r['valor'])) {
                                    $perguntaDados['textos_livres'][] = [
                                        'texto' => $r['valor'],
                                        'respondente' => $resp->nome_respondente,
                                        'data' => $resp->created_at->format('d/m/Y'),
                                    ];
                                }
                            }
                        }
                    }

                    $dados['perguntas'][] = $perguntaDados;
                }
            }
        }

        return view('admin.relatorios.pesquisa-satisfacao', compact('pesquisas', 'pesquisasSelecionadas', 'dados'));
    }

    /**
     * Relatório de Documentos por Servidor
     * Agrupa documentos por criador (servidor/técnico) com métricas de prazo
     */
    public function documentosPorServidor(Request $request)
    {
        $usuario = auth('interno')->user();

        // Apenas Administrador e Gestores podem acessar este relatório
        if (!$usuario->isAdmin() && !$usuario->isGestor()) {
            abort(403, 'Você não tem permissão para acessar este relatório.');
        }

        $tiposDocumento = TipoDocumento::orderBy('nome')->get(['id', 'nome']);

        // Buscar todos os servidores que criaram documentos
        $servidoresQuery = UsuarioInterno::whereHas('documentosCriados')
            ->orderBy('nome');

        // Regra de visibilidade por perfil
        // - Admin: vê tudo
        // - Gestor Estadual: vê usuários do próprio setor
        // - Gestor Municipal: vê usuários do próprio município e setor
        if (!$usuario->isAdmin()) {
            $servidoresQuery->where('setor', $usuario->setor);

            if ($usuario->isMunicipal()) {
                $servidoresQuery->where('municipio_id', $usuario->municipio_id);
            }
        }

        $servidores = $servidoresQuery->get(['id', 'nome', 'nivel_acesso', 'setor']);

        // Filtros
        $servidorId     = $request->input('servidor_id');
        $tipoDocId      = $request->input('tipo_documento_id');
        $statusPrazo    = $request->input('status_prazo'); // atrasado, em_dia, vencendo, sem_prazo
        $dataInicio     = $request->input('data_inicio');
        $dataFim        = $request->input('data_fim');
        $statusDoc      = $request->input('status');

        // ── Query base para os cards de totais ──
        $baseQuery = DocumentoDigital::query()
            ->whereNotNull('numero_documento');

        if (!$usuario->isAdmin()) {
            $baseQuery->whereHas('usuarioCriador', function ($q) use ($usuario) {
                $q->where('setor', $usuario->setor);

                if ($usuario->isMunicipal()) {
                    $q->where('municipio_id', $usuario->municipio_id);
                }
            });
        }

        // ── Totais globais (sem filtro de servidor) ──
        $totalGeral         = (clone $baseQuery)->count();
        $totalComPrazo      = (clone $baseQuery)->whereNotNull('data_vencimento')->count();
        $totalAtrasados     = (clone $baseQuery)->whereNotNull('data_vencimento')
                                ->whereNull('prazo_finalizado_em')
                                ->where('data_vencimento', '<', now()->toDateString())
                                ->count();
        $totalVencendo      = (clone $baseQuery)->whereNotNull('data_vencimento')
                                ->whereNull('prazo_finalizado_em')
                                ->where('data_vencimento', '>=', now()->toDateString())
                                ->where('data_vencimento', '<=', now()->addDays(5)->toDateString())
                                ->count();

        $totais = [
            'total'         => $totalGeral,
            'com_prazo'     => $totalComPrazo,
            'atrasados'     => $totalAtrasados,
            'vencendo'      => $totalVencendo,
        ];

        // ── Dados agrupados por servidor ──
        $dadosPorServidor = [];

        foreach ($servidores as $servidor) {
            $sq = DocumentoDigital::query()
                ->whereNotNull('numero_documento')
                ->where('usuario_criador_id', $servidor->id);

            if (!$usuario->isAdmin()) {
                $sq->whereHas('usuarioCriador', function ($q) use ($usuario) {
                    $q->where('setor', $usuario->setor);

                    if ($usuario->isMunicipal()) {
                        $q->where('municipio_id', $usuario->municipio_id);
                    }
                });
            }

            if ($tipoDocId) {
                $sq->where('tipo_documento_id', $tipoDocId);
            }
            if ($dataInicio) {
                $sq->whereDate('created_at', '>=', $dataInicio);
            }
            if ($dataFim) {
                $sq->whereDate('created_at', '<=', $dataFim);
            }
            if ($statusDoc) {
                $sq->where('status', $statusDoc);
            }

            $totalServidor      = (clone $sq)->count();
            $atrasadosServidor  = (clone $sq)->whereNotNull('data_vencimento')
                                    ->whereNull('prazo_finalizado_em')
                                    ->where('data_vencimento', '<', now()->toDateString())
                                    ->count();
            $vencendoServidor   = (clone $sq)->whereNotNull('data_vencimento')
                                    ->whereNull('prazo_finalizado_em')
                                    ->where('data_vencimento', '>=', now()->toDateString())
                                    ->where('data_vencimento', '<=', now()->addDays(5)->toDateString())
                                    ->count();
            $emDiaServidor      = (clone $sq)->whereNotNull('data_vencimento')
                                    ->whereNull('prazo_finalizado_em')
                                    ->where('data_vencimento', '>', now()->addDays(5)->toDateString())
                                    ->count();
            $finalizadosServidor = (clone $sq)->whereNotNull('prazo_finalizado_em')->count();

            // Aplica filtro de status_prazo (filtra servidores que não têm dados no filtro escolhido)
            if ($statusPrazo === 'atrasado' && $atrasadosServidor === 0) continue;
            if ($statusPrazo === 'vencendo' && $vencendoServidor === 0) continue;
            if ($statusPrazo === 'em_dia' && $emDiaServidor === 0) continue;

            // Pula servidores sem documentos nos filtros
            if ($totalServidor === 0) continue;

            // Se filtrou por servidor específico
            if ($servidorId && $servidor->id != $servidorId) continue;

            $dadosPorServidor[] = [
                'servidor'      => $servidor,
                'total'         => $totalServidor,
                'atrasados'     => $atrasadosServidor,
                'vencendo'      => $vencendoServidor,
                'em_dia'        => $emDiaServidor,
                'finalizados'   => $finalizadosServidor,
            ];
        }

        // Ordena por atrasados desc
        usort($dadosPorServidor, fn($a, $b) => $b['atrasados'] <=> $a['atrasados']);

        // ── Documentos detalhados (com paginação) — filtrado ──
        $docQuery = DocumentoDigital::query()
            ->whereNotNull('numero_documento')
            ->with([
                'tipoDocumento:id,nome',
                'usuarioCriador:id,nome,nivel_acesso,setor',
                'processo:id,numero_processo,estabelecimento_id',
                'processo.estabelecimento:id,nome_fantasia,razao_social,municipio_id',
                'processo.estabelecimento.municipio:id,nome',
                'ordemServico:id,numero,status',
            ]);

        if (!$usuario->isAdmin()) {
            $docQuery->whereHas('usuarioCriador', function ($q) use ($usuario) {
                $q->where('setor', $usuario->setor);

                if ($usuario->isMunicipal()) {
                    $q->where('municipio_id', $usuario->municipio_id);
                }
            });
        }

        if ($servidorId) {
            $docQuery->where('usuario_criador_id', $servidorId);
        }
        if ($tipoDocId) {
            $docQuery->where('tipo_documento_id', $tipoDocId);
        }
        if ($dataInicio) {
            $docQuery->whereDate('created_at', '>=', $dataInicio);
        }
        if ($dataFim) {
            $docQuery->whereDate('created_at', '<=', $dataFim);
        }
        if ($statusDoc) {
            $docQuery->where('status', $statusDoc);
        }

        // Filtro por status de prazo
        if ($statusPrazo === 'atrasado') {
            $docQuery->whereNotNull('data_vencimento')
                     ->whereNull('prazo_finalizado_em')
                     ->where('data_vencimento', '<', now()->toDateString());
        } elseif ($statusPrazo === 'vencendo') {
            $docQuery->whereNotNull('data_vencimento')
                     ->whereNull('prazo_finalizado_em')
                     ->where('data_vencimento', '>=', now()->toDateString())
                     ->where('data_vencimento', '<=', now()->addDays(5)->toDateString());
        } elseif ($statusPrazo === 'em_dia') {
            $docQuery->whereNotNull('data_vencimento')
                     ->whereNull('prazo_finalizado_em')
                     ->where('data_vencimento', '>', now()->addDays(5)->toDateString());
        } elseif ($statusPrazo === 'sem_prazo') {
            $docQuery->whereNull('data_vencimento');
        }

        $documentos = $docQuery->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.relatorios.documentos-por-servidor', compact(
            'totais',
            'dadosPorServidor',
            'documentos',
            'tiposDocumento',
            'servidores',
        ));
    }

}

