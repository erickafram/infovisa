@extends('layouts.admin')

@section('title', 'Detalhes do Processo')
@section('page-title', 'Detalhes do Processo')

@section('content')
<div class="max-w-8xl mx-auto" x-data="processoData()">
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

    {{-- Alerta de Processo Parado --}}
    @if($processo->status === 'parado')
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-red-800">⚠️ Processo Parado</h3>
                    <p class="text-xs text-red-700 mt-0.5"><strong>Motivo:</strong> {{ $processo->motivo_parada }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs text-red-600">
                <span>📅 Parado em: {{ $processo->data_parada->format('d/m/Y H:i') }}</span>
                @if($processo->usuarioParada)
                <span>👤 Por: {{ $processo->usuarioParada->nome }}</span>
                @endif
            </div>
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
                        <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" class="text-lg font-bold text-blue-600 hover:text-blue-800 hover:underline">{{ $estabelecimento->nome_fantasia ?? $estabelecimento->nome_razao_social }}</a>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $estabelecimento->documento_formatado }}</p>
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

        {{-- Botão Acompanhar --}}
        <div class="mt-6 pt-6 border-t border-gray-200">
            <form action="{{ route('admin.estabelecimentos.processos.toggleAcompanhamento', [$estabelecimento->id, $processo->id]) }}" method="POST" class="inline-block">
                @csrf
                @if($processo->estaAcompanhadoPor(Auth::guard('interno')->id()))
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                        Parar de Acompanhar
                    </button>
                @else
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Acompanhar Processo
                    </button>
                @endif
            </form>
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
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
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
                    @if($processo->status !== 'arquivado')
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
                    @endif
                    <button @click="modalOrdemServico = true" 
                            class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Ordem de Serviço
                    </button>
                    <button @click="modalAlertas = true" 
                            class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Alertas
                        @if($alertas->where('status', 'pendente')->count() > 0)
                        <span class="ml-auto px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                            {{ $alertas->where('status', 'pendente')->count() }}
                        </span>
                        @endif
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
                    <a href="{{ route('admin.estabelecimentos.processos.integra', [$estabelecimento->id, $processo->id]) }}" 
                       class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Processo na Íntegra
                    </a>
                    
                    @if($processo->status !== 'arquivado')
                    <button @click="modalPastas = true; carregarPastas()" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        Pastas Processo
                    </button>
                    
                    @if($processo->status === 'parado')
                    <form action="{{ route('admin.estabelecimentos.processos.reiniciar', [$estabelecimento->id, $processo->id]) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Reiniciar Processo
                        </button>
                    </form>
                    @else
                    <button @click="modalParar = true" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Parar Processo
                    </button>
                    @endif
                    @endif
                    @if($processo->status === 'arquivado')
                    <form action="{{ route('admin.estabelecimentos.processos.desarquivar', [$estabelecimento->id, $processo->id]) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Desarquivar Processo
                        </button>
                    </form>
                    @else
                    <button @click="modalArquivar = true" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Arquivar Processo
                    </button>
                    @endif
                               <button @click="modalHistorico = true" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Histórico
                    </button>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Excluir Processo
                    </button>
                </div>
            </div>

            {{-- Responsáveis Designados --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Responsáveis
                        @if($designacoes->where('status', 'pendente')->count() > 0)
                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-purple-100 text-purple-800">
                                {{ $designacoes->where('status', 'pendente')->count() }}
                            </span>
                        @endif
                    </h3>
                    <button @click="modalDesignar = true; carregarUsuarios()" 
                            class="text-xs font-medium text-purple-600 hover:text-purple-700 transition-colors">
                        + Designar
                    </button>
                </div>

                @if($designacoes->isEmpty())
                    <p class="text-xs text-gray-500 text-center py-3">Nenhum responsável designado</p>
                @else
                    <div class="space-y-3">
                        @foreach($designacoes as $designacao)
                            <div class="border border-gray-200 rounded-lg p-3 {{ $designacao->status === 'pendente' ? 'bg-purple-50' : ($designacao->status === 'em_andamento' ? 'bg-blue-50' : 'bg-gray-50') }}">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-900 truncate">
                                            @if($designacao->setor_designado && !$designacao->usuario_designado_id)
                                                {{-- Apenas Setor --}}
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                    Setor: {{ $designacao->setor_designado }}
                                                </span>
                                            @elseif($designacao->setor_designado && $designacao->usuario_designado_id)
                                                {{-- Setor + Usuário --}}
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    {{ $designacao->usuarioDesignado->nome }} ({{ $designacao->setor_designado }})
                                                </span>
                                            @else
                                                {{-- Apenas Usuário --}}
                                                {{ $designacao->usuarioDesignado->nome }}
                                            @endif
                                            
                                            @if($designacao->usuario_designado_id === auth('interno')->id() && $designacao->status === 'pendente')
                                                <form action="{{ route('admin.estabelecimentos.processos.designacoes.concluir', [$estabelecimento->id, $processo->id, $designacao->id]) }}" method="POST" class="inline-block ml-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="text-xs px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">
                                                        Marcar como Resolvido
                                                    </button>
                                                </form>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-600 mt-0.5">
                                            {{ Str::limit($designacao->descricao_tarefa, 60) }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded whitespace-nowrap
                                        {{ $designacao->status === 'pendente' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $designacao->status === 'em_andamento' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $designacao->status === 'concluida' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $designacao->status === 'cancelada' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ match($designacao->status) {
                                            'pendente' => 'Pendente',
                                            'em_andamento' => 'Em Andamento',
                                            'concluida' => 'Concluída',
                                            'cancelada' => 'Cancelada',
                                            default => $designacao->status
                                        } }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    @if($designacao->data_limite)
                                        <span class="flex items-center gap-1 {{ $designacao->isAtrasada() ? 'text-red-600 font-semibold' : '' }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $designacao->data_limite->format('d/m/Y') }}
                                        </span>
                                    @endif
                                    <span>•</span>
                                    <span>{{ $designacao->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
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
                    </div>
                </div>

                {{-- Tabs de Documentos --}}
                <div class="border-b border-gray-200">
                    <nav class="flex px-6 overflow-x-auto" aria-label="Tabs">
                        <button @click="pastaAtiva = null" 
                                :class="pastaAtiva === null ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                            Todos
                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full"
                                  :class="pastaAtiva === null ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'">
                                {{ $documentosDigitais->count() + $processo->documentos->where('tipo_documento', '!=', 'documento_digital')->count() }}
                            </span>
                        </button>
                        
                        {{-- Pastas Dinâmicas --}}
                        <template x-for="pasta in pastas" :key="pasta.id">
                            <button @click="pastaAtiva = pasta.id"
                                    :class="pastaAtiva === pasta.id ? 'border-b-2' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                                    :style="pastaAtiva === pasta.id ? `color: ${pasta.cor}; border-color: ${pasta.cor}` : ''"
                                    class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                <span x-text="pasta.nome"></span>
                                <span class="ml-1 px-2 py-0.5 text-xs rounded-full"
                                      :style="`background-color: ${pasta.cor}20; color: ${pasta.cor}`"
                                      x-text="contarDocumentosPorPasta(pasta.id)">
                                </span>
                            </button>
                        </template>
                    </nav>
                </div>

                {{-- Lista de Documentos --}}
                <div class="p-6">
                    @if($todosDocumentos->isEmpty())
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-900 mb-1">Nenhum documento anexado</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            {{-- Lista Unificada de Documentos (Digitais e Arquivos Externos) --}}
                            @foreach($todosDocumentos as $item)
                                @if($item['tipo'] === 'digital')
                                    @php
                                        $docDigital = $item['documento'];
                                    @endphp
                                <div x-data="{ pastaDocumento: {{ $docDigital->pasta_id ?? 'null' }} }"
                                     x-show="pastaAtiva === null || pastaAtiva === pastaDocumento"
                                     class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 rounded-lg border-l-2 border-green-500 hover:bg-gray-100 transition-colors">
                                    
                                    {{-- Checkbox de seleção --}}
                                    <input type="checkbox" 
                                           x-show="selecionarMultiplos" 
                                           :value="'doc_digital_{{ $docDigital->id }}'"
                                           @change="toggleDocumento('doc_digital_{{ $docDigital->id }}')"
                                           class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                    
                                    @php
                                        $assinaturasPendentes = $docDigital->assinaturas()->where('status', 'pendente')->count();
                                        $todasAssinaturas = $docDigital->assinaturas()->count();
                                        $temAssinaturasPendentes = $assinaturasPendentes > 0;
                                        
                                        // Verificar se o usuário logado precisa assinar este documento
                                        $usuarioLogado = auth('interno')->user();
                                        $assinaturaUsuario = $docDigital->assinaturas()
                                            ->where('usuario_interno_id', $usuarioLogado->id)
                                            ->where('status', 'pendente')
                                            ->first();
                                        $usuarioPrecisaAssinar = $assinaturaUsuario !== null && $docDigital->status !== 'rascunho';
                                    @endphp
                                    
                                    @if($docDigital->status === 'rascunho')
                                        <a href="{{ route('admin.documentos.edit', $docDigital->id) }}" 
                                           class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer hover:bg-gray-200 rounded-lg p-2 -m-2 transition-colors">
                                    @elseif($docDigital->arquivo_pdf && !$temAssinaturasPendentes)
                                        <div @click="pdfUrl = '{{ route('admin.estabelecimentos.processos.visualizar', [$estabelecimento->id, $processo->id, $docDigital->id]) }}'; modalVisualizador = true" 
                                             class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer">
                                    @else
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                    @endif
                                        {{-- Ícone do documento digital --}}
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                                <span class="text-2xl">📄</span>
                                            </div>
                                        </div>
                                        
                                        {{-- Informações do documento --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900">{{ $docDigital->nome ?? $docDigital->tipoDocumento->nome }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $docDigital->numero_documento }}</p>
                                            <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-500">
                                                <span class="flex items-center gap-1 whitespace-nowrap">
                                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $docDigital->created_at->format('d/m/Y H:i') }}
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    {{ $docDigital->usuarioCriador->nome }}
                                                </span>
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium whitespace-nowrap">
                                                    Interno
                                                </span>
                                                @if($docDigital->status === 'rascunho')
                                                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full font-medium whitespace-nowrap flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                        </svg>
                                                        Rascunho
                                                    </span>
                                                @elseif($temAssinaturasPendentes)
                                                    <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full font-medium whitespace-nowrap flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        Aguardando {{ $assinaturasPendentes }}/{{ $todasAssinaturas }} assinatura(s)
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium whitespace-nowrap flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        Assinado
                                                    </span>
                                                @endif
                                                
                                                {{-- Botão Assinar se o usuário logado precisa assinar --}}
                                                @if($usuarioPrecisaAssinar)
                                                    <a href="{{ route('admin.assinatura.assinar', $docDigital->id) }}" 
                                                       class="px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-1 whitespace-nowrap">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                        </svg>
                                                        Assinar Agora
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @if($docDigital->status === 'rascunho')
                                        </a>
                                    @else
                                        </div>
                                    @endif
                                    
                                    {{-- Ações --}}
                                    <div class="flex items-center gap-2 sm:flex-shrink-0">
                                        {{-- Mover para Pasta - Apenas se NÃO for rascunho --}}
                                        @if($docDigital->status !== 'rascunho')
                                        <div class="relative" x-data="{ menuAberto: false }">
                                            <button @click.stop="menuAberto = !menuAberto"
                                                    class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                                    title="Mover para pasta">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                </svg>
                                            </button>
                                            <div x-show="menuAberto" 
                                                 @click.away="menuAberto = false"
                                                 x-transition
                                                 class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                                                 style="display: none;">
                                                <div class="py-1">
                                                    <button @click="moverDocumentoDigitalParaPasta({{ $docDigital->id }}, null, $el); menuAberto = false"
                                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                        </svg>
                                                        Todos (sem pasta)
                                                    </button>
                                                    <template x-for="pasta in pastas" :key="pasta.id">
                                                        <button @click="moverDocumentoDigitalParaPasta({{ $docDigital->id }}, pasta.id, $el); menuAberto = false"
                                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                            <svg class="w-4 h-4" :style="`color: ${pasta.cor}`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                            </svg>
                                                            <span x-text="pasta.nome"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if($docDigital->status !== 'rascunho' && $docDigital->arquivo_pdf)
                                            <a href="{{ route('admin.documentos.pdf', $docDigital->id) }}" 
                                               class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                               title="Download">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </a>
                                        @endif

                                        {{-- Menu de 3 Pontos --}}
                                        <div class="relative" x-data="{ menuAberto: false }">
                                            <button @click.stop="menuAberto = !menuAberto"
                                                    class="p-2 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
                                                    title="Mais opções">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                                </svg>
                                            </button>
                                            <div x-show="menuAberto" 
                                                 @click.away="menuAberto = false"
                                                 x-transition
                                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                                                 style="display: none;">
                                                <div class="py-1">
                                                    @if($docDigital->status === 'rascunho')
                                                        <a href="{{ route('admin.documentos.edit', $docDigital->id) }}"
                                                           class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                            Editar
                                                        </a>
                                                    @endif

                                                    <button @click="if(confirm('Tem certeza que deseja excluir este documento?')) { excluirDocumentoDigital({{ $docDigital->id }}) }; menuAberto = false"
                                                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Excluir
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @elseif($item['tipo'] === 'ordem_servico')
                                    @php
                                        $os = $item['documento'];
                                    @endphp
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-600 hover:bg-blue-100 transition-colors">
                                    <a href="{{ route('admin.ordens-servico.show', $os) }}" 
                                       class="flex items-center gap-3 flex-1 min-w-0">
                                        {{-- Ícone da OS --}}
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                </svg>
                                            </div>
                                        </div>
                                        
                                        {{-- Informações da OS --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                                Ordem de Serviço #{{ $os->numero }}
                                                {!! $os->status_badge !!}
                                            </p>
                                            <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-600">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $os->created_at->format('d/m/Y H:i') }}
                                                </span>
                                                @if($os->estabelecimento)
                                                <span>•</span>
                                                <span class="truncate">{{ $os->estabelecimento->nome_fantasia }}</span>
                                                @endif
                                                @if($os->municipio)
                                                <span>•</span>
                                                <span>{{ $os->municipio->nome }}/{{ $os->municipio->uf }}</span>
                                                @endif
                                                <span>•</span>
                                                {!! $os->competencia_badge !!}
                                            </div>
                                        </div>
                                    </a>
                                    
                                    {{-- Botão Ver OS --}}
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.ordens-servico.show', $os) }}" 
                                           class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Ver OS
                                        </a>
                                    </div>
                                </div>
                                @elseif($item['tipo'] === 'arquivo')
                                    @php
                                        $documento = $item['documento'];
                                    @endphp
                                <div x-data="{ pastaDocumento: {{ $documento->pasta_id ?? 'null' }} }"
                                     x-show="pastaAtiva === null || pastaAtiva === pastaDocumento"
                                     class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 rounded-lg border-l-2 border-red-500 hover:bg-gray-100 transition-colors">
                                    <div @click="abrirVisualizadorAnotacoes({{ $documento->id }}, '{{ route('admin.estabelecimentos.processos.visualizar', [$estabelecimento->id, $processo->id, $documento->id]) }}')" 
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
                                                @if($documento->tipo_documento === 'documento_digital')
                                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium whitespace-nowrap flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        Documento Digital
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-gray-200 text-gray-700 rounded-full font-medium whitespace-nowrap">
                                                        Arquivo Externo
                                                    </span>
                                                @endif
                                                @if($documento->pasta_id)
                                                    <span class="flex items-center gap-1 px-2 py-0.5 rounded-full font-medium whitespace-nowrap"
                                                          x-data="{ pasta: pastas.find(p => p.id === {{ $documento->pasta_id }}) }"
                                                          :style="pasta ? `background-color: ${pasta.cor}20; color: ${pasta.cor}` : 'background-color: #E5E7EB; color: #6B7280'">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                        </svg>
                                                        <span x-text="pasta ? pasta.nome : 'Pasta'"></span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Ações --}}
                                    <div class="flex items-center gap-2 sm:flex-shrink-0">
                                        {{-- Mover para Pasta --}}
                                        <div class="relative" x-data="{ menuAberto: false }">
                                            <button @click.stop="menuAberto = !menuAberto"
                                                    class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                                    title="Mover para pasta">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                </svg>
                                            </button>
                                            
                                            {{-- Dropdown Menu --}}
                                            <div x-show="menuAberto" 
                                                 @click.away="menuAberto = false"
                                                 x-transition
                                                 class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                                                 style="display: none;">
                                                <div class="py-1">
                                                    <button @click="moverParaPasta({{ $documento->id }}, 'arquivo', null, $el); menuAberto = false"
                                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                        </svg>
                                                        Todos (sem pasta)
                                                    </button>
                                                    <template x-for="pasta in pastas" :key="pasta.id">
                                                        <button @click="moverParaPasta({{ $documento->id }}, 'arquivo', pasta.id, $el); menuAberto = false"
                                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                            <svg class="w-4 h-4" :style="`color: ${pasta.cor}`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                            </svg>
                                                            <span x-text="pasta.nome"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="{{ route('admin.estabelecimentos.processos.download', [$estabelecimento->id, $processo->id, $documento->id]) }}" 
                                           class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                           title="Download">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>

                                        {{-- Menu de 3 Pontos --}}
                                        <div class="relative" x-data="{ menuAberto: false }">
                                            <button @click.stop="menuAberto = !menuAberto"
                                                    class="p-2 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
                                                    title="Mais opções">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                                </svg>
                                            </button>
                                            
                                            {{-- Dropdown Menu --}}
                                            <div x-show="menuAberto" 
                                                 @click.away="menuAberto = false"
                                                 x-transition
                                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                                                 style="display: none;">
                                                <div class="py-1">
                                                    <button @click="documentoEditando = {{ $documento->id }}; nomeEditando = '{{ $documento->nome_original }}'; modalEditarNome = true; menuAberto = false"
                                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Renomear
                                                    </button>

                                                    <form action="{{ route('admin.estabelecimentos.processos.deleteArquivo', [$estabelecimento->id, $processo->id, $documento->id]) }}" 
                                                          method="POST"
                                                          onsubmit="return confirm('Tem certeza que deseja remover este arquivo?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                            Excluir
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
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

    {{-- Modal de Designar Responsável --}}
    <template x-teleport="body">
        <div x-show="modalDesignar" 
             x-cloak
             @keydown.escape.window="modalDesignar = false"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Overlay --}}
            <div @click="modalDesignar = false"
                 style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5);"></div>
            
            {{-- Modal Content --}}
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; max-width: 600px; padding: 0 1rem;">
                <div class="bg-white rounded-xl shadow-2xl p-6" @click.stop>
                    {{-- Close Button --}}
                    <button @click="modalDesignar = false"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- Header --}}
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Designar Responsável
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Atribua este processo a um usuário interno do município</p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('admin.estabelecimentos.processos.designar', [$estabelecimento->id, $processo->id]) }}">
                        @csrf
                        
                        {{-- Campo oculto para tipo de designação (sempre usuário) --}}
                        <input type="hidden" name="tipo_designacao" value="usuario">

                        {{-- Selecionar Usuários --}}
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Usuários <span class="text-red-500">*</span>
                            </label>
                            <div class="border border-gray-300 rounded-lg p-3 max-h-60 overflow-y-auto bg-gray-50">
                                <template x-if="usuariosPorSetor.length === 0">
                                    <p class="text-sm text-gray-500 text-center py-2">Carregando usuários...</p>
                                </template>

                                {{-- Todos os usuários agrupados por setor --}}
                                <div class="space-y-3">
                                    <template x-for="grupo in usuariosPorSetor" :key="grupo.setor.codigo">
                                        <div x-show="grupo.usuarios.length > 0">
                                            <p class="text-xs font-semibold text-gray-600 uppercase mb-2" x-text="grupo.setor.nome"></p>
                                            <div class="space-y-2 ml-2">
                                                <template x-for="usuario in grupo.usuarios" :key="usuario.id">
                                                    <label class="flex items-start gap-3 p-2 hover:bg-white rounded cursor-pointer transition-colors">
                                                        <input type="checkbox" 
                                                               name="usuarios_designados[]" 
                                                               :value="usuario.id"
                                                               x-model="usuariosDesignados"
                                                               class="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900" x-text="usuario.nome"></p>
                                                            <p class="text-xs text-gray-500" x-text="usuario.cargo || usuario.nivel_acesso"></p>
                                                        </div>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <p class="mt-1 text-xs font-medium text-blue-600" x-show="usuariosDesignados.length > 0">
                                <span x-text="usuariosDesignados.length"></span> usuário(s) selecionado(s)
                            </p>
                        </div>

                        {{-- Descrição da Tarefa --}}
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição da Tarefa <span class="text-red-500">*</span>
                            </label>
                            <textarea name="descricao_tarefa" 
                                      x-model="descricaoTarefa"
                                      rows="4"
                                      required
                                      maxlength="1000"
                                      placeholder="Descreva o que precisa ser feito neste processo..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none"></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Máximo de 1000 caracteres
                            </p>
                        </div>

                        {{-- Data Limite (Opcional) --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Data Limite (Opcional)
                            </label>
                            <input type="date" 
                                   name="data_limite"
                                   x-model="dataLimite"
                                   :min="new Date().toISOString().split('T')[0]"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <p class="mt-1 text-xs text-gray-500">
                                Deixe em branco se não houver prazo específico
                            </p>
                        </div>

                        {{-- Info --}}
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-xs text-blue-700">
                                    O usuário designado receberá uma notificação na dashboard e poderá visualizar a tarefa atribuída.
                                </p>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    @click="modalDesignar = false"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                Designar Responsável
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

    {{-- Modal de Visualização de PDF com Anotações --}}
    <template x-teleport="body">
        <div x-show="modalVisualizadorAnotacoes" 
             x-cloak
             @keydown.escape.window="fecharModalPDF()"
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
            
            {{-- Modal Content - Tela Toda --}}
            <div class="bg-white h-full flex flex-col" @click.stop>
                    {{-- Header --}}
                    <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Visualizar e Anotar PDF
                        </h3>
                        <button @click="fecharModalPDF()"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- PDF Viewer com Anotações --}}
                    <div class="flex-1 overflow-auto p-4">
                        <template x-if="documentoIdAnotacoes && pdfUrlAnotacoes">
                            <div x-data="pdfViewerAnotacoes(documentoIdAnotacoes, pdfUrlAnotacoes, [])" 
                                 x-init="init()"
                                 class="pdf-viewer-container bg-white rounded-lg shadow-lg border border-gray-200">
                                @include('components.pdf-viewer-anotacoes')
                            </div>
                        </template>
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

    {{-- Modal de Pastas do Processo --}}
    <template x-if="modalPastas">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalPastas" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalPastas = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    {{-- Header --}}
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                Gerenciar Pastas do Processo
                            </h3>
                            <button @click="modalPastas = false" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4">
                        {{-- Formulário de Nova Pasta --}}
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">
                                <span x-show="!pastaEditando">Nova Pasta</span>
                                <span x-show="pastaEditando">Editar Pasta</span>
                            </h4>
                            <form @submit.prevent="salvarPasta()" class="space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nome da Pasta *</label>
                                        <input type="text" x-model="nomePasta" required
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                               placeholder="Ex: Documentos Técnicos">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Cor</label>
                                        <input type="color" x-model="corPasta"
                                               class="w-full h-10 px-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Descrição</label>
                                    <textarea x-model="descricaoPasta" rows="2"
                                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                              placeholder="Descrição opcional da pasta"></textarea>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors">
                                        <span x-show="!pastaEditando">Criar Pasta</span>
                                        <span x-show="pastaEditando">Salvar Alterações</span>
                                    </button>
                                    <button type="button" x-show="pastaEditando" @click="cancelarEdicao()"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Lista de Pastas --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Pastas Criadas</h4>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <template x-if="pastas.length === 0">
                                    <div class="text-center py-8 text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                        </svg>
                                        <p class="text-sm">Nenhuma pasta criada ainda</p>
                                    </div>
                                </template>
                                <template x-for="pasta in pastas" :key="pasta.id">
                                    <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:shadow-sm transition-shadow">
                                        <div class="flex items-center gap-3 flex-1">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" :style="`background-color: ${pasta.cor}20`">
                                                <svg class="w-5 h-5" :style="`color: ${pasta.cor}`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h5 class="text-sm font-medium text-gray-900" x-text="pasta.nome"></h5>
                                                <p class="text-xs text-gray-500" x-text="pasta.descricao || 'Sem descrição'"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button @click="editarPasta(pasta)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button @click="excluirPasta(pasta.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <button @click="modalPastas = false" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Parar Processo --}}
    <template x-if="modalParar">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalParar" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalParar = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    <form action="{{ route('admin.estabelecimentos.processos.parar', [$estabelecimento->id, $processo->id]) }}" method="POST">
                        @csrf
                        
                        {{-- Header --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-red-600 to-red-700 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Parar Processo
                            </h3>
                            <button type="button" @click="modalParar = false" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Conteúdo --}}
                        <div class="px-6 py-6">
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-4">
                                    Você está prestes a parar o processo <strong>{{ $processo->numero_processo }}</strong>. 
                                    Por favor, informe o motivo da parada.
                                </p>
                                
                                <label for="motivo_parada" class="block text-sm font-medium text-gray-700 mb-2">
                                    Motivo da Parada <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    name="motivo_parada" 
                                    id="motivo_parada" 
                                    rows="4"
                                    required
                                    minlength="10"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"
                                    placeholder="Descreva o motivo da parada (mínimo 10 caracteres)..."></textarea>
                                
                                @error('motivo_parada')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            <strong>Atenção:</strong> O processo será marcado como parado e esta ação ficará registrada no histórico.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                            <button type="button" @click="modalParar = false" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                                Parar Processo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Arquivar Processo --}}
    <template x-if="modalArquivar">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalArquivar" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalArquivar = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    <form action="{{ route('admin.estabelecimentos.processos.arquivar', [$estabelecimento->id, $processo->id]) }}" method="POST">
                        @csrf
                        
                        {{-- Header --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-orange-600 to-orange-700 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                Arquivar Processo
                            </h3>
                            <button type="button" @click="modalArquivar = false" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Conteúdo --}}
                        <div class="px-6 py-6">
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-4">
                                    Você está prestes a arquivar o processo <strong>{{ $processo->numero_processo }}</strong>. 
                                    Por favor, informe o motivo do arquivamento.
                                </p>
                                
                                <label for="motivo_arquivamento" class="block text-sm font-medium text-gray-700 mb-2">
                                    Motivo do Arquivamento <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    name="motivo_arquivamento" 
                                    id="motivo_arquivamento" 
                                    rows="4"
                                    required
                                    minlength="10"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"
                                    placeholder="Descreva o motivo do arquivamento (mínimo 10 caracteres)..."></textarea>
                                
                                @error('motivo_arquivamento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            <strong>Atenção:</strong> O processo será marcado como arquivado e esta ação ficará registrada no histórico.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                            <button type="button" @click="modalArquivar = false" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition-colors">
                                Arquivar Processo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal de Histórico do Processo --}}
    <template x-if="modalHistorico">
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="modalHistorico" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalHistorico = false"></div>

                {{-- Modal --}}
                <div class="inline-block w-full max-w-3xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                    {{-- Header --}}
                    <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Histórico do Processo
                        </h3>
                        <button @click="modalHistorico = false" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                        {{-- Buscar eventos do histórico --}}
                        @php
                            try {
                                $eventos = $processo->eventos()->with('usuario')->get();
                            } catch (\Exception $e) {
                                // Tabela ainda não existe - migration não foi executada
                                $eventos = collect();
                            }
                        @endphp

                        {{-- Linha do Tempo --}}
                        <div class="relative">
                            @forelse($eventos as $evento)
                            <div class="flex gap-4 pb-8 {{ $loop->last ? '' : 'border-l-2 border-gray-200' }} ml-4">
                                {{-- Ícone do Evento --}}
                                <div class="absolute left-0 flex items-center justify-center w-8 h-8 rounded-full border-2 border-white
                                    @if($evento->cor === 'blue') bg-blue-100
                                    @elseif($evento->cor === 'purple') bg-purple-100
                                    @elseif($evento->cor === 'green') bg-green-100
                                    @elseif($evento->cor === 'red') bg-red-100
                                    @elseif($evento->cor === 'yellow') bg-yellow-100
                                    @else bg-gray-100
                                    @endif">
                                    @if($evento->icone === 'plus')
                                    <svg class="w-4 h-4 @if($evento->cor === 'blue') text-blue-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    @elseif($evento->icone === 'upload')
                                    <svg class="w-4 h-4 @if($evento->cor === 'purple') text-purple-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    @elseif($evento->icone === 'document')
                                    <svg class="w-4 h-4 @if($evento->cor === 'green') text-green-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    @elseif($evento->icone === 'trash')
                                    <svg class="w-4 h-4 @if($evento->cor === 'red') text-red-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    @elseif($evento->icone === 'refresh')
                                    <svg class="w-4 h-4 @if($evento->cor === 'yellow') text-yellow-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    @elseif($evento->icone === 'archive')
                                    <svg class="w-4 h-4 @if($evento->cor === 'orange') text-orange-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                    @elseif($evento->icone === 'check')
                                    <svg class="w-4 h-4 @if($evento->cor === 'green') text-green-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @elseif($evento->icone === 'pause')
                                    <svg class="w-4 h-4 @if($evento->cor === 'red') text-red-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @elseif($evento->icone === 'play')
                                    <svg class="w-4 h-4 @if($evento->cor === 'green') text-green-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @endif
                                </div>

                                {{-- Conteúdo do Evento --}}
                                <div class="flex-1 ml-12">
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-semibold text-gray-900">{{ $evento->titulo }}</h4>
                                                <p class="text-xs text-gray-600 mt-0.5">{{ $evento->descricao }}</p>
                                                <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                        {{ $evento->usuario->nome ?? 'Sistema' }}
                                                    </span>
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        {{ $evento->created_at->format('d/m/Y H:i') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Nenhum evento registrado</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <button @click="modalHistorico = false" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Scripts Alpine.js --}}
    <script>
        function processoData() {
            return {
                // Modais
                modalUpload: false,
                modalVisualizador: false,
                modalVisualizadorAnotacoes: false,
                modalEditarNome: false,
                modalDocumentoDigital: false,
                modalPastas: false,
                modalHistorico: false,
                modalArquivar: false,
                modalParar: false,
                modalDesignar: false,
                modalOrdemServico: false,
                modalAlertas: false,
                
                // Dados gerais
                pdfUrl: '',
                pdfUrlAnotacoes: '',
                documentoIdAnotacoes: null,
                documentoEditando: null,
                nomeEditando: '',
                selecionarMultiplos: false, // Para seleção múltipla de documentos
                
                // Pastas
                pastas: [],
                pastaAtiva: null, // null = Todos, ou ID da pasta
                pastaEditando: null,
                nomePasta: '',
                descricaoPasta: '',
                corPasta: '#3B82F6',
                
                // Designação
                setores: [],
                usuariosPorSetor: [],
                usuariosDesignados: [],
                descricaoTarefa: '',
                dataLimite: '',
                isCompetenciaEstadual: false,
                
                // Documentos (para contagem) - incluindo documentos digitais e arquivos
                documentos: [
                    @foreach($documentosDigitais as $docDigital)
                        { id: {{ $docDigital->id }}, pasta_id: {{ $docDigital->pasta_id ?? 'null' }}, tipo: 'digital' },
                    @endforeach
                    @foreach($processo->documentos->where('tipo_documento', '!=', 'documento_digital') as $documento)
                        { id: {{ $documento->id }}, pasta_id: {{ $documento->pasta_id ?? 'null' }}, tipo: 'arquivo' },
                    @endforeach
                ],

                // Inicialização
                init() {
                    this.carregarPastas();
                },

                // Função para mostrar notificações
                mostrarNotificacao(mensagem, tipo = 'success') {
                    const container = document.createElement('div');
                    container.className = `fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 ${tipo === 'success' ? 'border-green-500' : 'border-red-500'} p-4 animate-slide-in`;
                    container.style.animation = 'slideIn 0.3s ease-out';
                    
                    container.innerHTML = `
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ${tipo === 'success' ? 'text-green-500' : 'text-red-500'} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                ${tipo === 'success' 
                                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>'}
                            </svg>
                            <p class="text-sm font-medium ${tipo === 'success' ? 'text-green-800' : 'text-red-800'}">${mensagem}</p>
                        </div>
                    `;
                    
                    document.body.appendChild(container);
                    
                    setTimeout(() => {
                        container.style.animation = 'slideOut 0.3s ease-in';
                        setTimeout(() => container.remove(), 300);
                    }, 3000);
                },

                // Métodos de Pastas
                carregarPastas() {
                    fetch('{{ route('admin.estabelecimentos.processos.pastas.index', [$estabelecimento->id, $processo->id]) }}')
                        .then(response => response.json())
                        .then(data => {
                            this.pastas = data;
                        })
                        .catch(error => console.error('Erro ao carregar pastas:', error));
                },

                salvarPasta() {
                    const url = this.pastaEditando 
                        ? '{{ route('admin.estabelecimentos.processos.pastas.update', [$estabelecimento->id, $processo->id, ':id']) }}'.replace(':id', this.pastaEditando.id)
                        : '{{ route('admin.estabelecimentos.processos.pastas.store', [$estabelecimento->id, $processo->id]) }}';
                    
                    const method = this.pastaEditando ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            nome: this.nomePasta,
                            descricao: this.descricaoPasta,
                            cor: this.corPasta
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            this.cancelarEdicao();
                            this.carregarPastas();
                            // Pequeno delay para garantir que as pastas foram carregadas antes de fechar
                            setTimeout(() => {
                                alert(result.message);
                            }, 100);
                        }
                    })
                    .catch(error => console.error('Erro ao salvar pasta:', error));
                },

                editarPasta(pasta) {
                    this.pastaEditando = pasta;
                    this.nomePasta = pasta.nome;
                    this.descricaoPasta = pasta.descricao || '';
                    this.corPasta = pasta.cor;
                },

                cancelarEdicao() {
                    this.pastaEditando = null;
                    this.nomePasta = '';
                    this.descricaoPasta = '';
                    this.corPasta = '#3B82F6';
                },

                excluirPasta(pastaId) {
                    if (!confirm('Tem certeza que deseja excluir esta pasta? Os documentos e arquivos serão movidos para "Todos".')) {
                        return;
                    }

                    fetch('{{ route('admin.estabelecimentos.processos.pastas.destroy', [$estabelecimento->id, $processo->id, ':id']) }}'.replace(':id', pastaId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            this.carregarPastas();
                            alert(result.message);
                        }
                    })
                    .catch(error => console.error('Erro ao excluir pasta:', error));
                },

                moverParaPasta(itemId, tipo, pastaId, element) {
                    fetch('{{ route('admin.estabelecimentos.processos.pastas.mover', [$estabelecimento->id, $processo->id]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            tipo: tipo,
                            item_id: itemId,
                            pasta_id: pastaId
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            // Encontrar o elemento pai com x-data
                            const docElement = element.closest('[x-data]');
                            if (docElement && docElement.__x) {
                                // Atualizar a variável pastaDocumento do Alpine.js
                                docElement.__x.$data.pastaDocumento = pastaId;
                            }
                            
                            // Atualizar o array de documentos
                            const docIndex = this.documentos.findIndex(doc => doc.id === itemId && doc.tipo === tipo);
                            if (docIndex !== -1) {
                                this.documentos[docIndex].pasta_id = pastaId;
                            }
                            
                            // Mostrar mensagem de sucesso
                            this.mostrarNotificacao(result.message, 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao mover item:', error);
                        this.mostrarNotificacao('Erro ao mover o item. Tente novamente.', 'error');
                    });
                },

                contarDocumentosPorPasta(pastaId) {
                    return this.documentos.filter(doc => doc.pasta_id === pastaId).length;
                },

                // Métodos para Documentos Digitais
                moverDocumentoDigitalParaPasta(documentoId, pastaId, element) {
                    fetch(`/admin/documentos/${documentoId}/mover-pasta`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ pasta_id: pastaId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Encontrar o elemento pai com x-data
                            const docElement = element.closest('[x-data]');
                            if (docElement && docElement.__x) {
                                // Atualizar a variável pastaDocumento do Alpine.js
                                docElement.__x.$data.pastaDocumento = pastaId;
                            }
                            
                            // Atualizar o array de documentos
                            const docIndex = this.documentos.findIndex(doc => doc.id === documentoId && doc.tipo === 'digital');
                            if (docIndex !== -1) {
                                this.documentos[docIndex].pasta_id = pastaId;
                            }
                            
                            // Mostrar mensagem de sucesso
                            this.mostrarNotificacao(data.message || 'Documento movido com sucesso!', 'success');
                        } else {
                            this.mostrarNotificacao(data.message || 'Erro ao mover documento', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        this.mostrarNotificacao('Erro ao mover documento', 'error');
                    });
                },

                renomearDocumentoDigital(documentoId, novoNome) {
                    fetch(`/admin/documentos/${documentoId}/renomear`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ nome: novoNome })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Erro ao renomear documento');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao renomear documento');
                    });
                },

                excluirDocumentoDigital(documentoId) {
                    fetch(`/admin/documentos/${documentoId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Erro ao excluir documento');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao excluir documento');
                    });
                },

                // Abre o visualizador de PDF com ferramentas de anotação
                async abrirVisualizadorAnotacoes(documentoId, pdfUrl) {
                    this.documentoIdAnotacoes = documentoId;
                    this.pdfUrlAnotacoes = pdfUrl;
                    this.modalVisualizadorAnotacoes = true;
                    
                    // Notificar que o modal PDF foi aberto
                    window.dispatchEvent(new CustomEvent('pdf-modal-aberto'));
                    
                    // Carrega automaticamente o documento na IA
                    await this.carregarDocumentoNaIA();
                },

                // Carrega documento na IA para perguntas
                async carregarDocumentoNaIA() {
                    if (!this.documentoIdAnotacoes) {
                        alert('Nenhum documento selecionado');
                        return;
                    }

                    // Mostra loading
                    const loadingMsg = 'Carregando documento na IA...';
                    console.log(loadingMsg);

                    try {
                        // Chama endpoint para extrair texto do PDF
                        const response = await fetch(`{{ route('admin.assistente-ia.extrair-pdf') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                documento_id: this.documentoIdAnotacoes,
                                estabelecimento_id: {{ $estabelecimento->id }},
                                processo_id: {{ $processo->id }}
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Dispara evento customizado para o componente do chat
                            window.dispatchEvent(new CustomEvent('documento-carregado', {
                                detail: {
                                    documento_id: this.documentoIdAnotacoes,
                                    nome_documento: data.nome_documento,
                                    conteudo: data.conteudo,
                                    total_caracteres: data.total_caracteres
                                }
                            }));

                            // NÃO fecha o modal - mantém aberto para visualização
                            // this.modalVisualizadorAnotacoes = false;

                            // Não mostra alert - IA já mostra mensagem no chat
                            // alert('✅ Documento carregado! Agora você pode fazer perguntas sobre ele no chat da IA.');
                        } else {
                            alert('❌ ' + (data.message || 'Erro ao carregar documento'));
                        }
                    } catch (error) {
                        console.error('Erro ao carregar documento:', error);
                        alert('❌ Erro ao carregar documento na IA');
                    }
                },

                // Fecha o modal PDF e dispara evento para fechar assistente de documento
                fecharModalPDF() {
                    this.modalVisualizadorAnotacoes = false;
                    // Dispara evento para notificar que o modal PDF foi fechado
                    window.dispatchEvent(new CustomEvent('pdf-modal-fechado'));
                },

                // Carrega setores e usuários para designação
                carregarUsuarios() {
                    fetch(`{{ route('admin.estabelecimentos.processos.usuarios.designacao', [$estabelecimento->id, $processo->id]) }}`)
                        .then(response => response.json())
                        .then(data => {
                            this.setores = data.setores || [];
                            this.usuariosPorSetor = data.usuariosPorSetor || [];
                            this.isCompetenciaEstadual = data.isCompetenciaEstadual || false;
                        })
                        .catch(error => {
                            console.error('Erro ao carregar setores e usuários:', error);
                            alert('Erro ao carregar setores e usuários');
                        });
                }
            }
        }
    </script>

    {{-- Modal Criar Ordem de Serviço --}}
    <div x-show="modalOrdemServico" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div x-show="modalOrdemServico" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 @click="modalOrdemServico = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div x-show="modalOrdemServico"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                
                <form action="{{ route('admin.ordens-servico.store') }}" method="POST">
                    @csrf
                    
                    {{-- Campos ocultos --}}
                    <input type="hidden" name="tipo_vinculacao" value="com_estabelecimento">
                    <input type="hidden" name="estabelecimento_id" value="{{ $estabelecimento->id }}">
                    <input type="hidden" name="processo_id" value="{{ $processo->id }}">
                    <input type="hidden" name="municipio_id" value="{{ $processo->estabelecimento->municipio_id }}">
                    
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Nova Ordem de Serviço
                            </h3>
                            <button type="button" 
                                    @click="modalOrdemServico = false" 
                                    class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-4 space-y-4">
                        {{-- Informações do Processo (Read-only) --}}
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-purple-900 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Vinculado ao Processo
                            </h4>
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <span class="text-gray-600">Estabelecimento:</span>
                                    <p class="font-medium text-gray-900">{{ $estabelecimento->nome_fantasia }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Processo:</span>
                                    <p class="font-medium text-gray-900">{{ $processo->numero_processo }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Período de Execução --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Data Início
                                </label>
                                <input type="date" 
                                       name="data_inicio" 
                                       value="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Data Fim
                                </label>
                                <input type="date" 
                                       name="data_fim" 
                                       value="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                            </div>
                        </div>

                        {{-- Tipos de Ação --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipos de Ação <span class="text-red-500">*</span>
                            </label>
                            <select name="tipos_acao_ids[]" 
                                    id="tipos-acao-select"
                                    class="w-full" 
                                    multiple="multiple" 
                                    required>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Digite para pesquisar tipos de ação</p>
                        </div>

                        {{-- Técnicos --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Técnicos Responsáveis <span class="text-red-500">*</span>
                            </label>
                            <select name="tecnicos_ids[]" 
                                    id="tecnicos-select"
                                    class="w-full" 
                                    multiple="multiple" 
                                    required>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Digite para pesquisar técnicos</p>
                        </div>

                        {{-- Observações --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Observações
                            </label>
                            <textarea name="observacoes" 
                                      rows="3"
                                      placeholder="Observações sobre a ordem de serviço..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm resize-none"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                        <button type="button" 
                                @click="modalOrdemServico = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Criar Ordem de Serviço
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Alertas --}}
    <div x-show="modalAlertas" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div x-show="modalAlertas" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" 
                 @click="modalAlertas = false"></div>

            {{-- Modal --}}
            <div x-show="modalAlertas"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                
                {{-- Header --}}
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            Alertas do Processo
                        </h3>
                        <button type="button" 
                                @click="modalAlertas = false" 
                                class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-6">
                    {{-- Form Criar Alerta --}}
                    <form method="POST" action="{{ route('admin.estabelecimentos.processos.alertas.criar', [$estabelecimento->id, $processo->id]) }}" class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
                        @csrf
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Criar Novo Alerta</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Descrição *</label>
                                <input type="text" 
                                       name="descricao" 
                                       required
                                       maxlength="500"
                                       placeholder="Ex: Verificar documentação pendente"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Data do Alerta *</label>
                                <input type="date" 
                                       name="data_alerta" 
                                       required
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            </div>
                        </div>
                        
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Adicionar Alerta
                            </button>
                        </div>
                    </form>

                    {{-- Lista de Alertas --}}
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @forelse($alertas as $alerta)
                        <div class="border rounded-lg p-4 {{ $alerta->isVencido() ? 'bg-red-50 border-red-200' : ($alerta->isProximo() ? 'bg-orange-50 border-orange-200' : 'bg-white border-gray-200') }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        @if($alerta->status === 'pendente')
                                            @if($alerta->isVencido())
                                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Vencido</span>
                                            @elseif($alerta->isProximo())
                                                <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">Próximo</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">Pendente</span>
                                            @endif
                                        @elseif($alerta->status === 'visualizado')
                                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">Visualizado</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Concluído</span>
                                        @endif
                                        
                                        <span class="text-xs text-gray-500">
                                            <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $alerta->data_alerta->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-900 mb-1">{{ $alerta->descricao }}</p>
                                    
                                    <p class="text-xs text-gray-500">
                                        Criado por {{ $alerta->usuarioCriador->nome }} • {{ $alerta->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                
                                <div class="flex items-center gap-1">
                                    @if($alerta->status === 'pendente')
                                        <form method="POST" action="{{ route('admin.estabelecimentos.processos.alertas.visualizar', [$estabelecimento->id, $processo->id, $alerta->id]) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    title="Marcar como visualizado"
                                                    class="p-1.5 text-yellow-600 hover:bg-yellow-100 rounded transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($alerta->status !== 'concluido')
                                        <form method="POST" action="{{ route('admin.estabelecimentos.processos.alertas.concluir', [$estabelecimento->id, $processo->id, $alerta->id]) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    title="Marcar como concluído"
                                                    class="p-1.5 text-green-600 hover:bg-green-100 rounded transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('admin.estabelecimentos.processos.alertas.excluir', [$estabelecimento->id, $processo->id, $alerta->id]) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este alerta?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Excluir alerta"
                                                class="p-1.5 text-red-600 hover:bg-red-100 rounded transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <p class="text-sm">Nenhum alerta cadastrado</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button type="button" 
                            @click="modalAlertas = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Select2 CSS --}}
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Container do Select2 */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        min-height: 42px;
        padding: 4px;
        background-color: #ffffff;
        transition: all 0.2s ease;
    }
    
    /* Estado de foco - borda roxa com sombra suave */
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #9333ea;
        box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1);
        background-color: #ffffff;
        outline: none;
    }
    
    /* Tags selecionadas - roxo com texto branco */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #9333ea !important;
        border: none !important;
        color: #ffffff !important;
        padding: 6px 10px !important;
        border-radius: 0.375rem !important;
        font-weight: 500 !important;
        text-decoration: none !important; /* Remove qualquer linha cortando */
        line-height: 1.5 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        margin: 2px !important;
    }
    
    /* Garante que o texto dentro do chip não tenha decoração */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
        text-decoration: none !important;
        color: #ffffff !important;
        font-size: 0.875rem !important;
    }
    
    /* Botão de remover tag */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #ffffff !important;
        background-color: transparent !important;
        border: none !important;
        font-size: 1.25rem !important;
        font-weight: bold !important;
        line-height: 1 !important;
        padding: 0 !important;
        margin: 0 !important;
        margin-right: 4px !important;
        text-decoration: none !important; /* Remove qualquer linha */
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 16px !important;
        height: 16px !important;
        border-radius: 50% !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fca5a5 !important;
        background-color: rgba(255, 255, 255, 0.2) !important;
        text-decoration: none !important;
        transform: scale(1.1);
    }
    
    /* Dropdown */
    .select2-dropdown {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    /* Opções no dropdown - estado normal */
    .select2-container--default .select2-results__option {
        padding: 8px 12px;
        transition: all 0.15s ease;
    }
    
    /* Opções destacadas (hover/foco) - CORREÇÃO DE ACESSIBILIDADE */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #f3e8ff !important; /* Roxo muito claro */
        color: #581c87 !important; /* Roxo escuro para alto contraste */
        font-weight: 500;
    }
    
    /* Opções já selecionadas */
    .select2-container--default .select2-results__option[aria-selected="true"] {
        background-color: #ede9fe;
        color: #6b21a8;
    }
    
    /* Campo de busca dentro do select */
    .select2-container--default .select2-search--inline .select2-search__field {
        color: #1f2937;
        font-size: 0.875rem;
    }
    
    .select2-container--default .select2-search--inline .select2-search__field::placeholder {
        color: #9ca3af;
    }
    
    /* Placeholder quando vazio */
    .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
        color: #9ca3af;
    }
    
    /* Mensagem "Nenhum resultado" */
    .select2-container--default .select2-results__option--no-results {
        color: #6b7280;
        font-style: italic;
    }
</style>
@endpush

{{-- Select2 JS --}}
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa Select2 para Tipos de Ação
    $('#tipos-acao-select').select2({
        ajax: {
            url: '{{ route("admin.ordens-servico.api.search-tipos-acao") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                return {
                    results: data.results.map(function(item) {
                        return {
                            id: item.id,
                            text: item.text,
                            codigo: item.codigo
                        };
                    }),
                    pagination: data.pagination
                };
            },
            cache: true
        },
        placeholder: 'Digite para pesquisar tipos de ação...',
        minimumInputLength: 0,
        allowClear: true,
        width: '100%',
        language: {
            inputTooShort: function() {
                return 'Digite para pesquisar...';
            },
            searching: function() {
                return 'Buscando...';
            },
            noResults: function() {
                return 'Nenhum resultado encontrado';
            },
            loadingMore: function() {
                return 'Carregando mais resultados...';
            }
        },
        templateResult: function(item) {
            if (item.loading) return item.text;
            
            var $result = $('<div class="py-2">' +
                '<div class="font-medium text-gray-900">' + item.text + '</div>' +
                (item.codigo ? '<div class="text-xs text-gray-500">Código: ' + item.codigo + '</div>' : '') +
                '</div>');
            return $result;
        },
        templateSelection: function(item) {
            return item.text;
        }
    });

    // Inicializa Select2 para Técnicos
    $('#tecnicos-select').select2({
        ajax: {
            url: '{{ route("admin.ordens-servico.api.search-tecnicos") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                return {
                    results: data.results.map(function(item) {
                        return {
                            id: item.id,
                            text: item.text,
                            email: item.email,
                            nivel: item.nivel
                        };
                    }),
                    pagination: data.pagination
                };
            },
            cache: true
        },
        placeholder: 'Digite para pesquisar técnicos...',
        minimumInputLength: 0,
        allowClear: true,
        width: '100%',
        language: {
            inputTooShort: function() {
                return 'Digite para pesquisar...';
            },
            searching: function() {
                return 'Buscando...';
            },
            noResults: function() {
                return 'Nenhum resultado encontrado';
            },
            loadingMore: function() {
                return 'Carregando mais resultados...';
            }
        },
        templateResult: function(item) {
            if (item.loading) return item.text;
            
            var $result = $('<div class="py-2">' +
                '<div class="font-medium text-gray-900">' + item.text + '</div>' +
                (item.email ? '<div class="text-xs text-gray-500">' + item.email + '</div>' : '') +
                '</div>');
            return $result;
        },
        templateSelection: function(item) {
            return item.text;
        }
    });

    // Carrega dados iniciais quando o modal é aberto
    const modalOrdemServico = document.querySelector('[x-show="modalOrdemServico"]');
    if (modalOrdemServico) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'style') {
                    const isVisible = !modalOrdemServico.style.display || modalOrdemServico.style.display !== 'none';
                    if (isVisible) {
                        // Trigger para carregar dados iniciais
                        $('#tipos-acao-select').select2('open');
                        $('#tipos-acao-select').select2('close');
                        $('#tecnicos-select').select2('open');
                        $('#tecnicos-select').select2('close');
                    }
                }
            });
        });
        observer.observe(modalOrdemServico, { attributes: true });
    }
});
</script>
@endpush

@endsection
