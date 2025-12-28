@extends('layouts.admin')

@section('title', 'Todos os Processos')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Todos os Processos</h1>
            <p class="text-sm text-gray-600 mt-1">Gerencie e filtre todos os processos do sistema</p>
            
            @if(!auth('interno')->user()->isAdmin())
                @if(auth('interno')->user()->isEstadual())
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Exibindo apenas processos de competência estadual
                    </div>
                @elseif(auth('interno')->user()->isMunicipal() && auth('interno')->user()->municipio_id)
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Exibindo apenas processos do município: {{ auth('interno')->user()->municipioRelacionado->nome ?? auth('interno')->user()->municipio }}
                    </div>
                @endif
            @endif
        </div>
        
        @if(isset($processosComPendencias) && $processosComPendencias->count() > 0)
        <a href="{{ route('admin.documentos-pendentes.index') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium flex items-center gap-2">
            <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
            {{ $processosComPendencias->count() }} Doc. Pendentes
        </a>
        @endif
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('admin.processos.index-geral') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Número do Processo -->
                <div>
                    <label for="numero_processo" class="block text-sm font-medium text-gray-700 mb-1">
                        Número do Processo
                    </label>
                    <input 
                        type="text" 
                        id="numero_processo" 
                        name="numero_processo" 
                        value="{{ request('numero_processo') }}"
                        placeholder="Ex: 2025/00001"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                </div>

                <!-- Estabelecimento -->
                <div>
                    <label for="estabelecimento" class="block text-sm font-medium text-gray-700 mb-1">
                        Estabelecimento
                    </label>
                    <input 
                        type="text" 
                        id="estabelecimento" 
                        name="estabelecimento" 
                        value="{{ request('estabelecimento') }}"
                        placeholder="Nome ou CNPJ"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                </div>

                <!-- Tipo de Processo -->
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Processo
                    </label>
                    <select 
                        id="tipo" 
                        name="tipo"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                        <option value="">Todos os tipos</option>
                        @foreach($tiposProcesso as $tipo)
                            <option value="{{ $tipo->codigo }}" {{ request('tipo') == $tipo->codigo ? 'selected' : '' }}>
                                {{ $tipo->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Status
                    </label>
                    <select 
                        id="status" 
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                        <option value="">Todos os status</option>
                        @foreach($statusDisponiveis as $key => $nome)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Ano -->
                <div>
                    <label for="ano" class="block text-sm font-medium text-gray-700 mb-1">
                        Ano
                    </label>
                    <select 
                        id="ano" 
                        name="ano"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                        <option value="">Todos os anos</option>
                        @foreach($anos as $anoItem)
                            <option value="{{ $anoItem }}" {{ request('ano') == $anoItem ? 'selected' : '' }}>
                                {{ $anoItem }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Ordenação -->
                <div>
                    <label for="ordenacao" class="block text-sm font-medium text-gray-700 mb-1">
                        Ordenar por
                    </label>
                    <select 
                        id="ordenacao" 
                        name="ordenacao"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                        <option value="recentes" {{ request('ordenacao') == 'recentes' ? 'selected' : '' }}>Mais recentes</option>
                        <option value="antigos" {{ request('ordenacao') == 'antigos' ? 'selected' : '' }}>Mais antigos</option>
                        <option value="numero" {{ request('ordenacao') == 'numero' ? 'selected' : '' }}>Número do processo</option>
                        <option value="estabelecimento" {{ request('ordenacao') == 'estabelecimento' ? 'selected' : '' }}>Estabelecimento</option>
                    </select>
                </div>

                <!-- Filtro por Responsável -->
                <div>
                    <label for="responsavel" class="block text-sm font-medium text-gray-700 mb-1">
                        Responsável/Setor
                    </label>
                    <select 
                        id="responsavel" 
                        name="responsavel"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                        <option value="">Todos</option>
                        <option value="meus" {{ request('responsavel') == 'meus' ? 'selected' : '' }}>📌 Meus processos</option>
                        @if(auth('interno')->user()->setor)
                        <option value="meu_setor" {{ request('responsavel') == 'meu_setor' ? 'selected' : '' }}>🏢 Processos do meu setor</option>
                        @endif
                        <option value="nao_atribuido" {{ request('responsavel') == 'nao_atribuido' ? 'selected' : '' }}>⚠️ Não atribuídos</option>
                    </select>
                </div>

                <!-- Filtro por Docs. Obrigatórios -->
                <div>
                    <label for="docs_obrigatorios" class="block text-sm font-medium text-gray-700 mb-1">
                        Docs. Obrigatórios
                    </label>
                    <select 
                        id="docs_obrigatorios" 
                        name="docs_obrigatorios"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                        <option value="">Todos</option>
                        <option value="completos" {{ request('docs_obrigatorios') == 'completos' ? 'selected' : '' }}>✅ Docs. Completos</option>
                        <option value="pendentes" {{ request('docs_obrigatorios') == 'pendentes' ? 'selected' : '' }}>⏳ Docs. Pendentes</option>
                    </select>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Filtrar
                </button>
                <a href="{{ route('admin.processos.index-geral') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Limpar Filtros
                </a>
            </div>
        </form>
    </div>

    <!-- Resultados -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Header da Tabela -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">
                    Processos Encontrados
                    <span class="ml-2 text-sm font-normal text-gray-500">({{ $processos->total() }} {{ $processos->total() == 1 ? 'processo' : 'processos' }})</span>
                </h2>
            </div>
        </div>

        <!-- Tabela -->
        @if($processos->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Número
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estabelecimento
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Com (Setor/Responsável)
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Docs. Licenciamento
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data Criação
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($processos as $processo)
                            <tr class="hover:bg-gray-50 transition cursor-pointer" onclick="window.location='{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}'">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $processo->numero_processo }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $processo->estabelecimento->cnpj ?? $processo->estabelecimento->cpf }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $processo->tipo_nome }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($processo->status_cor == 'blue') bg-blue-100 text-blue-800
                                        @elseif($processo->status_cor == 'yellow') bg-yellow-100 text-yellow-800
                                        @elseif($processo->status_cor == 'orange') bg-orange-100 text-orange-800
                                        @elseif($processo->status_cor == 'green') bg-green-100 text-green-800
                                        @elseif($processo->status_cor == 'red') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $processo->status_nome }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($processo->setor_atual || $processo->responsavel_atual_id)
                                        <div class="flex flex-col gap-0.5">
                                            @if($processo->setor_atual)
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-cyan-700">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                    {{ $processo->setor_atual_nome }}
                                                </span>
                                            @endif
                                            @if($processo->responsavelAtual)
                                                <span class="text-xs text-gray-600">
                                                    {{ $processo->responsavelAtual->nome }}
                                                </span>
                                            @endif
                                            @if($processo->responsavel_desde)
                                                <span class="text-[10px] text-gray-400">
                                                    desde {{ $processo->responsavel_desde->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Não atribuído</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($statusDocsObrigatorios[$processo->id]))
                                        @php $docs = $statusDocsObrigatorios[$processo->id]; @endphp
                                        <div class="flex flex-col gap-1">
                                            @if($docs['completo'])
                                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    {{ $docs['ok'] }}/{{ $docs['total'] }} OK
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">
                                                    {{ $docs['ok'] }}/{{ $docs['total'] }}
                                                    @if($docs['nao_enviado'] > 0)
                                                        <span class="text-gray-500">({{ $docs['nao_enviado'] }} falta)</span>
                                                    @endif
                                                </span>
                                            @endif
                                            @if($docs['pendente'] > 0)
                                                <span class="text-[10px] text-purple-600 font-medium">+{{ $docs['pendente'] }} pendente(s)</span>
                                            @endif
                                            @if($docs['rejeitado'] > 0)
                                                <span class="text-[10px] text-red-600 font-medium">{{ $docs['rejeitado'] }} rejeitado(s)</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $processo->created_at->format('d/m/Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $processos->links() }}
            </div>
        @else
            <!-- Sem Resultados -->
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum processo encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Tente ajustar os filtros ou limpar a busca.
                </p>
                <div class="mt-6">
                    <a href="{{ route('admin.processos.index-geral') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Limpar Filtros
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
