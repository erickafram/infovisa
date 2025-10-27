@extends('layouts.admin')

@section('title', 'Detalhes do Município')
@section('page-title', $municipio->nome)

@section('content')
<div class="max-w-8xl mx-auto">
    
    {{-- Breadcrumb --}}
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-600">
            <a href="{{ route('admin.configuracoes.municipios.index') }}" class="hover:text-blue-600 transition">
                Municípios
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">{{ $municipio->nome }}</span>
        </nav>
    </div>

    {{-- Informações Principais --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $municipio->nome }}</h2>
                    <p class="text-sm text-gray-500">Código IBGE: {{ $municipio->codigo_ibge }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.configuracoes.municipios.edit', $municipio->id) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Editar
                </a>
                <span class="px-4 py-2 rounded-lg {{ $municipio->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $municipio->ativo ? 'Ativo' : 'Inativo' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-600 mb-1">UF</p>
                <p class="text-lg font-semibold text-gray-900">{{ $municipio->uf }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Slug</p>
                <p class="text-lg font-mono text-gray-900">{{ $municipio->slug }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Cadastrado em</p>
                <p class="text-lg text-gray-900">{{ $municipio->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Estatísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Estabelecimentos</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['estabelecimentos_total'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['estabelecimentos_ativos'] }} ativos</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pendentes</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['estabelecimentos_pendentes'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Aguardando aprovação</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pactuações Municipais</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['pactuacoes_municipais'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Atividades próprias</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Descentralizações</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['pactuacoes_excecoes'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Atividades estaduais</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Estabelecimentos Recentes --}}
    @if($municipio->estabelecimentos()->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Estabelecimentos Recentes</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CNPJ/CPF</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cadastrado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($municipio->estabelecimentos()->latest()->take(5)->get() as $estabelecimento)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $estabelecimento->cnpj ?? $estabelecimento->cpf }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $estabelecimento->status === 'aprovado' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $estabelecimento->status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $estabelecimento->status === 'rejeitado' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($estabelecimento->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $estabelecimento->created_at->format('d/m/Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($municipio->estabelecimentos()->count() > 5)
        <div class="mt-4 text-center">
            <a href="{{ route('admin.estabelecimentos.index', ['municipio' => $municipio->nome]) }}" 
               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Ver todos os estabelecimentos →
            </a>
        </div>
        @endif
    </div>
    @endif

    {{-- Pactuações --}}
    @if($municipio->pactuacoes()->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pactuações Municipais</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CNAE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($municipio->pactuacoes as $pactuacao)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $pactuacao->cnae_codigo }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $pactuacao->cnae_descricao }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full {{ $pactuacao->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $pactuacao->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
