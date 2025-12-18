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

            {{-- Meus Processos --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                         <h3 class="text-sm font-semibold text-gray-800">Processos Recentes</h3>
                    </div>
                    <a href="{{ route('company.processos.index') }}" class="text-[10px] font-bold text-purple-600 hover:text-purple-700 bg-purple-50 hover:bg-purple-100 px-2 py-0.5 rounded transition-colors uppercase tracking-wide">
                        Ver todos
                    </a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($ultimosProcessos as $processo)
                    <div class="group flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 transition-colors">
                        <div class="flex-1 min-w-0 pr-4">
                            <h4 class="text-sm font-medium text-gray-900 truncate">
                                {{ $processo->numero }}
                            </h4>
                            <p class="text-[10px] text-gray-500 mt-0.5">
                                {{ $processo->tipoProcesso->nome ?? '-' }}
                            </p>
                        </div>
                        <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold rounded 
                            @if($processo->status === 'concluido') bg-green-50 text-green-700
                            @elseif($processo->status === 'em_andamento') bg-blue-50 text-blue-700
                            @elseif($processo->status === 'arquivado') bg-gray-50 text-gray-600
                            @else bg-gray-50 text-gray-600 @endif">
                            {{ str_replace('_', ' ', ucfirst($processo->status)) }}
                        </span>
                    </div>
                    @empty
                     <div class="px-4 py-6 text-center text-xs text-gray-400">
                        Nenhum processo recente.
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
                            <span class="text-xs font-semibold">Meus Negócios</span>
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
