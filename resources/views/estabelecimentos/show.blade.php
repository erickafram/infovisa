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
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $estabelecimento->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $estabelecimento->ativo ? 'Estabelecimento Ativo' : 'Estabelecimento Inativo' }}
            </span>
        </div>
    </div>

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
                    {{-- Editar --}}
                    <a href="{{ route('admin.estabelecimentos.edit', $estabelecimento->id) }}" 
                       class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Dados
                    </a>

                    {{-- Responsáveis --}}
                    <a href="{{ route('admin.estabelecimentos.responsaveis.index', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Responsáveis
                    </a>

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
                    <button class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Histórico
                    </button>

                    <hr class="my-4">

                    {{-- Desativar/Ativar --}}
                    @if($estabelecimento->ativo)
                    <button class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Desativar
                    </button>
                    @else
                    <button class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Ativar
                    </button>
                    @endif

                    {{-- Excluir --}}
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
                        <label class="block text-sm font-medium text-gray-500 mb-2">Razão Social / Nome</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->razao_social ?? $estabelecimento->nome }}</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Nome Fantasia</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->nome_fantasia ?? '-' }}</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">CNPJ/CPF</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $estabelecimento->documento_formatado }}</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Tipo de Setor</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->tipo_setor ? ucfirst($estabelecimento->tipo_setor->value) : '-' }}</p>
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
</div>
@endsection
