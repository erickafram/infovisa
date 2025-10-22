@extends('layouts.admin')

@section('title', 'Configurações')
@section('page-title', 'Configurações do Sistema')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <p class="text-gray-600">Gerencie as configurações e parâmetros do sistema</p>
    </div>

    {{-- Grid de Cards de Configurações --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        {{-- Tipos de Processo --}}
        <a href="{{ route('admin.configuracoes.tipos-processo.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Tipos de Processo</h3>
                    <p class="text-sm text-gray-600">Configure os tipos de processos disponíveis no sistema</p>
                </div>
            </div>
        </a>

        {{-- Tipos de Documento --}}
        <a href="{{ route('admin.configuracoes.tipos-documento.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Tipos de Documento</h3>
                    <p class="text-sm text-gray-600">Configure os tipos de documentos disponíveis</p>
                </div>
            </div>
        </a>

        {{-- Modelos de Documentos --}}
        <a href="{{ route('admin.configuracoes.modelos-documento.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Modelos de Documentos</h3>
                    <p class="text-sm text-gray-600">Crie e gerencie modelos de documentos digitais</p>
                </div>
            </div>
        </a>

        {{-- Placeholder para futuras configurações --}}
        <div class="block bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-gray-400 mb-1">Mais configurações</h3>
                    <p class="text-sm text-gray-400">Em breve</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
