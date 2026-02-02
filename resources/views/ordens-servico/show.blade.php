@extends('layouts.admin')

@section('title', 'Detalhes da Ordem de Serviço')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Clean --}}
    <div class="bg-white border-b border-gray-200">
        <div class="container-fluid px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.ordens-servico.index') }}" 
                       class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="text-lg font-semibold text-gray-900">OS #{{ $ordemServico->numero }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    {!! $ordemServico->status_badge !!}
                    {!! $ordemServico->competencia_badge !!}
                    <a href="{{ route('admin.ordens-servico.pdf', $ordemServico) }}" 
                       target="_blank"
                       class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Baixar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-6">
        {{-- Layout de 2 Colunas: Menu Lateral (25%) + Conteúdo (75%) --}}
        <div class="flex flex-col lg:flex-row gap-6">
            
            {{-- ========================================
                COLUNA ESQUERDA: Menu de Ações (25%)
            ======================================== --}}
            <aside class="lg:w-1/4 space-y-5">
                {{-- Card de Menu de Opções --}}
                <div class="bg-white rounded-lg border border-gray-200 sticky top-6">
                    <div class="p-3 space-y-1.5">
                        @php
                            $isTecnicoAtribuido = $ordemServico->tecnicos_ids && in_array(auth()->id(), $ordemServico->tecnicos_ids);
                            $isGestor = auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual() || auth('interno')->user()->isMunicipal();
                        @endphp
                        
                        @if($ordemServico->status === 'finalizada')
                            {{-- Botão Reiniciar OS (apenas para gestores) --}}
                            @if($isGestor)
                            <form method="POST" action="{{ route('admin.ordens-servico.reiniciar', $ordemServico) }}" 
                                  onsubmit="return confirm('Tem certeza que deseja reiniciar esta OS? Ela voltará ao status \'Em Andamento\'.')">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-orange-700 bg-orange-50 rounded-md hover:bg-orange-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reiniciar OS
                                </button>
                            </form>
                            @endif
                        @elseif($ordemServico->status === 'cancelada')
                            {{-- Botão Reativar OS (apenas para gestores) --}}
                            @if($isGestor)
                            <form method="POST" action="{{ route('admin.ordens-servico.reativar', $ordemServico) }}" 
                                  onsubmit="return confirm('Tem certeza que deseja reativar esta OS? Ela voltará ao status Em Andamento.')">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-green-700 bg-green-50 rounded-md hover:bg-green-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reativar OS
                                </button>
                            </form>
                            @else
                            <div class="text-center py-3 px-3 bg-red-50 rounded-md border border-red-200">
                                <p class="text-xs text-red-700 font-medium">Ordem de Serviço Cancelada</p>
                            </div>
                            @endif
                        @else
                            @php
                                $usuarioLogado = auth('interno')->user();
                                $ehTecnico = in_array($usuarioLogado->nivel_acesso->value, ['tecnico_estadual', 'tecnico_municipal']);
                                
                                // Verifica se o técnico está vinculado a alguma atividade pendente
                                $tecnicoTemAtividadePendente = false;
                                if ($ehTecnico && $ordemServico->atividades_tecnicos) {
                                    foreach ($ordemServico->atividades_tecnicos as $ativ) {
                                        $statusAtiv = $ativ['status'] ?? 'pendente';
                                        $tecnicosAtiv = $ativ['tecnicos'] ?? [];
                                        if ($statusAtiv !== 'finalizada' && in_array($usuarioLogado->id, $tecnicosAtiv)) {
                                            $tecnicoTemAtividadePendente = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            
                            @if(!$ehTecnico)
                            {{-- Botão Editar - Apenas para Admin e Gestores --}}
                            <a href="{{ route('admin.ordens-servico.edit', $ordemServico) }}" 
                               class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar OS
                            </a>
                            @endif
                            
                            @if($ehTecnico && $tecnicoTemAtividadePendente && $ordemServico->status === 'em_andamento')
                            {{-- Botão Finalizar Atividades - Apenas para Técnicos vinculados a atividades pendentes --}}
                            <a href="#secao-atividades" 
                               class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-green-700 bg-green-50 rounded-md hover:bg-green-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Finalizar Atividade
                            </a>
                            @endif
                            
                            @if(!$ehTecnico)
                            {{-- Botão Cancelar OS - Apenas para Admin e Gestores --}}
                            <button type="button" 
                                    onclick="abrirModalCancelarOS()"
                                    class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancelar OS
                            </button>
                            @endif
                        @endif
                        
                        {{-- Botão Voltar --}}
                        <a href="{{ route('admin.ordens-servico.index') }}" 
                           class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Voltar
                        </a>
                    </div>
                </div>

                {{-- Card de Informações Rápidas --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-3 space-y-2">
                        @if($ordemServico->processo)
                        <div>
                            <label class="text-xs font-medium text-gray-500">Processo</label>
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$ordemServico->processo->estabelecimento_id, $ordemServico->processo->id]) }}" 
                               class="block text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                {{ $ordemServico->processo->numero_processo }}
                            </a>
                        </div>
                        <div class="border-t border-gray-100 pt-2"></div>
                        @endif
                        @php
                            // Prioriza o município do estabelecimento (via municipio_id), se existir
                            $municipioExibir = null;
                            if ($ordemServico->estabelecimento && $ordemServico->estabelecimento->municipio_id) {
                                $municipioExibir = \App\Models\Municipio::find($ordemServico->estabelecimento->municipio_id);
                            } elseif ($ordemServico->municipio_id) {
                                $municipioExibir = $ordemServico->municipio;
                            }
                        @endphp
                        @if($municipioExibir)
                        <div>
                            <label class="text-xs font-medium text-gray-500">Município</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $municipioExibir->nome }}/{{ $municipioExibir->uf }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Card de Datas --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-3 space-y-1.5">
                        <div class="flex justify-between items-center py-1.5">
                            <label class="text-xs text-gray-500">Abertura</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_abertura ? $ordemServico->data_abertura->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-t border-gray-100">
                            <label class="text-xs text-gray-500">Início</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_inicio ? $ordemServico->data_inicio->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-t border-gray-100">
                            <label class="text-xs text-gray-500">Término</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_fim ? $ordemServico->data_fim->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        @if($ordemServico->data_conclusao)
                        <div class="flex justify-between items-center py-1.5 border-t border-gray-100">
                            <label class="text-xs text-gray-500">Conclusão</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_conclusao->format('d/m/Y') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

            </aside>

            {{-- ========================================
                COLUNA DIREITA: Conteúdo Principal (75%)
            ======================================== --}}
            <main class="lg:w-3/4 space-y-6">
            {{-- Informações do Estabelecimento --}}
            @if($ordemServico->estabelecimento)
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Estabelecimento</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">Razão Social</label>
                            <a href="{{ route('admin.estabelecimentos.show', $ordemServico->estabelecimento->id) }}" 
                               class="block text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                {{ $ordemServico->estabelecimento->razao_social }}
                            </a>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Nome Fantasia</label>
                            <a href="{{ route('admin.estabelecimentos.show', $ordemServico->estabelecimento->id) }}" 
                               class="block text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                {{ $ordemServico->estabelecimento->nome_fantasia }}
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="text-xs text-gray-500">
                                {{ $ordemServico->estabelecimento->tipo_pessoa === 'fisica' ? 'CPF' : 'CNPJ' }}
                            </label>
                            <a href="{{ route('admin.estabelecimentos.show', $ordemServico->estabelecimento->id) }}" 
                               class="block text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition-colors font-mono">
                                @if($ordemServico->estabelecimento->tipo_pessoa === 'fisica')
                                    {{ $ordemServico->estabelecimento->cpf_formatado ?? '-' }}
                                @else
                                    {{ $ordemServico->estabelecimento->cnpj_formatado ?? '-' }}
                                @endif
                            </a>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">CEP</label>
                            <p class="text-sm font-medium text-gray-900 font-mono">{{ $ordemServico->estabelecimento->cep ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-gray-100">
                        <label class="text-xs text-gray-500">Endereço</label>
                        <p class="text-sm font-medium text-gray-900" id="endereco-completo">
                            {{ $ordemServico->estabelecimento->logradouro ?? $ordemServico->estabelecimento->endereco ?? '-' }}
                            @if($ordemServico->estabelecimento->complemento) - {{ $ordemServico->estabelecimento->complemento }}@endif
                            , {{ $ordemServico->estabelecimento->bairro }}
                            - {{ $ordemServico->estabelecimento->cidade }}/{{ $ordemServico->estabelecimento->estado }}
                        </p>
                    </div>

                    {{-- Status de Equipamentos de Imagem --}}
                    @php
                        $codigosAtividadesRadiacao = \App\Models\AtividadeEquipamentoRadiacao::where('ativo', true)
                            ->pluck('codigo_atividade')
                            ->map(fn($c) => preg_replace('/[^0-9]/', '', $c))
                            ->unique()
                            ->filter()
                            ->toArray();
                        
                        $atividadesEstabelecimento = $ordemServico->estabelecimento->getTodasAtividades();
                        $exigeEquipamentos = false;
                        foreach ($atividadesEstabelecimento as $codigo) {
                            if (in_array($codigo, $codigosAtividadesRadiacao)) {
                                $exigeEquipamentos = true;
                                break;
                            }
                        }
                    @endphp

                    @if($exigeEquipamentos)
                    <div class="pt-2 border-t border-gray-100">
                        @if($ordemServico->estabelecimento->equipamentosRadiacao()->count() > 0)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-green-800">Equipamentos Registrados</p>
                                        <p class="text-xs text-green-700 mt-1">Este estabelecimento possui <strong>{{ $ordemServico->estabelecimento->equipamentosRadiacao()->count() }}</strong> equipamento(s) de imagem cadastrado(s).</p>
                                        <a href="{{ route('admin.estabelecimentos.equipamentos-radiacao.index', $ordemServico->estabelecimento->id) }}" 
                                           class="inline-flex items-center gap-1 text-xs text-green-600 hover:text-green-800 font-medium mt-2">
                                            Ver equipamentos
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @elseif($ordemServico->estabelecimento->declaracao_sem_equipamentos_imagem)
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-amber-800">Declaração: Não Possui Equipamentos</p>
                                        <p class="text-xs text-amber-700 mt-1">O estabelecimento declarou formalmente que <strong>não possui equipamentos de imagem</strong>, mesmo possuindo atividades que normalmente exigem.</p>
                                        
                                        @if($ordemServico->estabelecimento->declaracao_sem_equipamentos_opcoes)
                                        <div class="mt-2 pt-2 border-t border-amber-200">
                                            <p class="text-xs font-medium text-amber-800 mb-1">Confirmações:</p>
                                            <div class="space-y-1">
                                                @php
                                                    $opcoes = json_decode($ordemServico->estabelecimento->declaracao_sem_equipamentos_opcoes, true) ?? [];
                                                @endphp
                                                @if(in_array('opcao_1', $opcoes))
                                                <div class="flex items-start gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    <span class="text-xs text-amber-800">Não executa atividades de diagnóstico por imagem neste estabelecimento</span>
                                                </div>
                                                @endif
                                                @if(in_array('opcao_2', $opcoes))
                                                <div class="flex items-start gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    <span class="text-xs text-amber-800">Não possui equipamentos de diagnóstico por imagem instalados no local</span>
                                                </div>
                                                @endif
                                                @if(in_array('opcao_3', $opcoes))
                                                <div class="flex items-start gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    <span class="text-xs text-amber-800">Os exames, quando necessários, são integralmente terceirizados ou realizados em outro estabelecimento regularmente licenciado</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                        
                                        @if($ordemServico->estabelecimento->declaracao_sem_equipamentos_imagem_justificativa)
                                        <p class="text-xs text-amber-700 mt-2 pt-2 border-t border-amber-200"><strong>Justificativa:</strong> {{ $ordemServico->estabelecimento->declaracao_sem_equipamentos_imagem_justificativa }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-red-800">Equipamentos Não Registrados</p>
                                        <p class="text-xs text-red-700 mt-1">Este estabelecimento não possui equipamentos de imagem cadastrados e nem declaração formal.</p>
                                        <a href="{{ route('admin.estabelecimentos.equipamentos-radiacao.index', $ordemServico->estabelecimento->id) }}" 
                                           class="inline-flex items-center gap-1 text-xs text-red-600 hover:text-red-800 font-medium mt-2">
                                            Cadastrar equipamentos
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                    {{-- Mapa de Localização --}}
                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Localização
                            </label>
                            @php
                                // Usa logradouro como prioridade (campo da API), senão usa endereco
                                $logradouro = $ordemServico->estabelecimento->logradouro ?? $ordemServico->estabelecimento->endereco ?? '';
                                $numero = $ordemServico->estabelecimento->numero ?? '';
                                $bairro = $ordemServico->estabelecimento->bairro ?? '';
                                $cidade = $ordemServico->estabelecimento->cidade ?? '';
                                $estado = $ordemServico->estabelecimento->estado ?? 'TO';
                                $cep = $ordemServico->estabelecimento->cep ?? '';
                                
                                // Monta o endereço completo para o Google Maps
                                $partes = [];
                                if ($logradouro) {
                                    // Se o logradouro já contém número (ex: "07 DE SETEMBRO, 340-B"), usa direto
                                    $partes[] = $logradouro;
                                }
                                if ($bairro) {
                                    $partes[] = $bairro;
                                }
                                if ($cidade) {
                                    $partes[] = $cidade;
                                }
                                if ($estado) {
                                    $partes[] = $estado;
                                }
                                if ($cep) {
                                    $partes[] = preg_replace('/[^0-9]/', '', $cep);
                                }
                                $partes[] = 'Brasil';
                                
                                $enderecoCompleto = implode(', ', $partes);
                                $endereco = urlencode($enderecoCompleto);
                                
                                $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query=" . $endereco;
                                $googleMapsEmbedUrl = "https://www.google.com/maps?q=" . $endereco . "&output=embed";
                            @endphp
                            <a href="{{ $googleMapsUrl }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Abrir no Maps
                            </a>
                        </div>
                        
                        {{-- Iframe do Google Maps --}}
                        <div class="relative w-full rounded-lg overflow-hidden border border-gray-300 bg-gray-100" style="height: 300px;">
                            <iframe 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                style="border:0" 
                                referrerpolicy="no-referrer-when-downgrade"
                                src="{{ $googleMapsEmbedUrl }}"
                                allowfullscreen>
                            </iframe>
                        </div>
                        
                        
                        {{-- Debug: Mostra o endereço que está sendo usado --}}
                        <div class="mt-2 p-2 bg-gray-50 rounded text-xs text-gray-600">
                            <strong>Endereço usado no mapa:</strong> {{ $enderecoCompleto }}
                        </div>
                        
                        <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Clique em "Abrir no Maps" para ver rotas e mais detalhes
                        </p>
                    </div>
                </div>
            </div>
            @else
            {{-- Aviso quando não há estabelecimento --}}
            <div class="bg-amber-50 rounded-lg shadow border border-amber-200 p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-amber-900 mb-1">Ordem de Serviço sem Estabelecimento</h3>
                        <p class="text-sm text-amber-800 mb-3">
                            Esta OS foi criada sem um estabelecimento vinculado. Você pode vincular um estabelecimento ao editar ou finalizar a ordem de serviço.
                        </p>
                        @if($ordemServico->status !== 'finalizada')
                        <a href="{{ route('admin.ordens-servico.edit', $ordemServico) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar e Vincular Estabelecimento
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Atividades por Técnico (Nova Seção) --}}
            @if($ordemServico->atividades_tecnicos && count($ordemServico->atividades_tecnicos) > 0)
            <div id="secao-atividades" class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Atividades por Técnico
                    </h2>
                    @php
                        $totalAtividades = count($ordemServico->atividades_tecnicos);
                        $atividadesFinalizadas = collect($ordemServico->atividades_tecnicos)->filter(fn($a) => ($a['status'] ?? 'pendente') === 'finalizada')->count();
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">
                            {{ $atividadesFinalizadas }}/{{ $totalAtividades }} concluídas
                        </span>
                    </div>
                </div>
                <div class="px-5 py-5 space-y-4">
                    @foreach($ordemServico->atividades_tecnicos as $index => $atividade)
                        @php
                            $statusAtividade = $atividade['status'] ?? 'pendente';
                            $responsavelId = $atividade['responsavel_id'] ?? null;
                            $responsavel = $responsavelId ? \App\Models\UsuarioInterno::find($responsavelId) : null;
                            $tecnicosIds = $atividade['tecnicos'] ?? [];
                            $tecnicos = \App\Models\UsuarioInterno::whereIn('id', $tecnicosIds)->get();
                            $finalizadaPor = isset($atividade['finalizada_por']) ? \App\Models\UsuarioInterno::find($atividade['finalizada_por']) : null;
                            $finalizadaEm = isset($atividade['finalizada_em']) ? \Carbon\Carbon::parse($atividade['finalizada_em']) : null;
                            $usuarioLogadoAtribuido = in_array(auth('interno')->id(), $tecnicosIds);
                        @endphp
                        
                        <div class="border rounded-xl overflow-hidden {{ $statusAtividade === 'finalizada' ? 'border-green-200 bg-green-50/50' : 'border-gray-200 bg-white' }}">
                            {{-- Header da Atividade --}}
                            <div class="px-4 py-3 flex items-center justify-between {{ $statusAtividade === 'finalizada' ? 'bg-green-100/50' : 'bg-gray-50' }}">
                                <div class="flex items-center gap-3">
                                    @if($statusAtividade === 'finalizada')
                                        <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $atividade['nome_atividade'] ?? 'Atividade' }}</h4>
                                        @if($statusAtividade === 'finalizada' && $finalizadaEm)
                                            <p class="text-xs text-green-700">
                                                Finalizada em {{ $finalizadaEm->format('d/m/Y H:i') }}
                                                @if($finalizadaPor) por {{ $finalizadaPor->nome }} @endif
                                            </p>
                                        @else
                                            <p class="text-xs text-amber-700">Pendente</p>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Botão Finalizar (apenas para técnicos atribuídos e se não finalizada) --}}
                                @if($statusAtividade !== 'finalizada' && $usuarioLogadoAtribuido && $ordemServico->status === 'em_andamento')
                                    <button type="button" 
                                            onclick="abrirModalFinalizarAtividade({{ $index }}, '{{ addslashes($atividade['nome_atividade'] ?? 'Atividade') }}')"
                                            class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Finalizar Atividade
                                    </button>
                                @elseif($statusAtividade === 'finalizada')
                                    @php
                                        $statusExecucao = $atividade['status_execucao'] ?? 'concluido';
                                        $statusLabel = match($statusExecucao) {
                                            'concluido' => '✓ Concluída',
                                            'parcial' => '⚠ Parcial',
                                            'nao_concluido' => '✗ Não concluída',
                                            default => '✓ Concluída'
                                        };
                                        $statusClass = match($statusExecucao) {
                                            'concluido' => 'text-green-700 bg-green-100',
                                            'parcial' => 'text-yellow-700 bg-yellow-100',
                                            'nao_concluido' => 'text-red-700 bg-red-100',
                                            default => 'text-green-700 bg-green-100'
                                        };
                                    @endphp
                                    <span class="px-3 py-1.5 text-xs font-medium {{ $statusClass }} rounded-lg">
                                        {{ $statusLabel }}
                                    </span>
                                @endif
                            </div>
                            
                            {{-- Técnicos da Atividade --}}
                            <div class="px-4 py-3">
                                <p class="text-xs font-medium text-gray-500 mb-2">Técnicos atribuídos:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tecnicos as $tecnico)
                                        <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs {{ $tecnico->id == $responsavelId ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-gray-100 text-gray-700' }}">
                                            <div class="w-5 h-5 rounded-full {{ $tecnico->id == $responsavelId ? 'bg-indigo-600' : 'bg-gray-500' }} flex items-center justify-center">
                                                <span class="text-white font-bold text-[10px]">{{ strtoupper(substr($tecnico->nome, 0, 1)) }}</span>
                                            </div>
                                            <span class="font-medium">{{ $tecnico->nome }}</span>
                                            @if($tecnico->id == $responsavelId)
                                                <span class="text-[10px] bg-indigo-200 px-1 rounded">Responsável</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                
                                {{-- Observações da finalização --}}
                                @if($statusAtividade === 'finalizada' && !empty($atividade['observacoes_finalizacao']))
                                    <div class="mt-3 p-2 bg-green-50 rounded-lg border border-green-200">
                                        <p class="text-xs font-medium text-green-800 mb-1">Observações:</p>
                                        <p class="text-xs text-green-700">{{ $atividade['observacoes_finalizacao'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            {{-- Técnicos Responsáveis (fallback para OSs antigas sem atividades_tecnicos) --}}
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Técnicos Responsáveis</h2>
                    @if($ordemServico->tecnicos()->count() > 0)
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">
                        {{ $ordemServico->tecnicos()->count() }}
                    </span>
                    @endif
                </div>
                <div class="px-5 py-5">
                    @if($ordemServico->tecnicos()->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($ordemServico->tecnicos() as $tecnico)
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-lg border border-green-200">
                                    <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-xs">
                                            {{ strtoupper(substr($tecnico->nome, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium text-gray-900">{{ $tecnico->nome }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">Nenhum técnico atribuído</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Ações Vinculadas e Status de Execução --}}
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Ações Vinculadas</h2>
                    @if($ordemServico->tiposAcao()->count() > 0)
                    <div class="flex items-center gap-2">
                        @if($ordemServico->status === 'finalizada' && $ordemServico->acoes_executadas_ids)
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">
                            {{ count($ordemServico->acoes_executadas_ids) }} executadas
                        </span>
                        @endif
                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded">
                            {{ $ordemServico->tiposAcao()->count() }} total
                        </span>
                    </div>
                    @endif
                </div>
                <div class="px-5 py-5">
                    @if($ordemServico->tiposAcao()->count() > 0)
                        <div class="space-y-2">
                            @foreach($ordemServico->tiposAcao() as $tipoAcao)
                                @php
                                    $foiExecutada = $ordemServico->status === 'finalizada' && 
                                                    $ordemServico->acoes_executadas_ids && 
                                                    in_array($tipoAcao->id, $ordemServico->acoes_executadas_ids);
                                    $naoFoiExecutada = $ordemServico->status === 'finalizada' && 
                                                       (!$ordemServico->acoes_executadas_ids || 
                                                        !in_array($tipoAcao->id, $ordemServico->acoes_executadas_ids));
                                @endphp
                                
                                @if($ordemServico->status === 'finalizada')
                                    {{-- OS Finalizada: Mostra status de execução --}}
                                    @if($foiExecutada)
                                        <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                            <div class="flex-shrink-0 w-5 h-5 bg-green-600 rounded-full flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-green-900">{{ $tipoAcao->descricao }}</span>
                                            <span class="ml-auto text-xs text-green-600 font-semibold">Executada</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                            <div class="flex-shrink-0 w-5 h-5 bg-gray-400 rounded-full flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-gray-600">{{ $tipoAcao->descricao }}</span>
                                            <span class="ml-auto text-xs text-gray-500 font-semibold">Não executada</span>
                                        </div>
                                    @endif
                                @else
                                    {{-- OS Não Finalizada: Mostra apenas as ações --}}
                                    <div class="flex items-center gap-3 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                        <div class="flex-shrink-0 w-5 h-5 bg-purple-600 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-purple-900">{{ $tipoAcao->descricao }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">Nenhuma ação cadastrada</p>
                    @endif
                </div>
            </div>

            {{-- Documento Anexo --}}
            @if($ordemServico->documento_anexo_path)
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Documento Anexo
                    </h2>
                </div>
                <div class="px-5 py-5">
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $ordemServico->documento_anexo_nome }}</p>
                                <p class="text-xs text-gray-500 mt-1">Documento em PDF</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ Storage::url($ordemServico->documento_anexo_path) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Visualizar
                            </a>
                            <a href="{{ Storage::url($ordemServico->documento_anexo_path) }}" 
                               download
                               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Observações --}}
            @if($ordemServico->observacoes)
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Observações</h2>
                </div>
                <div class="px-5 py-5">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $ordemServico->observacoes }}</p>
                </div>
            </div>
            @endif

            {{-- Informações do Cancelamento --}}
            @if($ordemServico->status === 'cancelada' && $ordemServico->motivo_cancelamento)
            <div class="bg-white rounded-lg border border-red-200">
                <div class="px-5 py-4 border-b border-red-100 bg-red-50">
                    <h2 class="text-base font-semibold text-red-900 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Motivo do Cancelamento
                    </h2>
                </div>
                <div class="px-5 py-5 space-y-4">
                    <div>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $ordemServico->motivo_cancelamento }}</p>
                    </div>
                    @if($ordemServico->cancelada_em)
                    <div class="flex items-center gap-2 text-xs text-gray-500 pt-3 border-t border-gray-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Cancelada em {{ $ordemServico->cancelada_em->format('d/m/Y \à\s H:i') }}</span>
                        @if($ordemServico->cancelada_por)
                        <span class="mx-1">•</span>
                        <span>por {{ \App\Models\UsuarioInterno::find($ordemServico->cancelada_por)->nome ?? 'Usuário' }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif
            </main>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- Estilos removidos - mapa agora usa Google Maps --}}
