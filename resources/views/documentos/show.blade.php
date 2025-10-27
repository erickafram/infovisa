@extends('layouts.admin')

@section('title', 'Visualizar Documento')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-4">
            <div class="flex items-center gap-2 text-xs text-gray-600 mb-2">
                @if($documento->processo)
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}" class="hover:text-blue-600 transition">
                        Processo {{ $documento->processo->numero_processo }}
                    </a>
                @else
                    <a href="{{ route('admin.documentos.index') }}" class="hover:text-blue-600 transition">
                        Documentos
                    </a>
                @endif
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium">{{ $documento->nome ?? $documento->tipoDocumento->nome }}</span>
            </div>
        </div>

        {{-- Documento --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            {{-- Cabeçalho do Documento --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h1 class="text-xl font-semibold mb-1">{{ $documento->nome ?? $documento->tipoDocumento->nome }}</h1>
                        <p class="text-blue-100 text-sm">{{ $documento->numero_documento }}</p>
                    </div>
                    <div class="flex gap-2">
                        @if($documento->status !== 'rascunho' && $documento->arquivo_pdf)
                            <a href="{{ route('admin.documentos.pdf', $documento->id) }}" 
                               class="px-3 py-1.5 text-sm bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Baixar PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Informações do Documento --}}
            <div class="p-5 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Processo --}}
                    @if($documento->processo)
                        <div>
                            <h3 class="text-xs font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Processo Vinculado
                            </h3>
                            <p class="text-sm text-gray-900 font-medium">{{ $documento->processo->numero_processo }}</p>
                            <p class="text-xs text-gray-600">{{ $documento->processo->estabelecimento->nome_fantasia ?? $documento->processo->estabelecimento->razao_social }}</p>
                        </div>
                    @endif

                    {{-- Tipo de Documento --}}
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Tipo de Documento
                        </h3>
                        <p class="text-sm text-gray-900 font-medium">{{ $documento->tipoDocumento->nome }}</p>
                    </div>

                    {{-- Criado por --}}
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Criado por
                        </h3>
                        <p class="text-sm text-gray-900 font-medium">{{ $documento->usuarioCriador->nome }}</p>
                        <p class="text-xs text-gray-600">{{ $documento->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    {{-- Status --}}
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Status
                        </h3>
                        @if($documento->status === 'rascunho')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                Rascunho
                            </span>
                        @elseif($documento->status === 'aguardando_assinatura')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Aguardando Assinatura
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Finalizado
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Conteúdo do Documento --}}
            <div class="p-5">
                <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Conteúdo
                </h2>
                <div class="prose prose-sm max-w-none">
                    {!! $documento->conteudo !!}
                </div>
            </div>

            {{-- Assinaturas --}}
            @if($documento->assinaturas->count() > 0)
                <div class="p-5 border-t border-gray-200 bg-gray-50">
                    <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Assinaturas Digitais
                    </h2>
                    <div class="space-y-2">
                        @foreach($documento->assinaturas as $assinatura)
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $assinatura->usuarioInterno->nome }}</p>
                                        <p class="text-xs text-gray-600">{{ $assinatura->usuarioInterno->cargo ?? 'Cargo não informado' }}</p>
                                    </div>
                                </div>
                                <div>
                                    @if($assinatura->status === 'assinado')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Assinado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Pendente
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Botões de Ação --}}
        <div class="mt-4 flex gap-2">
            @if($documento->processo)
                <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Voltar ao Processo
                </a>
            @else
                <a href="{{ route('admin.documentos.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Voltar
                </a>
            @endif

            @if($documento->status === 'rascunho')
                <a href="{{ route('admin.documentos.edit', $documento->id) }}" 
                   class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Editar Rascunho
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
