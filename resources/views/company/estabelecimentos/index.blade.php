@extends('layouts.company')

@section('title', 'Meus Estabelecimentos')
@section('page-title', 'Meus Estabelecimentos')

@section('content')
<div class="space-y-6">
    {{-- Mensagem de Sucesso --}}
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

    {{-- Cabeçalho --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Gerencie seus estabelecimentos cadastrados</p>
        </div>
        <a href="{{ route('company.estabelecimentos.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Estabelecimento
        </a>
    </div>

    {{-- Estatísticas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">Total</p>
            <p class="text-2xl font-bold text-gray-900">{{ $estatisticas['total'] }}</p>
        </div>
        <a href="{{ route('company.estabelecimentos.index', ['status' => 'pendente']) }}" 
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-gray-500 mb-1">Pendentes</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $estatisticas['pendentes'] }}</p>
        </a>
        <a href="{{ route('company.estabelecimentos.index', ['status' => 'aprovado']) }}"
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-gray-500 mb-1">Aprovados</p>
            <p class="text-2xl font-bold text-green-600">{{ $estatisticas['aprovados'] }}</p>
        </a>
        <a href="{{ route('company.estabelecimentos.index', ['status' => 'rejeitado']) }}"
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-gray-500 mb-1">Rejeitados</p>
            <p class="text-2xl font-bold text-red-600">{{ $estatisticas['rejeitados'] }}</p>
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('company.estabelecimentos.index') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por nome, CNPJ ou CPF..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    Buscar
                </button>
                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('company.estabelecimentos.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                    Limpar
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Lista de Estabelecimentos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($estabelecimentos->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome/Razão Social</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cidade</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($estabelecimentos as $estabelecimento)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $estabelecimento->documento_formatado }}</div>
                            <div class="text-xs text-gray-500">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: $estabelecimento->nome_completo }}</div>
                            @if($estabelecimento->nome_fantasia && $estabelecimento->razao_social)
                            <div class="text-xs text-gray-500">{{ $estabelecimento->razao_social }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $estabelecimento->cidade }} - {{ $estabelecimento->estado }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($estabelecimento->status === 'aprovado') bg-green-100 text-green-800
                                @elseif($estabelecimento->status === 'pendente') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($estabelecimento->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ver detalhes
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Paginação --}}
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $estabelecimentos->links() }}
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum estabelecimento encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">Comece cadastrando seu primeiro estabelecimento.</p>
            <div class="mt-6">
                <a href="{{ route('company.estabelecimentos.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Novo Estabelecimento
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
