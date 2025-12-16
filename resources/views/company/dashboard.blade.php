@extends('layouts.company')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho de Boas-vindas --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white">
        <h1 class="text-2xl font-bold">Olá, {{ auth('externo')->user()->nome }}!</h1>
        <p class="mt-1 text-blue-100">Bem-vindo ao seu painel de controle.</p>
    </div>

    {{-- Cards de Estatísticas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Estabelecimentos --}}
        <a href="{{ route('company.estabelecimentos.index') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500">Estabelecimentos</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $estatisticasEstabelecimentos['total'] }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- Pendentes --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500">Pendentes</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $estatisticasEstabelecimentos['pendentes'] }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Processos --}}
        <a href="{{ route('company.processos.index') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500">Processos</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $estatisticasProcessos['total'] }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- Em Andamento --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500">Em Andamento</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ $estatisticasProcessos['em_andamento'] }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Área de Alertas (placeholder para futuro) --}}
    {{-- @if($alertas->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Alertas</h2>
        </div>
        <div class="divide-y divide-gray-200">
            -- Alertas serão listados aqui --
        </div>
    </div>
    @endif --}}

    {{-- Últimos Registros --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Últimos Estabelecimentos --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Últimos Estabelecimentos</h2>
                <a href="{{ route('company.estabelecimentos.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Ver todos
                </a>
            </div>
            
            @if($ultimosEstabelecimentos->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($ultimosEstabelecimentos as $estabelecimento)
                <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" 
                   class="block px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: $estabelecimento->nome_completo }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $estabelecimento->documento_formatado }}
                            </p>
                        </div>
                        <span class="ml-4 px-2 py-1 text-xs font-medium rounded-full 
                            @if($estabelecimento->status === 'aprovado') bg-green-100 text-green-800
                            @elseif($estabelecimento->status === 'pendente') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($estabelecimento->status) }}
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="px-6 py-8 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Nenhum estabelecimento</p>
            </div>
            @endif
        </div>

        {{-- Últimos Processos --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Últimos Processos</h2>
                <a href="{{ route('company.processos.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Ver todos
                </a>
            </div>
            
            @if($ultimosProcessos->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($ultimosProcessos as $processo)
                <a href="{{ route('company.processos.show', $processo->id) }}" 
                   class="block px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $processo->numero }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $processo->tipoProcesso->nome ?? 'N/A' }}
                            </p>
                        </div>
                        <span class="ml-4 px-2 py-1 text-xs font-medium rounded-full 
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
    </div>
</div>
@endsection
