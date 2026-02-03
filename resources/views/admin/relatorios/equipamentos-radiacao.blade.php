@extends('layouts.admin')

@section('title', 'Relatório - Equipamentos de Imagem')

@section('content')
<div class="space-y-6" x-data="relatorioEquipamentosRadiacao()">
    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.relatorios.index') }}" 
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Equipamentos de Imagem</h1>
                <p class="text-gray-500 mt-1">Estabelecimentos com atividades que exigem equipamentos de imagem</p>
            </div>
        </div>
        <button @click="exportarExcel()" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exportar Excel
        </button>
    </div>

    {{-- Cards de Resumo --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-blue-100 rounded-lg">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900">{{ $totais['total'] }}</p>
                    <p class="text-xs text-gray-500">Total de estabelecimentos</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-green-100 rounded-lg">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-green-600">{{ $totais['com_equipamentos'] }}</p>
                    <p class="text-xs text-gray-500">Com equipamentos</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-red-100 rounded-lg">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-red-600">{{ $totais['sem_equipamentos'] }}</p>
                    <p class="text-xs text-gray-500">Sem equipamentos</p>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.relatorios.equipamentos-radiacao.declaracoes') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 hover:shadow-md hover:border-amber-300 transition-all cursor-pointer group">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-amber-100 rounded-lg group-hover:bg-amber-200 transition-colors">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-amber-600 group-hover:text-amber-700 transition-colors">{{ $totais['declaracoes_sem_equipamentos'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 group-hover:text-amber-600 transition-colors">Declarou não ter</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Toggle Visualização: Tabela / Mapa --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Visualização</h3>
            <div class="inline-flex rounded-lg border border-gray-200 p-1 bg-gray-50">
                <button @click="visualizacao = 'tabela'" 
                        :class="visualizacao === 'tabela' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 rounded-md text-sm font-medium transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Tabela
                </button>
                <button @click="visualizacao = 'mapa'" 
                        :class="visualizacao === 'mapa' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 rounded-md text-sm font-medium transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Mapa
                </button>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4" x-show="visualizacao === 'tabela'" x-collapse>
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" 
                       x-model="filtros.busca" 
                       @input.debounce.300ms="filtrar()"
                       placeholder="Buscar por nome ou CNPJ..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>
            
            <div>
                <select x-model="filtros.status" @change="filtrar()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Todos os status</option>
                    <option value="com">Com equipamentos</option>
                    <option value="sem">Sem equipamentos</option>
                </select>
            </div>

            <div>
                <select x-model="filtros.atividade" @change="filtrar()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Todas as atividades</option>
                    @foreach($atividades as $atividade)
                        <option value="{{ $atividade->id }}">{{ $atividade->descricao_atividade }}</option>
                    @endforeach
                </select>
            </div>

            <button @click="limparFiltros()" 
                    x-show="filtros.busca || filtros.status || filtros.atividade"
                    class="px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                Limpar filtros
            </button>
        </div>
    </div>

    {{-- Visualização: Mapa do Tocantins com Leaflet --}}
    @php
        // Preparar dados das cidades para o mapa
        $cidadesEquipamentos = [];
        foreach($estabelecimentos as $est) {
            $municipio = $est->cidade ?? 'Sem município';
            $municipio = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', $municipio);
            $municipio = trim($municipio);
            
            if (empty($municipio)) {
                $municipio = 'Sem município';
            }
            
            if (!isset($cidadesEquipamentos[$municipio])) {
                $cidadesEquipamentos[$municipio] = [
                    'total' => 0,
                    'estabelecimentos' => 0
                ];
            }
            $cidadesEquipamentos[$municipio]['total'] += $est->equipamentos_count;
            $cidadesEquipamentos[$municipio]['estabelecimentos']++;
        }
        
        // Filtrar apenas cidades que têm equipamentos cadastrados
        $cidadesComEquipamentos = array_filter($cidadesEquipamentos, function($dados) {
            return $dados['total'] > 0;
        });
    @endphp
    <div x-show="visualizacao === 'mapa'" x-collapse class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Mapa Leaflet --}}
            <div class="lg:col-span-2">
                <div class="relative rounded-xl overflow-hidden border border-gray-200" style="height: 700px;">
                    {{-- Container do Mapa Leaflet --}}
                    <div id="mapa-tocantins" class="w-full h-full z-10"></div>
                    
                    {{-- Legenda sobreposta --}}
                    <div class="absolute bottom-4 left-4 bg-white rounded-lg shadow-lg p-4 border border-gray-200 z-[1000]">
                        <h4 class="font-semibold text-gray-900 text-sm mb-3">Equipamentos por Município</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded" style="background-color: #e5e7eb;"></div>
                                <span class="text-gray-600">Sem equipamentos</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded" style="background-color: #fef08a;"></div>
                                <span class="text-gray-600">1-2 equipamentos</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded" style="background-color: #fbbf24;"></div>
                                <span class="text-gray-600">3-5 equipamentos</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded" style="background-color: #f97316;"></div>
                                <span class="text-gray-600">6-10 equipamentos</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded" style="background-color: #dc2626;"></div>
                                <span class="text-gray-600">Mais de 10</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Lista de cidades --}}
            <div class="lg:col-span-1">
                <div class="bg-gray-50 rounded-xl p-4 h-full overflow-y-auto" style="max-height: 700px;">
                    <h3 class="font-bold text-gray-900 mb-4 sticky top-0 bg-gray-50 pb-2">
                        Cidades com Equipamentos
                        <span class="text-sm font-normal text-gray-500">(clique para ver detalhes)</span>
                    </h3>
                    <div class="space-y-2">
                        @if(count($cidadesComEquipamentos) > 0)
                        @foreach($cidadesComEquipamentos as $cidade => $dados)
                            <div class="bg-white rounded-lg p-3 border border-gray-200 hover:border-orange-300 hover:shadow-md transition-all cursor-pointer"
                                 @mouseenter="cidadeSelecionada = '{{ addslashes($cidade) }}'"
                                 @mouseleave="cidadeSelecionada = null"
                                 @click="abrirModalCidade('{{ addslashes($cidade) }}')">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 text-sm">{{ $cidade }}</h4>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $dados['estabelecimentos'] }} estabelecimento{{ $dados['estabelecimentos'] > 1 ? 's' : '' }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold text-white
                                            {{ $dados['total'] > 10 ? 'bg-red-600' : ($dados['total'] > 5 ? 'bg-orange-600' : 'bg-yellow-500') }}">
                                            {{ $dados['total'] }}
                                        </span>
                                        <span class="text-xs text-gray-500 mt-1">equip.</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Nenhuma cidade com equipamentos cadastrados</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Modal de Estabelecimentos da Cidade --}}
    <div x-show="modalCidade" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="fecharModalCidade()"></div>
            
            {{-- Modal Content --}}
            <div class="relative bg-white rounded-lg shadow-lg transform transition-all sm:max-w-2xl sm:w-full mx-4"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                {{-- Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 border-b border-blue-700 px-6 py-4 rounded-t-lg flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white" x-text="cidadeModal"></h3>
                            <p class="text-blue-100 text-sm mt-0.5">Estabelecimentos</p>
                        </div>
                    </div>
                    <button @click="fecharModalCidade()" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                {{-- Body --}}
                <div class="px-6 py-4 max-h-[50vh] overflow-y-auto bg-gray-50">
                    <template x-if="estabelecimentosCidade.length > 0">
                        <div class="space-y-3">
                            <template x-for="est in estabelecimentosCidade" :key="est.id">
                                <div class="bg-white rounded-lg p-4 border border-blue-100 hover:border-blue-300 hover:shadow-md transition-all">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-gray-900" x-text="est.nome"></h4>
                                            <p class="text-sm text-gray-600 mt-1" x-text="est.cnpj"></p>
                                            <div class="flex flex-wrap gap-1.5 mt-3">
                                                <template x-for="ativ in est.atividades" :key="ativ">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700" x-text="ativ"></span>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-center flex-shrink-0">
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-lg font-bold text-white bg-blue-600"
                                                  x-text="est.equipamentos">
                                            </span>
                                            <span class="text-xs text-gray-500 mt-1">itens</span>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
                                        <span class="text-xs text-gray-500 font-medium" x-text="'ID: ' + est.id"></span>
                                        <a :href="'{{ url('/admin/estabelecimentos') }}/' + est.id + '/equipamentos-radiacao'" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors">
                                            Ver
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="estabelecimentosCidade.length === 0">
                        <div class="text-center py-12">
                            <svg class="w-14 h-14 text-blue-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-gray-600 text-sm font-medium">Nenhum estabelecimento encontrado.</p>
                        </div>
                    </template>
                </div>
                
                {{-- Footer --}}
                <div class="bg-gray-50 px-5 py-2 rounded-b-lg border-t border-gray-200 flex justify-end">
                    <button @click="fecharModalCidade()" 
                            class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded transition-colors">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-show="visualizacao === 'tabela'" x-collapse>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estabelecimento
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            CNPJ
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Atividade(s) com Radiação
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Equipamentos
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($estabelecimentos as $estabelecimento)
                        <tr class="hover:bg-gray-50 transition-colors"
                            x-show="filtrarLinha({{ json_encode([
                                'id' => $estabelecimento->id,
                                'nome' => $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social,
                                'cnpj' => $estabelecimento->cnpj,
                                'tem_equipamentos' => $estabelecimento->equipamentos_count > 0,
                                'atividades_codigos' => $estabelecimento->atividades_radiacao->pluck('codigo_atividade')->map(fn($c) => preg_replace('/[^0-9]/', '', $c))->toArray()
                            ]) }})">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social }}
                                </div>
                                @if($estabelecimento->nome_fantasia && $estabelecimento->razao_social)
                                    <div class="text-xs text-gray-500">
                                        {{ $estabelecimento->razao_social }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $estabelecimento->cnpj ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $estabelecimento->cnpj) : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($estabelecimento->atividades_radiacao as $atividade)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                            {{ Str::limit($atividade->descricao_atividade, 30) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-lg font-semibold {{ $estabelecimento->equipamentos_count > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $estabelecimento->equipamentos_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($estabelecimento->equipamentos_count > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Cadastrado
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Pendente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.estabelecimentos.equipamentos-radiacao.index', $estabelecimento) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                                <p class="text-lg font-medium">Nenhum estabelecimento encontrado</p>
                                <p class="text-sm">Não há estabelecimentos com atividades que exijam equipamentos de imagem.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Contador de resultados filtrados --}}
    <div class="text-sm text-gray-500 text-center" x-show="visualizacao === 'tabela' && resultadosFiltrados !== null">
        Mostrando <span class="font-medium" x-text="resultadosFiltrados"></span> de {{ count($estabelecimentos) }} estabelecimentos
    </div>
</div>

<script>
function relatorioEquipamentosRadiacao() {
    return {
        visualizacao: 'tabela', // 'tabela' ou 'mapa'
        cidadeSelecionada: null,
        filtros: {
            busca: '',
            status: '',
            atividade: ''
        },
        resultadosFiltrados: null,
        
        // Mapa Leaflet
        mapa: null,
        geojsonLayer: null,
        mapaIniciado: false,
        
        // Modal
        modalCidade: false,
        cidadeModal: '',
        estabelecimentosCidade: [],
        
        // Dados dos estabelecimentos por cidade
        dadosEstabelecimentos: {!! json_encode($estabelecimentos->map(function($est) {
            $cidade = $est->cidade ?? 'Sem município';
            $cidade = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', $cidade);
            $cidade = trim($cidade) ?: 'Sem município';
            
            return [
                'id' => $est->id,
                'nome' => $est->nome_fantasia ?: $est->razao_social,
                'cnpj' => $est->cnpj ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $est->cnpj) : 'N/A',
                'cidade' => $cidade,
                'equipamentos' => $est->equipamentos_count,
                'atividades' => $est->atividades_radiacao->pluck('descricao')->toArray()
            ];
        })) !!},
        
        // Mapeamento de ID da atividade para código normalizado
        atividadesMap: {!! json_encode($atividades->mapWithKeys(fn($a) => [$a->id => preg_replace('/[^0-9]/', '', $a->codigo_atividade)])) !!},

        // Inicializar
        init() {
            this.$watch('visualizacao', (value) => {
                if (value === 'mapa') {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.initMapa();
                            if (this.mapa) {
                                this.mapa.invalidateSize();
                            }
                        }, 100);
                    });
                }
            });
        },
        
        // Modal de Cidade
        abrirModalCidade(cidade) {
            this.cidadeModal = cidade;
            this.estabelecimentosCidade = this.dadosEstabelecimentos.filter(est => est.cidade === cidade);
            this.modalCidade = true;
            document.body.style.overflow = 'hidden';
        },
        
        fecharModalCidade() {
            this.modalCidade = false;
            this.cidadeModal = '';
            this.estabelecimentosCidade = [];
            document.body.style.overflow = '';
        },

        filtrarLinha(item) {
            // Filtro de busca
            if (this.filtros.busca) {
                const busca = this.filtros.busca.toLowerCase();
                const nome = item.nome.toLowerCase();
                const cnpj = (item.cnpj || '').replace(/\D/g, '');
                const buscaLimpa = busca.replace(/\D/g, '');
                
                if (!nome.includes(busca) && !cnpj.includes(buscaLimpa)) {
                    return false;
                }
            }

            // Filtro de status
            if (this.filtros.status === 'com' && !item.tem_equipamentos) {
                return false;
            }
            if (this.filtros.status === 'sem' && item.tem_equipamentos) {
                return false;
            }

            // Filtro de atividade (compara código normalizado)
            if (this.filtros.atividade) {
                const codigoFiltro = this.atividadesMap[this.filtros.atividade];
                if (codigoFiltro && !item.atividades_codigos.includes(codigoFiltro)) {
                    return false;
                }
            }

            return true;
        },

        filtrar() {
            this.$nextTick(() => {
                const visibleRows = document.querySelectorAll('tbody tr[x-show]:not([style*="display: none"])');
                this.resultadosFiltrados = visibleRows.length;
            });
        },

        limparFiltros() {
            this.filtros.busca = '';
            this.filtros.status = '';
            this.filtros.atividade = '';
            this.resultadosFiltrados = null;
        },

        exportarExcel() {
            const params = new URLSearchParams(this.filtros);
            window.location.href = `{{ route('admin.relatorios.equipamentos-radiacao.export') }}?${params.toString()}`;
        },

        // Inicializar mapa Leaflet
        initMapa() {
            if (this.mapaIniciado) return;
            
            // Criar o mapa centrado no Tocantins
            this.mapa = L.map('mapa-tocantins', {
                center: [-10.2, -48.3],
                zoom: 6,
                minZoom: 6,
                maxZoom: 8  // Apenas 2 níveis de zoom (6, 7, 8)
            });

            // Camada base limpa (sem nomes de cidades) - CartoDB Positron sem labels
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://carto.com/">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 8
            }).addTo(this.mapa);

            // Dados dos municípios com equipamentos
            const dadosMunicipios = @json($cidadesEquipamentos);
            
            // Função para determinar cor baseado no número de equipamentos
            const getColor = (total) => {
                if (total === 0 || total === undefined) return '#e5e7eb';
                if (total <= 2) return '#fef08a';
                if (total <= 5) return '#fbbf24';
                if (total <= 10) return '#f97316';
                return '#dc2626';
            };

            // Função para normalizar nomes (remover acentos e converter para minúsculas)
            const normalizar = (str) => {
                if (!str) return '';
                return str.normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();
            };

            // Criar mapa normalizado dos dados
            const dadosMunicipiosNormalizados = {};
            Object.keys(dadosMunicipios).forEach(nome => {
                const nomeNormalizado = normalizar(nome);
                dadosMunicipiosNormalizados[nomeNormalizado] = {
                    ...dadosMunicipios[nome],
                    nomeOriginal: nome
                };
            });

            // Função para buscar dados pelo nome do município
            const buscarDados = (nomeMunicipio) => {
                const nomeNormalizado = normalizar(nomeMunicipio);
                return dadosMunicipiosNormalizados[nomeNormalizado] || { total: 0, estabelecimentos: 0, nomeOriginal: nomeMunicipio };
            };

            // Carregar GeoJSON dos municípios do Tocantins
            // Usando API do IBGE para nomes e TopoJSON convertido para GeoJSON
            const urlMunicipios = 'https://servicodados.ibge.gov.br/api/v1/localidades/estados/17/municipios';
            // GeoJSON oficial do IBGE via GitHub (mais confiável)
            const urlGeoJson = 'https://raw.githubusercontent.com/tbrugz/geodata-br/master/geojson/geojs-17-mun.json';

            // Layer para os labels dos municípios (não adiciona inicialmente)
            this.labelsLayer = L.layerGroup();

            Promise.all([
                fetch(urlMunicipios).then(r => r.json()),
                fetch(urlGeoJson).then(r => r.json())
            ])
            .then(([municipios, geoData]) => {
                // Criar mapa de código -> nome do município (da API oficial)
                const codigoParaNome = {};
                municipios.forEach(m => {
                    codigoParaNome[m.id] = m.nome;
                });

                // O GeoJSON do geodata-br já tem o nome em 'name', mas vamos usar o código IBGE para garantir
                geoData.features.forEach(feature => {
                    // O geodata-br usa 'id' como código do município
                    const codigo = feature.properties.id || feature.properties.codarea;
                    // Usa o nome da API do IBGE se disponível, senão usa o 'name' do GeoJSON
                    feature.properties.nome = codigoParaNome[codigo] || feature.properties.name || 'Desconhecido';
                });

                this.geojsonLayer = L.geoJSON(geoData, {
                    style: (feature) => {
                        const nomeMunicipio = feature.properties.nome;
                        const dados = buscarDados(nomeMunicipio);
                        return {
                            fillColor: getColor(dados.total),
                            weight: 1,
                            opacity: 1,
                            color: '#374151',
                            fillOpacity: 0.75
                        };
                    },
                    onEachFeature: (feature, layer) => {
                        const nomeMunicipio = feature.properties.nome;
                        const dados = buscarDados(nomeMunicipio);
                        
                        // Calcular centroide real do polígono usando Turf.js
                        let center;
                        try {
                            const centroid = turf.centroid(feature);
                            center = L.latLng(centroid.geometry.coordinates[1], centroid.geometry.coordinates[0]);
                        } catch (e) {
                            // Fallback para bounding box se houver erro
                            center = layer.getBounds().getCenter();
                        }
                        
                        // Criar label APENAS para municípios COM equipamentos
                        if (dados.total > 0) {
                            const self = this;
                            const label = L.marker(center, {
                                icon: L.divIcon({
                                    className: 'mapa-label-destaque mapa-label-clicavel',
                                    html: `<span data-cidade="${dados.nomeOriginal}">${nomeMunicipio}</span>`,
                                    iconSize: [100, 20],
                                    iconAnchor: [50, 10]
                                })
                            });
                            // Tornar o label clicável
                            label.on('click', () => {
                                self.abrirModalCidade(dados.nomeOriginal);
                            });
                            this.labelsLayer.addLayer(label);
                        }
                        
                        // Tooltip com mais detalhes
                        layer.bindTooltip(`
                            <div class="font-semibold text-base">${nomeMunicipio}</div>
                            <div class="mt-1">Equipamentos: <strong>${dados.total}</strong></div>
                            <div>Estabelecimentos: <strong>${dados.estabelecimentos}</strong></div>
                        `, { sticky: true, className: 'mapa-tooltip' });

                        // Eventos de hover
                        layer.on({
                            mouseover: (e) => {
                                const layer = e.target;
                                layer.setStyle({
                                    weight: 3,
                                    color: '#f97316',
                                    fillOpacity: 0.9
                                });
                                layer.bringToFront();
                            },
                            mouseout: (e) => {
                                this.geojsonLayer.resetStyle(e.target);
                            },
                            click: (e) => {
                                if (dados.total > 0) {
                                    // Usar o nome original do cadastro para abrir o modal
                                    this.abrirModalCidade(dados.nomeOriginal);
                                }
                            }
                        });
                    }
                }).addTo(this.mapa);

                // Ajustar zoom para mostrar todo o Tocantins
                this.mapa.fitBounds(this.geojsonLayer.getBounds());
                
                // Adicionar labels (apenas municípios com equipamentos)
                this.labelsLayer.addTo(this.mapa);
            })
            .catch(error => {
                console.error('Erro ao carregar GeoJSON:', error);
                // Fallback: mostrar mensagem de erro no mapa
                document.getElementById('mapa-tocantins').innerHTML = `
                    <div class="flex items-center justify-center h-full bg-gray-100">
                        <div class="text-center p-8">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-gray-600">Erro ao carregar o mapa.</p>
                            <p class="text-gray-500 text-sm mt-2">Por favor, tente novamente mais tarde.</p>
                        </div>
                    </div>
                `;
            });

            this.mapaIniciado = true;
        }
    }
}
</script>
@endsection

