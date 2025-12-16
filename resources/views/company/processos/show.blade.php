@extends('layouts.company')

@section('title', 'Detalhes do Processo')
@section('page-title', 'Detalhes do Processo')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <a href="{{ route('company.processos.index') }}" class="text-sm text-blue-600 hover:text-blue-700 flex items-center mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar para lista
            </a>
            <h1 class="text-xl font-bold text-gray-900">Processo {{ $processo->numero }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $processo->tipoProcesso->nome ?? 'Tipo não definido' }}</p>
        </div>
        <span class="px-3 py-1.5 text-sm font-medium rounded-full 
            @if($processo->status === 'concluido') bg-green-100 text-green-800
            @elseif($processo->status === 'em_andamento') bg-blue-100 text-blue-800
            @elseif($processo->status === 'arquivado') bg-gray-100 text-gray-800
            @else bg-yellow-100 text-yellow-800 @endif">
            {{ str_replace('_', ' ', ucfirst($processo->status)) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Informações Principais --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Dados do Processo --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações do Processo</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Número</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $processo->numero }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Tipo</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $processo->tipoProcesso->nome ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Data de Abertura</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $processo->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Última Atualização</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $processo->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($processo->observacao)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-500">Observações</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $processo->observacao }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Documentos --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Documentos</h2>
                </div>
                
                @if($processo->documentos && $processo->documentos->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($processo->documentos as $documento)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $documento->nome ?? $documento->nome_original }}</p>
                                <p class="text-xs text-gray-500">{{ $documento->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-6 py-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhum documento anexado</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Estabelecimento --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Estabelecimento</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $processo->estabelecimento->nome_fantasia ?: $processo->estabelecimento->razao_social ?: $processo->estabelecimento->nome_completo }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ $processo->estabelecimento->documento_formatado }}</p>
                    </div>
                    <a href="{{ route('company.estabelecimentos.show', $processo->estabelecimento->id) }}" 
                       class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                        Ver estabelecimento
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Timeline/Histórico --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Histórico</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Processo criado</p>
                            <p class="text-xs text-gray-500">{{ $processo->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if($processo->status === 'concluido')
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Processo concluído</p>
                            <p class="text-xs text-gray-500">{{ $processo->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
