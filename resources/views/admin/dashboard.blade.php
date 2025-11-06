@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Mensagem de boas-vindas --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl shadow-sm p-8 border border-blue-100/50 hover:shadow transition-all duration-200">
        <h2 class="text-2xl font-semibold text-gray-800">
            Olá, {{ auth('interno')->user()->nome }}! 👋
        </h2>
        <p class="mt-2 text-sm text-gray-600">
            Bem-vindo ao painel administrativo do InfoVISA
        </p>
    </div>

    {{-- Grid de 4 Cards Principais --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- CARD 1: Minhas Ordens de Serviço --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-all duration-200 border border-gray-100 hover:border-gray-200">
            {{-- Header --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/80 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Minhas OS</h3>
                        <p class="text-xs text-gray-600">{{ $stats['ordens_servico_andamento'] ?? 0 }} em andamento</p>
                    </div>
                </div>
            </div>
            
            {{-- Lista de OSs --}}
            <div class="flex-1 overflow-y-auto max-h-80">
                @if(isset($ordens_servico_andamento) && $ordens_servico_andamento->count() > 0)
                <div class="divide-y divide-gray-50">
                    @foreach($ordens_servico_andamento as $os)
                    @php
                        $prazoVencido = $os->data_fim && $os->data_fim->isPast();
                        $prazoUrgente = $os->data_fim && !$prazoVencido && $os->data_fim->diffInDays(now()) <= 3;
                    @endphp
                    
                    <a href="{{ route('admin.ordens-servico.show', $os) }}" class="block px-5 py-3.5 hover:bg-blue-50/50 transition-colors">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                {{-- Número e Status --}}
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold text-blue-600">#{{ $os->numero }}</span>
                                    @if($prazoVencido)
                                        <span class="px-2 py-0.5 bg-red-50 text-red-600 text-xs font-medium rounded-full">Vencido</span>
                                    @elseif($prazoUrgente)
                                        <span class="px-2 py-0.5 bg-orange-50 text-orange-600 text-xs font-medium rounded-full">Urgente</span>
                                    @endif
                                </div>
                                
                                {{-- Estabelecimento --}}
                                @if($os->estabelecimento)
                                <p class="text-xs text-gray-700 font-medium truncate mb-1.5">
                                    {{ $os->estabelecimento->nome_fantasia }}
                                </p>
                                @endif
                                
                                {{-- Informações --}}
                                <div class="flex items-center gap-2.5 text-xs text-gray-500">
                                    @if($os->municipio)
                                    <span class="flex items-center gap-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        {{ $os->municipio->nome }}
                                    </span>
                                    @endif
                                    @if($os->data_fim)
                                    <span class="flex items-center gap-1 {{ $prazoVencido ? 'text-red-600 font-medium' : ($prazoUrgente ? 'text-orange-600 font-medium' : '') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $os->data_fim->format('d/m') }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Ícone de seta --}}
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="p-8 text-center bg-white/50 rounded-lg m-2">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm text-gray-500">Nenhuma OS atribuída</p>
                </div>
                @endif
            </div>
            
            {{-- Footer --}}
            <div class="px-5 py-3.5 bg-gray-50/50">
                <a href="{{ route('admin.ordens-servico.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 flex items-center justify-center gap-1.5 transition-colors">
                    Ver todas
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- CARD 2: Processos Acompanhados --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-all duration-200 border border-gray-100 hover:border-gray-200">
            {{-- Header --}}
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/80 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Processos</h3>
                        <p class="text-xs text-gray-600">{{ $processos_acompanhados->count() ?? 0 }} acompanhando</p>
                    </div>
                </div>
            </div>
            
            {{-- Lista de Processos --}}
            <div class="flex-1 overflow-y-auto max-h-80">
                @if(isset($processos_acompanhados) && $processos_acompanhados->count() > 0)
                <div class="divide-y divide-gray-50">
                    @foreach($processos_acompanhados as $processo)
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}" class="block px-5 py-3.5 hover:bg-purple-50/50 transition-colors">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                {{-- Número --}}
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold text-purple-600">{{ $processo->numero_processo }}</span>
                                </div>
                                
                                {{-- Estabelecimento --}}
                                <p class="text-xs text-gray-700 font-medium truncate mb-1.5">
                                    {{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}
                                </p>
                                
                                {{-- Informações --}}
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span>{{ $processo->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            
                            {{-- Ícone de seta --}}
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="p-8 text-center bg-white/50 rounded-lg m-2">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Nenhum processo acompanhado</p>
                </div>
                @endif
            </div>
            
            {{-- Footer --}}
            <div class="px-5 py-3.5 bg-gray-50/50">
                <a href="{{ route('admin.processos.index-geral') }}" class="text-xs font-medium text-purple-600 hover:text-purple-700 flex items-center justify-center gap-1.5 transition-colors">
                    Ver todos
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- CARD 3: Assinaturas Pendentes --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-all duration-200 border border-gray-100 hover:border-gray-200">
            {{-- Header --}}
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/80 rounded-lg">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Assinaturas</h3>
                        <p class="text-xs text-gray-600">{{ $stats['documentos_pendentes_assinatura'] ?? 0 }} pendentes</p>
                    </div>
                </div>
            </div>
            
            {{-- Lista de Documentos --}}
            <div class="flex-1 overflow-y-auto max-h-80">
                @if(isset($documentos_pendentes_assinatura) && $documentos_pendentes_assinatura->count() > 0)
                <div class="divide-y divide-gray-50">
                    @foreach($documentos_pendentes_assinatura as $assinatura)
                    <div class="px-5 py-3.5 hover:bg-amber-50/50 transition-colors">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                {{-- Tipo de Documento --}}
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold text-amber-600">
                                        {{ $assinatura->documentoDigital->tipoDocumento->nome ?? 'Documento' }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-xs font-medium rounded-full">Pendente</span>
                                </div>
                                
                                {{-- Processo --}}
                                @if($assinatura->documentoDigital->processo)
                                <p class="text-xs text-gray-700 font-medium truncate mb-1.5">
                                    Processo {{ $assinatura->documentoDigital->processo->numero_processo }}
                                </p>
                                @endif
                                
                                {{-- Informações --}}
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span>{{ $assinatura->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            
                            {{-- Botão Assinar --}}
                            <a href="{{ route('admin.assinatura.assinar', $assinatura->documentoDigital->id) }}" 
                               class="flex-shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-lg transition-colors">
                                Assinar
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-8 text-center bg-white/50 rounded-lg m-2">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Nenhuma assinatura pendente</p>
                </div>
                @endif
            </div>
            
            {{-- Footer --}}
            <div class="px-5 py-3.5 bg-gray-50/50">
                <a href="{{ route('admin.assinatura.pendentes') }}" class="text-xs font-medium text-amber-600 hover:text-amber-700 flex items-center justify-center gap-1.5 transition-colors">
                    Ver todas
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- CARD 4: Processos Designados --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-all duration-200 border border-gray-100 hover:border-gray-200">
            {{-- Header --}}
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/80 rounded-lg">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Designações</h3>
                        <p class="text-xs text-gray-600">{{ $stats['processos_designados_pendentes'] ?? 0 }} pendentes</p>
                    </div>
                </div>
            </div>
            
            {{-- Lista de Designações --}}
            <div class="flex-1 overflow-y-auto max-h-80">
                @if(isset($processos_designados) && $processos_designados->count() > 0)
                <div class="divide-y divide-gray-50">
                    @foreach($processos_designados as $designacao)
                    <div class="px-5 py-3.5 hover:bg-emerald-50/50 transition-colors">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                {{-- Processo --}}
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold text-emerald-600">
                                        Proc. {{ $designacao->processo->numero_processo }}
                                    </span>
                                    @if($designacao->data_limite && $designacao->isAtrasada())
                                        <span class="px-2 py-0.5 bg-red-50 text-red-600 text-xs font-medium rounded-full">Atrasado</span>
                                    @elseif($designacao->data_limite && $designacao->isProximoDoPrazo())
                                        <span class="px-2 py-0.5 bg-orange-50 text-orange-600 text-xs font-medium rounded-full">Urgente</span>
                                    @endif
                                </div>
                                
                                {{-- Tarefa --}}
                                <p class="text-xs text-gray-700 font-medium truncate mb-1.5">
                                    {{ Str::limit($designacao->descricao_tarefa, 50) }}
                                </p>
                                
                                {{-- Informações --}}
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span>{{ $designacao->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            
                            {{-- Botão Ver --}}
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$designacao->processo->estabelecimento_id, $designacao->processo->id]) }}" 
                               class="flex-shrink-0 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition-colors">
                                Ver
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-8 text-center bg-white/50 rounded-lg m-2">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <p class="text-sm text-gray-500">Nenhuma designação pendente</p>
                </div>
                @endif
            </div>
            
            {{-- Footer --}}
            <div class="px-5 py-3.5 bg-gray-50/50">
                <a href="{{ route('admin.processos.index-geral') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 flex items-center justify-center gap-1.5 transition-colors">
                    Ver todos
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>


    {{-- Remover todos os outros cards antigos abaixo --}}
    @if(false) {{-- Desabilitar cards antigos --}}
    @if($stats['documentos_pendentes_assinatura'] > 0)
    <div class="bg-white border-l-4 border-yellow-400 rounded-lg shadow-sm">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">
                    Aguardando Assinatura
                    <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded" style="background-color: #fef3c7; color: #92400e;">{{ $stats['documentos_pendentes_assinatura'] }}</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Documentos que precisam da sua assinatura</p>
            </div>
            <a href="{{ route('admin.assinatura.pendentes') }}" 
               class="text-xs font-medium text-yellow-600 hover:text-yellow-700 transition-colors">
                Ver todos →
            </a>
        </div>
        
        @if($documentos_pendentes_assinatura->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($documentos_pendentes_assinatura as $assinatura)
            <div class="px-4 py-2.5 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $assinatura->documentoDigital->tipoDocumento->nome ?? 'Documento' }}
                            </p>
                            <span class="px-1.5 py-0.5 text-xs font-medium rounded" style="background-color: #fef3c7; color: #92400e;">
                                Pendente
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">
                            @if($assinatura->documentoDigital->processo)
                                Proc. {{ $assinatura->documentoDigital->processo->numero }}
                            @else
                                #{{ $assinatura->documentoDigital->id }}
                            @endif
                            <span class="mx-1">•</span>
                            {{ $assinatura->created_at->format('d/m/y') }}
                        </p>
                    </div>
                    <a href="{{ route('admin.assinatura.assinar', $assinatura->documentoDigital->id) }}" 
                       class="flex-shrink-0 px-3 py-1 text-xs font-medium text-white rounded transition-colors" style="background-color: #f59e0b;">
                        Assinar
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- Processos Designados para Você --}}
    @if($stats['processos_designados_pendentes'] > 0)
    <div class="bg-white border-l-4 border-purple-400 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 border-r border-t border-b border-gray-100">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">
                    Processos Designados para Você
                    <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded bg-purple-100 text-purple-800">{{ $stats['processos_designados_pendentes'] }}</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Processos que foram atribuídos para você resolver</p>
            </div>
        </div>
        
        @if($processos_designados->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($processos_designados as $designacao)
            <div class="px-4 py-3 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$designacao->processo->estabelecimento_id, $designacao->processo->id]) }}" 
                               class="text-sm font-semibold text-purple-600 hover:text-purple-800 transition-colors">
                                Processo #{{ $designacao->processo->numero_processo }}
                            </a>
                            <span class="px-1.5 py-0.5 text-xs font-medium rounded 
                                {{ $designacao->status === 'pendente' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $designacao->status === 'em_andamento' ? 'bg-blue-100 text-blue-800' : '' }}">
                                {{ $designacao->status === 'pendente' ? 'Pendente' : 'Em Andamento' }}
                            </span>
                            @if($designacao->data_limite)
                                @if($designacao->isAtrasada())
                                    <span class="px-1.5 py-0.5 text-xs font-bold rounded bg-red-100 text-red-800">
                                        ⚠️ Atrasado
                                    </span>
                                @elseif($designacao->isProximoDoPrazo())
                                    <span class="px-1.5 py-0.5 text-xs font-bold rounded bg-orange-100 text-orange-800">
                                        ⏰ Urgente
                                    </span>
                                @endif
                            @endif
                        </div>
                        <p class="text-xs text-gray-600 mb-1">
                            {{ $designacao->processo->estabelecimento->nome_fantasia }}
                        </p>
                        <p class="text-sm text-gray-700 mb-2">
                            <strong>Tarefa:</strong> {{ Str::limit($designacao->descricao_tarefa, 150) }}
                        </p>
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Designado por: {{ $designacao->usuarioDesignador->nome }}
                            </span>
                            @if($designacao->data_limite)
                                <span class="flex items-center gap-1 {{ $designacao->isAtrasada() ? 'text-red-600 font-semibold' : ($designacao->isProximoDoPrazo() ? 'text-orange-600 font-semibold' : '') }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Prazo: {{ $designacao->data_limite->format('d/m/Y') }}
                                </span>
                            @endif
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $designacao->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        @if($designacao->status === 'pendente')
                            <form action="{{ route('admin.estabelecimentos.processos.designacoes.atualizar', [$designacao->processo->estabelecimento_id, $designacao->processo->id, $designacao->id]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="em_andamento">
                                <button type="submit" class="w-full px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 transition-colors whitespace-nowrap">
                                    Iniciar
                                </button>
                            </form>
                        @elseif($designacao->status === 'em_andamento')
                            <form action="{{ route('admin.estabelecimentos.processos.designacoes.atualizar', [$designacao->processo->estabelecimento_id, $designacao->processo->id, $designacao->id]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="concluida">
                                <button type="submit" class="w-full px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 transition-colors whitespace-nowrap">
                                    Concluir
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.estabelecimentos.processos.show', [$designacao->processo->estabelecimento_id, $designacao->processo->id]) }}" 
                           class="px-3 py-1.5 text-xs font-medium text-purple-600 bg-purple-50 rounded hover:bg-purple-100 transition-colors text-center whitespace-nowrap">
                            Ver Processo
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- Documentos em Rascunho Pendentes de Finalização --}}
    @if($stats['documentos_rascunho_pendentes'] > 0)
    <div class="bg-white border-l-4 border-blue-400 rounded-lg shadow-sm">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">
                    Rascunhos Pendentes
                    <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded" style="background-color: #dbeafe; color: #1e40af;">{{ $stats['documentos_rascunho_pendentes'] }}</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Documentos em rascunho aguardando finalização</p>
            </div>
            <a href="{{ route('admin.documentos.index') }}" 
               class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                Ver todos →
            </a>
        </div>
        
        @if($documentos_rascunho_pendentes->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($documentos_rascunho_pendentes as $assinatura)
            <div class="px-4 py-2.5 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $assinatura->documentoDigital->tipoDocumento->nome ?? 'Documento' }}
                            </p>
                            <span class="px-1.5 py-0.5 text-xs font-medium rounded" style="background-color: #f3f4f6; color: #4b5563;">
                                Rascunho
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">
                            @if($assinatura->documentoDigital->processo)
                                Proc. {{ $assinatura->documentoDigital->processo->numero_processo ?? 'S/N' }}
                            @else
                                #{{ $assinatura->documentoDigital->id }}
                            @endif
                            <span class="mx-1">•</span>
                            {{ $assinatura->created_at->format('d/m/y') }}
                        </p>
                    </div>
                    <a href="{{ route('admin.documentos.edit', $assinatura->documentoDigital->id) }}" 
                       class="flex-shrink-0 px-3 py-1 text-xs font-medium text-white rounded transition-colors" style="background-color: #3b82f6;">
                        Editar
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- Lista de Estabelecimentos Pendentes --}}
    @if($estabelecimentos_pendentes->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200">
        <div class="px-4 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-base leading-6 font-semibold text-gray-900">
                Estabelecimentos Aguardando Aprovação
            </h3>
            <a href="{{ route('admin.estabelecimentos.pendentes') }}" 
               class="text-sm font-medium text-blue-600 hover:text-blue-700">
                Ver todos ({{ $stats['estabelecimentos_pendentes'] }}) →
            </a>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($estabelecimentos_pendentes as $estabelecimento)
            <div class="p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    {{-- Informações do Estabelecimento --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <h4 class="text-sm font-semibold text-gray-900 truncate">
                                {{ $estabelecimento->nome_razao_social }}
                            </h4>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-xs text-gray-600">
                            <div>
                                <span class="font-medium">Documento:</span>
                                {{ $estabelecimento->documento_formatado }}
                            </div>
                            <div>
                                <span class="font-medium">Município:</span>
                                {{ $estabelecimento->cidade }}/{{ $estabelecimento->estado }}
                            </div>
                            <div>
                                <span class="font-medium">Cadastrado:</span>
                                {{ $estabelecimento->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>

                    {{-- Ações --}}
                    <div class="flex gap-2">
                        <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Visualizar
                        </a>
                        <form action="{{ route('admin.estabelecimentos.aprovar', $estabelecimento->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Tem certeza que deseja aprovar este estabelecimento?')"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Aprovar
                            </button>
                        </form>
                        <button onclick="showRejectModal{{ $estabelecimento->id }}()"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Rejeitar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal de Rejeição --}}
            <div id="modal-rejeitar-{{ $estabelecimento->id }}" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white border-gray-100">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Rejeitar Estabelecimento</h3>
                            <button onclick="hideRejectModal{{ $estabelecimento->id }}()" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <form action="{{ route('admin.estabelecimentos.rejeitar', $estabelecimento->id) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Motivo da Rejeição *</label>
                                <textarea name="motivo_rejeicao" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                          placeholder="Descreva o motivo da rejeição..."></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" onclick="hideRejectModal{{ $estabelecimento->id }}()"
                                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                                    Cancelar
                                </button>
                                <button type="submit"
                                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    Rejeitar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                function showRejectModal{{ $estabelecimento->id }}() {
                    document.getElementById('modal-rejeitar-{{ $estabelecimento->id }}').classList.remove('hidden');
                }
                function hideRejectModal{{ $estabelecimento->id }}() {
                    document.getElementById('modal-rejeitar-{{ $estabelecimento->id }}').classList.add('hidden');
                }
            </script>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Processos Acompanhados --}}
    @if($processos_acompanhados->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200">
        <div class="px-4 py-4 border-b border-gray-200">
            <h3 class="text-base leading-6 font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Processos que Você Está Acompanhando
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Processo
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estabelecimento
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Atualizado
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($processos_acompanhados as $processo)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $processo->numero_processo }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">
                                {{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $processo->estabelecimento->documento_formatado }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $processo->tipo_nome }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($processo->status_cor === 'blue') bg-blue-100 text-blue-800
                                @elseif($processo->status_cor === 'yellow') bg-yellow-100 text-yellow-800
                                @elseif($processo->status_cor === 'orange') bg-orange-100 text-orange-800
                                @elseif($processo->status_cor === 'green') bg-green-100 text-green-800
                                @elseif($processo->status_cor === 'red') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $processo->status_nome }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $processo->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}"
                               class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Tabelas de Dados Recentes --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Usuários Externos Recentes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200">
            <div class="px-4 py-4">
                <h3 class="text-base leading-6 font-semibold text-gray-900 mb-3">
                    Usuários Externos Recentes
                </h3>
                <div class="flow-root">
                    <ul class="-my-3 divide-y divide-gray-200">
                        @forelse($usuarios_externos_recentes as $usuario)
                        <li class="py-3">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-medium text-xs">
                                            {{ substr($usuario->nome, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $usuario->nome }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $usuario->email }}
                                    </p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $usuario->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="py-3 text-center text-gray-500 text-xs">
                            Nenhum usuário externo cadastrado ainda.
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Usuários Internos Recentes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200">
            <div class="px-4 py-4">
                <h3 class="text-base leading-6 font-semibold text-gray-900 mb-3">
                    Usuários Internos Recentes
                </h3>
                <div class="flow-root">
                    <ul class="-my-3 divide-y divide-gray-200">
                        @forelse($usuarios_internos_recentes as $usuario)
                        <li class="py-3">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-purple-600 font-medium text-xs">
                                            {{ substr($usuario->nome, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $usuario->nome }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $usuario->nivel_acesso->label() }}
                                    </p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $usuario->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="py-3 text-center text-gray-500 text-xs">
                            Nenhum usuário interno cadastrado ainda.
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif {{-- Fim do @if(false) que desabilita cards antigos --}}
</div>
@endsection