@push('styles')
{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>
<style>
    #mapa-tocantins {
        background: #f3f4f6;
    }
    .leaflet-container {
        font-family: inherit;
    }
    
    /* Tooltip personalizado */
    .mapa-tooltip {
        background: white !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
        padding: 10px 14px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        font-family: inherit !important;
    }
    .mapa-tooltip .leaflet-tooltip-content {
        margin: 0;
    }
    
    /* Labels dos municípios */
    .mapa-label {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .mapa-label span {
        display: block;
        text-align: center;
        font-size: 9px;
        font-weight: 500;
        color: #374151;
        text-shadow: 
            1px 1px 0 white,
            -1px -1px 0 white,
            1px -1px 0 white,
            -1px 1px 0 white,
            0 1px 0 white,
            0 -1px 0 white,
            1px 0 0 white,
            -1px 0 0 white;
        white-space: nowrap;
        pointer-events: none;
    }
    
    /* Labels destacados (municípios com equipamentos) */
    .mapa-label-destaque {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .mapa-label-destaque span {
        display: block;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        color: #b91c1c;
        text-shadow: 
            1px 1px 0 white,
            -1px -1px 0 white,
            1px -1px 0 white,
            -1px 1px 0 white,
            0 1px 0 white,
            0 -1px 0 white,
            1px 0 0 white,
            -1px 0 0 white;
        white-space: nowrap;
        pointer-events: none;
    }
    
    /* Labels clicáveis (municípios com equipamentos) */
    .mapa-label-clicavel {
        cursor: pointer !important;
        pointer-events: auto !important;
    }
    .mapa-label-clicavel span {
        pointer-events: auto !important;
        cursor: pointer !important;
    }
    .mapa-label-clicavel:hover span {
        color: #7c2d12 !important;
        text-decoration: underline;
    }
</style>
@endpush

@push('scripts')
{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
        crossorigin=""></script>
{{-- Turf.js para cálculos geométricos (centroide) --}}
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
@endpush
