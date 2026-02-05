@extends('layouts.company')

@section('title', 'Início')
@section('page-title', 'Página Inicial')

@section('content')
{{-- Tour Guiado para Novos Usuários --}}
<x-tour-guiado />

<div class="space-y-6">

    {{-- Aviso Importante - Sistema Antigo x Novo --}}
    <div x-data="{ mostrarAviso: localStorage.getItem('ocultarAvisoInfovisa') !== 'true' }" x-show="mostrarAviso" x-cloak>
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-4 shadow-sm relative">
            <button 
                @click="localStorage.setItem('ocultarAvisoInfovisa', 'true'); mostrarAviso = false"
                class="absolute top-3 right-3 p-1.5 text-amber-500 hover:text-amber-700 hover:bg-amber-100 rounded-full transition-all"
                title="Fechar aviso"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="flex items-center gap-3 pr-8">
                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-900">
                        📌 Processos até 2025 → <a href="https://sistemas.saude.to.gov.br/infovisa2/" target="_blank" class="text-blue-600 hover:underline font-bold">Clique aqui para acessar o sistema antigo</a>
                    </p>
                    <p class="text-xs text-amber-700 mt-0.5">Processos a partir de 2026 devem ser feitos neste novo sistema (InfoVISA 3.0)</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Saudação e Data --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-lg font-bold text-gray-800">
                Olá, {{ explode(' ', auth('externo')->user()->nome)[0] }}! 👋
            </h1>
            <p class="text-xs text-gray-500">Bem-vindo ao painel da sua empresa</p>
        </div>
        <div class="flex items-center gap-1.5 text-xs text-gray-500 bg-white px-3 py-1.5 rounded-lg border border-gray-200">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>{{ now()->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- =====================================================
         SEÇÃO: ATENÇÃO NECESSÁRIA (Alertas Urgentes)
         ===================================================== --}}
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

    @if($temAlertas)
    <div class="bg-gradient-to-r from-red-50 via-orange-50 to-amber-50 rounded-xl border border-red-200 p-3 shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center animate-pulse">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-red-800">⚠️ Atenção Necessária</h2>
                <p class="text-xs text-red-600">{{ $totalAlertas }} {{ $totalAlertas == 1 ? 'item pendente' : 'itens pendentes' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            
            {{-- 1. Documentos com Prazo (URGENTE) --}}
            @if($documentosComPrazo->count() > 0)
            <div class="bg-white rounded-lg border border-amber-300 overflow-hidden shadow-sm">
                <div class="bg-amber-500 px-3 py-1.5 flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-bold text-xs">Com Prazo</span>
                    </div>
                    <span class="bg-white text-amber-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                        {{ $documentosComPrazo->count() }}
                    </span>
                </div>
                <div class="divide-y divide-gray-100 max-h-32 overflow-y-auto">
                    @foreach($documentosComPrazo->take(2) as $documento)
                    <a href="{{ route('company.processos.show', $documento->processo_id) }}" 
                       class="flex items-center justify-between px-3 py-2 hover:bg-amber-50 transition-colors group">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-800 truncate">
                                {{ $documento->tipoDocumento->nome ?? 'Notificação' }}
                            </p>
                            <p class="text-[10px] {{ $documento->vencido ? 'text-red-600 font-bold' : 'text-amber-600' }}">
                                @if($documento->vencido)
                                    Vencido há {{ abs($documento->dias_faltando) }}d
                                @elseif($documento->dias_faltando == 0)
                                    Vence HOJE!
                                @elseif($documento->dias_faltando == 1)
                                    Vence amanhã
                                @else
                                    {{ $documento->dias_faltando }} dias
                                @endif
                            </p>
                        </div>
                        <div class="{{ $documento->vencido ? 'bg-red-500' : 'bg-amber-500' }} text-white px-2 py-1 rounded text-[10px] font-bold">
                            Ver
                        </div>
                    </a>
                    @endforeach
                </div>
                @if($documentosComPrazo->count() > 2)
                <div class="px-3 py-1.5 bg-amber-50 border-t border-amber-200">
                    <a href="{{ route('company.alertas.index') }}" class="text-[10px] font-bold text-amber-700">
                        Ver todos os {{ $documentosComPrazo->count() }} documentos →
                    </a>
                </div>
                @endif
            </div>
            @endif

            {{-- 2. Documentos Rejeitados (Precisa Corrigir) --}}
            @if($documentosRejeitados->count() > 0)
            <div class="bg-white rounded-lg border border-red-300 overflow-hidden shadow-sm">
                <div class="bg-red-500 px-3 py-1.5 flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="font-bold text-xs">Rejeitados</span>
                    </div>
                    <span class="bg-white text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                        {{ $documentosRejeitados->count() }}
                    </span>
                </div>
                <div class="divide-y divide-gray-100 max-h-32 overflow-y-auto">
                    @foreach($documentosRejeitados->take(2) as $documento)
                    <a href="{{ route('company.processos.show', $documento->processo_id) }}" 
                       class="flex items-center justify-between px-3 py-2 hover:bg-red-50 transition-colors group">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-800 truncate">
                                {{ $documento->tipoDocumentoObrigatorio->nome ?? $documento->nome_original ?? 'Documento' }}
                            </p>
                            <p class="text-[10px] text-red-600 truncate">
                                {{ Str::limit($documento->motivo_rejeicao ?? 'Corrigir', 30) }}
                            </p>
                        </div>
                        <div class="bg-red-500 text-white px-2 py-1 rounded text-[10px] font-bold">
                            Ver
                        </div>
                    </a>
                    @endforeach
                </div>
                @if($documentosRejeitados->count() > 2)
                <div class="px-3 py-1.5 bg-red-50 border-t border-red-200">
                    <a href="{{ route('company.alertas.index') }}" class="text-[10px] font-bold text-red-700">
                        +{{ $documentosRejeitados->count() - 2 }} mais →
                    </a>
                </div>
                @endif
            </div>
            @endif

            {{-- 3. Novos Documentos para Visualizar --}}
            @if($documentosPendentesVisualizacao->count() > 0)
            <div class="bg-white rounded-lg border border-blue-300 overflow-hidden shadow-sm">
                <div class="bg-blue-500 px-3 py-1.5 flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="font-bold text-xs">Novos Docs</span>
                    </div>
                    <span class="bg-white text-blue-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                        {{ $documentosPendentesVisualizacao->count() }}
                    </span>
                </div>
                <div class="divide-y divide-gray-100 max-h-32 overflow-y-auto">
                    @foreach($documentosPendentesVisualizacao->take(2) as $documento)
                    <a href="{{ route('company.processos.documento-digital.visualizar', [$documento->processo_id, $documento->id]) }}" 
                       target="_blank"
                       class="flex items-center justify-between px-3 py-2 hover:bg-blue-50 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-800 truncate">
                                {{ $documento->tipoDocumento->nome ?? 'Documento' }}
                            </p>
                            <p class="text-[10px] text-gray-500">
                                Nº {{ $documento->numero_documento }}
                            </p>
                        </div>
                        <div class="bg-blue-500 text-white px-2 py-1 rounded text-[10px] font-bold">
                            Ver
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 4. Alertas/Lembretes --}}
            @if($alertasPendentes->count() > 0)
            <div class="bg-white rounded-lg border border-orange-300 overflow-hidden shadow-sm">
                <div class="bg-orange-500 px-3 py-1.5 flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="font-bold text-xs">Lembretes</span>
                    </div>
                    <span class="bg-white text-orange-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                        {{ $alertasPendentes->count() }}
                    </span>
                </div>
                <div class="divide-y divide-gray-100 max-h-32 overflow-y-auto">
                    @foreach($alertasPendentes->take(2) as $alerta)
                    <div class="flex items-center justify-between px-3 py-2 hover:bg-orange-50 transition-colors {{ $alerta->isVencido() ? 'bg-red-50' : '' }}">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-800 truncate">{{ $alerta->descricao }}</p>
                            <p class="text-[10px] text-gray-500">
                                {{ $alerta->data_alerta->format('d/m') }}
                            </p>
                        </div>
                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded
                            {{ $alerta->isVencido() ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $alerta->isVencido() ? 'Vencido' : 'Pendente' }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @if($alertasPendentes->count() > 2)
                <div class="px-3 py-1.5 bg-orange-50 border-t border-orange-200">
                    <a href="{{ route('company.alertas.index') }}" class="text-[10px] font-bold text-orange-700">
                        +{{ $alertasPendentes->count() - 2 }} mais →
                    </a>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- =====================================================
         SEÇÃO: AÇÕES RÁPIDAS (O que você quer fazer?)
         ===================================================== --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3">
        <h2 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
            <span class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </span>
            Acesso Rápido
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            {{-- Botão 1: Novo Estabelecimento --}}
            <a href="{{ route('company.estabelecimentos.create') }}" 
               id="tour-novo-cadastro"
               class="group flex items-center gap-2 p-2.5 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg transition-all">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-green-800 block truncate">Novo Cadastro</span>
                    <span class="text-[10px] text-green-600">Estabelecimento</span>
                </div>
            </a>

            {{-- Botão 2: Ver Estabelecimentos --}}
            <a href="{{ route('company.estabelecimentos.index') }}" 
               id="tour-meus-estabelecimentos"
               class="group flex items-center gap-2 p-2.5 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-all">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-blue-800 block truncate">Estabelecimentos</span>
                    <span class="text-[10px] text-blue-600">{{ $estatisticasEstabelecimentos['total'] }} cadastrados</span>
                </div>
            </a>

            {{-- Botão 3: Ver Processos --}}
            <a href="{{ route('company.processos.index') }}" 
               id="tour-meus-processos"
               class="group flex items-center gap-2 p-2.5 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg transition-all">
                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-purple-800 block truncate">Processos</span>
                    <span class="text-[10px] text-purple-600">{{ $estatisticasProcessos['total'] }} abertos</span>
                </div>
            </a>

            {{-- Botão 4: Alertas/Pendências --}}
            <a href="{{ route('company.alertas.index') }}" 
               id="tour-alertas"
               class="group flex items-center gap-2 p-2.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg transition-all relative">
                @if($totalAlertas > 0)
                <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                    {{ $totalAlertas }}
                </div>
                @endif
                <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-amber-800 block truncate">Pendências</span>
                    <span class="text-[10px] text-amber-600">{{ $totalAlertas > 0 ? $totalAlertas . ' alertas' : 'Em dia ✓' }}</span>
                </div>
            </a>
        </div>
    </div>

    {{-- =====================================================
         SEÇÃO: RESUMO GERAL (Cards de Status)
         ===================================================== --}}
    <div id="tour-estatisticas" class="grid grid-cols-2 lg:grid-cols-4 gap-2">
        {{-- Card 1: Total Estabelecimentos --}}
        <div class="bg-white rounded-lg p-2 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[9px] font-medium text-gray-500 uppercase">Estabelecimentos</p>
                    <p class="text-lg font-bold text-gray-800">{{ $estatisticasEstabelecimentos['total'] }}</p>
                </div>
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-1 flex items-center gap-1 flex-wrap">
                <span class="text-[8px] font-medium px-1 py-0.5 rounded bg-green-100 text-green-700">{{ $estatisticasEstabelecimentos['aprovados'] }} aprov.</span>
                @if($estatisticasEstabelecimentos['pendentes'] > 0)
                <span class="text-[8px] font-medium px-1 py-0.5 rounded bg-amber-100 text-amber-700">{{ $estatisticasEstabelecimentos['pendentes'] }} pend.</span>
                @endif
            </div>
        </div>

        {{-- Card 2: Processos Ativos --}}
        <div class="bg-white rounded-lg p-2 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[9px] font-medium text-gray-500 uppercase">Processos</p>
                    <p class="text-lg font-bold text-gray-800">{{ $estatisticasProcessos['total'] }}</p>
                </div>
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-1 flex items-center gap-1 flex-wrap">
                @if($estatisticasProcessos['em_andamento'] > 0)
                <span class="text-[8px] font-medium px-1 py-0.5 rounded bg-blue-100 text-blue-700">{{ $estatisticasProcessos['em_andamento'] }} andamento</span>
                @endif
                @if($estatisticasProcessos['concluidos'] > 0)
                <span class="text-[8px] font-medium px-1 py-0.5 rounded bg-green-100 text-green-700">{{ $estatisticasProcessos['concluidos'] }} concl.</span>
                @endif
            </div>
        </div>

        {{-- Card 3: Pendências --}}
        <div class="bg-white rounded-lg p-2 border border-gray-200 shadow-sm {{ $totalAlertas > 0 ? 'border-amber-300' : '' }}">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[9px] font-medium text-gray-500 uppercase">Pendências</p>
                    <p class="text-lg font-bold {{ $totalAlertas > 0 ? 'text-amber-600' : 'text-green-600' }}">{{ $totalAlertas }}</p>
                </div>
                <div class="w-8 h-8 {{ $totalAlertas > 0 ? 'bg-amber-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center flex-shrink-0">
                    @if($totalAlertas > 0)
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    @else
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    @endif
                </div>
            </div>
            <div class="mt-1">
                @if($totalAlertas > 0)
                <span class="text-[8px] font-medium px-1 py-0.5 rounded bg-amber-100 text-amber-700">Requer atenção</span>
                @else
                <span class="text-[8px] font-medium px-1 py-0.5 rounded bg-green-100 text-green-700">✓ Em dia</span>
                @endif
            </div>
        </div>

        {{-- Card 4: Status Geral --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-2 shadow-sm text-white">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[9px] font-medium text-blue-100 uppercase">Situação</p>
                    <p class="text-xs font-bold mt-0.5">
                        @if($totalAlertas == 0 && $estatisticasEstabelecimentos['pendentes'] == 0)
                            Tudo certo! 🎉
                        @elseif($totalAlertas > 0)
                            Atenção ⚠️
                        @else
                            Em análise 📋
                        @endif
                    </p>
                </div>
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                    @if($totalAlertas == 0 && $estatisticasEstabelecimentos['pendentes'] == 0)
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @else
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @endif
                </div>
            </div>
            <div class="mt-1.5">
                <p class="text-[10px] text-blue-100">
                    @if($totalAlertas == 0 && $estatisticasEstabelecimentos['pendentes'] == 0)
                        Documentos em ordem
                    @elseif($totalAlertas > 0)
                        Verifique pendências
                    @else
                        Aguardando análise
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- =====================================================
         SEÇÃO: ÚLTIMOS ESTABELECIMENTOS
         ===================================================== --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-3 py-2.5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Meus Estabelecimentos
            </h3>
            <a href="{{ route('company.estabelecimentos.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                Ver todos
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        
        @if($ultimosEstabelecimentos->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($ultimosEstabelecimentos as $estabelecimento)
            <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" 
               class="flex items-center justify-between px-3 py-2.5 hover:bg-gray-50 transition-colors group">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-blue-600 font-bold text-xs">
                            {{ strtoupper(substr($estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: 'E', 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-800 truncate group-hover:text-blue-600">
                            {{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: $estabelecimento->nome_completo ?: 'Sem Nome' }}
                        </p>
                        <p class="text-[10px] text-gray-500 flex items-center gap-1">
                            <span>{{ $estabelecimento->documento_formatado }}</span>
                            @if($estabelecimento->municipio)
                            <span>•</span>
                            <span>{{ $estabelecimento->municipio->nome ?? '' }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-full
                        @if($estabelecimento->status === 'aprovado') bg-green-100 text-green-700
                        @elseif($estabelecimento->status === 'pendente') bg-amber-100 text-amber-700
                        @else bg-red-100 text-red-700 @endif">
                        @if($estabelecimento->status === 'aprovado') ✓ Aprovado
                        @elseif($estabelecimento->status === 'pendente') ⏳ Pendente
                        @else ✕ Rejeitado @endif
                    </span>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="px-3 py-8 text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h4 class="text-sm font-semibold text-gray-800 mb-1">Nenhum estabelecimento</h4>
            <p class="text-xs text-gray-500 mb-3">Cadastre seu primeiro estabelecimento</p>
            <a href="{{ route('company.estabelecimentos.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Cadastrar Agora
            </a>
        </div>
        @endif
    </div>

    {{-- =====================================================
         SEÇÃO: DICAS E AJUDA
         ===================================================== --}}
    <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 rounded-xl border border-blue-200 p-3">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs font-bold text-blue-800 mb-1">💡 Dica importante</h4>
                <p class="text-xs text-blue-700 leading-relaxed">
                    Mantenha seus dados atualizados e fique atento aos prazos das notificações. 
                    Documentos não respondidos podem gerar penalidades.
                </p>
                <div class="flex items-center gap-2 mt-2">
                    <a href="{{ route('company.perfil.index') }}" class="inline-flex items-center text-[10px] font-semibold text-blue-600 hover:text-blue-800 bg-white px-2 py-1 rounded-lg border border-blue-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Atualizar Perfil
                    </a>
                    <button onclick="localStorage.removeItem('infovisa_tour_visto'); location.reload();" 
                            class="inline-flex items-center text-[10px] font-semibold text-indigo-600 hover:text-indigo-800 bg-white px-2 py-1 rounded-lg border border-indigo-200">
                        <span class="mr-1">🤖</span>
                        Ver Tour Novamente
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
