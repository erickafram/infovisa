@extends('layouts.admin')

@section('title', 'Detalhes da Lista de Documentos')
@section('page-title', 'Detalhes da Lista de Documentos')

@section('content')
<div class="max-w-8xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.configuracoes.listas-documento.index') }}" 
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <a href="{{ route('admin.configuracoes.listas-documento.edit', $lista) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
        </a>
    </div>

    {{-- Informações Básicas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $lista->nome }}</h2>
                @if($lista->descricao)
                <p class="text-sm text-gray-600 mt-1">{{ $lista->descricao }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-sm font-medium {{ $lista->escopo_cor }} rounded-full">
                    {{ $lista->escopo_label }}
                </span>
                @if($lista->ativo)
                <span class="px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">Ativo</span>
                @else
                <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-600 rounded-full">Inativo</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Tipo de Processo</label>
                <p class="text-sm text-gray-900 mt-1">
                    @if($lista->tipoProcesso)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-800 rounded-lg border border-indigo-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ $lista->tipoProcesso->nome }}
                    </span>
                    @else
                    <span class="text-gray-400">Não definido</span>
                    @endif
                </p>
            </div>
            @if($lista->municipio)
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Município</label>
                <p class="text-sm text-gray-900 mt-1">{{ $lista->municipio->nome }}</p>
            </div>
            @endif
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Criado por</label>
                <p class="text-sm text-gray-900 mt-1">{{ $lista->criadoPor->nome ?? 'Sistema' }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Criado em</label>
                <p class="text-sm text-gray-900 mt-1">{{ $lista->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Atualizado em</label>
                <p class="text-sm text-gray-900 mt-1">{{ $lista->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- Atividades Vinculadas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Atividades Vinculadas ({{ $lista->atividades->count() }})
        </h3>
        
        @if($lista->atividades->isEmpty())
        <p class="text-sm text-gray-500">Nenhuma atividade vinculada</p>
        @else
        <div class="flex flex-wrap gap-2">
            @foreach($lista->atividades as $atividade)
            <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-50 text-purple-800 text-sm rounded-lg border border-purple-200">
                <span class="font-medium">{{ $atividade->nome }}</span>
                @if($atividade->tipoServico)
                <span class="text-purple-500">({{ $atividade->tipoServico->nome }})</span>
                @endif
                @if($atividade->codigo_cnae)
                <span class="text-xs text-purple-400 font-mono">{{ $atividade->codigo_cnae }}</span>
                @endif
            </span>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Documentos Exigidos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Documentos Exigidos ({{ $lista->tiposDocumentoObrigatorio->count() }})
        </h3>
        
        @if($lista->tiposDocumentoObrigatorio->isEmpty())
        <p class="text-sm text-gray-500">Nenhum documento exigido</p>
        @else
        <div class="space-y-3">
            @foreach($lista->tiposDocumentoObrigatorio as $doc)
            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0 mt-0.5">
                    @if($doc->pivot->obrigatorio)
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-red-100 text-red-600 rounded-full" title="Obrigatório">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                    @else
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-gray-100 text-gray-500 rounded-full" title="Opcional">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900">{{ $doc->nome }}</span>
                        @if($doc->pivot->obrigatorio)
                        <span class="px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700 rounded">Obrigatório</span>
                        @else
                        <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded">Opcional</span>
                        @endif
                    </div>
                    @if($doc->descricao)
                    <p class="text-xs text-gray-500 mt-1">{{ $doc->descricao }}</p>
                    @endif
                    @if($doc->pivot->observacao)
                    <p class="text-xs text-blue-600 mt-1 italic">Obs: {{ $doc->pivot->observacao }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
