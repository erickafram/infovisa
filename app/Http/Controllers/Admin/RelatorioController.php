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
}

