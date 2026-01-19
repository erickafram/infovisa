@extends('layouts.company')

@section('title', 'Dashboard')
@section('page-title', 'Visão Geral')

@section('content')
<div class="space-y-6">
    {{-- Header Section Compacto --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight flex items-center gap-2">
                Olá, {{ auth('externo')->user()->nome }}! 👋
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Painel de controle da empresa.
            </p>
        </div>
        <div class="flex items-center gap-2">
             <span class="text-xs font-medium text-gray-500 bg-white px-3 py-1.5 rounded-md border border-gray-200 shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ now()->format('d/m/Y') }}
             </span>
        </div>
    </div>

    {{-- Stats Grid Compacto --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $statCards = [
                [
                    'title' => 'Estabelecimentos',
                    'value' => $estatisticasEstabelecimentos['total'] ?? 0,
                    'color' => 'blue',
                    'route' => 'company.estabelecimentos.index',
                    'label' => 'Total'
                ],
                [
                    'title' => 'Pendentes',
                    'value' => $estatisticasEstabelecimentos['pendentes'] ?? 0,
                    'color' => 'amber',
                    'route' => 'company.estabelecimentos.index',
                    'label' => 'Análise'
                ],
                [
                    'title' => 'Processos',
                    'value' => $estatisticasProcessos['total'] ?? 0,
                    'color' => 'purple',
                    'route' => 'company.processos.index',
                    'label' => 'Abertos'
                ],
                [
                    'title' => 'Em Andamento',
                    'value' => $estatisticasProcessos['em_andamento'] ?? 0,
                    'color' => 'cyan',
                    'route' => 'company.processos.index',
                    'label' => 'Ativos'
                ]
            ];
        @endphp

        @foreach($statCards as $card)
            <a href="{{ route($card['route']) }}" 
               class="group relative bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md hover:border-{{ $card['color'] }}-200 transition-all duration-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $card['title'] }}</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <h3 class="text-2xl font-bold text-gray-800 group-hover:text-{{ $card['color'] }}-600 transition-colors">
                                {{ $card['value'] }}
                            </h3>
                            <span class="text-[10px] font-medium px-1.5 py-0.5 bg-{{ $card['color'] }}-50 text-{{ $card['color'] }}-600 rounded-full">
                                {{ $card['label'] }}
                            </span>
                        </div>
                    </div>
                    <div class="p-2 bg-{{ $card['color'] }}-50 rounded-lg text-{{ $card['color'] }}-600 group-hover:bg-{{ $card['color'] }}-100 transition-colors">
                        @if($card['color'] == 'blue') 
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        @elseif($card['color'] == 'amber')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @elseif($card['color'] == 'purple')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        @elseif($card['color'] == 'cyan')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Alertas e Documentos Pendentes --}}
    @if($alertasPendentes->count() > 0 || $documentosPendentesVisualizacao->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        {{-- Alertas Pendentes --}}
        @if($alertasPendentes->count() > 0)
        <div class="bg-white rounded-xl border border-orange-200 shadow-sm">
            <div class="px-4 py-3 border-b border-orange-100 bg-orange-50 rounded-t-xl flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-orange-800">Alertas Pendentes</h3>
                    <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                        {{ $alertasPendentes->count() }}
                    </span>
                </div>
                <a href="{{ route('company.alertas.index') }}" class="text-[10px] font-bold text-orange-600 hover:text-orange-700 bg-orange-100 hover:bg-orange-200 px-2 py-0.5 rounded transition-colors uppercase tracking-wide">
                    Ver todos
                </a>
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @foreach($alertasPendentes as $alerta)
                <div class="px-4 py-3 hover:bg-gray-50 transition-colors {{ $alerta->isVencido() ? 'bg-red-50' : ($alerta->isProximo() ? 'bg-yellow-50' : '') }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 line-clamp-2">{{ $alerta->descricao }}</p>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span class="text-[10px] text-gray-500 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $alerta->data_alerta->format('d/m/Y') }}
                                </span>
                                <a href="{{ route('company.processos.show', $alerta->processo_id) }}" class="text-[10px] text-blue-600 hover:underline truncate max-w-32">
                                    {{ $alerta->processo->estabelecimento->nome_fantasia ?? $alerta->processo->numero }}
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded
                                @if($alerta->isVencido()) bg-red-100 text-red-700
                                @elseif($alerta->isProximo()) bg-yellow-100 text-yellow-700
                                @else bg-blue-100 text-blue-700
                                @endif">
                                @if($alerta->isVencido()) Vencido
                                @elseif($alerta->isProximo()) Próximo
                                @else Pendente
                                @endif
                            </span>
                            <form action="{{ route('company.processos.alertas.concluir', [$alerta->processo_id, $alerta->id]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                    class="p-1 text-green-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors"
                                    title="Marcar como resolvido"
                                    onclick="return confirm('Confirma que este alerta foi resolvido?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Documentos Pendentes de Visualização --}}
        @if($documentosPendentesVisualizacao->count() > 0)
        <div class="bg-white rounded-xl border border-red-200 shadow-sm">
            <div class="px-4 py-3 border-b border-red-100 bg-red-50 rounded-t-xl flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-red-800">Documentos no Processo</h3>
                    <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                        {{ $documentosPendentesVisualizacao->count() }}
                    </span>
                </div>
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @foreach($documentosPendentesVisualizacao as $documento)
                <div class="px-4 py-3 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $documento->tipoDocumento->nome ?? 'Documento' }}
                            </p>
                            <p class="text-[10px] text-gray-500 mt-0.5">
                                Nº {{ $documento->numero_documento }}
                            </p>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span class="text-[10px] text-gray-500 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $documento->created_at->format('d/m/Y') }}
                                </span>
                                <span class="text-[10px] text-gray-500 truncate max-w-32">
                                    {{ $documento->processo->estabelecimento->nome_fantasia ?? $documento->processo->numero }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('company.processos.documento-digital.visualizar', [$documento->processo_id, $documento->id]) }}" 
                           target="_blank"
                           class="flex items-center gap-1 px-2 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Visualizar
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
    @endif

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        {{-- SECTION 1: MAIN LISTS --}}
        <div class="xl:col-span-2 space-y-6">
            
            {{-- Meus Estabelecimentos --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                         <h3 class="text-sm font-semibold text-gray-800">Meus Estabelecimentos</h3>
                    </div>
                    <a href="{{ route('company.estabelecimentos.index') }}" class="text-[10px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-2 py-0.5 rounded transition-colors uppercase tracking-wide">
                        Ver todos
                    </a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($ultimosEstabelecimentos as $estabelecimento)
                    <div class="group flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 transition-colors">
                        <div class="flex-1 min-w-0 pr-4">
                            <h4 class="text-sm font-medium text-gray-900 truncate">
                                <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" class="hover:underline">
                                    {{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: $estabelecimento->nome_completo ?: 'Sem Nome' }}
                                </a>
                            </h4>
                            <p class="text-[10px] text-gray-500 mt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                {{ $estabelecimento->documento_formatado }}
                            </p>
                        </div>
                        <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold rounded 
                            @if($estabelecimento->status === 'aprovado') bg-green-50 text-green-700
                            @elseif($estabelecimento->status === 'pendente') bg-amber-50 text-amber-700
                            @else bg-red-50 text-red-700 @endif">
                            {{ ucfirst($estabelecimento->status) }}
                        </span>
                        <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" class="hidden group-hover:block ml-2 text-gray-400 hover:text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    @empty
                    <div class="px-4 py-6 text-center">
                        <span class="text-xs text-gray-400">Nenhum estabelecimento.</span>
                        <div class="mt-2">
                            <a href="{{ route('company.estabelecimentos.create') }}" class="text-xs font-bold text-blue-600 hover:underline">
                                Cadastrar Agora
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- SECTION 2: SIDEBAR (Compact Actions) --}}
        <div class="space-y-6">
            
            {{-- Quick Actions - Strip Style --}}
            <div class="bg-blue-600 rounded-xl shadow-lg p-4 text-white overflow-hidden relative">
                <div class="relative z-10">
                    <h3 class="text-sm font-bold mb-3">Acesso Rápido</h3>
                    
                    <div class="space-y-2">
                        <a href="{{ route('company.estabelecimentos.create') }}" class="flex items-center w-full bg-white/10 hover:bg-white/20 border border-white/10 px-3 py-2 rounded-lg transition-all">
                            <div class="w-6 h-6 rounded bg-white/20 flex items-center justify-center text-white mr-2.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <span class="text-xs font-semibold">Novo Estabelecimento</span>
                        </a>

                        <a href="{{ route('company.estabelecimentos.index') }}" class="flex items-center w-full bg-white/10 hover:bg-white/20 border border-white/10 px-3 py-2 rounded-lg transition-all">
                            <div class="w-6 h-6 rounded bg-white/20 flex items-center justify-center text-white mr-2.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <span class="text-xs font-semibold">Meus Estabelecimentos</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Info/Tips Card Compact --}}
            <div class="bg-amber-50 rounded-xl border border-amber-100 p-4">
                <div class="flex gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <h4 class="text-xs font-bold text-amber-800">Dica:</h4>
                        <p class="text-[10px] text-amber-700 mt-1 leading-snug">
                            Mantenha seus dados cadastrais atualizados para evitar pendências.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
