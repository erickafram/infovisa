@extends('layouts.admin')

@section('title', 'Nova Ordem de Serviço')
@section('page-title', 'Nova Ordem de Serviço')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.ordens-servico.index') }}" 
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all"
               title="Voltar para lista">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">Nova Ordem de Serviço</h1>
                <p class="text-sm text-gray-500">Preencha os dados abaixo para gerar uma nova OS.</p>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.ordens-servico.store') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Main Column (Left) --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- 1. Vinculação --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 relative z-20">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 rounded-t-xl">
                        <h2 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold ring-2 ring-white">1</span>
                            Vinculação
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Tipo de Abertura</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="relative flex flex-col p-4 bg-white border-2 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50/30 transition-all group">
                                    <input type="radio" name="tipo_vinculacao" value="com_estabelecimento" id="com_estabelecimento" 
                                           {{ old('tipo_vinculacao', 'sem_estabelecimento') == 'com_estabelecimento' ? 'checked' : '' }}
                                           class="absolute top-4 right-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                    <span class="font-bold text-gray-900 group-hover:text-blue-700">Com Estabelecimento</span>
                                    <span class="text-xs text-gray-500 mt-1">Vinculado a uma empresa e processo existente.</span>
                                </label>

                                <label class="relative flex flex-col p-4 bg-white border-2 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50/30 transition-all group">
                                    <input type="radio" name="tipo_vinculacao" value="sem_estabelecimento" id="sem_estabelecimento" 
                                           {{ old('tipo_vinculacao', 'sem_estabelecimento') == 'sem_estabelecimento' ? 'checked' : '' }}
                                           class="absolute top-4 right-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <span class="font-bold text-gray-900 group-hover:text-blue-700">Avulsa / Fiscalização</span>
                                    <span class="text-xs text-gray-500 mt-1">Para fiscalizações de rotina ou denúncias.</span>
                                </label>
                            </div>
                        </div>

                        <div id="estabelecimento-container" style="display: none;" class="space-y-5 pt-5 border-t border-gray-100">
                            <div>
                                <label for="estabelecimento_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Buscar Estabelecimento <span class="text-red-500">*</span>
                                </label>
                                <select name="estabelecimento_id" id="estabelecimento_id" class="w-full">
                                    <option value="">Digite para buscar...</option>
                                    @if(old('estabelecimento_id'))
                                        @php
                                            $estabelecimentoSelecionado = $estabelecimentos->firstWhere('id', old('estabelecimento_id'));
                                        @endphp
                                        @if($estabelecimentoSelecionado)
                                        <option value="{{ $estabelecimentoSelecionado->id }}" selected>
                                            {{ $estabelecimentoSelecionado->cnpj }} - {{ $estabelecimentoSelecionado->nome_fantasia }}
                                        </option>
                                        @endif
                                    @endif
                                </select>
                                @error('estabelecimento_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div id="processo-container">
                                <label for="processo_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Processo Vinculado <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="processo_id" id="processo_id" disabled
                                            class="w-full pl-3 pr-10 py-2 text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-gray-50 disabled:bg-gray-100 disabled:text-gray-400">
                                        <option value="">Selecione primeiro um estabelecimento</option>
                                    </select>
                                    @error('processo_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                {{-- Feedback Processo --}}
                                <div id="processo-info" class="hidden mt-2 p-3 bg-blue-50 border border-blue-100 rounded-lg text-sm text-blue-700 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span id="processo-count" class="font-medium"></span>
                                </div>
                                <div id="processo-sem-processo" class="hidden mt-2 p-3 bg-amber-50 border border-amber-100 rounded-lg text-sm text-amber-800 flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <div>
                                        <p class="font-bold">Atenção</p>
                                        <p>Este estabelecimento não possui processos ativos. É obrigatório ter um processo para vincular a OS.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Escopo da OS --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 relative z-10">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 rounded-t-xl">
                        <h2 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold ring-2 ring-white">2</span>
                            Escopo da Ordem de Serviço
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        {{-- Tipos de Ação - Botão para abrir modal --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Tipos de Ação <span class="text-red-500">*</span>
                            </label>
                            <button type="button" onclick="abrirModalTiposAcao()" 
                                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg hover:bg-gray-100 hover:border-blue-400 transition-all text-left">
                                <span id="tipos-acao-display" class="text-gray-500">Clique para selecionar tipos de ação...</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="tipos-acao-tags" class="flex flex-wrap gap-2 mt-2"></div>
                            {{-- Hidden inputs para enviar os valores --}}
                            <div id="tipos-acao-hidden-inputs"></div>
                            @error('tipos_acao_ids')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Técnicos Responsáveis - Botão para abrir modal --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Técnicos Responsáveis <span class="text-red-500">*</span>
                            </label>
                            <button type="button" onclick="abrirModalTecnicos()" 
                                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg hover:bg-gray-100 hover:border-blue-400 transition-all text-left">
                                <span id="tecnicos-display" class="text-gray-500">Clique para selecionar técnicos...</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="tecnicos-tags" class="flex flex-wrap gap-2 mt-2"></div>
                            {{-- Hidden inputs para enviar os valores --}}
                            <div id="tecnicos-hidden-inputs"></div>
                            @error('tecnicos_ids')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Modal Tipos de Ação --}}
                <div id="modal-tipos-acao" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="fecharModalTiposAcao()"></div>
                        <div class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto">
                            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900">Selecionar Tipos de Ação</h3>
                                <button type="button" onclick="fecharModalTiposAcao()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            {{-- Campo de Pesquisa --}}
                            <div class="px-6 py-3 border-b border-gray-100 bg-gray-50">
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" id="pesquisa-tipos-acao" placeholder="Pesquisar tipo de ação..." 
                                           class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onkeyup="filtrarTiposAcao()">
                                </div>
                            </div>
                            <div class="px-6 py-4 max-h-72 overflow-y-auto" id="lista-tipos-acao">
                                <div class="space-y-2">
                                    @foreach($tiposAcao as $tipoAcao)
                                    <label class="tipo-acao-item flex items-center p-3 bg-gray-50 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors border border-transparent hover:border-blue-200" data-nome="{{ strtolower($tipoAcao->descricao) }}">
                                        <input type="checkbox" class="tipo-acao-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                               value="{{ $tipoAcao->id }}" data-label="{{ $tipoAcao->descricao }}"
                                               {{ in_array($tipoAcao->id, old('tipos_acao_ids', [])) ? 'checked' : '' }}>
                                        <span class="ml-3 text-sm text-gray-700">{{ $tipoAcao->descricao }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <p id="sem-resultados-tipos" class="hidden text-center text-gray-500 py-4">Nenhum tipo de ação encontrado</p>
                            </div>
                            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                                <button type="button" onclick="fecharModalTiposAcao()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancelar
                                </button>
                                <button type="button" onclick="confirmarTiposAcao()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                    Confirmar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Técnicos --}}
                <div id="modal-tecnicos" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="fecharModalTecnicos()"></div>
                        <div class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto">
                            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900">Selecionar Técnicos Responsáveis</h3>
                                <button type="button" onclick="fecharModalTecnicos()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            {{-- Campo de Pesquisa --}}
                            <div class="px-6 py-3 border-b border-gray-100 bg-gray-50">
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" id="pesquisa-tecnicos" placeholder="Pesquisar técnico por nome..." 
                                           class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onkeyup="filtrarTecnicos()">
                                </div>
                            </div>
                            <div class="px-6 py-4 max-h-72 overflow-y-auto" id="lista-tecnicos">
                                <div class="space-y-2">
                                    @foreach($tecnicos as $tecnico)
                                    <label class="tecnico-item flex items-center p-3 bg-gray-50 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors border border-transparent hover:border-blue-200" data-nome="{{ strtolower($tecnico->nome) }}">
                                        <input type="checkbox" class="tecnico-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                               value="{{ $tecnico->id }}" data-label="{{ $tecnico->nome }}"
                                               {{ in_array($tecnico->id, old('tecnicos_ids', [])) ? 'checked' : '' }}>
                                        <span class="ml-3 text-sm text-gray-700">{{ $tecnico->nome }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <p id="sem-resultados-tecnicos" class="hidden text-center text-gray-500 py-4">Nenhum técnico encontrado</p>
                            </div>
                            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                                <button type="button" onclick="fecharModalTecnicos()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancelar
                                </button>
                                <button type="button" onclick="confirmarTecnicos()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                    Confirmar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sidebar (Right) --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- 3. Detalhes e Prazos --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-xl">
                        <h2 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-sm">3</span>
                            <span class="text-base">Prazos e Detalhes</span>
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        
                        {{-- Datas em Grid --}}
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Data Início --}}
                            <div class="relative">
                                <label for="data_inicio" class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Início <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="data_inicio" name="data_inicio" value="{{ old('data_inicio') }}" required min="{{ now()->format('Y-m-d') }}"
                                       class="w-full px-4 py-3 text-sm font-medium rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white hover:border-gray-300 transition-all">
                                @error('data_inicio') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Data Fim --}}
                            <div class="relative">
                                <label for="data_fim" class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Término <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="data_fim" name="data_fim" value="{{ old('data_fim') }}" required min="{{ old('data_inicio', now()->format('Y-m-d')) }}"
                                       class="w-full px-4 py-3 text-sm font-medium rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white hover:border-gray-300 transition-all">
                                @error('data_fim') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Observações --}}
                        <div>
                            <label for="observacoes" class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Observações
                            </label>
                            <textarea id="observacoes" name="observacoes" rows="4" placeholder="Descreva detalhes adicionais sobre a ordem de serviço..."
                                      class="w-full px-4 py-3 text-sm rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white hover:border-gray-300 transition-all resize-none">{{ old('observacoes') }}</textarea>
                            @error('observacoes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Upload --}}
                        <div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                Anexo (PDF)
                            </label>
                            <div class="flex items-center justify-center w-full">
                                <label for="documento_anexo" class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gradient-to-b from-gray-50 to-white hover:from-blue-50 hover:to-white hover:border-blue-400 transition-all group">
                                    <div class="flex flex-col items-center justify-center py-4">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center mb-2 transition-colors">
                                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500 group-hover:text-blue-600">Clique para anexar</p>
                                        <p class="text-xs text-gray-400">PDF até 10MB</p>
                                    </div>
                                    <input id="documento_anexo" name="documento_anexo" type="file" accept=".pdf,application/pdf" class="hidden" />
                                </label>
                            </div>
                            {{-- Arquivo Selecionado Feedback --}}
                            <div id="arquivo-selecionado" class="hidden mt-3 p-3 bg-green-50 rounded-xl border border-green-200 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <span id="nome-arquivo" class="text-sm font-medium text-green-700 truncate max-w-[140px]"></span>
                                </div>
                                <button type="button" onclick="removerArquivo()" class="text-green-600 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            @error('documento_anexo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>

                {{-- Ações Sticky --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm hover:shadow-md transition-all mb-3 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Criar Ordem de Serviço
                    </button>
                    <a href="{{ route('admin.ordens-servico.index') }}" 
                       class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-sm">
                        Cancelar
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    /* Customização do Select2 para combinar com Tailwind */
    .select2-container--default .select2-selection--single {
        min-height: 44px !important;
        border-radius: 0.5rem !important;
        border-color: #e5e7eb !important;
        background-color: #f9fafb !important;
        padding-top: 5px !important;
    }
    .select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 42px !important; padding-left: 12px !important; }
    /* Esconder inputs originais de radio para custom styling */
    input[type="radio"]:focus { outline: none; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const comEstabelecimentoRadio = document.getElementById('com_estabelecimento');
        const semEstabelecimentoRadio = document.getElementById('sem_estabelecimento');
        const estabelecimentoContainer = document.getElementById('estabelecimento-container');
        const estabelecimentoSelect = document.getElementById('estabelecimento_id');
        const documentoInput = document.getElementById('documento_anexo');

        function toggleEstabelecimentoField() {
            if (comEstabelecimentoRadio.checked) {
                estabelecimentoContainer.style.display = 'block';
                estabelecimentoSelect.required = true;
            } else {
                estabelecimentoContainer.style.display = 'none';
                estabelecimentoSelect.required = false;
                $(estabelecimentoSelect).val(null).trigger('change');
            }
        }

        comEstabelecimentoRadio.addEventListener('change', toggleEstabelecimentoField);
        semEstabelecimentoRadio.addEventListener('change', toggleEstabelecimentoField);
        toggleEstabelecimentoField();

        documentoInput.addEventListener('change', function(e) {
            const arquivo = e.target.files[0];
            const arquivoContainer = document.getElementById('arquivo-selecionado');
            if (arquivo) {
                if (arquivo.size > 10 * 1024 * 1024) { alert('Máximo 10MB'); this.value = ''; return; }
                if (arquivo.type !== 'application/pdf') { alert('Apenas PDF'); this.value = ''; return; }
                document.getElementById('nome-arquivo').textContent = arquivo.name;
                arquivoContainer.classList.remove('hidden');
            } else {
                arquivoContainer.classList.add('hidden');
            }
        });

        window.removerArquivo = function() {
            documentoInput.value = '';
            document.getElementById('arquivo-selecionado').classList.add('hidden');
        };

        $('#estabelecimento_id').select2({
            ajax: {
                url: '/admin/ordens-servico/api/buscar-estabelecimentos',
                dataType: 'json',
                delay: 250,
                data: (params) => ({ q: params.term, page: params.page || 1 }),
                processResults: (data, params) => ({ results: data.results, pagination: { more: data.pagination.more } }),
                cache: true
            },
            placeholder: 'Busque por CNPJ ou Nome...',
            minimumInputLength: 2,
            width: '100%'
        });

        // Funções para Modal de Tipos de Ação
        window.abrirModalTiposAcao = function() {
            document.getElementById('modal-tipos-acao').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.fecharModalTiposAcao = function() {
            document.getElementById('modal-tipos-acao').classList.add('hidden');
            document.body.style.overflow = '';
        };

        window.confirmarTiposAcao = function() {
            const checkboxes = document.querySelectorAll('.tipo-acao-checkbox:checked');
            const tagsContainer = document.getElementById('tipos-acao-tags');
            const hiddenContainer = document.getElementById('tipos-acao-hidden-inputs');
            const display = document.getElementById('tipos-acao-display');
            
            tagsContainer.innerHTML = '';
            hiddenContainer.innerHTML = '';
            
            if (checkboxes.length === 0) {
                display.textContent = 'Clique para selecionar tipos de ação...';
                display.classList.add('text-gray-500');
                display.classList.remove('text-gray-700');
            } else {
                display.textContent = checkboxes.length + ' tipo(s) selecionado(s)';
                display.classList.remove('text-gray-500');
                display.classList.add('text-gray-700');
                
                checkboxes.forEach(cb => {
                    // Tag visual
                    const tag = document.createElement('span');
                    tag.className = 'inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full';
                    tag.innerHTML = cb.dataset.label + '<button type="button" onclick="removerTipoAcao(' + cb.value + ')" class="hover:text-blue-900"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>';
                    tagsContainer.appendChild(tag);
                    
                    // Hidden input
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tipos_acao_ids[]';
                    input.value = cb.value;
                    hiddenContainer.appendChild(input);
                });
            }
            
            fecharModalTiposAcao();
        };

        window.removerTipoAcao = function(id) {
            const checkbox = document.querySelector('.tipo-acao-checkbox[value="' + id + '"]');
            if (checkbox) checkbox.checked = false;
            confirmarTiposAcao();
        };

        // Funções para Modal de Técnicos
        window.abrirModalTecnicos = function() {
            document.getElementById('modal-tecnicos').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.fecharModalTecnicos = function() {
            document.getElementById('modal-tecnicos').classList.add('hidden');
            document.body.style.overflow = '';
        };

        window.confirmarTecnicos = function() {
            const checkboxes = document.querySelectorAll('.tecnico-checkbox:checked');
            const tagsContainer = document.getElementById('tecnicos-tags');
            const hiddenContainer = document.getElementById('tecnicos-hidden-inputs');
            const display = document.getElementById('tecnicos-display');
            
            tagsContainer.innerHTML = '';
            hiddenContainer.innerHTML = '';
            
            if (checkboxes.length === 0) {
                display.textContent = 'Clique para selecionar técnicos...';
                display.classList.add('text-gray-500');
                display.classList.remove('text-gray-700');
            } else {
                display.textContent = checkboxes.length + ' técnico(s) selecionado(s)';
                display.classList.remove('text-gray-500');
                display.classList.add('text-gray-700');
                
                checkboxes.forEach(cb => {
                    // Tag visual
                    const tag = document.createElement('span');
                    tag.className = 'inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full';
                    tag.innerHTML = cb.dataset.label + '<button type="button" onclick="removerTecnico(' + cb.value + ')" class="hover:text-green-900"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>';
                    tagsContainer.appendChild(tag);
                    
                    // Hidden input
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tecnicos_ids[]';
                    input.value = cb.value;
                    hiddenContainer.appendChild(input);
                });
            }
            
            fecharModalTecnicos();
        };

        window.removerTecnico = function(id) {
            const checkbox = document.querySelector('.tecnico-checkbox[value="' + id + '"]');
            if (checkbox) checkbox.checked = false;
            confirmarTecnicos();
        };

        // Funções de Filtro/Pesquisa
        window.filtrarTiposAcao = function() {
            const termo = document.getElementById('pesquisa-tipos-acao').value.toLowerCase();
            const items = document.querySelectorAll('.tipo-acao-item');
            let encontrados = 0;
            
            items.forEach(item => {
                const nome = item.dataset.nome;
                if (nome.includes(termo)) {
                    item.style.display = 'flex';
                    encontrados++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            document.getElementById('sem-resultados-tipos').classList.toggle('hidden', encontrados > 0);
        };

        window.filtrarTecnicos = function() {
            const termo = document.getElementById('pesquisa-tecnicos').value.toLowerCase();
            const items = document.querySelectorAll('.tecnico-item');
            let encontrados = 0;
            
            items.forEach(item => {
                const nome = item.dataset.nome;
                if (nome.includes(termo)) {
                    item.style.display = 'flex';
                    encontrados++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            document.getElementById('sem-resultados-tecnicos').classList.toggle('hidden', encontrados > 0);
        };

        // Limpar pesquisa ao abrir modal
        const originalAbrirTiposAcao = window.abrirModalTiposAcao;
        window.abrirModalTiposAcao = function() {
            document.getElementById('pesquisa-tipos-acao').value = '';
            filtrarTiposAcao();
            originalAbrirTiposAcao();
        };

        const originalAbrirTecnicos = window.abrirModalTecnicos;
        window.abrirModalTecnicos = function() {
            document.getElementById('pesquisa-tecnicos').value = '';
            filtrarTecnicos();
            originalAbrirTecnicos();
        };

        // Inicializar com valores old() se existirem
        confirmarTiposAcao();
        confirmarTecnicos();

        // Logic for fetching Processos
        const processoSelect = document.getElementById('processo_id');
        const processoInfo = document.getElementById('processo-info');
        const processoSemProcesso = document.getElementById('processo-sem-processo');
        const submitButton = document.querySelector('button[type="submit"]');

        $('#estabelecimento_id').on('change', function() {
            const estId = $(this).val();
            processoSelect.innerHTML = '<option value="">Carregando...</option>';
            processoSelect.disabled = true;
            processoInfo.classList.add('hidden');
            processoSemProcesso.classList.add('hidden');

            if(!estId) {
                processoSelect.innerHTML = '<option value="">Selecione um estabelecimento</option>';
                submitButton.disabled = false; return;
            }

            fetch(`/admin/ordens-servico/api/processos-estabelecimento/${estId}`)
                .then(r => r.json())
                .then(data => {
                    if(data.success && data.processos.length > 0) {
                        processoSelect.innerHTML = '<option value="">Selecione um processo</option>';
                        data.processos.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.id;
                            opt.textContent = `${p.numero_processo} - ${p.tipo_label}`;
                            processoSelect.appendChild(opt);
                        });
                        processoSelect.disabled = false;
                        document.getElementById('processo-count').textContent = `${data.total} processo(s) encontrado(s)`;
                        processoInfo.classList.remove('hidden');
                        submitButton.disabled = false;
                    } else {
                        processoSelect.innerHTML = '<option value="">Sem processos</option>';
                        processoSemProcesso.classList.remove('hidden');
                        submitButton.disabled = true;
                    }
                })
                .catch(() => {
                    processoSelect.innerHTML = '<option>Erro ao carregar</option>';
                    submitButton.disabled = false;
                });
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            if(comEstabelecimentoRadio.checked && estabelecimentoSelect.value && !processoSelect.value) {
                e.preventDefault();
                alert('Selecione um processo vinculado.');
                processoSelect.focus();
            }
        });
    });
</script>
@endpush
