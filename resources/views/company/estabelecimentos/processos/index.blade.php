@extends('layouts.company')

@section('title', 'Processos do Estabelecimento')
@section('page-title', 'Processos do Estabelecimento')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" 
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Processos</h1>
                <p class="text-sm text-gray-500">{{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social }}</p>
            </div>
        </div>
        <a href="{{ route('company.estabelecimentos.processos.create', $estabelecimento->id) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Abrir Processo
        </a>
    </div>

    {{-- Grid de Processos --}}
    @if($estabelecimento->processos->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($estabelecimento->processos->sortByDesc('created_at') as $processo)
        <a href="{{ route('company.processos.show', $processo->id) }}" 
           class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md hover:border-blue-300 transition-all group">
            {{-- Status Badge --}}
            <div class="flex items-center justify-between mb-3">
                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full uppercase tracking-wide
                    @if($processo->status === 'concluido' || $processo->status === 'aprovado') bg-green-100 text-green-700
                    @elseif($processo->status === 'em_analise' || $processo->status === 'em_andamento') bg-blue-100 text-blue-700
                    @elseif($processo->status === 'aberto') bg-yellow-100 text-yellow-700
                    @elseif($processo->status === 'arquivado') bg-gray-100 text-gray-600
                    @elseif($processo->status === 'parado') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-700 @endif">
                    {{ $processo->status_nome ?? str_replace('_', ' ', ucfirst($processo->status)) }}
                </span>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
            
            {{-- Número do Processo --}}
            <p class="text-base font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                {{ $processo->numero_processo }}
            </p>
            
            {{-- Tipo --}}
            <p class="text-sm text-gray-600 mt-1">
                {{ $processo->tipoProcesso->nome ?? $processo->tipo_nome ?? 'N/A' }}
            </p>
            
            {{-- Data --}}
            <p class="text-xs text-gray-400 mt-3 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $processo->created_at->format('d/m/Y') }}
            </p>
        </a>
        @endforeach
    </div>
    @else
    {{-- Estado Vazio --}}
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900">Nenhum processo</h3>
        <p class="text-sm text-gray-500 mt-1 mb-6">Comece abrindo um novo processo para este estabelecimento.</p>
        <a href="{{ route('company.estabelecimentos.processos.create', $estabelecimento->id) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Abrir Primeiro Processo
        </a>
    </div>
    @endif
</div>
@endsection
