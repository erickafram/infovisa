@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-4">
    {{-- Mensagem de boas-vindas --}}
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-xl font-bold text-gray-900">
            Olá, {{ auth('interno')->user()->nome }}! 👋
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Bem-vindo ao painel administrativo do InfoVISA. 
            Nível de acesso: <span class="font-semibold text-blue-600">{{ auth('interno')->user()->nivel_acesso->label() }}</span>
        </p>
    </div>

    {{-- Documentos Pendentes de Assinatura --}}
    @if($stats['documentos_pendentes_assinatura'] > 0)
    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-400 rounded-lg shadow-md">
        <div class="px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        ✍️ Você tem {{ $stats['documentos_pendentes_assinatura'] }} documento(s) aguardando assinatura
                    </h3>
                    <p class="text-sm text-gray-600">
                        Documentos digitais que precisam da sua assinatura para serem finalizados
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.assinatura.pendentes') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600 transition-colors shadow-sm">
                Ver Documentos
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        
        @if($documentos_pendentes_assinatura->count() > 0)
        <div class="px-4 pb-4">
            <div class="bg-white rounded-lg shadow-sm divide-y divide-gray-200">
                @foreach($documentos_pendentes_assinatura as $assinatura)
                <div class="p-3 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $assinatura->documentoDigital->tipoDocumento->nome ?? 'Documento' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    @if($assinatura->documentoDigital->processo)
                                        Processo: {{ $assinatura->documentoDigital->processo->numero }}
                                    @else
                                        ID: #{{ $assinatura->documentoDigital->id }}
                                    @endif
                                    • Criado em {{ $assinatura->created_at->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('admin.assinatura.assinar', $assinatura->documentoDigital->id) }}" 
                           class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                            ✍️ Assinar
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Documentos em Rascunho Pendentes de Finalização --}}
    @if($stats['documentos_rascunho_pendentes'] > 0)
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 rounded-lg shadow-md">
        <div class="px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-400 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        📝 Você tem {{ $stats['documentos_rascunho_pendentes'] }} documento(s) em rascunho aguardando finalização
                    </h3>
                    <p class="text-sm text-gray-600">
                        Documentos digitais onde você está definido como assinante, mas ainda estão em rascunho
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.documentos.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors shadow-sm">
                Ver Documentos
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        
        @if($documentos_rascunho_pendentes->count() > 0)
        <div class="px-4 pb-4">
            <div class="bg-white rounded-lg shadow-sm divide-y divide-gray-200">
                @foreach($documentos_rascunho_pendentes as $assinatura)
                <div class="p-3 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $assinatura->documentoDigital->tipoDocumento->nome ?? 'Documento' }}
                                    </p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        Rascunho
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    @if($assinatura->documentoDigital->processo)
                                        Processo: {{ $assinatura->documentoDigital->processo->numero_processo ?? 'S/N' }}
                                    @else
                                        ID: #{{ $assinatura->documentoDigital->id }}
                                    @endif
                                    • Criado em {{ $assinatura->created_at->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('admin.documentos.edit', $assinatura->documentoDigital->id) }}" 
                           class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                            📝 Editar
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Lista de Estabelecimentos Pendentes --}}
    @if($estabelecimentos_pendentes->count() > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-base leading-6 font-semibold text-gray-900">
                Estabelecimentos Aguardando Aprovação
            </h3>
            <a href="{{ route('admin.estabelecimentos.pendentes') }}" 
               class="text-sm font-medium text-blue-600 hover:text-blue-700">
                Ver todos ({{ $stats['estabelecimentos_pendentes'] }}) →
            </a>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($estabelecimentos_pendentes as $estabelecimento)
            <div class="p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    {{-- Informações do Estabelecimento --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <h4 class="text-sm font-semibold text-gray-900 truncate">
                                {{ $estabelecimento->nome_razao_social }}
                            </h4>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-xs text-gray-600">
                            <div>
                                <span class="font-medium">Documento:</span>
                                {{ $estabelecimento->documento_formatado }}
                            </div>
                            <div>
                                <span class="font-medium">Município:</span>
                                {{ $estabelecimento->cidade }}/{{ $estabelecimento->estado }}
                            </div>
                            <div>
                                <span class="font-medium">Cadastrado:</span>
                                {{ $estabelecimento->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>

                    {{-- Ações --}}
                    <div class="flex gap-2">
                        <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Visualizar
                        </a>
                        <form action="{{ route('admin.estabelecimentos.aprovar', $estabelecimento->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Tem certeza que deseja aprovar este estabelecimento?')"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Aprovar
                            </button>
                        </form>
                        <button onclick="showRejectModal{{ $estabelecimento->id }}()"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Rejeitar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal de Rejeição --}}
            <div id="modal-rejeitar-{{ $estabelecimento->id }}" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Rejeitar Estabelecimento</h3>
                            <button onclick="hideRejectModal{{ $estabelecimento->id }}()" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <form action="{{ route('admin.estabelecimentos.rejeitar', $estabelecimento->id) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Motivo da Rejeição *</label>
                                <textarea name="motivo_rejeicao" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                          placeholder="Descreva o motivo da rejeição..."></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" onclick="hideRejectModal{{ $estabelecimento->id }}()"
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

            <script>
                function showRejectModal{{ $estabelecimento->id }}() {
                    document.getElementById('modal-rejeitar-{{ $estabelecimento->id }}').classList.remove('hidden');
                }
                function hideRejectModal{{ $estabelecimento->id }}() {
                    document.getElementById('modal-rejeitar-{{ $estabelecimento->id }}').classList.add('hidden');
                }
            </script>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Processos Acompanhados --}}
    @if($processos_acompanhados->count() > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-4 border-b border-gray-200">
            <h3 class="text-base leading-6 font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Processos que Você Está Acompanhando
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Processo
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estabelecimento
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Atualizado
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($processos_acompanhados as $processo)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $processo->numero_processo }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">
                                {{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $processo->estabelecimento->documento_formatado }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $processo->tipo_nome }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($processo->status_cor === 'blue') bg-blue-100 text-blue-800
                                @elseif($processo->status_cor === 'yellow') bg-yellow-100 text-yellow-800
                                @elseif($processo->status_cor === 'orange') bg-orange-100 text-orange-800
                                @elseif($processo->status_cor === 'green') bg-green-100 text-green-800
                                @elseif($processo->status_cor === 'red') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $processo->status_nome }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $processo->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}"
                               class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Tabelas de Dados Recentes --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Usuários Externos Recentes --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4">
                <h3 class="text-base leading-6 font-semibold text-gray-900 mb-3">
                    Usuários Externos Recentes
                </h3>
                <div class="flow-root">
                    <ul class="-my-3 divide-y divide-gray-200">
                        @forelse($usuarios_externos_recentes as $usuario)
                        <li class="py-3">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-medium text-xs">
                                            {{ substr($usuario->nome, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $usuario->nome }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $usuario->email }}
                                    </p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $usuario->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="py-3 text-center text-gray-500 text-xs">
                            Nenhum usuário externo cadastrado ainda.
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Usuários Internos Recentes --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4">
                <h3 class="text-base leading-6 font-semibold text-gray-900 mb-3">
                    Usuários Internos Recentes
                </h3>
                <div class="flow-root">
                    <ul class="-my-3 divide-y divide-gray-200">
                        @forelse($usuarios_internos_recentes as $usuario)
                        <li class="py-3">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-purple-600 font-medium text-xs">
                                            {{ substr($usuario->nome, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $usuario->nome }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $usuario->nivel_acesso->label() }}
                                    </p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $usuario->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="py-3 text-center text-gray-500 text-xs">
                            Nenhum usuário interno cadastrado ainda.
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

