@extends('layouts.admin')

@section('title', 'Relatório de Estabelecimentos por CNAE')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <a href="{{ route('admin.relatorios.index') }}" class="hover:text-gray-700">Relatórios</a>
            <span>/</span>
            <span class="text-gray-900">Estabelecimentos por CNAE</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Estabelecimentos por CNAE</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $escopoVisual }}</p>
    </div>

    {{-- Cards resumo --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-xs text-gray-500 font-medium">Total Estabelecimentos</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($totais['estabelecimentos'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-xs text-gray-500 font-medium">CNAEs Distintos</p>
            <p class="text-xl font-bold text-cyan-600">{{ number_format($totais['cnaes'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-xs text-gray-500 font-medium">Comp. Estadual</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($totais['estadual'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-xs text-gray-500 font-medium">Comp. Municipal</p>
            <p class="text-xl font-bold text-emerald-600">{{ number_format($totais['municipal'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.relatorios.estabelecimentos-cnae') }}" class="flex flex-wrap items-end gap-3">
            @if(auth('interno')->user()->isAdmin())
                <div class="w-36">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Competência</label>
                    <select name="competencia" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                        <option value="">Todas</option>
                        <option value="estadual" @selected(request('competencia') === 'estadual')>Estadual</option>
                        <option value="municipal" @selected(request('competencia') === 'municipal')>Municipal</option>
                    </select>
                </div>
                <div class="w-44">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Município</label>
                    <select name="municipio_id" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                        <option value="">Todos</option>
                        @foreach($municipios as $municipio)
                            <option value="{{ $municipio->id }}" @selected((string) request('municipio_id') === (string) $municipio->id)>{{ $municipio->nome }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Buscar CNAE</label>
                <input type="text" name="busca_cnae" value="{{ request('busca_cnae') }}" placeholder="Código ou descrição..."
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div class="w-32">
                <label class="block text-xs font-medium text-gray-600 mb-1">CNAE exato</label>
                <input type="text" name="cnae" value="{{ request('cnae') }}" placeholder="4721102"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Buscar Estabelecimento</label>
                <input type="text" name="busca_estabelecimento" value="{{ request('busca_estabelecimento') }}" placeholder="Nome, razão social, CNPJ..."
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-3 py-1.5 bg-cyan-600 text-white text-sm rounded-lg hover:bg-cyan-700">Filtrar</button>
                <a href="{{ route('admin.relatorios.estabelecimentos-cnae') }}" class="px-3 py-1.5 bg-gray-100 text-gray-600 text-sm rounded-lg hover:bg-gray-200">Limpar</a>
            </div>
        </form>
    </div>

    {{-- CNAE selecionado --}}
    @if($cnaeSelecionado)
        <div class="flex items-center gap-2 text-sm">
            <span class="text-gray-500">Filtrando pelo CNAE:</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-cyan-100 text-cyan-800 rounded-full font-semibold text-xs">
                {{ preg_replace('/^(\d{4})(\d)(\d{2})$/', '$1-$2/$3', $cnaeSelecionado) }}
                <a href="{{ route('admin.relatorios.estabelecimentos-cnae', request()->except('cnae', 'page', 'est_page')) }}" class="hover:text-cyan-950">&times;</a>
            </span>
        </div>
    @endif

    {{-- Lista de estabelecimentos --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="divide-y divide-gray-100">
            @forelse($estabelecimentos as $estabelecimento)
                <div class="px-5 py-4 hover:bg-gray-50/60 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                        {{-- Nome e dados --}}
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" class="text-sm font-semibold text-cyan-700 hover:text-cyan-900 hover:underline">
                                {{ $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social }}
                            </a>
                            @if($estabelecimento->nome_fantasia && $estabelecimento->razao_social)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $estabelecimento->razao_social }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-xs text-gray-500">
                                @if($estabelecimento->cnpj)
                                    <span>CNPJ: {{ $estabelecimento->cnpj }}</span>
                                @elseif($estabelecimento->cpf)
                                    <span>CPF: {{ $estabelecimento->cpf }}</span>
                                @endif
                                <span>{{ $estabelecimento->municipio->nome ?? '-' }}</span>
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[11px] font-medium {{ $estabelecimento->escopo_competencia_cor }}">
                                    {{ $estabelecimento->escopo_competencia_label }}
                                </span>
                            </div>
                        </div>
                        {{-- CNAEs --}}
                        <div class="flex flex-wrap gap-1 sm:justify-end sm:max-w-[50%]">
                            @foreach($estabelecimento->cnaes_relatorio as $cnae)
                                <a href="{{ route('admin.relatorios.estabelecimentos-cnae', array_merge(request()->except('page', 'est_page'), ['cnae' => $cnae['codigo']])) }}"
                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] {{ $cnaeSelecionado === $cnae['codigo'] ? 'bg-cyan-100 text-cyan-800 font-semibold' : 'bg-gray-100 text-gray-600' }} hover:bg-cyan-100 hover:text-cyan-800 transition"
                                   title="{{ $cnae['descricao'] }}">
                                    {{ $cnae['codigo_formatado'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-sm text-gray-400">
                    Nenhum estabelecimento encontrado para os filtros informados.
                </div>
            @endforelse
        </div>

        @if($estabelecimentos->hasPages())
            <div class="px-5 py-3 border-t border-gray-200 bg-gray-50/50">
                {{ $estabelecimentos->links('pagination.tailwind-clean') }}
            </div>
        @endif
    </div>

    {{-- Resumo de CNAEs (colapsável) --}}
    <div x-data="{ aberto: false }" class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <button @click="aberto = !aberto" type="button" class="w-full flex items-center justify-between px-5 py-3 text-left hover:bg-gray-50 transition-colors">
            <span class="text-sm font-semibold text-gray-700">Resumo por CNAE ({{ $resumoCnaes->count() }})</span>
            <svg :class="aberto && 'rotate-180'" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="aberto" x-cloak class="border-t border-gray-100">
            <div class="divide-y divide-gray-50">
                @foreach($resumoCnaes as $item)
                    <a href="{{ route('admin.relatorios.estabelecimentos-cnae', array_merge(request()->except('page', 'est_page'), ['cnae' => $item['codigo']])) }}"
                       class="flex items-center justify-between px-5 py-2.5 hover:bg-gray-50 transition-colors {{ $cnaeSelecionado === $item['codigo'] ? 'bg-cyan-50' : '' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-xs font-mono font-semibold text-cyan-700 shrink-0">{{ $item['codigo_formatado'] }}</span>
                            <span class="text-sm text-gray-700 truncate">{{ $item['descricao'] }}</span>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 ml-4">
                            <span class="text-[11px] text-gray-400">{{ $item['competencias_label'] }}</span>
                            <span class="inline-flex items-center justify-center min-w-[28px] px-1.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ $item['total_estabelecimentos'] }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection