@extends('layouts.admin')

@section('title', 'Busca Diário Oficial - Tocantins')
@section('page-title', 'Diário Oficial - TO')

@section('content')
<div class="max-w-8xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="p-3 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Busca Diário Oficial</h1>
                <p class="text-gray-600">Encontre documentos publicados no Diário Oficial do Tocantins</p>
            </div>
        </div>
    </div>

    <!-- Buscas Salvas -->
    <div id="savedSearchesSection" class="mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <h2 class="text-lg font-semibold text-gray-900">Buscas Salvas</h2>
                        <span id="savedSearchesCount" class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full">0</span>
                    </div>
                    <button id="toggleSavedSearches" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="savedSearchesList" class="p-6 hidden">
                <div id="savedSearchesContent" class="space-y-3">
                    <!-- Buscas salvas serão inseridas aqui via JavaScript -->
                </div>
                <div id="noSavedSearches" class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <p>Nenhuma busca salva ainda</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário de Busca -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Nova Busca</h2>
        </div>
        <div class="p-6">
            <form id="searchForm" class="space-y-4">
                @csrf
                
                <!-- Texto de Busca -->
                <div>
                    <label for="texto" class="block text-sm font-medium text-gray-700 mb-2">
                        Texto para buscar *
                    </label>
                    <input type="text" 
                           id="texto" 
                           name="texto" 
                           required
                           minlength="3"
                           placeholder="Ex: PORTARIA, DECRETO, nome da empresa..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    <p class="mt-1 text-sm text-gray-500">Mínimo 3 caracteres</p>
                </div>

                <!-- Período -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="data_inicial" class="block text-sm font-medium text-gray-700 mb-2">
                            Data Inicial *
                        </label>
                        <input type="date" 
                               id="data_inicial" 
                               name="data_inicial" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    </div>
                    <div>
                        <label for="data_final" class="block text-sm font-medium text-gray-700 mb-2">
                            Data Final *
                        </label>
                        <input type="date" 
                               id="data_final" 
                               name="data_final" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" 
                            id="searchButton"
                            class="flex-1 md:flex-none px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-lg hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-md hover:shadow-lg">
                        <svg class="inline-block w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Buscar
                    </button>
                    <button type="button" 
                            id="saveSearchButton"
                            class="px-6 py-3 bg-white border-2 border-indigo-600 text-indigo-600 font-medium rounded-lg hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                        <svg class="inline-block w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        Salvar Busca
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading" class="hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-4 border-indigo-600 mb-4"></div>
                <p class="text-gray-600 font-medium">Buscando nos Diários Oficiais...</p>
                <p class="text-gray-500 text-sm mt-2">Isso pode levar alguns segundos</p>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div id="results" class="hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h2 class="text-lg font-semibold text-gray-900">Resultados da Busca</h2>
                        <span id="resultsCount" class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">0</span>
                    </div>
                </div>
            </div>
            <div id="resultsList" class="divide-y divide-gray-200">
                <!-- Resultados serão inseridos aqui via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Error -->
    <div id="error" class="hidden">
        <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-red-800 font-semibold mb-1">Erro na busca</h3>
                    <p id="errorMessage" class="text-red-700"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Salvar Busca -->
<div id="saveSearchModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 rounded-t-xl">
            <h3 class="text-xl font-semibold text-white">Salvar Busca</h3>
        </div>
        <form id="saveSearchForm" class="p-6">
            @csrf
            <div class="mb-4">
                <label for="search_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nome da Busca *
                </label>
                <input type="text" 
                       id="search_name" 
                       name="nome" 
                       required
                       placeholder="Ex: Portarias de Licenciamento"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="flex gap-3">
                <button type="button" 
                        id="cancelSaveButton"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/diario-search.js') }}?v={{ time() }}"></script>
@endpush
