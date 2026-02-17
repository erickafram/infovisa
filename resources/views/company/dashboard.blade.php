@extends('layouts.company')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $temAlertas = $alertasPendentes->count() > 0 || 
                  $documentosPendentesVisualizacao->count() > 0 || 
                  $documentosRejeitados->count() > 0 || 
                  $documentosComPrazo->count() > 0;
    $totalAlertas = $alertasPendentes->count() + 
                    $documentosPendentesVisualizacao->count() + 
                    $documentosRejeitados->count() + 
                    $documentosComPrazo->count();
@endphp

<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Olá, {{ explode(' ', auth('externo')->user()->nome)[0] }}!</h1>
            <p class="text-[11px] text-gray-400">{{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM') }}</p>
        </div>
        @if($totalAlertas > 0)
        <a href="{{ route('company.alertas.index') }}" class="flex items-center gap-2 px-3 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 transition">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $totalAlertas }} {{ $totalAlertas == 1 ? 'pendência' : 'pendências' }}
        </a>
        @endif
    </div>

    {{-- Aviso Sistema Antigo --}}
    <div x-data="{ mostrarAviso: localStorage.getItem('ocultarAvisoInfovisa') !== 'true' }" x-show="mostrarAviso" x-cloak>
        <div class="flex items-start gap-3 p-3 rounded-lg border bg-amber-50 border-amber-200">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-amber-900">Processos até 2025 → <a href="https://sistemas.saude.to.gov.br/infovisa2/" target="_blank" class="text-blue-600 hover:underline font-bold">Acessar sistema antigo</a></p>
                <p class="text-xs mt-0.5 text-amber-700">Processos a partir de 2026 devem ser feitos neste novo sistema (InfoVISA 3.0)</p>
            </div>
            <button @click="localStorage.setItem('ocultarAvisoInfovisa', 'true'); mostrarAviso = false" class="p-1 text-amber-500 hover:text-amber-700 hover:bg-amber-100 rounded-full transition-all flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Documentos com prazo urgente (alerta vermelho no topo, como OSs vencidas no admin) --}}
    @if($documentosComPrazo->count() > 0)
    <div class="bg-white rounded-lg border border-red-200 shadow-sm">
        <div class="px-4 py-3 border-b border-red-100 bg-red-50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-red-900">Documentos com Prazo</h3>
                    <p class="text-xs text-red-600">Responda antes do vencimento</p>
                </div>
            </div>
            <span class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded-full font-bold">{{ $documentosComPrazo->count() }}</span>
        </div>
        <div class="divide-y divide-gray-50 max-h-[280px] overflow-y-auto scrollbar-thin">
            @foreach($documentosComPrazo as $documento)
            <a href="{{ route('company.processos.show', $documento->processo_id) }}" class="block px-4 py-3.5 hover:bg-red-50/50 transition">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <div class="w-8 h-8 rounded-lg {{ $documento->vencido ? 'bg-red-100' : 'bg-amber-100' }} flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 {{ $documento->vencido ? 'text-red-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ $documento->tipoDocumento->nome ?? 'Notificação' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $documento->processo->estabelecimento->nome_fantasia ?? $documento->processo->estabelecimento->razao_social ?? '' }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1.5 rounded-full flex-shrink-0 whitespace-nowrap {{ $documento->vencido ? 'bg-red-100 text-red-700' : ($documento->dias_faltando <= 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                        @if($documento->vencido)
                            Vencido {{ abs($documento->dias_faltando) }}d
                        @elseif($documento->dias_faltando == 0)
                            Hoje!
                        @elseif($documento->dias_faltando == 1)
                            Amanhã
                        @else
                            {{ $documento->dias_faltando }}d
                        @endif
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Stats Cards (3 cards como no admin) --}}
    <div id="tour-stats-cards" class="grid grid-cols-3 gap-3">
        {{-- Estabelecimentos --}}
        <a href="{{ route('company.estabelecimentos.index') }}" id="tour-meus-estabelecimentos" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md hover:border-blue-200 transition-all group">
            <div class="flex items-center gap-2 px-2.5 py-2">
                <div class="w-7 h-7 rounded-lg bg-blue-500 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Estabelecimentos</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight">{{ $estatisticasEstabelecimentos['total'] }}</p>
                </div>
            </div>
            <div class="px-2.5 py-1 bg-blue-50/80 border-t border-blue-100/60 flex items-center gap-2 text-[10px]">
                <span class="flex items-center gap-1 text-green-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="font-semibold">{{ $estatisticasEstabelecimentos['aprovados'] }}</span> aprovados
                </span>
                @if($estatisticasEstabelecimentos['pendentes'] > 0)
                <span class="text-gray-300">|</span>
                <span class="flex items-center gap-1 text-amber-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-semibold">{{ $estatisticasEstabelecimentos['pendentes'] }}</span> pendentes
                </span>
                @endif
            </div>
        </a>

        {{-- Processos --}}
        <a href="{{ route('company.processos.index') }}" id="tour-meus-processos" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md hover:border-purple-200 transition-all group">
            <div class="flex items-center gap-2 px-2.5 py-2">
                <div class="w-7 h-7 rounded-lg bg-purple-500 flex items-center justify-center flex-shrink-0 group-hover:bg-purple-600 transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Processos</p>
                    <p class="text-lg font-bold text-gray-900 leading-tight">{{ $estatisticasProcessos['total'] }}</p>
                </div>
            </div>
            <div class="px-2.5 py-1 bg-purple-50/80 border-t border-purple-100/60 flex items-center gap-2 text-[10px]">
                @if($estatisticasProcessos['em_andamento'] > 0)
                <span class="flex items-center gap-1 text-blue-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="font-semibold">{{ $estatisticasProcessos['em_andamento'] }}</span> em andamento
                </span>
                @endif
                @if($estatisticasProcessos['concluidos'] > 0)
                @if($estatisticasProcessos['em_andamento'] > 0)<span class="text-gray-300">|</span>@endif
                <span class="flex items-center gap-1 text-green-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="font-semibold">{{ $estatisticasProcessos['concluidos'] }}</span> concluídos
                </span>
                @endif
                @if($estatisticasProcessos['em_andamento'] == 0 && $estatisticasProcessos['concluidos'] == 0)
                <span class="text-gray-400">Nenhum processo ativo</span>
                @endif
            </div>
        </a>

        {{-- Pendências --}}
        <a href="{{ route('company.alertas.index') }}" id="tour-alertas" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden hover:shadow-md hover:border-amber-200 transition-all group {{ $totalAlertas > 0 ? 'border-amber-200' : '' }}">
            <div class="flex items-center gap-2 px-2.5 py-2">
                <div class="w-7 h-7 rounded-lg {{ $totalAlertas > 0 ? 'bg-amber-500' : 'bg-green-500' }} flex items-center justify-center flex-shrink-0 group-hover:opacity-90 transition">
                    @if($totalAlertas > 0)
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @else
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Pendências</p>
                    <p class="text-lg font-bold {{ $totalAlertas > 0 ? 'text-amber-600' : 'text-green-600' }} leading-tight">{{ $totalAlertas }}</p>
                </div>
            </div>
            <div class="px-2.5 py-1 {{ $totalAlertas > 0 ? 'bg-amber-50/80 border-t border-amber-100/60' : 'bg-green-50/80 border-t border-green-100/60' }} flex items-center gap-1 text-[10px] {{ $totalAlertas > 0 ? 'text-amber-600' : 'text-green-600' }}">
                @if($totalAlertas > 0)
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Requer sua atenção
                @else
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Tudo em dia!
                @endif
                <svg class="w-3 h-3 ml-auto text-gray-300 group-hover:text-gray-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>
    </div>

    {{-- Layout Principal em 3 colunas (igual admin) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Coluna 1: MEUS ESTABELECIMENTOS --}}
        <div id="tour-novo-cadastro" class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <h3 class="text-sm font-semibold text-gray-800">Meus Estabelecimentos</h3>
                    <span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded-full font-bold">{{ $estatisticasEstabelecimentos['total'] }}</span>
                </div>
                <a href="{{ route('company.estabelecimentos.index') }}" class="text-[11px] text-gray-400 hover:text-blue-600 transition">ver todos</a>
            </div>

            {{-- Botão Novo Cadastro --}}
            <a href="{{ route('company.estabelecimentos.create') }}" class="flex items-center gap-2.5 px-3 py-2.5 border-b border-gray-100 hover:bg-green-50/50 transition group">
                <div class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 group-hover:bg-green-200 transition">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-[13px] font-semibold text-green-700">Novo Estabelecimento</span>
                <svg class="w-3 h-3 ml-auto text-gray-300 group-hover:text-green-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

            {{-- Lista de Estabelecimentos --}}
            <div class="divide-y divide-gray-50 min-h-[120px] max-h-[350px] overflow-y-auto">
                @forelse($ultimosEstabelecimentos as $estabelecimento)
                <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" class="flex items-center gap-2.5 px-3 py-2 hover:bg-blue-50/50 transition">
                    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center flex-shrink-0">
                        <span class="text-blue-600 font-bold text-[10px]">{{ strtoupper(substr($estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: 'E', 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-gray-800 truncate">
                            {{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: $estabelecimento->nome_completo ?: 'Sem Nome' }}
                        </p>
                        <p class="text-[11px] text-gray-400 truncate">
                            {{ $estabelecimento->documento_formatado }}
                            @if($estabelecimento->municipio)
                                · {{ $estabelecimento->municipio->nome ?? '' }}
                            @endif
                        </p>
                    </div>
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full
                        @if($estabelecimento->status === 'aprovado') bg-green-100 text-green-700
                        @elseif($estabelecimento->status === 'pendente') bg-amber-100 text-amber-700
                        @else bg-red-100 text-red-700 @endif">
                        @if($estabelecimento->status === 'aprovado') Aprovado
                        @elseif($estabelecimento->status === 'pendente') Pendente
                        @else Rejeitado @endif
                    </span>
                </a>
                @empty
                <div class="p-6 text-center">
                    <svg class="w-8 h-8 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <p class="text-xs text-gray-400">Nenhum estabelecimento</p>
                    <a href="{{ route('company.estabelecimentos.create') }}" class="text-[11px] text-blue-600 font-semibold hover:underline mt-1 inline-block">Cadastrar agora</a>
                </div>
                @endforelse
            </div>

            @if($estatisticasEstabelecimentos['rejeitados'] > 0)
            <div class="px-3 py-2 border-t border-gray-100 bg-red-50/50">
                <a href="{{ route('company.estabelecimentos.index') }}" class="flex items-center gap-1.5 text-[11px] text-red-600 font-medium hover:text-red-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    {{ $estatisticasEstabelecimentos['rejeitados'] }} rejeitado(s) - clique para verificar
                </a>
            </div>
            @endif
        </div>

        {{-- Coluna 2: MEUS PROCESSOS --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <h3 class="text-sm font-semibold text-gray-800">Meus Processos</h3>
                    <span class="text-[10px] px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded-full font-bold">{{ $estatisticasProcessos['total'] }}</span>
                </div>
                <a href="{{ route('company.processos.index') }}" class="text-[11px] text-gray-400 hover:text-purple-600 transition">ver todos</a>
            </div>

            <div class="divide-y divide-gray-50 min-h-[120px] max-h-[350px] overflow-y-auto">
                @forelse($ultimosProcessos as $processo)
                <a href="{{ route('company.processos.show', $processo->id) }}" class="flex items-center gap-2.5 px-3 py-2 hover:bg-purple-50/50 transition">
                    <div class="w-6 h-6 rounded-md bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-gray-800 flex items-center gap-1">
                            <span>{{ $processo->numero_processo ?? 'Processo #'.$processo->id }}</span>
                            @if($processo->tipoProcesso)
                            <span class="text-[9px] px-1 py-0.5 rounded bg-gray-50 text-gray-400">{{ $processo->tipoProcesso->nome }}</span>
                            @endif
                        </p>
                        <p class="text-[11px] text-gray-400 truncate">{{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social ?? '-' }}</p>
                    </div>
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full
                        @if($processo->status === 'em_andamento' || $processo->status === 'aberto') bg-blue-100 text-blue-700
                        @elseif($processo->status === 'concluido') bg-green-100 text-green-700
                        @elseif($processo->status === 'arquivado') bg-gray-100 text-gray-600
                        @else bg-yellow-100 text-yellow-700 @endif">
                        @if($processo->status === 'em_andamento') Em andamento
                        @elseif($processo->status === 'aberto') Aberto
                        @elseif($processo->status === 'concluido') Concluído
                        @elseif($processo->status === 'arquivado') Arquivado
                        @else {{ ucfirst(str_replace('_', ' ', $processo->status)) }} @endif
                    </span>
                </a>
                @empty
                <div class="p-6 text-center">
                    <svg class="w-8 h-8 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-xs text-gray-400">Nenhum processo</p>
                    <p class="text-[11px] text-gray-300 mt-0.5">Processos aparecem aqui quando iniciados</p>
                </div>
                @endforelse
            </div>

            {{-- Resumo por status --}}
            @if($estatisticasProcessos['total'] > 0)
            <div class="px-3 py-2 border-t border-gray-100 bg-gray-50/50 flex items-center gap-3 text-[10px] flex-wrap">
                @if($estatisticasProcessos['em_andamento'] > 0)
                <span class="flex items-center gap-1 text-blue-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    {{ $estatisticasProcessos['em_andamento'] }} em andamento
                </span>
                @endif
                @if($estatisticasProcessos['concluidos'] > 0)
                <span class="flex items-center gap-1 text-green-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    {{ $estatisticasProcessos['concluidos'] }} concluídos
                </span>
                @endif
                @if($estatisticasProcessos['arquivados'] > 0)
                <span class="flex items-center gap-1 text-gray-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                    {{ $estatisticasProcessos['arquivados'] }} arquivados
                </span>
                @endif
            </div>
            @endif
        </div>

        {{-- Coluna 3: PENDÊNCIAS E AÇÕES --}}
        <div class="space-y-4">
            {{-- Documentos Rejeitados --}}
            @if($documentosRejeitados->count() > 0)
            <div id="tour-docs-rejeitados" class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-800">Docs Rejeitados</h3>
                        <span class="text-[10px] px-1.5 py-0.5 bg-red-100 text-red-700 rounded-full font-bold">{{ $documentosRejeitados->count() }}</span>
                    </div>
                </div>
                <div class="divide-y divide-gray-50 max-h-[160px] overflow-y-auto">
                    @foreach($documentosRejeitados->take(5) as $documento)
                    <a href="{{ route('company.processos.show', $documento->processo_id) }}" class="flex items-center gap-2.5 px-3 py-2 hover:bg-red-50/50 transition">
                        <div class="w-6 h-6 rounded-md bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-medium text-gray-800 truncate">{{ $documento->tipoDocumentoObrigatorio->nome ?? $documento->nome_original ?? 'Documento' }}</p>
                            <p class="text-[11px] text-red-500 truncate">{{ Str::limit($documento->motivo_rejeicao ?? 'Corrigir e reenviar', 40) }}</p>
                        </div>
                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-red-100 text-red-700">Corrigir</span>
                    </a>
                    @endforeach
                </div>
                @if($documentosRejeitados->count() > 5)
                <div class="px-3 py-1.5 border-t border-gray-100 text-center">
                    <a href="{{ route('company.alertas.index') }}" class="text-[11px] text-red-600 font-medium hover:underline">Ver todos os {{ $documentosRejeitados->count() }} →</a>
                </div>
                @endif
            </div>
            @endif

            {{-- Novos Documentos para Visualizar --}}
            @if($documentosPendentesVisualizacao->count() > 0)
            <div id="tour-novos-docs" class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-800">Novos Documentos</h3>
                        <span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded-full font-bold">{{ $documentosPendentesVisualizacao->count() }}</span>
                    </div>
                </div>
                <div class="divide-y divide-gray-50 max-h-[160px] overflow-y-auto">
                    @foreach($documentosPendentesVisualizacao->take(5) as $documento)
                    <a href="{{ route('company.processos.documento-digital.visualizar', [$documento->processo_id, $documento->id]) }}" target="_blank" class="flex items-center gap-2.5 px-3 py-2 hover:bg-blue-50/50 transition">
                        <div class="w-6 h-6 rounded-md bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-medium text-gray-800 truncate">{{ $documento->tipoDocumento->nome ?? 'Documento' }}</p>
                            <p class="text-[11px] text-gray-400">Nº {{ $documento->numero_documento }}</p>
                        </div>
                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700">Visualizar</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Alertas com Prazo --}}
            @if($alertasPendentes->count() > 0)
            <div id="tour-alertas-prazo" class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-800">Alertas</h3>
                        <span class="text-[10px] px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded-full font-bold">{{ $alertasPendentes->count() }}</span>
                    </div>
                    <a href="{{ route('company.alertas.index') }}" class="text-[11px] text-gray-400 hover:text-amber-600 transition">ver todos</a>
                </div>
                <div class="divide-y divide-gray-50 max-h-[160px] overflow-y-auto">
                    @foreach($alertasPendentes->take(5) as $alerta)
                    <a href="{{ route('company.alertas.index') }}" class="flex items-center gap-2.5 px-3 py-2 hover:bg-amber-50/50 transition {{ $alerta->isVencido() ? 'bg-red-50/50' : '' }}">
                        <div class="w-6 h-6 rounded-md {{ $alerta->isVencido() ? 'bg-red-100' : 'bg-amber-100' }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 {{ $alerta->isVencido() ? 'text-red-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-medium text-gray-800 truncate">{{ $alerta->descricao }}</p>
                            <p class="text-[11px] {{ $alerta->isVencido() ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                                <svg class="w-3 h-3 inline-block -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Prazo: {{ $alerta->data_alerta->format('d/m/Y') }}
                            </p>
                        </div>
                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full {{ $alerta->isVencido() ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $alerta->isVencido() ? 'Vencido' : $alerta->data_alerta->diffInDays(now()) . 'd' }}
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quando não há pendências na coluna 3 --}}
            @if(!$temAlertas)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-3 py-2 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <h3 class="text-sm font-semibold text-gray-800">Pendências</h3>
                </div>
                <div class="p-6 text-center">
                    <svg class="w-8 h-8 text-green-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-xs text-gray-400">Tudo em dia!</p>
                    <p class="text-[11px] text-gray-300 mt-0.5">Nenhuma pendência no momento</p>
                </div>
            </div>
            @endif

            {{-- Acesso Rápido --}}
            <div id="tour-estatisticas" class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-3 py-2 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <h3 class="text-sm font-semibold text-gray-800">Acesso Rápido</h3>
                </div>
                <div class="p-2 grid grid-cols-2 gap-1.5">
                    <a href="{{ route('company.estabelecimentos.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-green-50 border border-transparent hover:border-green-200 transition group">
                        <div class="w-6 h-6 bg-green-100 rounded-md flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span class="text-[11px] font-semibold text-gray-700 group-hover:text-green-700">Novo Cadastro</span>
                    </a>
                    <a href="{{ route('company.estabelecimentos.index') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-blue-50 border border-transparent hover:border-blue-200 transition group">
                        <div class="w-6 h-6 bg-blue-100 rounded-md flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span class="text-[11px] font-semibold text-gray-700 group-hover:text-blue-700">Estabelecimentos</span>
                    </a>
                    <a href="{{ route('company.processos.index') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-purple-50 border border-transparent hover:border-purple-200 transition group">
                        <div class="w-6 h-6 bg-purple-100 rounded-md flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="text-[11px] font-semibold text-gray-700 group-hover:text-purple-700">Processos</span>
                    </a>
                    <a href="{{ route('company.perfil.index') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 border border-transparent hover:border-gray-200 transition group">
                        <div class="w-6 h-6 bg-gray-100 rounded-md flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <span class="text-[11px] font-semibold text-gray-700 group-hover:text-gray-900">Meu Perfil</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
<x-assistente-ia-externo-chat />
@endsection
