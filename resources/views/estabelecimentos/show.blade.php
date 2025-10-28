@extends('layouts.admin')

@section('title', 'Detalhes do Estabelecimento')
@section('page-title', 'Detalhes do Estabelecimento')

@section('content')
<div class="space-y-6">
    {{-- Header com botões --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $estabelecimento->nome_fantasia }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $estabelecimento->documento_formatado }}</p>
            </div>
        </div>

        {{-- Badge de Status --}}
        <div class="flex items-center gap-2">
            @php
                $statusConfig = [
                    'pendente' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pendente'],
                    'aprovado' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Aprovado'],
                    'rejeitado' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Rejeitado'],
                    'arquivado' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Arquivado'],
                ];
                $config = $statusConfig[$estabelecimento->status] ?? $statusConfig['pendente'];
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                {{ $config['label'] }}
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $estabelecimento->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $estabelecimento->ativo ? 'Ativo' : 'Inativo' }}
            </span>
        </div>
    </div>

    {{-- Alerta de Status Pendente/Rejeitado --}}
    @if($estabelecimento->status === 'pendente')
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-yellow-800">
                    Este estabelecimento está aguardando aprovação
                </p>
                <p class="mt-1 text-sm text-yellow-700">
                    Analise os dados e aprove ou rejeite o cadastro.
                </p>
            </div>
        </div>
    </div>
    @elseif($estabelecimento->status === 'rejeitado')
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-red-800">
                    Este estabelecimento foi rejeitado
                </p>
                @if($estabelecimento->motivo_rejeicao)
                <p class="mt-1 text-sm text-red-700">
                    <strong>Motivo:</strong> {{ $estabelecimento->motivo_rejeicao }}
                </p>
                @endif
                @if($estabelecimento->aprovadoPor)
                <p class="mt-1 text-xs text-red-600">
                    Rejeitado por {{ $estabelecimento->aprovadoPor->nome }} em {{ $estabelecimento->aprovado_em->format('d/m/Y H:i') }}
                </p>
                @endif
            </div>
        </div>
    </div>
    @elseif($estabelecimento->status === 'aprovado' && $estabelecimento->aprovadoPor)
    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    Aprovado por <strong>{{ $estabelecimento->aprovadoPor->nome }}</strong> em {{ $estabelecimento->aprovado_em->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Layout de 2 Colunas --}}
    <style>
        @media (max-width: 768px) {
            .estabelecimento-container {
                flex-direction: column !important;
            }
            .estabelecimento-menu {
                width: 100% !important;
                min-width: unset !important;
            }
            .estabelecimento-menu-sticky {
                position: relative !important;
                top: 0 !important;
            }
        }
    </style>
    <div class="estabelecimento-container" style="display: flex; gap: 1.5rem;">
        {{-- Coluna Esquerda - Menu de Ações --}}
        <div class="estabelecimento-menu space-y-4" style="width: 280px; min-width: 280px;">
            <div class="estabelecimento-menu-sticky bg-white rounded-lg shadow-sm border border-gray-200 p-4 sticky top-20">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Ações</h3>
                <div class="space-y-2">
                    @if($estabelecimento->ativo)
                    {{-- Editar --}}
                    <a href="{{ route('admin.estabelecimentos.edit', $estabelecimento->id) }}" 
                       class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Dados
                    </a>

                    {{-- Responsáveis (apenas para pessoa jurídica) --}}
                    @if($estabelecimento->tipo_pessoa === 'juridica')
                    <a href="{{ route('admin.estabelecimentos.responsaveis.index', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Responsáveis
                    </a>
                    @endif

                    {{-- Atividades --}}
                    <a href="{{ route('admin.estabelecimentos.atividades.edit', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Atividades
                    </a>

                    {{-- Processos --}}
                    <a href="{{ route('admin.estabelecimentos.processos.index', $estabelecimento->id) }}" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Processos
                    </a>

                    {{-- Documentos --}}
                    <button class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Documentos
                    </button>

                    {{-- Histórico --}}
                    <a href="{{ route('admin.estabelecimentos.historico', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Histórico
                    </a>

                    {{-- Usuários Vinculados --}}
                    <a href="{{ route('admin.estabelecimentos.usuarios.index', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Usuários Vinculados
                    </a>

                    <hr class="my-4">

                    {{-- Ações de Aprovação --}}
                    @if($estabelecimento->status === 'pendente')
                        <button onclick="document.getElementById('modal-aprovar').classList.remove('hidden')"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Aprovar
                        </button>

                        <button onclick="document.getElementById('modal-rejeitar').classList.remove('hidden')"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Rejeitar
                        </button>
                    @elseif($estabelecimento->status === 'rejeitado')
                        <button onclick="document.getElementById('modal-reiniciar').classList.remove('hidden')"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reiniciar
                        </button>
                    @endif

                    @if(auth('interno')->user()->nivel_acesso->isAdmin())
                    <hr class="my-4">

                    {{-- Voltar para Pendente (apenas para aprovados sem processos) --}}
                    @if($estabelecimento->status === 'aprovado' && $estabelecimento->processos()->count() === 0)
                        <button onclick="document.getElementById('modal-voltar-pendente').classList.remove('hidden')"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>
                            </svg>
                            Voltar para Pendente
                        </button>
                    @endif

                    {{-- Alterar Competência (apenas para administradores) --}}
                    <button onclick="document.getElementById('modal-alterar-competencia').classList.remove('hidden')"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-white bg-{{ $estabelecimento->isCompetenciaEstadual() ? 'purple' : 'blue' }}-600 hover:bg-{{ $estabelecimento->isCompetenciaEstadual() ? 'purple' : 'blue' }}-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Alterar Competência
                    </button>

                    {{-- Desativar --}}
                    <button onclick="document.getElementById('modal-desativar').classList.remove('hidden')"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Desativar
                    </button>
                    @endif

                    @else
                    {{-- Estabelecimento Desativado - Mostrar apenas Histórico, Ativar e Excluir --}}
                    {{-- Histórico --}}
                    <a href="{{ route('admin.estabelecimentos.historico', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Histórico
                    </a>

                    @if(auth('interno')->user()->nivel_acesso->isAdmin())
                    <hr class="my-4">

                    <form action="{{ route('admin.estabelecimentos.ativar', $estabelecimento->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Tem certeza que deseja reativar este estabelecimento?')"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ativar
                        </button>
                    </form>
                    @endif
                    @endif

                    {{-- Excluir (apenas admin) --}}
                    @if(auth('interno')->user()->nivel_acesso->isAdmin())
                    <form action="{{ route('admin.estabelecimentos.destroy', $estabelecimento->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('⚠️ ATENÇÃO!\n\nTem certeza que deseja EXCLUIR este estabelecimento?\n\nEsta ação é IRREVERSÍVEL e irá remover:\n- Todos os dados do estabelecimento\n- Responsáveis vinculados\n- Histórico de processos\n- Documentos anexados\n\nDeseja continuar?');"
                          class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors group">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Excluir Estabelecimento
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Coluna Direita - Dados do Estabelecimento --}}
        <div class="space-y-6" style="flex: 1;">
            {{-- Informações Gerais --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informações Gerais
                </h3>
                <div class="grid grid-cols-2 gap-x-8">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'Razão Social' : 'Nome Completo' }}</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->nome_razao_social }}</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Nome Fantasia</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->nome_fantasia ?? '-' }}</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $estabelecimento->documento_formatado }}</p>
                    </div>
                    
                    @if($estabelecimento->tipo_pessoa === 'fisica')
                    {{-- Campos específicos de Pessoa Física --}}
                    @if($estabelecimento->rg)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">RG</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->rg }}</p>
                    </div>
                    @endif
                    @if($estabelecimento->orgao_emissor)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Órgão Emissor</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->orgao_emissor }}</p>
                    </div>
                    @endif
                    @endif
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Tipo de Setor</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->tipo_setor ? ucfirst($estabelecimento->tipo_setor->value) : '-' }}</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Situação Cadastral</label>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $estabelecimento->situacao_cor }}">
                            {{ $estabelecimento->situacao_label }}
                        </span>
                    </div>
                    @if($estabelecimento->natureza_juridica)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Natureza Jurídica</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->natureza_juridica }}</p>
                    </div>
                    @endif
                    @if($estabelecimento->porte)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Porte</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->porte }}</p>
                    </div>
                    @endif
                    @if($estabelecimento->descricao_situacao_cadastral)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Situação Cadastral</label>
                        <p class="text-sm text-gray-900">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                {{ $estabelecimento->descricao_situacao_cadastral === 'ATIVA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $estabelecimento->descricao_situacao_cadastral }}
                            </span>
                        </p>
                        @if($estabelecimento->data_situacao_cadastral)
                        <p class="text-xs text-gray-500 mt-1">Desde: {{ $estabelecimento->data_situacao_cadastral->format('d/m/Y') }}</p>
                        @endif
                    </div>
                    @endif
                    @if($estabelecimento->telefone)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Telefone Principal</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $estabelecimento->telefone }}</p>
                    </div>
                    @endif
                    @if($estabelecimento->telefone2)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Telefone 2</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $estabelecimento->telefone2 }}</p>
                    </div>
                    @endif
                    @if($estabelecimento->email)
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">E-mail</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->email }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Endereço --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Endereço
                </h3>
                <div class="grid grid-cols-2 gap-x-8">
                    <div class="col-span-2 mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Logradouro</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->endereco }}, {{ $estabelecimento->numero }}</p>
                    </div>
                    @if($estabelecimento->complemento)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Complemento</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->complemento }}</p>
                    </div>
                    @endif
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Bairro</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->bairro }}</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Município</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->cidade }}</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Estado</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->estado }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">CEP</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $estabelecimento->cep }}</p>
                    </div>
                </div>
            </div>

            {{-- Atividades Econômicas --}}
            <div id="atividades" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Atividades Econômicas Exercidas
                </h3>
                
                @if($estabelecimento->atividades_exercidas && count($estabelecimento->atividades_exercidas) > 0)
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($estabelecimento->atividades_exercidas as $atividade)
                        <div class="flex items-start gap-3 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-sm transition-shadow">
                            @if(isset($atividade['principal']) && $atividade['principal'])
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500 text-white shadow-sm">
                                ⭐ Principal
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-400 text-white shadow-sm">
                                Secundária
                            </span>
                            @endif
                            <div class="flex-1">
                                <p class="text-sm font-bold text-gray-900 mb-1">{{ $atividade['codigo'] ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-700">{{ $atividade['descricao'] ?? 'Sem descrição' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Nenhuma atividade econômica cadastrada.</p>
                        <p class="mt-1 text-xs text-gray-400">As atividades são selecionadas durante o cadastro do estabelecimento.</p>
                    </div>
                @endif
            </div>

            {{-- Informações do Sistema --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informações do Sistema
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Cadastrado em</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $estabelecimento->created_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Última atualização</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $estabelecimento->updated_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">ID do Sistema</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono">#{{ $estabelecimento->id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Aprovar --}}
    <div id="modal-aprovar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Aprovar Estabelecimento</h3>
                    <button onclick="document.getElementById('modal-aprovar').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('admin.estabelecimentos.aprovar', $estabelecimento->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                        <textarea id="observacao" name="observacao" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Adicione uma observação sobre a aprovação..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-aprovar').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Aprovar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Rejeitar --}}
    <div id="modal-rejeitar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Rejeitar Estabelecimento</h3>
                    <button onclick="document.getElementById('modal-rejeitar').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('admin.estabelecimentos.rejeitar', $estabelecimento->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="motivo_rejeicao" class="block text-sm font-medium text-gray-700 mb-2">Motivo da Rejeição *</label>
                        <textarea id="motivo_rejeicao" name="motivo_rejeicao" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Descreva o motivo da rejeição..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="observacao_rejeitar" class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                        <textarea id="observacao_rejeitar" name="observacao" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Observações adicionais..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-rejeitar').classList.add('hidden')"
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

    {{-- Modal Reiniciar --}}
    <div id="modal-reiniciar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Reiniciar Estabelecimento</h3>
                    <button onclick="document.getElementById('modal-reiniciar').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">O status do estabelecimento voltará para "Pendente" e poderá ser reanalisado.</p>
                <form action="{{ route('admin.estabelecimentos.reiniciar', $estabelecimento->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="observacao_reiniciar" class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                        <textarea id="observacao_reiniciar" name="observacao" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                  placeholder="Motivo do reinício..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-reiniciar').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                            Reiniciar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Desativar --}}
    <div id="modal-desativar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Desativar Estabelecimento</h3>
                    <button onclick="document.getElementById('modal-desativar').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">O estabelecimento será desativado e ficará inativo no sistema.</p>
                <form action="{{ route('admin.estabelecimentos.desativar', $estabelecimento->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="motivo_desativar" class="block text-sm font-medium text-gray-700 mb-2">Motivo da Desativação *</label>
                        <textarea id="motivo_desativar" name="motivo" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Descreva o motivo da desativação..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-desativar').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Desativar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Voltar para Pendente --}}
    <div id="modal-voltar-pendente" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Voltar para Pendente</h3>
                    <button onclick="document.getElementById('modal-voltar-pendente').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">O estabelecimento voltará para o status "Pendente" e poderá ser reanalisado ou rejeitado.</p>
                <form action="{{ route('admin.estabelecimentos.voltar-pendente', $estabelecimento->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="observacao_voltar" class="block text-sm font-medium text-gray-700 mb-2">Motivo *</label>
                        <textarea id="observacao_voltar" name="observacao" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                  placeholder="Informe o motivo para voltar para pendente..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-voltar-pendente').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Alterar Competência --}}
    <div id="modal-alterar-competencia" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Alterar Competência</h3>
                    </div>
                    <button onclick="document.getElementById('modal-alterar-competencia').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                {{-- Alerta de Aviso --}}
                <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">⚠️ Atenção: Alteração Manual de Competência</p>
                            <p class="text-xs text-yellow-700 mt-1">
                                Esta ação sobrescreve a regra de pactuação automática. Use apenas em casos excepcionais (decisão judicial, administrativa, etc.).
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Competência Atual --}}
                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-600 mb-1">Competência Atual:</p>
                    <p class="text-lg font-bold text-{{ $estabelecimento->isCompetenciaEstadual() ? 'purple' : 'blue' }}-600">
                        {{ $estabelecimento->isCompetenciaEstadual() ? '🏛️ ESTADUAL' : '🏘️ MUNICIPAL' }}
                        @if($estabelecimento->competencia_manual)
                            <span class="text-xs font-normal text-orange-600">(Alterada Manualmente)</span>
                        @endif
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.estabelecimentos.alterar-competencia', $estabelecimento->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nova Competência *</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="relative flex flex-col p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                <input type="radio" name="competencia_manual" value="municipal" required
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 mb-2">
                                <div>
                                    <span class="block text-sm font-semibold text-gray-900">🏘️ Municipal</span>
                                    <span class="block text-xs text-gray-500 mt-1">Forçar competência municipal</span>
                                </div>
                            </label>
                            <label class="relative flex flex-col p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-500 transition-colors">
                                <input type="radio" name="competencia_manual" value="estadual" required
                                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 mb-2">
                                <div>
                                    <span class="block text-sm font-semibold text-gray-900">🏛️ Estadual</span>
                                    <span class="block text-xs text-gray-500 mt-1">Forçar competência estadual</span>
                                </div>
                            </label>
                            <label class="relative flex flex-col p-4 border-2 border-green-200 bg-green-50 rounded-lg cursor-pointer hover:border-green-500 transition-colors">
                                <input type="radio" name="competencia_manual" value="automatica" required
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 mb-2">
                                <div>
                                    <span class="block text-sm font-semibold text-gray-900">⚙️ Automática</span>
                                    <span class="block text-xs text-gray-500 mt-1">Seguir regras de pactuação</span>
                                </div>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            💡 Selecione "Automática" para remover o override manual e voltar a seguir as regras de pactuação.
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="motivo_alteracao" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo da Alteração * <span class="text-xs text-gray-500">(mínimo 10 caracteres)</span>
                        </label>
                        <textarea id="motivo_alteracao" name="motivo_alteracao_competencia" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm"
                                  placeholder="Ex: Decisão judicial nº 12345/2025 determinou a transferência de competência para o município..."></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Informe o motivo legal/administrativo para esta alteração (decisão judicial, portaria, etc.)
                        </p>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-alterar-competencia').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium">
                            Confirmar Alteração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
