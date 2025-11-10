@extends('layouts.admin')

@section('title', 'Nova Ordem de Serviço')

@section('content')
<div class="max-w-8xl mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-3">
            <a href="{{ route('admin.ordens-servico.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border-2 border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-blue-300 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Nova Ordem de Serviço</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Preencha os dados para criar uma nova OS</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <form method="POST" action="{{ route('admin.ordens-servico.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

        {{-- Card: Estabelecimento e Processo --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-5">
                <div class="space-y-4">
                    {{-- Tipo de Vinculação --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Vinculação de Estabelecimento
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center cursor-pointer bg-white px-3 py-2.5 rounded-lg border-2 border-gray-300 hover:border-blue-500 transition-colors">
                                <input type="radio" 
                                       name="tipo_vinculacao" 
                                       value="com_estabelecimento" 
                                       id="com_estabelecimento"
                                       {{ old('tipo_vinculacao', 'sem_estabelecimento') == 'com_estabelecimento' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <div class="ml-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900">Com estabelecimento</span>
                                </div>
                            </label>
                            <label class="flex items-center cursor-pointer bg-white px-4 py-3 rounded-lg border-2 border-gray-300 hover:border-blue-500 transition-colors flex-1">
                                <input type="radio" 
                                       name="tipo_vinculacao" 
                                       value="sem_estabelecimento" 
                                       id="sem_estabelecimento"
                                       {{ old('tipo_vinculacao', 'sem_estabelecimento') == 'sem_estabelecimento' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <div class="ml-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900">Sem estabelecimento</span>
                                </div>
                            </label>
                        </div>
                        <div class="mt-2 bg-blue-50 border-l-4 border-blue-400 p-2.5 rounded-r">
                            <p class="text-xs text-blue-800 flex items-start gap-2">
                                <svg class="w-3.5 h-3.5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Você pode vincular posteriormente ao editar ou finalizar.</span>
                            </p>
                        </div>
                    </div>

                    {{-- Estabelecimento --}}
                    <div id="estabelecimento-container" style="display: none;">
                        <label for="estabelecimento_id" class="block text-sm font-semibold text-gray-900 mb-2">
                            Buscar Estabelecimento <span class="text-red-500">*</span>
                        </label>
                        <select name="estabelecimento_id" 
                                id="estabelecimento_id" 
                                class="w-full @error('estabelecimento_id') border-red-500 @enderror">
                            <option value="">Digite para buscar...</option>
                            @if(old('estabelecimento_id'))
                                @php
                                    $estabelecimentoSelecionado = $estabelecimentos->firstWhere('id', old('estabelecimento_id'));
                                @endphp
                                @if($estabelecimentoSelecionado)
                                <option value="{{ $estabelecimentoSelecionado->id }}" selected>
                                    {{ $estabelecimentoSelecionado->cnpj }} - {{ $estabelecimentoSelecionado->nome_fantasia }} - {{ $estabelecimentoSelecionado->razao_social }}
                                </option>
                                @endif
                            @endif
                        </select>
                        @error('estabelecimento_id')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-600 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Digite pelo menos 2 caracteres para buscar por <strong>CNPJ</strong>, <strong>Nome Fantasia</strong> ou <strong>Razão Social</strong>
                        </p>
                    </div>

                    {{-- Processo --}}
                    <div id="processo-container">
                        <label for="processo_id" class="block text-sm font-semibold text-gray-900 mb-2">
                            Processo Vinculado <span class="text-gray-500 font-normal">(Opcional)</span>
                        </label>
                        <select name="processo_id" 
                                id="processo_id" 
                                disabled
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100 @error('processo_id') border-red-500 @enderror">
                            <option value="">Selecione primeiro um estabelecimento</option>
                        </select>
                        @error('processo_id')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <div class="mt-2 space-y-2">
                            <div id="processo-info" class="hidden bg-green-50 border border-green-200 rounded-lg p-3">
                                <span class="text-xs text-green-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span id="processo-count" class="font-medium"></span>
                                </span>
                            </div>
                            <div id="processo-sem-processo" class="hidden bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <span class="text-xs text-amber-800 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span>Este estabelecimento não possui processos ativos. Você pode criar um processo antes de criar a ordem de serviço.</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        {{-- Card: Upload de Documento --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3.5 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h2 class="text-base font-semibold text-gray-900">Documento Anexo</h2>
                    <span class="ml-auto text-xs bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full">Opcional</span>
                </div>
            </div>
            <div class="p-5">
                <div class="space-y-3">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-2.5 rounded-r">
                        <p class="text-xs text-blue-800 flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Anexe arquivos complementares (ofícios, denúncias, etc.)</span>
                        </p>
                    </div>

                    {{-- Campo de upload --}}
                    <div>
                        <label for="documento_anexo" class="block text-sm font-semibold text-gray-900 mb-2">
                            Anexar Documento (PDF) <span class="text-gray-500 font-normal">(Opcional)</span>
                        </label>
                        <div class="flex items-center justify-center w-full">
                            <label for="documento_anexo" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-600">
                                        <span class="font-semibold">Clique para fazer upload</span> ou arraste o arquivo
                                    </p>
                                    <p class="text-xs text-gray-500">Apenas arquivos PDF (Máx. 10MB)</p>
                                </div>
                                <input id="documento_anexo" 
                                       name="documento_anexo" 
                                       type="file" 
                                       accept=".pdf,application/pdf"
                                       class="hidden" />
                            </label>
                        </div>
                        
                        {{-- Nome do arquivo selecionado --}}
                        <div id="arquivo-selecionado" class="hidden mt-3 bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm text-green-800">
                                    <strong>Arquivo selecionado:</strong> <span id="nome-arquivo"></span>
                                </span>
                                <button type="button" 
                                        onclick="removerArquivo()"
                                        class="ml-auto text-green-600 hover:text-green-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        @error('documento_anexo')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-2 text-xs text-gray-600 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Exemplos: denúncia, solicitação do MPE, ofício, notificação, etc.
                        </p>
                    </div>
                </div>
            </div>

        {{-- Card: Tipos de Ação --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3.5 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <h2 class="text-base font-semibold text-gray-900">Tipos de Ação</h2>
                </div>
            </div>
            <div class="p-5">
                
                <div>
                    <label for="tipos_acao_ids" class="block text-sm font-medium text-gray-700 mb-2">
                        Selecione as ações que serão executadas <span class="text-red-500">*</span>
                    </label>
                    <select name="tipos_acao_ids[]" 
                            id="tipos_acao_ids" 
                            multiple
                            required
                            class="w-full">
                        @foreach($tiposAcao as $tipoAcao)
                        <option value="{{ $tipoAcao->id }}" {{ in_array($tipoAcao->id, old('tipos_acao_ids', [])) ? 'selected' : '' }}>
                            {{ $tipoAcao->descricao }}
                        </option>
                        @endforeach
                    </select>
                    @error('tipos_acao_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-600">
                        💡 Clique para selecionar múltiplas ações. Use a busca para encontrar rapidamente.
                    </p>
                </div>
            </div>

        {{-- Card: Técnicos Responsáveis --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3.5 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <h2 class="text-base font-semibold text-gray-900">Técnicos Responsáveis</h2>
                </div>
            </div>
            <div class="p-5">
                
                <div>
                    <label for="tecnicos_ids" class="block text-sm font-medium text-gray-700 mb-2">
                        Selecione os técnicos que executarão a ordem de serviço <span class="text-red-500">*</span>
                    </label>
                    <select name="tecnicos_ids[]" 
                            id="tecnicos_ids" 
                            multiple
                            required
                            class="w-full">
                        @foreach($tecnicos as $tecnico)
                        <option value="{{ $tecnico->id }}" {{ in_array($tecnico->id, old('tecnicos_ids', [])) ? 'selected' : '' }}>
                            {{ $tecnico->nome }}
                        </option>
                        @endforeach
                    </select>
                    @error('tecnicos_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-600">
                        💡 Clique para selecionar múltiplos técnicos. Use a busca para encontrar rapidamente.
                    </p>
                </div>
            </div>

        {{-- Card: Período e Observações --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3.5 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h2 class="text-base font-semibold text-gray-900">Período e Observações</h2>
                </div>
            </div>
            <div class="p-5">
                
                <div class="space-y-4">

                    {{-- Período de Execução --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Período de Execução
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Data de Início --}}
                            <div>
                                <label for="data_inicio" class="block text-xs font-medium text-gray-600 mb-1">
                                    Data de Início <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       id="data_inicio" 
                                       name="data_inicio" 
                                       value="{{ old('data_inicio') }}"
                                       required
                                       min="{{ now()->format('Y-m-d') }}"
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_inicio') border-red-500 @enderror">
                                @error('data_inicio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Data Fim --}}
                            <div>
                                <label for="data_fim" class="block text-xs font-medium text-gray-600 mb-1">
                                    Data de Término <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       id="data_fim" 
                                       name="data_fim" 
                                       value="{{ old('data_fim') }}"
                                       required
                                       min="{{ old('data_inicio', now()->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_fim') border-red-500 @enderror">
                                @error('data_fim')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-600 flex items-center gap-1">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Informe o período previsto para a execução. A data inicial deve ser hoje ou posterior e o término não pode ser anterior ao início.
                        </p>
                    </div>

                    {{-- Observações --}}
                    <div>
                        <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">
                            Observações
                        </label>
                        <textarea id="observacoes" 
                                  name="observacoes" 
                                  rows="4"
                                  placeholder="Descreva informações adicionais sobre esta ordem de serviço..."
                                  class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('observacoes') border-red-500 @enderror">{{ old('observacoes') }}</textarea>
                        @error('observacoes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        {{-- Botões --}}
        <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.ordens-servico.index') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancelar
            </a>
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Salvar Ordem de Serviço
            </button>
        </div>
    </form>
</div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    /* Customização do Choices.js */
    .choices__inner {
        min-height: 44px !important;
        padding: 6px 12px !important;
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
        background-color: white !important;
    }
    
    .choices__inner:focus,
    .choices.is-focused .choices__inner {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    .choices__list--dropdown {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        margin-top: 4px !important;
        z-index: 100 !important;
        max-height: 300px !important;
    }
    
    .choices__list--dropdown .choices__item--selectable {
        padding: 10px 12px !important;
    }
    
    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: #eff6ff !important;
        color: #1e40af !important;
    }
    
    .choices__item--choice {
        font-size: 0.875rem !important;
    }
    
    .choices__list--multiple .choices__item {
        background-color: #3b82f6 !important;
        border: 1px solid #2563eb !important;
        border-radius: 0.375rem !important;
        padding: 4px 8px !important;
        margin: 2px !important;
        font-size: 0.875rem !important;
    }
    
    .choices__button {
        border-left: 1px solid #2563eb !important;
        padding: 0 8px !important;
        opacity: 0.8 !important;
    }
    
    .choices__button:hover {
        opacity: 1 !important;
    }
    
    .choices__input {
        background-color: transparent !important;
        font-size: 0.875rem !important;
        padding: 4px 0 !important;
    }
    
    .choices__placeholder {
        opacity: 0.5 !important;
    }
    
    /* Evita que o dropdown fique por baixo de outros elementos */
    .choices[data-type*="select-multiple"].is-open .choices__inner {
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    
    /* Melhora o espaçamento quando há itens selecionados */
    .choices__list--multiple {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
    }
    
    /* Customização do Select2 */
    .select2-container--default .select2-selection--single {
        height: 44px !important;
        padding: 6px 12px !important;
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
        background-color: white !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px !important;
        color: #374151 !important;
        padding-left: 0 !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    .select2-dropdown {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }
    
    .select2-results__option {
        padding: 10px 12px !important;
        font-size: 0.875rem !important;
    }
    
    .select2-results__option--highlighted {
        background-color: #eff6ff !important;
        color: #1e40af !important;
    }
    
    .select2-results__option--selected {
        background-color: #3b82f6 !important;
        color: white !important;
    }
    
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
        padding: 8px 12px !important;
    }
    
    .select2-search--dropdown .select2-search__field:focus {
        border-color: #3b82f6 !important;
        outline: none !important;
    }
    
    /* Estilo para radio button selecionado */
    input[type="radio"]:checked + span {
        color: #1e40af !important;
    }
    
    input[type="radio"]:checked ~ * label {
        border-color: #3b82f6 !important;
        background-color: #eff6ff !important;
    }
    
    label:has(input[type="radio"]:checked) {
        border-color: #3b82f6 !important;
        background-color: #eff6ff !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Controle de exibição do campo de estabelecimento e documento
        const comEstabelecimentoRadio = document.getElementById('com_estabelecimento');
        const semEstabelecimentoRadio = document.getElementById('sem_estabelecimento');
        const estabelecimentoContainer = document.getElementById('estabelecimento-container');
        const estabelecimentoSelect = document.getElementById('estabelecimento_id');
        const documentoInput = document.getElementById('documento_anexo');

        function toggleEstabelecimentoField() {
            if (comEstabelecimentoRadio.checked) {
                // Com estabelecimento
                estabelecimentoContainer.style.display = 'block';
                estabelecimentoSelect.required = true;
                
            } else {
                // Sem estabelecimento
                estabelecimentoContainer.style.display = 'none';
                estabelecimentoSelect.required = false;
                // Limpa seleção ao ocultar
                $(estabelecimentoSelect).val(null).trigger('change');
            }
        }

        comEstabelecimentoRadio.addEventListener('change', toggleEstabelecimentoField);
        semEstabelecimentoRadio.addEventListener('change', toggleEstabelecimentoField);

        // Inicializa o estado correto ao carregar a página
        toggleEstabelecimentoField();

        // Controle de exibição do arquivo selecionado
        documentoInput.addEventListener('change', function(e) {
            const arquivo = e.target.files[0];
            const arquivoSelecionado = document.getElementById('arquivo-selecionado');
            const nomeArquivo = document.getElementById('nome-arquivo');
            
            if (arquivo) {
                // Valida tamanho (10MB)
                if (arquivo.size > 10 * 1024 * 1024) {
                    alert('O arquivo deve ter no máximo 10MB');
                    e.target.value = '';
                    arquivoSelecionado.classList.add('hidden');
                    return;
                }
                
                // Valida tipo
                if (arquivo.type !== 'application/pdf') {
                    alert('Apenas arquivos PDF são permitidos');
                    e.target.value = '';
                    arquivoSelecionado.classList.add('hidden');
                    return;
                }
                
                nomeArquivo.textContent = arquivo.name;
                arquivoSelecionado.classList.remove('hidden');
            } else {
                arquivoSelecionado.classList.add('hidden');
            }
        });
        
        // Função global para remover arquivo
        window.removerArquivo = function() {
            documentoInput.value = '';
            document.getElementById('arquivo-selecionado').classList.add('hidden');
        };

        // Inicializa Select2 para busca de estabelecimentos
        $('#estabelecimento_id').select2({
            ajax: {
                url: '/admin/ordens-servico/api/buscar-estabelecimentos',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // termo de busca
                        page: params.page || 1
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            placeholder: 'Busque por CNPJ, Nome Fantasia ou Razão Social...',
            minimumInputLength: 2,
            language: {
                inputTooShort: function () {
                    return 'Digite pelo menos 2 caracteres para buscar';
                },
                searching: function () {
                    return 'Buscando...';
                },
                noResults: function () {
                    return 'Nenhum estabelecimento encontrado';
                },
                errorLoading: function () {
                    return 'Erro ao carregar resultados';
                }
            },
            width: '100%',
            theme: 'default'
        });

        // Inicializa Choices.js para Tipos de Ação
        const tiposAcaoSelect = new Choices('#tipos_acao_ids', {
            removeItemButton: true,
            searchEnabled: true,
            searchPlaceholderValue: 'Digite para pesquisar...',
            noResultsText: 'Nenhum tipo de ação encontrado',
            noChoicesText: 'Nenhum tipo de ação disponível',
            itemSelectText: '',
            placeholder: true,
            placeholderValue: 'Clique para selecionar as ações',
            maxItemCount: -1,
            shouldSort: false,
            position: 'bottom', // Dropdown sempre abre para baixo
            searchResultLimit: 10,
            renderChoiceLimit: 10,
            addItemText: (value) => {
                return `Pressione Enter para adicionar <b>"${value}"</b>`;
            },
            classNames: {
                containerOuter: 'choices',
                containerInner: 'choices__inner',
                input: 'choices__input',
                inputCloned: 'choices__input--cloned',
                list: 'choices__list',
                listItems: 'choices__list--multiple',
                listSingle: 'choices__list--single',
                listDropdown: 'choices__list--dropdown',
                item: 'choices__item',
                itemSelectable: 'choices__item--selectable',
                itemDisabled: 'choices__item--disabled',
                itemChoice: 'choices__item--choice',
                placeholder: 'choices__placeholder',
                group: 'choices__group',
                groupHeading: 'choices__heading',
                button: 'choices__button',
                activeState: 'is-active',
                focusState: 'is-focused',
                openState: 'is-open',
                disabledState: 'is-disabled',
                highlightedState: 'is-highlighted',
                selectedState: 'is-selected',
                flippedState: 'is-flipped',
                loadingState: 'is-loading',
                noResults: 'has-no-results',
                noChoices: 'has-no-choices'
            }
        });

        // Inicializa Choices.js para Técnicos
        const tecnicosSelect = new Choices('#tecnicos_ids', {
            removeItemButton: true,
            searchEnabled: true,
            searchPlaceholderValue: 'Digite para pesquisar...',
            noResultsText: 'Nenhum técnico encontrado',
            noChoicesText: 'Nenhum técnico disponível',
            itemSelectText: '',
            placeholder: true,
            placeholderValue: 'Clique para selecionar os técnicos',
            maxItemCount: -1,
            shouldSort: false,
            position: 'bottom', // Dropdown sempre abre para baixo
            searchResultLimit: 10,
            renderChoiceLimit: 10,
            addItemText: (value) => {
                return `Pressione Enter para adicionar <b>"${value}"</b>`;
            }
        });

        // Buscar processos ao selecionar estabelecimento
        const processoSelect = document.getElementById('processo_id');
        const processoInfo = document.getElementById('processo-info');
        const processoCount = document.getElementById('processo-count');
        const processoSemProcesso = document.getElementById('processo-sem-processo');
        const submitButton = document.querySelector('button[type="submit"]');

        $('#estabelecimento_id').on('change', function() {
            const estabelecimentoId = $(this).val();
            
            // Limpa o select de processos
            processoSelect.innerHTML = '<option value="">Carregando processos...</option>';
            processoSelect.disabled = true;
            processoSelect.classList.add('bg-gray-100');
            processoInfo.classList.add('hidden');
            processoSemProcesso.classList.add('hidden');
            
            if (!estabelecimentoId) {
                processoSelect.innerHTML = '<option value="">Nenhum estabelecimento selecionado</option>';
                // Habilita botão de submit mesmo sem estabelecimento
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                return;
            }

            // Busca processos via API
            fetch(`/admin/ordens-servico/api/processos-estabelecimento/${estabelecimentoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.processos.length > 0) {
                        // Tem processos disponíveis
                        processoSelect.innerHTML = '<option value="">Selecione um processo</option>';
                        
                        data.processos.forEach(processo => {
                            const option = document.createElement('option');
                            option.value = processo.id;
                            option.textContent = `${processo.numero_processo} - ${processo.tipo_label}`;
                            processoSelect.appendChild(option);
                        });
                        
                        processoSelect.disabled = false;
                        processoSelect.classList.remove('bg-gray-100');
                        
                        // Mostra informação de quantidade
                        processoCount.textContent = `${data.total} processo(s) disponível(is)`;
                        processoInfo.classList.remove('hidden');
                        
                        // Habilita botão de submit
                        submitButton.disabled = false;
                        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                        
                    } else {
                        // Não tem processos
                        processoSelect.innerHTML = '<option value="">Nenhum processo disponível</option>';
                        processoSelect.disabled = true;
                        processoSemProcesso.classList.remove('hidden');
                        
                        // Habilita botão de submit mesmo sem processo (pode criar OS sem processo)
                        submitButton.disabled = false;
                        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar processos:', error);
                    processoSelect.innerHTML = '<option value="">Erro ao carregar processos</option>';
                    processoSelect.disabled = true;
                    
                    // Habilita botão de submit mesmo com erro (pode criar OS sem processo)
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                });
        });
    });
</script>
@endpush
