@extends('layouts.admin')

@section('title', 'Relatórios')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Relatórios</h1>
            <p class="text-gray-500 mt-1">Selecione um relatório para visualizar ou exportar</p>
        </div>
    </div>

    {{-- Grid de Relatórios Disponíveis --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        
        {{-- Relatório: Equipamentos de Imagem --}}
        <a href="{{ route('admin.relatorios.equipamentos-radiacao') }}" 
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-orange-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-orange-100 rounded-lg group-hover:bg-orange-200 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-orange-600 transition-colors">
                        Equipamentos de Imagem
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Status de cadastro por estabelecimento
                    </p>
                    <div class="mt-2 flex items-center text-xs text-orange-600 font-medium">
                        <span>Ver</span>
                        <svg class="w-3 h-3 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </a>

        {{-- Relatório: Estabelecimentos --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 opacity-60">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-blue-100 rounded-lg flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900">
                        Estabelecimentos
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Por status e competência
                    </p>
                    <div class="mt-2 flex items-center text-xs text-gray-400 font-medium">
                        <span>Em breve</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Relatório: Processos --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 opacity-60">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-green-100 rounded-lg flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900">
                        Processos
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Por tipo, status e período
                    </p>
                    <div class="mt-2 flex items-center text-xs text-gray-400 font-medium">
                        <span>Em breve</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Relatório: Ordens de Serviço --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 opacity-60">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-purple-100 rounded-lg flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900">
                        Ordens de Serviço
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Por fiscal e status
                    </p>
                    <div class="mt-2 flex items-center text-xs text-gray-400 font-medium">
                        <span>Em breve</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Relatório: Estatísticas Gerais --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 opacity-60">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-indigo-100 rounded-lg flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900">
                        Estatísticas
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Gráficos e indicadores
                    </p>
                    <div class="mt-2 flex items-center text-xs text-gray-400 font-medium">
                        <span>Em breve</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