@endpush

@push('scripts')
<script>
    // ========================================
    // Funções para Cancelar OS
    // ========================================
    
    // Função para abrir modal de cancelar OS
    function abrirModalCancelarOS() {
        const modal = document.getElementById('modalCancelarOS');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            console.error('Modal de cancelamento não encontrado');
            alert('Erro ao abrir modal. Recarregue a página e tente novamente.');
        }
    }

    // Função para fechar modal de cancelar OS
    function fecharModalCancelarOS() {
        const modal = document.getElementById('modalCancelarOS');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            // Limpa o textarea ao fechar
            const textarea = document.getElementById('motivo_cancelamento');
            if (textarea) {
                textarea.value = '';
                validarMotivoCancelamento();
            }
        }
    }

    // Função para validar motivo de cancelamento
    function validarMotivoCancelamento() {
        const textarea = document.getElementById('motivo_cancelamento');
        const btnConfirmar = document.getElementById('btnConfirmarCancelamento');
        const countElement = document.getElementById('motivoCount');
        const helpElement = document.getElementById('motivoHelp');
        
        if (!textarea || !btnConfirmar || !countElement || !helpElement) return;
        
        const length = textarea.value.length;
        const minLength = 20;
        
        // Atualiza contador
        countElement.textContent = `${length} / ${minLength}`;
        
        // Atualiza cor do contador
        if (length >= minLength) {
            countElement.classList.remove('text-gray-400', 'text-red-500');
            countElement.classList.add('text-green-600');
            helpElement.classList.remove('text-gray-500', 'text-red-500');
            helpElement.classList.add('text-green-600');
            helpElement.textContent = '✓ Mínimo atingido';
            btnConfirmar.disabled = false;
        } else {
            countElement.classList.remove('text-gray-400', 'text-green-600');
            countElement.classList.add('text-red-500');
            helpElement.classList.remove('text-gray-500', 'text-green-600');
            helpElement.classList.add('text-red-500');
            helpElement.textContent = `Faltam ${minLength - length} caracteres`;
            btnConfirmar.disabled = true;
        }
    }

    // ========================================
    // Funções para Finalizar Atividade Individual
    // ========================================
    
    // Função para abrir modal de finalizar atividade
    function abrirModalFinalizarAtividade(index, nomeAtividade) {
        document.getElementById('atividadeIndex').value = index;
        document.getElementById('nomeAtividadeModal').textContent = nomeAtividade;
        document.getElementById('observacoes_atividade').value = '';
        
        // Limpa seleção de status
        const radios = document.querySelectorAll('input[name="status_execucao"]');
        radios.forEach(radio => radio.checked = false);
        
        // Limpa seleção de estabelecimento se existir
        const selectEstab = document.getElementById('estabelecimento_id_atividade');
        if (selectEstab) selectEstab.value = '';
        
        document.getElementById('modalFinalizarAtividade').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Função para fechar modal de finalizar atividade
    function fecharModalFinalizarAtividade() {
        document.getElementById('modalFinalizarAtividade').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Função para confirmar finalização da atividade
    async function confirmarFinalizarAtividade() {
        const index = document.getElementById('atividadeIndex').value;
        const observacoes = document.getElementById('observacoes_atividade').value;
        const btnFinalizar = document.getElementById('btnFinalizarAtividade');
        
        // Valida status de execução
        const statusSelecionado = document.querySelector('input[name="status_execucao"]:checked');
        if (!statusSelecionado) {
            alert('⚠️ Selecione o status da execução.');
            return;
        }
        
        // Valida observações
        if (!observacoes || observacoes.trim().length < 10) {
            alert('⚠️ Informe as observações (mínimo 10 caracteres).');
            return;
        }
        
        // Pega estabelecimento se existir
        const selectEstab = document.getElementById('estabelecimento_id_atividade');
        const estabelecimentoId = selectEstab ? selectEstab.value : null;
        
        // Desabilita botão e mostra loading
        btnFinalizar.disabled = true;
        btnFinalizar.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Finalizando...';

        try {
            const response = await fetch('{{ route("admin.ordens-servico.finalizar-atividade", $ordemServico) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    atividade_index: index,
                    status_execucao: statusSelecionado.value,
                    observacoes: observacoes,
                    estabelecimento_id: estabelecimentoId
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Sucesso
                fecharModalFinalizarAtividade();
                
                // Mostra mensagem de sucesso
                if (data.os_finalizada) {
                    alert('✅ ' + data.message + '\n\n🎉 Todas as atividades foram concluídas! A OS foi finalizada automaticamente.');
                } else {
                    alert('✅ ' + data.message);
                }
                
                // Recarrega a página para atualizar o status
                window.location.reload();
            } else {
                // Erro
                alert('❌ ' + (data.message || 'Erro ao finalizar atividade'));
                resetarBotaoFinalizarAtividade();
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao finalizar atividade. Tente novamente.');
            resetarBotaoFinalizarAtividade();
        }
    }

    // Função auxiliar para resetar o botão
    function resetarBotaoFinalizarAtividade() {
        const btnFinalizar = document.getElementById('btnFinalizarAtividade');
        btnFinalizar.disabled = false;
        btnFinalizar.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Finalizar';
    }
</script>
@endpush

@push('modals')
{{-- Modal Cancelar OS --}}
<div id="modalCancelarOS" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <form method="POST" action="{{ route('admin.ordens-servico.cancelar', $ordemServico) }}" id="formCancelarOS">
            @csrf
            {{-- Header --}}
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Cancelar Ordem de Serviço
                    </h3>
                    <button type="button" onclick="fecharModalCancelarOS()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-5">
                {{-- Aviso --}}
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-800">
                        <strong>Atenção:</strong> Esta ação cancelará a ordem de serviço.
                    </p>
                </div>

                {{-- Motivo do Cancelamento --}}
                <div>
                    <label for="motivo_cancelamento" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo do Cancelamento <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="motivo_cancelamento" 
                        name="motivo_cancelamento" 
                        rows="4" 
                        required
                        minlength="20"
                        oninput="validarMotivoCancelamento()"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none transition-all"
                        placeholder="Descreva o motivo do cancelamento..."></textarea>
                    <div class="flex items-center justify-between mt-1.5">
                        <p id="motivoHelp" class="text-xs text-gray-500">Mínimo de 20 caracteres</p>
                        <p id="motivoCount" class="text-xs font-medium text-gray-400">0 / 20</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
                <button type="button" 
                        onclick="fecharModalCancelarOS()"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                    Voltar
                </button>
                <button type="submit" 
                        id="btnConfirmarCancelamento"
                        disabled
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-sm hover:shadow transition-all disabled:bg-gray-300 disabled:cursor-not-allowed disabled:hover:shadow-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Confirmar Cancelamento
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Finalizar Atividade Individual --}}
<div id="modalFinalizarAtividade" class="hidden fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        {{-- Header Clean --}}
        <div class="sticky top-0 bg-white px-8 py-6 border-b border-gray-100">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-green-50 rounded-full mb-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900">Finalizar Atividade</h3>
                <p id="nomeAtividadeModal" class="text-sm text-gray-500 mt-1"></p>
            </div>
            <button type="button" onclick="fecharModalFinalizarAtividade()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form Clean --}}
        <form id="formFinalizarAtividade" class="px-8 py-6 space-y-6">
            <input type="hidden" id="atividadeIndex" name="atividade_index" value="">
            
            {{-- Status de Execução --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Status da execução <span class="text-red-500">*</span>
                </label>
                <div class="space-y-2.5">
                    <label class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-lg cursor-pointer hover:border-green-300 hover:bg-green-50/50 transition-all group">
                        <input type="radio" name="status_execucao" value="concluido" required class="w-4 h-4 text-green-600 focus:ring-green-500">
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">Concluído com sucesso</span>
                    </label>
                    <label class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-lg cursor-pointer hover:border-yellow-300 hover:bg-yellow-50/50 transition-all group">
                        <input type="radio" name="status_execucao" value="parcial" required class="w-4 h-4 text-yellow-600 focus:ring-yellow-500">
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">Concluído parcialmente</span>
                    </label>
                    <label class="flex items-center gap-3 p-3.5 border border-gray-200 rounded-lg cursor-pointer hover:border-red-300 hover:bg-red-50/50 transition-all group">
                        <input type="radio" name="status_execucao" value="nao_concluido" required class="w-4 h-4 text-red-600 focus:ring-red-500">
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">Não concluído</span>
                    </label>
                </div>
            </div>

            {{-- Observações --}}
            <div>
                <label for="observacoes_atividade" class="block text-sm font-medium text-gray-700 mb-2">
                    Observações <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="observacoes_atividade" 
                    name="observacoes" 
                    rows="4" 
                    required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none transition-all"
                    placeholder="Descreva como foi a execução desta atividade..."></textarea>
                <p class="mt-1.5 text-xs text-gray-400">Mínimo de 10 caracteres</p>
            </div>

            {{-- Aviso --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-xs text-blue-800">
                    <strong>💡 Informação:</strong> Ao finalizar sua atividade, ela será marcada como concluída. 
                    A OS será automaticamente finalizada quando todas as atividades forem concluídas por seus respectivos técnicos.
                </p>
            </div>

            {{-- Botões Centralizados --}}
            <div class="flex items-center justify-center gap-3 pt-4">
                <button type="button" 
                        onclick="fecharModalFinalizarAtividade()"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-all">
                    Cancelar
                </button>
                <button type="button" 
                        id="btnFinalizarAtividade"
                        onclick="confirmarFinalizarAtividade()"
                        class="inline-flex items-center gap-2 px-8 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-sm hover:shadow transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Finalizar
                </button>
            </div>
        </form>
    </div>
</div>
@endpush
