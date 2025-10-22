@extends('layouts.admin')

@section('title', 'Detalhes do Processo')
@section('page-title', 'Detalhes do Processo')

@section('content')
<div class="max-w-8xl mx-auto" x-data="{ modalUpload: false, modalVisualizador: false, pdfUrl: '', modalEditarNome: false, documentoEditando: null, nomeEditando: '', modalDocumentoDigital: false }">
    {{-- Botão Voltar --}}
    <div class="mb-6">
        <a href="{{ route('admin.estabelecimentos.processos.index', $estabelecimento->id) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-800 mb-2">Erro ao enviar arquivo:</p>
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Card Superior: Dados do Estabelecimento e Processo --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Dados do Estabelecimento --}}
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Nome do Estabelecimento
                </h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-lg font-bold text-gray-900">{{ $estabelecimento->nome_fantasia ?? $estabelecimento->nome_razao_social }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">CNPJ</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $estabelecimento->cnpj_formatado }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Telefone(s)</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $estabelecimento->telefone_formatado ?? 'Não informado' }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Endereço</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $estabelecimento->endereco }}, {{ $estabelecimento->numero }}{{ $estabelecimento->complemento ? ', ' . $estabelecimento->complemento : '' }} - {{ $estabelecimento->bairro }}, {{ $estabelecimento->cidade }} - {{ $estabelecimento->estado }}</p>
                    </div>
                </div>
            </div>

            {{-- Dados do Processo --}}
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Dados do Processo
                </h2>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Tipo de Processo</label>
                            <p class="text-sm text-gray-900 font-medium mt-1">{{ $processo->tipo_nome }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Número do Processo</label>
                            <p class="text-sm text-gray-900 font-medium mt-1">{{ $processo->numero_processo }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Status</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                    @if($processo->status_cor === 'blue') bg-blue-100 text-blue-800
                                    @elseif($processo->status_cor === 'yellow') bg-yellow-100 text-yellow-800
                                    @elseif($processo->status_cor === 'orange') bg-orange-100 text-orange-800
                                    @elseif($processo->status_cor === 'green') bg-green-100 text-green-800
                                    @elseif($processo->status_cor === 'red') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $processo->status_nome }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Ano</label>
                            <p class="text-sm text-gray-900 font-medium mt-1">{{ $processo->ano }}</p>
                        </div>
                    </div>
                    @if($processo->observacoes)
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Observações</label>
                            <p class="text-sm text-gray-700 mt-1">{{ $processo->observacoes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Botão Acompanhar (placeholder) --}}
        <div class="mt-6 pt-6 border-t border-gray-200">
            <button class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Acompanhar
            </button>
            <span class="ml-3 text-sm text-gray-500">Nenhum usuário acompanhando</span>
        </div>
    </div>

    {{-- Duas Colunas: Menu/Ações (esquerda) e Documentos (direita) --}}
    <style>
        @media (max-width: 768px) {
            .processo-container {
                flex-direction: column !important;
            }
            .processo-menu {
                width: 100% !important;
                min-width: unset !important;
            }
        }
    </style>
    <div class="processo-container" style="display: flex; gap: 1.5rem;">
        {{-- Coluna Esquerda: Menus e Ações --}}
        <div class="processo-menu space-y-6" style="width: 25%; min-width: 280px;">
            {{-- Menu de Opções --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    Menu de Opções
                </h3>
                <div class="space-y-2">
                    <button @click="modalUpload = true" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Upload de Arquivos
                    </button>
                    <a href="{{ route('admin.documentos.create', ['processo_id' => $processo->id]) }}" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Criar Documento Digital
                    </a>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Ordem de Serviço
                    </button>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Alertas
                    </button>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Designar Responsável
                    </button>
                </div>
            </div>

            {{-- Ações do Processo --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Ações do Processo
                </h3>
                <div class="space-y-2">
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Processo na Íntegra
                    </button>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        Pastas Processo
                    </button>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Parar Processo
                    </button>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Arquivar Processo
                    </button>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Excluir Processo
                    </button>
                </div>
            </div>

            {{-- Ordens de Serviço --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <button class="w-full flex items-center justify-between text-left">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Ordens de Serviço
                    </h3>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            {{-- Alertas --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <button class="w-full flex items-center justify-between text-left">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Alertas
                    </h3>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Coluna Direita: Lista de Documentos/Arquivos --}}
        <div style="flex: 1;">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                {{-- Header da Lista de Documentos --}}
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Lista de Documentos/Arquivos
                        </h2>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Selecionar Múltiplos
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Tabs de Documentos --}}
                <div class="border-b border-gray-200">
                    <nav class="flex px-6" aria-label="Tabs">
                        <button class="px-4 py-3 text-sm font-medium text-blue-600 border-b-2 border-blue-600">
                            Todos
                            <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-600 rounded-full">{{ $processo->documentos->count() }}</span>
                        </button>
                    </nav>
                </div>

                {{-- Lista de Documentos --}}
                <div class="p-6">
                    @if($processo->documentos->isEmpty())
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-900 mb-1">Nenhum documento anexado</p>
                            <p class="text-sm text-gray-500 mb-4">Comece fazendo upload de arquivos para este processo</p>
                            <button @click="modalUpload = true" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Upload de Arquivos
                            </button>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($processo->documentos as $documento)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                                    <div @click="pdfUrl = '{{ route('admin.estabelecimentos.processos.visualizar', [$estabelecimento->id, $processo->id, $documento->id]) }}'; modalVisualizador = true" 
                                         class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer">
                                        {{-- Ícone do arquivo --}}
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                                <span class="text-2xl">📄</span>
                                            </div>
                                        </div>
                                        
                                        {{-- Informações do arquivo --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $documento->nome_original }}</p>
                                            <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-500">
                                                <span class="flex items-center gap-1 whitespace-nowrap">
                                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span class="truncate">{{ $documento->created_at->format('d/m/Y H:i') }}</span>
                                                </span>
                                                <span class="flex items-center gap-1 min-w-0">
                                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    <span class="truncate">{{ $documento->usuario->nome ?? 'Sistema' }}</span>
                                                </span>
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium whitespace-nowrap">
                                                    {{ $documento->tipo_usuario === 'interno' ? 'Interno' : 'Externo' }}
                                                </span>
                                                <span class="text-gray-400 whitespace-nowrap">{{ $documento->tamanho_formatado }}</span>
                                                <span class="px-2 py-0.5 bg-gray-200 text-gray-700 rounded-full font-medium whitespace-nowrap">
                                                    Arquivo Externo
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Ações --}}
                                    <div class="flex items-center gap-2 sm:flex-shrink-0">
                                        <button @click.stop="documentoEditando = {{ $documento->id }}; nomeEditando = '{{ $documento->nome_original }}'; modalEditarNome = true"
                                                class="p-2 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
                                                title="Editar nome">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <a href="{{ route('admin.estabelecimentos.processos.download', [$estabelecimento->id, $processo->id, $documento->id]) }}" 
                                           class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                           title="Download">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.estabelecimentos.processos.deleteArquivo', [$estabelecimento->id, $processo->id, $documento->id]) }}" 
                                              method="POST"
                                              onsubmit="return confirm('Tem certeza que deseja remover este arquivo?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Remover">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Upload --}}
    <template x-teleport="body">
        <div x-show="modalUpload" 
             x-cloak
             @keydown.escape.window="modalUpload = false"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Overlay --}}
            <div @click="modalUpload = false"
                 style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5);"></div>
            
            {{-- Modal Content --}}
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 500px; padding: 0 1rem;">
                <div class="bg-white rounded-xl shadow-2xl p-6" @click.stop>
                    {{-- Close Button --}}
                    <button @click="modalUpload = false"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- Header --}}
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Upload de Arquivo</h3>
                        <p class="text-sm text-gray-600 mt-1">Envie um arquivo PDF para este processo</p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('admin.estabelecimentos.processos.upload', [$estabelecimento->id, $processo->id]) }}" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- Upload de Arquivo --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Arquivo PDF <span class="text-red-500">*</span>
                            </label>
                            <input type="file" 
                                   name="arquivo" 
                                   accept=".pdf"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <p class="mt-1 text-xs text-gray-500">
                                Apenas arquivos PDF. Tamanho máximo: 10MB
                            </p>
                        </div>

                        {{-- Info --}}
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-xs text-blue-700">
                                    O arquivo será identificado como "Arquivo Externo" na lista de documentos.
                                </p>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    @click="modalUpload = false"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                Enviar Arquivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Visualização de PDF --}}
    <template x-teleport="body">
        <div x-show="modalVisualizador" 
             x-cloak
             @keydown.escape.window="modalVisualizador = false"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Overlay --}}
            <div @click="modalVisualizador = false"
                 style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.75);"></div>
            
            {{-- Modal Content --}}
            <div style="position: absolute; top: 2%; left: 2%; right: 2%; bottom: 2%; max-width: 1200px; margin: 0 auto;">
                <div class="bg-white rounded-xl shadow-2xl h-full flex flex-col" @click.stop>
                    {{-- Header --}}
                    <div class="flex items-center justify-between p-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Visualizar Documento</h3>
                        <button @click="modalVisualizador = false"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- PDF Viewer --}}
                    <div class="flex-1 overflow-hidden">
                        <iframe :src="pdfUrl" 
                                class="w-full h-full border-0"
                                style="min-height: 500px;">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Editar Nome --}}
    <template x-teleport="body">
        <div x-show="modalEditarNome" 
             x-cloak
             @keydown.escape.window="modalEditarNome = false"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Overlay --}}
            <div @click="modalEditarNome = false"
                 style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5);"></div>
            
            {{-- Modal Content --}}
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 500px; padding: 0 1rem;">
                <div class="bg-white rounded-xl shadow-2xl p-6" @click.stop>
                    {{-- Close Button --}}
                    <button @click="modalEditarNome = false"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- Header --}}
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Editar Nome do Arquivo</h3>
                        <p class="text-sm text-gray-600 mt-1">Altere o nome de exibição do arquivo</p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" :action="`{{ route('admin.estabelecimentos.processos.show', [$estabelecimento->id, $processo->id]) }}`.replace('/processos/{{ $processo->id }}', `/processos/{{ $processo->id }}/documentos/${documentoEditando}/nome`)">
                        @csrf
                        @method('PATCH')
                        
                        {{-- Nome do Arquivo --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nome do Arquivo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nome_original" 
                                   x-model="nomeEditando"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Ex: Relatório Anual 2025.pdf">
                            <p class="mt-1 text-xs text-gray-500">
                                Este é o nome que aparecerá na lista de documentos
                            </p>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    @click="modalEditarNome = false"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Criar Documento Digital --}}
    <template x-teleport="body">
        <div x-show="modalDocumentoDigital" 
             x-cloak
             @keydown.escape.window="modalDocumentoDigital = false"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Overlay --}}
            <div @click="modalDocumentoDigital = false"
                 style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5);"></div>
            
            {{-- Modal Content --}}
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 600px; padding: 0 1rem;">
                <div class="bg-white rounded-xl shadow-2xl p-6" @click.stop>
                    {{-- Close Button --}}
                    <button @click="modalDocumentoDigital = false"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- Header --}}
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Criar Documento Digital
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Selecione um modelo para gerar o documento</p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('admin.estabelecimentos.processos.gerarDocumento', [$estabelecimento->id, $processo->id]) }}">
                        @csrf
                        
                        {{-- Selecionar Modelo --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Modelo de Documento <span class="text-red-500">*</span>
                            </label>
                            <select name="modelo_documento_id" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Selecione um modelo</option>
                                @foreach($modelosDocumento as $modelo)
                                    <option value="{{ $modelo->id }}">
                                        {{ $modelo->tipoDocumento->nome }}
                                        @if($modelo->descricao)
                                            - {{ Str::limit($modelo->descricao, 40) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                O documento será gerado em PDF e adicionado à lista de arquivos
                            </p>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    @click="modalDocumentoDigital = false"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                Gerar Documento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
