@extends('layouts.admin')

@section('title', 'Todos os Processos')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Todos os Processos</h1>
            <p class="text-sm text-gray-600 mt-1">Gerencie e filtre todos os processos do sistema</p>
        </div>
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
                                Data Criação
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Criado Por
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($processos as $processo)
                            <tr class="hover:bg-gray-50 transition">
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $processo->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $processo->usuario ? $processo->usuario->nome : 'Sistema' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}" 
                                       class="text-blue-600 hover:text-blue-900 inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Ver Detalhes
                                    </a>
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
