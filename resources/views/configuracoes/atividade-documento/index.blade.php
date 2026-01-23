@extends('layouts.admin')

@section('title', 'Documentos por Atividade')
@section('page-title', 'Configuração de Documentos por Atividade')

@section('content')
<div class="max-w-8xl mx-auto" x-data="{ activeTab: '{{ request('tab', 'atividades') }}', showModalLote: false }">
    {{-- Voltar --}}
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Configurações
        </a>
    </div>

    {{-- Descrição --}}
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-800">Nova Estrutura Simplificada</h3>
                <p class="text-sm text-blue-700 mt-1">
                    Configure quais documentos são exigidos para cada atividade (CNAE). 
                    Quando um estabelecimento fizer cadastro, o sistema automaticamente:
                </p>
                <ul class="text-sm text-blue-700 mt-2 list-disc list-inside space-y-1">
                    <li>Identifica as atividades exercidas pelo estabelecimento</li>
                    <li>Busca os documentos vinculados a cada atividade</li>
                    <li>Adiciona os documentos comuns (CNPJ, Contrato Social, etc.)</li>
                    <li>Remove duplicatas automaticamente</li>
                    <li>Filtra por escopo (estadual/municipal) e tipo de setor (público/privado)</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <p class="text-sm text-red-800">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Abas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200 bg-gray-50">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'documentos-comuns'" 
                        :class="activeTab === 'documentos-comuns' ? 'border-purple-500 text-purple-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Documentos Comuns
                    <span class="px-2 py-0.5 text-xs bg-purple-100 text-purple-800 rounded-full">{{ $documentosComuns->count() }}</span>
                </button>
                <button @click="activeTab = 'atividades'" 
                        :class="activeTab === 'atividades' ? 'border-cyan-500 text-cyan-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Documentos por Atividade
                </button>
                <button @click="activeTab = 'tipos-documento'" 
                        :class="activeTab === 'tipos-documento' ? 'border-amber-500 text-amber-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Tipos de Documento
                    <span class="px-2 py-0.5 text-xs bg-amber-100 text-amber-800 rounded-full">{{ $todosDocumentos->count() }}</span>
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- Tab: Documentos Comuns --}}
            <div x-show="activeTab === 'documentos-comuns'" x-transition>
                @include('configuracoes.atividade-documento.partials.tab-documentos-comuns')
            </div>

            {{-- Tab: Documentos por Atividade --}}
            <div x-show="activeTab === 'atividades'" x-transition>
                @include('configuracoes.atividade-documento.partials.tab-atividades')
            </div>

            {{-- Tab: Tipos de Documento --}}
            <div x-show="activeTab === 'tipos-documento'" x-transition>
                @include('configuracoes.atividade-documento.partials.tab-tipos-documento')
            </div>
        </div>
    </div>

    {{-- Modal: Aplicar em Lote --}}
    <div x-show="showModalLote" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showModalLote" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="fixed inset-0 bg-black/50" @click="showModalLote = false"></div>
            
            <div x-show="showModalLote" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-xl shadow-xl max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aplicar Documentos em Lote</h3>
                
                <form action="{{ route('admin.configuracoes.atividade-documento.aplicar-lote') }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        {{-- Seleção de Atividades --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Selecione as Atividades</label>
                            <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 space-y-2">
                                @foreach($tiposServico as $tipoServico)
                                    @if($tipoServico->atividadesAtivas->isNotEmpty())
                                    <div class="mb-3">
                                        <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $tipoServico->nome }}</h5>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach($tipoServico->atividadesAtivas as $atividade)
                                            <label class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                                                <input type="checkbox" name="atividades[]" value="{{ $atividade->id }}"
                                                       class="w-4 h-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                                                <span class="text-sm text-gray-700">{{ $atividade->nome }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- Seleção de Documentos --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Selecione os Documentos</label>
                            <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 space-y-2">
                                @foreach($documentosEspecificos as $doc)
                                <label class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" name="documentos[]" value="{{ $doc->id }}"
                                           class="w-4 h-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                                    <span class="text-sm text-gray-700">{{ $doc->nome }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Opções --}}
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="obrigatorio" value="1" checked
                                       class="w-4 h-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                                <span class="text-sm text-gray-700">Marcar como obrigatório</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="substituir" value="1"
                                       class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <span class="text-sm text-gray-700">Substituir documentos existentes</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showModalLote = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700">
                            Aplicar em Lote
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
