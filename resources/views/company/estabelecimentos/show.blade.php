@extends('layouts.company')

@section('title', 'Detalhes do Estabelecimento')
@section('page-title', 'Detalhes do Estabelecimento')

@section('content')
<div class="space-y-6">
    {{-- Mensagens --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    {{-- Alerta de Status --}}
    @if($estabelecimento->status === 'pendente')
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
        <div class="flex">
            <svg class="w-5 h-5 text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-yellow-800">Aguardando Aprovação</p>
                <p class="text-xs text-yellow-700 mt-1">Este estabelecimento está em análise pela Vigilância Sanitária.</p>
            </div>
        </div>
    </div>
    @elseif($estabelecimento->status === 'rejeitado')
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
        <div class="flex">
            <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-red-800">Estabelecimento Rejeitado</p>
                @if($estabelecimento->motivo_rejeicao)
                <p class="text-xs text-red-700 mt-1"><strong>Motivo:</strong> {{ $estabelecimento->motivo_rejeicao }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('company.estabelecimentos.index') }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: $estabelecimento->nome_completo }}</h2>
                <p class="text-sm text-gray-500">{{ $estabelecimento->documento_formatado }}</p>
            </div>
        </div>
        <span class="px-3 py-1.5 text-sm font-medium rounded-full 
            @if($estabelecimento->status === 'aprovado') bg-green-100 text-green-800
            @elseif($estabelecimento->status === 'pendente') bg-yellow-100 text-yellow-800
            @else bg-red-100 text-red-800 @endif">
            {{ ucfirst($estabelecimento->status) }}
        </span>
    </div>

    {{-- Layout 2 Colunas (ou 1 coluna se pendente/rejeitado) --}}
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Menu Lateral - Apenas para estabelecimentos aprovados --}}
        @if($estabelecimento->status === 'aprovado')
        <div class="lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sticky top-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Menu</h3>
                <nav class="space-y-1">
                    {{-- Editar Dados --}}
                    <a href="{{ route('company.estabelecimentos.edit', $estabelecimento->id) }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Dados
                    </a>

                    {{-- Responsáveis --}}
                    <a href="{{ route('company.estabelecimentos.responsaveis.index', $estabelecimento->id) }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Responsáveis
                    </a>

                    {{-- Atividades --}}
                    <a href="{{ route('company.estabelecimentos.atividades.edit', $estabelecimento->id) }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Atividades
                    </a>

                    {{-- Processos --}}
                    <a href="{{ route('company.estabelecimentos.processos.index', $estabelecimento->id) }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Processos
                        @if($estabelecimento->processos->count() > 0)
                        <span class="ml-auto bg-blue-100 text-blue-600 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $estabelecimento->processos->count() }}</span>
                        @endif
                    </a>

                    {{-- Usuários Vinculados --}}
                    <a href="{{ route('company.estabelecimentos.usuarios.index', $estabelecimento->id) }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Usuários Vinculados
                    </a>

                    {{-- Equipamentos de Imagem (só aparece se o estabelecimento exige) --}}
                    @if(\App\Models\AtividadeEquipamentoRadiacao::estabelecimentoExigeEquipamentos($estabelecimento))
                    <a href="{{ route('company.estabelecimentos.equipamentos-radiacao.index', $estabelecimento->id) }}" 
                       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-yellow-50 hover:text-yellow-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Equipamentos de Imagem
                        @php
                            $qtdEquipamentos = $estabelecimento->equipamentosRadiacao()->count();
                        @endphp
                        @if($qtdEquipamentos > 0)
                        <span class="ml-auto bg-yellow-100 text-yellow-600 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $qtdEquipamentos }}</span>
                        @else
                        <span class="ml-auto bg-red-100 text-red-600 text-xs font-semibold px-2 py-0.5 rounded-full">!</span>
                        @endif
                    </a>
                    @endif
                </nav>

                {{-- Abrir Processo --}}
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('company.estabelecimentos.processos.create', $estabelecimento->id) }}" 
                       class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Abrir Processo
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Conteúdo Principal --}}
        <div class="flex-1 space-y-6">
            {{-- Informações Gerais --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informações Gerais
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'Razão Social' : 'Nome Completo' }}</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->nome_razao_social }}</p>
                    </div>
                    @if($estabelecimento->nome_fantasia)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nome Fantasia</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->nome_fantasia }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $estabelecimento->documento_formatado }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Situação Cadastral</label>
                        <span class="px-2 py-0.5 text-xs font-medium rounded {{ $estabelecimento->situacao_cor }}">
                            {{ $estabelecimento->situacao_label }}
                        </span>
                    </div>
                    @if($estabelecimento->telefone)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Telefone</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->telefone_formatado }}</p>
                    </div>
                    @endif
                    @if($estabelecimento->email)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">E-mail</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->email }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Endereço --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Endereço
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Endereço Completo</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->endereco_completo }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Cidade</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->cidade }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                        <p class="text-sm text-gray-900">{{ $estabelecimento->estado }}</p>
                    </div>
                </div>
            </div>

            {{-- Atividades Exercidas --}}
            @if($estabelecimento->atividades_exercidas && count($estabelecimento->atividades_exercidas) > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Atividades Exercidas
                </h3>
                <div class="space-y-2">
                    @foreach($estabelecimento->atividades_exercidas as $atividade)
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="flex-shrink-0 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-mono rounded">
                            {{ $atividade['codigo'] ?? '-' }}
                        </span>
                        <span class="text-sm text-gray-700">{{ $atividade['descricao'] ?? '-' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Processos Recentes --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Processos
                    </h3>
                    @if($estabelecimento->status === 'aprovado')
                    <a href="{{ route('company.estabelecimentos.processos.index', $estabelecimento->id) }}" class="text-xs text-blue-600 hover:text-blue-700">Ver todos →</a>
                    @endif
                </div>
                
                @if($estabelecimento->processos->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($estabelecimento->processos->take(5) as $processo)
                    <a href="{{ route('company.processos.show', $processo->id) }}" class="block px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $processo->numero_processo }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $processo->tipoProcesso->nome ?? 'N/A' }}</p>
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
                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhum processo</p>
                    @if($estabelecimento->status === 'aprovado')
                    <a href="{{ route('company.estabelecimentos.processos.create', $estabelecimento->id) }}" class="mt-3 inline-flex items-center text-sm text-blue-600 hover:text-blue-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Abrir primeiro processo
                    </a>
                    @endif
                </div>
                @endif
            </div>

            {{-- Informações do Cadastro --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Informações do Cadastro</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-gray-500">Cadastrado em</dt>
                        <dd class="text-gray-900">{{ $estabelecimento->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($estabelecimento->aprovado_em)
                    <div>
                        <dt class="text-xs text-gray-500">Aprovado em</dt>
                        <dd class="text-gray-900">{{ $estabelecimento->aprovado_em->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
