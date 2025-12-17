@extends('layouts.company')

@section('title', 'Processos do Estabelecimento')
@section('page-title', 'Processos do Estabelecimento')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Processos</h2>
                <p class="text-sm text-gray-500">{{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social }}</p>
            </div>
        </div>
        <a href="{{ route('company.estabelecimentos.processos.create', $estabelecimento->id) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Abrir Processo
        </a>
    </div>

    {{-- Lista de Processos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($estabelecimento->processos->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($estabelecimento->processos as $processo)
            <a href="{{ route('company.processos.show', $processo->id) }}" class="block px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $processo->numero_processo }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $processo->tipoProcesso->nome ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-400 mt-1">Aberto em {{ $processo->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            @if($processo->status === 'concluido') bg-green-100 text-green-800
                            @elseif($processo->status === 'em_andamento') bg-blue-100 text-blue-800
                            @elseif($processo->status === 'aberto') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ str_replace('_', ' ', ucfirst($processo->status)) }}
                        </span>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-3 text-sm font-medium text-gray-900">Nenhum processo</h3>
            <p class="mt-1 text-sm text-gray-500">Comece abrindo um novo processo para este estabelecimento.</p>
            <div class="mt-4">
                <a href="{{ route('company.estabelecimentos.processos.create', $estabelecimento->id) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Abrir Primeiro Processo
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
