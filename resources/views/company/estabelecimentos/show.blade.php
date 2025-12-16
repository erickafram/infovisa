@extends('layouts.company')

@section('title', 'Detalhes do Estabelecimento')
@section('page-title', 'Detalhes do Estabelecimento')

@section('content')
<div class="space-y-6">
    {{-- Mensagem de Sucesso --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-green-800">Sucesso!</h3>
                <p class="text-sm text-green-700 mt-1">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Aviso de Status Pendente --}}
    @if($estabelecimento->status === 'pendente')
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-yellow-800">Aguardando Aprovação</h3>
                <p class="text-sm text-yellow-700 mt-1">Este estabelecimento está aguardando análise e aprovação da Vigilância Sanitária. Você será notificado quando houver uma atualização.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Cabeçalho --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <a href="{{ route('company.estabelecimentos.index') }}" class="text-sm text-blue-600 hover:text-blue-700 flex items-center mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar para lista
            </a>
            <h1 class="text-xl font-bold text-gray-900">{{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: $estabelecimento->nome_completo }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $estabelecimento->documento_formatado }}</p>
        </div>
        <span class="px-3 py-1.5 text-sm font-medium rounded-full 
            @if($estabelecimento->status === 'aprovado') bg-green-100 text-green-800
            @elseif($estabelecimento->status === 'pendente') bg-yellow-100 text-yellow-800
            @else bg-red-100 text-red-800 @endif">
            {{ ucfirst($estabelecimento->status) }}
        </span>
    </div>

    @if($estabelecimento->status === 'rejeitado' && $estabelecimento->motivo_rejeicao)
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-red-800">Motivo da Rejeição</h3>
                <p class="text-sm text-red-700 mt-1">{{ $estabelecimento->motivo_rejeicao }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Informações Principais --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Dados do Estabelecimento --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Dados do Estabelecimento</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if($estabelecimento->tipo_pessoa === 'juridica')
                    <div>
                        <dt class="text-xs font-medium text-gray-500">CNPJ</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->cnpj_formatado }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Razão Social</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->razao_social }}</dd>
                    </div>
                    @else
                    <div>
                        <dt class="text-xs font-medium text-gray-500">CPF</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->cpf_formatado }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Nome Completo</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->nome_completo }}</dd>
                    </div>
                    @endif
                    @if($estabelecimento->nome_fantasia)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Nome Fantasia</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->nome_fantasia }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Situação Cadastral</dt>
                        <dd class="text-sm mt-1">
                            <span class="px-2 py-0.5 text-xs font-medium rounded {{ $estabelecimento->situacao_cor }}">
                                {{ $estabelecimento->situacao_label }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Endereço --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Endereço</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-500">Endereço Completo</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->endereco_completo }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Cidade</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->cidade }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Estado</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->estado }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Contato --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Contato</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if($estabelecimento->telefone)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Telefone</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->telefone_formatado }}</dd>
                    </div>
                    @endif
                    @if($estabelecimento->email)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">E-mail</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->email }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Processos do Estabelecimento --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Processos</h2>
                </div>
                
                @if($estabelecimento->processos->count() > 0)
                <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                    @foreach($estabelecimento->processos as $processo)
                    <a href="{{ route('company.processos.show', $processo->id) }}" 
                       class="block px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $processo->numero }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $processo->tipoProcesso->nome ?? 'N/A' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($processo->status === 'concluido') bg-green-100 text-green-800
                                @elseif($processo->status === 'em_andamento') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ str_replace('_', ' ', ucfirst($processo->status)) }}
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="px-6 py-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhum processo</p>
                </div>
                @endif
            </div>

            {{-- Informações Adicionais --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Cadastrado em</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($estabelecimento->aprovado_em)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Aprovado em</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $estabelecimento->aprovado_em->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
