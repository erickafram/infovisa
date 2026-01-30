@extends('layouts.admin')

@section('title', 'Editar Ordem de Serviço')

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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Editar Ordem de Serviço</h1>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $ordemServico->numero }} - Atualize as informações</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <form method="POST" action="{{ route('admin.ordens-servico.update', $ordemServico) }}" class="space-y-5">
            @csrf
            @method('PUT')

        {{-- Card: Dados Principais --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3.5 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <h2 class="text-base font-semibold text-gray-900">Dados Principais</h2>
                </div>
            </div>
            <div class="p-5">
                
                @if(!$ordemServico->estabelecimento_id)
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-amber-800">
                            <p class="font-medium">Esta OS não possui estabelecimento vinculado</p>
                            <p class="mt-1">Você pode vincular um estabelecimento agora. Ao vincular, se o estabelecimento tiver um processo ativo, a OS será automaticamente vinculada a ele.</p>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Estabelecimento com Busca --}}
                    <div class="md:col-span-2">
                        <label for="estabelecimento_id" class="block text-sm font-semibold text-gray-900 mb-2">
                            Estabelecimento <span class="text-gray-500">(Opcional)</span>
                        </label>
                        <select name="estabelecimento_id" 
                                id="estabelecimento_id" 
                                class="w-full">
                            <option value="">Sem estabelecimento</option>
                            @if($ordemServico->estabelecimento_id && $ordemServico->estabelecimento)
                            <option value="{{ $ordemServico->estabelecimento->id }}" selected>
                                {{ $ordemServico->estabelecimento->cnpj ?? $ordemServico->estabelecimento->cpf }} - {{ $ordemServico->estabelecimento->nome_fantasia }} - {{ $ordemServico->estabelecimento->razao_social }}
                            </option>
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
                            Digite para buscar por <strong>CNPJ</strong>, <strong>CPF</strong>, <strong>Nome Fantasia</strong> ou <strong>Razão Social</strong>
                        </p>
                        @if($ordemServico->estabelecimento_id)
                        <p class="mt-2 text-xs text-blue-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ao alterar o estabelecimento, os processos disponíveis serão atualizados.
                        </p>
                        @endif
                    </div>

                    {{-- Processo (aparece quando estabelecimento é selecionado) --}}
                    <div class="md:col-span-2" id="processo-container" style="display: none;">
                        <label for="processo_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Processo Vinculado <span id="processo-obrigatorio-label" class="text-red-500" style="display: none;">*</span>
                        </label>
                        <div id="processo-loading" class="hidden">
                            <div class="flex items-center gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-blue-700">Buscando processos...</span>
                            </div>
                        </div>
                        <select name="processo_id" 
                                id="processo_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Sem processo vinculado</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            💡 Apenas processos ativos (aberto, em análise, pendente) são exibidos
                        </p>
                    </div>

                    {{-- Tipos de Ação (Múltiplos) --}}
                    <div class="md:col-span-2">
                        <label for="tipos_acao_ids" class="block text-sm font-medium text-gray-700 mb-1">
                            Tipos de Ação <span class="text-red-500">*</span>
                        </label>
                        
                        {{-- Campo visual para abrir modal --}}
                        <div id="tipos-acao-selecionados-edit" 
                             onclick="abrirModalTiposAcaoEdit()"
                             class="w-full min-h-[42px] px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-all">
                            <div id="tipos-acao-placeholder-edit" class="text-gray-400 text-sm">
                                Clique para selecionar tipos de ação...
                            </div>
                            <div id="tipos-acao-badges-edit" class="flex flex-wrap gap-2 hidden">
                                <!-- Badges serão inseridas via JavaScript -->
                            </div>
                        </div>
                        
                        {{-- Hidden inputs para enviar os IDs --}}
                        <div id="tipos-acao-hidden-inputs-edit"></div>
                        
                        @error('tipos_acao_ids')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Atribuição de Técnicos por Atividade --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Atribuição de Técnicos por Atividade <span class="text-red-500">*</span>
                        </label>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium">Nova estrutura de atribuição</p>
                                    <p class="mt-1">Cada atividade possui seus próprios técnicos atribuídos com um responsável designado.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div id="atividades-tecnicos-container-edit" class="space-y-4">
                            <!-- Será preenchido via JavaScript -->
                        </div>
                        
                        {{-- Hidden inputs para enviar a estrutura --}}
                        <div id="atividades-tecnicos-hidden-inputs-edit"></div>
                        @error('atividades_tecnicos')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Data de Início --}}
                    <div>
                        <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Início <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="data_inicio" 
                               name="data_inicio" 
                               value="{{ old('data_inicio', $ordemServico->data_inicio?->format('Y-m-d')) }}"
                               required
                               min="{{ now()->format('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_inicio') border-red-500 @enderror">
                        @error('data_inicio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Data Fim --}}
                    <div>
                        <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Término <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="data_fim" 
                               name="data_fim" 
                               value="{{ old('data_fim', $ordemServico->data_fim?->format('Y-m-d')) }}"
                               required
                               min="{{ old('data_inicio', $ordemServico->data_inicio?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_fim') border-red-500 @enderror">
                        @error('data_fim')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Observação sobre datas --}}
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-600 flex items-center gap-1">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            A data inicial deve ser hoje ou posterior e o término não pode ser anterior ao início.
                        </p>
                    </div>

                    {{-- Observações --}}
                    <div class="md:col-span-2">
                        <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">
                            Observações
                        </label>
                        <textarea id="observacoes" 
                                  name="observacoes" 
                                  rows="3"
                                  placeholder="Observações adicionais..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('observacoes') border-red-500 @enderror">{{ old('observacoes', $ordemServico->observacoes) }}</textarea>
                        @error('observacoes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.ordens-servico.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                    Voltar
                </a>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition">
                    Atualizar Ordem de Serviço
                </button>
            </div>
        </div>
    </form>

    {{-- Modal Tipos de Ação com SubAções --}}
    <div id="modal-tipos-acao-edit" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="fecharModalTiposAcaoEdit()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-2xl sm:w-full mx-auto overflow-hidden">
                {{-- Header com Gradient --}}
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white">Selecionar Tipos de Ação</h3>
                    </div>
                    <button type="button" onclick="fecharModalTiposAcaoEdit()" class="text-white/70 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                {{-- Campo de Pesquisa --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-b from-gray-50 to-white">
                    <div class="relative group">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="pesquisa-tipos-acao-edit" placeholder="Pesquise por ação ou subação..." 
                               class="w-full pl-12 pr-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white hover:border-gray-300 transition-all"
                               onkeyup="filtrarTiposAcaoEdit()">
                    </div>
                    <p class="text-xs text-gray-500 mt-2 ml-1">💡 Dica: Digite o nome da ação ou subação para filtrar</p>
                </div>
                
                {{-- Lista de Tipos de Ação --}}
                <div class="px-6 py-4 max-h-[60vh] overflow-y-auto" id="lista-tipos-acao-edit">
                    <div class="space-y-3">
                        @foreach($tiposAcao as $tipoAcao)
                        @php
                            $subAcoesTexto = $tipoAcao->subAcoesAtivas->pluck('descricao')->map(fn($d) => strtolower($d))->implode(' ');
                        @endphp
                        <div class="tipo-acao-item-edit bg-gradient-to-r from-gray-50 to-white rounded-xl border-2 border-gray-200 hover:border-blue-400 hover:shadow-md transition-all" 
                             data-nome="{{ strtolower($tipoAcao->descricao) }}" 
                             data-subacoes="{{ $subAcoesTexto }}">
                            @if($tipoAcao->subAcoesAtivas->count() > 0)
                                {{-- Ação com subações --}}
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-900">{{ $tipoAcao->descricao }}</span>
                                        </div>
                                        <span class="text-xs font-bold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full">{{ $tipoAcao->subAcoesAtivas->count() }} subações</span>
                                    </div>
                                    <div class="pl-4 space-y-2 border-l-3 border-indigo-300">
                                        {{-- Opção para selecionar APENAS a ação principal --}}
                                        <label class="flex items-center p-2.5 bg-blue-50 rounded-lg hover:bg-blue-100 cursor-pointer border border-blue-200 hover:border-blue-300 transition-all group">
                                            <input type="checkbox" class="tipo-acao-checkbox-edit rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4" 
                                                   value="{{ $tipoAcao->id }}" 
                                                   data-label="{{ $tipoAcao->descricao }}"
                                                   data-acao-label="{{ $tipoAcao->descricao }}"
                                                   data-is-acao-principal="true">
                                            <span class="ml-3 text-sm text-blue-700 group-hover:text-blue-800 transition-colors font-medium">{{ $tipoAcao->descricao }}</span>
                                            <span class="ml-auto text-xs bg-blue-200 text-blue-700 px-2 py-0.5 rounded-full">Ação Principal</span>
                                        </label>
                                        {{-- Subações --}}
                                        @foreach($tipoAcao->subAcoesAtivas as $subAcao)
                                        <label class="flex items-center p-2.5 bg-white rounded-lg hover:bg-indigo-50 cursor-pointer border border-transparent hover:border-indigo-200 transition-all group">
                                            <input type="checkbox" class="tipo-acao-checkbox-edit rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" 
                                                   value="{{ $tipoAcao->id }}" 
                                                   data-label="{{ $subAcao->descricao }}"
                                                   data-acao-label="{{ $tipoAcao->descricao }}"
                                                   data-sub-acao-id="{{ $subAcao->id }}"
                                                   data-sub-acao-label="{{ $subAcao->descricao }}">
                                            <span class="ml-3 text-sm text-gray-700 group-hover:text-indigo-700 transition-colors">{{ $subAcao->descricao }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                {{-- Ação sem subações --}}
                                <label class="flex items-center p-4 hover:bg-blue-50 cursor-pointer group">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <input type="checkbox" class="tipo-acao-checkbox-edit rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 ml-3" 
                                           value="{{ $tipoAcao->id }}" data-label="{{ $tipoAcao->descricao }}">
                                    <span class="ml-3 text-sm text-gray-700 group-hover:text-blue-700 transition-colors font-medium">{{ $tipoAcao->descricao }}</span>
                                </label>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <p id="sem-resultados-tipos-edit" class="hidden text-center text-gray-500 py-8">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Nenhum tipo de ação encontrado
                    </p>
                </div>
                
                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-white flex justify-end gap-3">
                    <button type="button" onclick="fecharModalTiposAcaoEdit()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all">
                        Cancelar
                    </button>
                    <button type="button" onclick="confirmarTiposAcaoEdit()" class="px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-sm hover:shadow-md transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Técnicos por Atividade --}}
    <div id="modal-tecnicos-atividade-edit" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="fecharModalTecnicosAtividadeEdit()"></div>
            <div class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-2xl sm:w-full mx-auto">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900" id="modal-atividade-titulo-edit">Atribuir Técnicos</h3>
                    <button type="button" onclick="fecharModalTecnicosAtividadeEdit()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="px-6 py-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Técnico Responsável <span class="text-red-500">*</span>
                        </label>
                        <select id="responsavel-select-edit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione o responsável...</option>
                            @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}">{{ $tecnico->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Técnicos Adicionais <span class="text-gray-500">(Opcional)</span>
                        </label>
                        <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg">
                            @foreach($tecnicos as $tecnico)
                            <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                <input type="checkbox" class="tecnico-adicional-checkbox-edit rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                       value="{{ $tecnico->id }}" data-nome="{{ $tecnico->nome }}">
                                <span class="ml-3 text-sm text-gray-700">{{ $tecnico->nome }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button type="button" onclick="fecharModalTecnicosAtividadeEdit()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="button" onclick="confirmarTecnicosAtividadeEdit()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Customização do Select2 */
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px;
        padding-left: 0;
        color: #111827;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .select2-dropdown {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem;
    }
    
    .select2-results__option {
        padding: 0.75rem 1rem;
    }
    
    .select2-results__option--highlighted {
        background-color: #3b82f6 !important;
    }
    
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #eff6ff;
        color: #1e40af;
    }
    
    .select2-container {
        width: 100% !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa Select2 para Estabelecimento com busca AJAX
        $('#estabelecimento_id').select2({
            placeholder: 'Digite para buscar estabelecimento...',
            allowClear: true,
            language: {
                inputTooShort: function() {
                    return 'Digite pelo menos 2 caracteres para buscar';
                },
                searching: function() {
                    return 'Buscando...';
                },
                noResults: function() {
                    return 'Nenhum estabelecimento encontrado';
                },
                errorLoading: function() {
                    return 'Erro ao carregar resultados';
                }
            },
            ajax: {
                url: '{{ route("admin.ordens-servico.api.buscar-estabelecimentos") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
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
            minimumInputLength: 2,
            templateResult: function(estabelecimento) {
                if (estabelecimento.loading) {
                    return estabelecimento.text;
                }
                return $('<span>' + estabelecimento.text + '</span>');
            },
            templateSelection: function(estabelecimento) {
                return estabelecimento.text;
            }
        });

        // Sincroniza mínimo da data fim com data início
        const dataInicioInput = document.getElementById('data_inicio');
        const dataFimInput = document.getElementById('data_fim');
        
        if (dataInicioInput && dataFimInput) {
            dataInicioInput.addEventListener('change', function() {
                const dataInicio = this.value;
                if (dataInicio) {
                    dataFimInput.min = dataInicio;
                    // Se data fim for menor que data início, limpa
                    if (dataFimInput.value && dataFimInput.value < dataInicio) {
                        dataFimInput.value = dataInicio;
                    }
                }
            });
        }

        // Variáveis globais para controle da nova estrutura
        let atividadesSelecionadasEdit = [];
        let atividadesTecnicosEdit = {};
        let atividadeAtualModalEdit = null;

        // Carrega dados existentes da OS
        const osAtividadesTecnicos = @json($ordemServico->atividades_tecnicos ?? []);
        const osTiposAcaoIds = @json($ordemServico->tipos_acao_ids ?? []);
        const tiposAcaoDisponiveis = @json($tiposAcao->keyBy('id')->toArray());

        // ========================================
        // Funções do Modal de Tipos de Ação
        // ========================================
        
        window.abrirModalTiposAcaoEdit = function() {
            document.getElementById('modal-tipos-acao-edit').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.fecharModalTiposAcaoEdit = function() {
            document.getElementById('modal-tipos-acao-edit').classList.add('hidden');
            document.body.style.overflow = '';
        };

        window.filtrarTiposAcaoEdit = function() {
            const termo = document.getElementById('pesquisa-tipos-acao-edit').value.toLowerCase().trim();
            const items = document.querySelectorAll('.tipo-acao-item-edit');
            let encontrados = 0;
            
            items.forEach(item => {
                const nomeAcao = item.dataset.nome || '';
                const subacoes = item.dataset.subacoes || '';
                
                const matchAcao = nomeAcao.includes(termo);
                const matchSubacao = subacoes.includes(termo);
                
                if (matchAcao || matchSubacao) {
                    item.style.display = '';
                    encontrados++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            document.getElementById('sem-resultados-tipos-edit').classList.toggle('hidden', encontrados > 0);
        };

        window.confirmarTiposAcaoEdit = function() {
            const checkboxes = document.querySelectorAll('.tipo-acao-checkbox-edit:checked');
            
            // Limpa seleções anteriores
            atividadesSelecionadasEdit = [];
            
            // Processa cada checkbox marcado
            checkboxes.forEach(cb => {
                const tipoAcaoId = parseInt(cb.value);
                const subAcaoId = cb.dataset.subAcaoId;
                const isAcaoPrincipal = cb.dataset.isAcaoPrincipal === 'true';
                const label = cb.dataset.subAcaoLabel || cb.dataset.label;
                
                atividadesSelecionadasEdit.push({
                    id: tipoAcaoId,
                    nome: label,
                    subAcaoId: subAcaoId ? parseInt(subAcaoId) : null,
                    isAcaoPrincipal: isAcaoPrincipal || !subAcaoId
                });
            });
            
            // Remove técnicos de atividades desmarcadas
            const idsAtuais = atividadesSelecionadasEdit.map(a => a.id);
            Object.keys(atividadesTecnicosEdit).forEach(atividadeId => {
                if (!idsAtuais.includes(parseInt(atividadeId))) {
                    delete atividadesTecnicosEdit[atividadeId];
                }
            });
            
            atualizarBadgesTiposAcaoEdit();
            atualizarInterfaceTecnicosEdit();
            fecharModalTiposAcaoEdit();
        };

        function atualizarBadgesTiposAcaoEdit() {
            const placeholder = document.getElementById('tipos-acao-placeholder-edit');
            const badgesContainer = document.getElementById('tipos-acao-badges-edit');
            const hiddenInputsContainer = document.getElementById('tipos-acao-hidden-inputs-edit');
            
            // Limpa containers
            badgesContainer.innerHTML = '';
            hiddenInputsContainer.innerHTML = '';
            
            if (atividadesSelecionadasEdit.length === 0) {
                placeholder.classList.remove('hidden');
                badgesContainer.classList.add('hidden');
                return;
            }
            
            placeholder.classList.add('hidden');
            badgesContainer.classList.remove('hidden');
            
            atividadesSelecionadasEdit.forEach(atividade => {
                // Cria badge - cor diferente para ação principal vs subação
                const badge = document.createElement('span');
                const isSubAcao = atividade.subAcaoId && !atividade.isAcaoPrincipal;
                const badgeClass = isSubAcao ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800';
                badge.className = `inline-flex items-center gap-1 px-2 py-1 ${badgeClass} text-xs font-medium rounded-full`;
                badge.innerHTML = `
                    ${atividade.nome}
                    ${atividade.isAcaoPrincipal ? '<span class="text-xs opacity-70">(Principal)</span>' : ''}
                    <button type="button" onclick="removerTipoAcaoEdit(${atividade.id}, ${atividade.subAcaoId || 'null'}, ${atividade.isAcaoPrincipal || false})" class="text-current hover:opacity-70">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                `;
                badgesContainer.appendChild(badge);
                
                // Cria hidden input
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'tipos_acao_ids[]';
                input.value = atividade.id;
                hiddenInputsContainer.appendChild(input);
            });
        }

        window.removerTipoAcaoEdit = function(tipoAcaoId, subAcaoId, isAcaoPrincipal) {
            // Remove da lista
            atividadesSelecionadasEdit = atividadesSelecionadasEdit.filter(a => {
                if (isAcaoPrincipal) {
                    return !(a.id === tipoAcaoId && a.isAcaoPrincipal);
                }
                if (subAcaoId) {
                    return !(a.id === tipoAcaoId && a.subAcaoId === subAcaoId);
                }
                return a.id !== tipoAcaoId;
            });
            
            // Desmarca checkbox
            const checkboxes = document.querySelectorAll(`.tipo-acao-checkbox-edit[value="${tipoAcaoId}"]`);
            checkboxes.forEach(cb => {
                const cbIsAcaoPrincipal = cb.dataset.isAcaoPrincipal === 'true';
                const cbSubAcaoId = cb.dataset.subAcaoId;
                
                if (isAcaoPrincipal && cbIsAcaoPrincipal) {
                    cb.checked = false;
                } else if (subAcaoId && cbSubAcaoId == subAcaoId) {
                    cb.checked = false;
                } else if (!isAcaoPrincipal && !subAcaoId && !cbSubAcaoId && !cbIsAcaoPrincipal) {
                    cb.checked = false;
                }
            });
            
            // Remove técnicos se não houver mais atividades com esse tipo
            const temOutraAtividade = atividadesSelecionadasEdit.some(a => a.id === tipoAcaoId);
            if (!temOutraAtividade) {
                delete atividadesTecnicosEdit[tipoAcaoId];
            }
            
            atualizarBadgesTiposAcaoEdit();
            atualizarInterfaceTecnicosEdit();
        };

        // Inicializa estrutura com dados existentes e marca checkboxes
        function inicializarDadosExistentes() {
            if (osAtividadesTecnicos && osAtividadesTecnicos.length > 0) {
                // Usa nova estrutura
                osAtividadesTecnicos.forEach(atividade => {
                    if (tiposAcaoDisponiveis[atividade.tipo_acao_id]) {
                        const tipoAcao = tiposAcaoDisponiveis[atividade.tipo_acao_id];
                        const nomeAtividade = atividade.nome_atividade || tipoAcao.descricao;
                        
                        atividadesSelecionadasEdit.push({
                            id: atividade.tipo_acao_id,
                            nome: nomeAtividade,
                            subAcaoId: atividade.sub_acao_id || null,
                            isAcaoPrincipal: !atividade.sub_acao_id
                        });
                        
                        atividadesTecnicosEdit[atividade.tipo_acao_id] = {
                            responsavel: atividade.responsavel_id,
                            tecnicos: atividade.tecnicos || []
                        };
                        
                        // Marca o checkbox correspondente
                        const checkboxes = document.querySelectorAll(`.tipo-acao-checkbox-edit[value="${atividade.tipo_acao_id}"]`);
                        checkboxes.forEach(cb => {
                            const subAcaoId = cb.dataset.subAcaoId;
                            const isAcaoPrincipal = cb.dataset.isAcaoPrincipal === 'true';
                            
                            if (atividade.sub_acao_id) {
                                // Se tem subação, marca a subação correspondente
                                if (subAcaoId == atividade.sub_acao_id) {
                                    cb.checked = true;
                                }
                            } else {
                                // Se não tem subação, marca a ação principal
                                if (isAcaoPrincipal || !subAcaoId) {
                                    cb.checked = true;
                                }
                            }
                        });
                    }
                });
            } else if (osTiposAcaoIds && osTiposAcaoIds.length > 0) {
                // Migra da estrutura antiga
                const osTecnicosIds = @json($ordemServico->tecnicos_ids ?? []);
                
                osTiposAcaoIds.forEach(tipoAcaoId => {
                    if (tiposAcaoDisponiveis[tipoAcaoId]) {
                        atividadesSelecionadasEdit.push({
                            id: tipoAcaoId,
                            nome: tiposAcaoDisponiveis[tipoAcaoId].descricao,
                            isAcaoPrincipal: true
                        });
                        
                        atividadesTecnicosEdit[tipoAcaoId] = {
                            responsavel: osTecnicosIds.length > 0 ? osTecnicosIds[0] : null,
                            tecnicos: osTecnicosIds || []
                        };
                        
                        // Marca o checkbox - prioriza ação principal se existir
                        const checkboxPrincipal = document.querySelector(`.tipo-acao-checkbox-edit[value="${tipoAcaoId}"][data-is-acao-principal="true"]`);
                        const checkboxSimples = document.querySelector(`.tipo-acao-checkbox-edit[value="${tipoAcaoId}"]:not([data-sub-acao-id])`);
                        
                        if (checkboxPrincipal) {
                            checkboxPrincipal.checked = true;
                        } else if (checkboxSimples) {
                            checkboxSimples.checked = true;
                        }
                    }
                });
            }
            
            atualizarBadgesTiposAcaoEdit();
            atualizarInterfaceTecnicosEdit();
        }

        // Inicializa dados existentes
        inicializarDadosExistentes();

        // Função para atualizar a interface de técnicos por atividade
        function atualizarInterfaceTecnicosEdit() {
            const container = document.getElementById('atividades-tecnicos-container-edit');
            
            if (atividadesSelecionadasEdit.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm italic">Selecione os tipos de ação para configurar os técnicos.</p>';
                return;
            }
            
            container.innerHTML = '';
            
            atividadesSelecionadasEdit.forEach(atividade => {
                const atividadeDiv = document.createElement('div');
                atividadeDiv.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';
                
                const tecnicosAtribuidos = atividadesTecnicosEdit[atividade.id] || { responsavel: null, tecnicos: [] };
                const responsavelNome = tecnicosAtribuidos.responsavel ? 
                    document.querySelector(`#responsavel-select-edit option[value="${tecnicosAtribuidos.responsavel}"]`)?.textContent || 'Técnico não encontrado' : 
                    'Não definido';
                
                const tecnicosAdicionais = tecnicosAtribuidos.tecnicos.length > 0 ? 
                    tecnicosAtribuidos.tecnicos.map(id => {
                        const option = document.querySelector(`#responsavel-select-edit option[value="${id}"]`);
                        return option ? option.textContent : 'Técnico não encontrado';
                    }).join(', ') : 'Nenhum';
                
                // Se tem subação, mostra a subação como título principal; se é ação principal, mostra badge
                let tituloAtividade;
                if (atividade.subAcaoId && !atividade.isAcaoPrincipal) {
                    tituloAtividade = `<span class="text-indigo-600">${atividade.nome}</span>`;
                } else if (atividade.isAcaoPrincipal) {
                    tituloAtividade = `${atividade.nome} <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-2">Principal</span>`;
                } else {
                    tituloAtividade = atividade.nome;
                }
                
                atividadeDiv.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-900">${tituloAtividade}</h4>
                        <button type="button" onclick="abrirModalTecnicosAtividadeEdit(${atividade.id}, '${atividade.nome}')" 
                                class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full hover:bg-blue-200 transition-colors">
                            ${tecnicosAtribuidos.responsavel ? 'Editar' : 'Atribuir'} Técnicos
                        </button>
                    </div>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><span class="font-medium">Responsável:</span> ${responsavelNome}</p>
                        <p><span class="font-medium">Técnicos adicionais:</span> ${tecnicosAdicionais}</p>
                    </div>
                `;
                
                container.appendChild(atividadeDiv);
            });
            
            // Atualiza os hidden inputs
            atualizarHiddenInputsTecnicosEdit();
        }

        // Funções para Modal de Técnicos por Atividade
        window.abrirModalTecnicosAtividadeEdit = function(atividadeId, atividadeNome) {
            atividadeAtualModalEdit = atividadeId;
            document.getElementById('modal-atividade-titulo-edit').textContent = `Atribuir Técnicos - ${atividadeNome}`;
            
            // Carrega dados existentes
            const tecnicosAtribuidos = atividadesTecnicosEdit[atividadeId] || { responsavel: null, tecnicos: [] };
            
            // Define responsável
            document.getElementById('responsavel-select-edit').value = tecnicosAtribuidos.responsavel || '';
            
            // Define técnicos adicionais
            document.querySelectorAll('.tecnico-adicional-checkbox-edit').forEach(cb => {
                cb.checked = tecnicosAtribuidos.tecnicos.includes(parseInt(cb.value));
            });
            
            document.getElementById('modal-tecnicos-atividade-edit').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.fecharModalTecnicosAtividadeEdit = function() {
            document.getElementById('modal-tecnicos-atividade-edit').classList.add('hidden');
            document.body.style.overflow = '';
            atividadeAtualModalEdit = null;
        };

        window.confirmarTecnicosAtividadeEdit = function() {
            if (!atividadeAtualModalEdit) return;
            
            const responsavelId = document.getElementById('responsavel-select-edit').value;
            if (!responsavelId) {
                alert('Selecione um técnico responsável.');
                return;
            }
            
            const tecnicosAdicionais = Array.from(document.querySelectorAll('.tecnico-adicional-checkbox-edit:checked'))
                .map(cb => parseInt(cb.value))
                .filter(id => id !== parseInt(responsavelId)); // Remove o responsável dos adicionais
            
            // Salva na estrutura
            atividadesTecnicosEdit[atividadeAtualModalEdit] = {
                responsavel: parseInt(responsavelId),
                tecnicos: [parseInt(responsavelId), ...tecnicosAdicionais] // Responsável sempre está incluído
            };
            
            // Atualiza interface
            atualizarInterfaceTecnicosEdit();
            fecharModalTecnicosAtividadeEdit();
        };

        // Função para atualizar os hidden inputs da estrutura de técnicos
        function atualizarHiddenInputsTecnicosEdit() {
            const container = document.getElementById('atividades-tecnicos-hidden-inputs-edit');
            container.innerHTML = '';
            
            // Cria a estrutura atividades_tecnicos
            const estrutura = atividadesSelecionadasEdit.map(atividade => {
                const tecnicosAtribuidos = atividadesTecnicosEdit[atividade.id];
                if (!tecnicosAtribuidos || !tecnicosAtribuidos.responsavel) {
                    return null; // Pula atividades sem técnicos atribuídos
                }
                
                return {
                    tipo_acao_id: parseInt(atividade.id),
                    sub_acao_id: atividade.subAcaoId ? parseInt(atividade.subAcaoId) : null,
                    nome_atividade: atividade.nome,
                    tecnicos: tecnicosAtribuidos.tecnicos,
                    responsavel_id: tecnicosAtribuidos.responsavel,
                    status: 'pendente'
                };
            }).filter(item => item !== null);
            
            // Cria hidden input com JSON
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'atividades_tecnicos';
            input.value = JSON.stringify(estrutura);
            container.appendChild(input);
            
            // Mantém compatibilidade com tecnicos_ids
            const tecnicosIds = [];
            estrutura.forEach(atividade => {
                tecnicosIds.push(...atividade.tecnicos);
            });
            const tecnicosUnicos = [...new Set(tecnicosIds)];
            
            tecnicosUnicos.forEach(tecnicoId => {
                const inputTecnico = document.createElement('input');
                inputTecnico.type = 'hidden';
                inputTecnico.name = 'tecnicos_ids[]';
                inputTecnico.value = tecnicoId;
                container.appendChild(inputTecnico);
            });
        }

        // Inicializa a interface
        atualizarInterfaceTecnicosEdit();

        // Buscar processos ao selecionar estabelecimento
        const estabelecimentoSelect = document.getElementById('estabelecimento_id');
        const processoContainer = document.getElementById('processo-container');
        const processoSelect = document.getElementById('processo_id');
        const processoLoading = document.getElementById('processo-loading');
        const processoObrigatorioLabel = document.getElementById('processo-obrigatorio-label');
        const processoAtualId = {{ $ordemServico->processo_id ?? 'null' }};

        // Mostra container se já tem estabelecimento selecionado
        if (estabelecimentoSelect.value) {
            processoContainer.style.display = 'block';
            processoObrigatorioLabel.style.display = 'inline';
            processoSelect.required = true;
            buscarProcessos(estabelecimentoSelect.value, processoAtualId);
        }

        // Listener do Select2 para estabelecimento
        $('#estabelecimento_id').on('change', function() {
            const estabelecimentoId = this.value;
            
            if (!estabelecimentoId) {
                processoContainer.style.display = 'none';
                processoObrigatorioLabel.style.display = 'none';
                processoSelect.required = false;
                processoSelect.innerHTML = '<option value="">Sem processo vinculado</option>';
                return;
            }

            processoContainer.style.display = 'block';
            processoObrigatorioLabel.style.display = 'inline';
            processoSelect.required = true;
            buscarProcessos(estabelecimentoId, processoAtualId);
        });

        async function buscarProcessos(estabelecimentoId, processoSelecionado = null) {
            // Mostra loading
            processoLoading.classList.remove('hidden');
            processoSelect.disabled = true;

            try {
                const response = await fetch(`${window.APP_URL}/admin/ordens-servico/estabelecimento/${estabelecimentoId}/processos`);
                const data = await response.json();

                // Limpa select
                processoSelect.innerHTML = '<option value="">Sem processo vinculado</option>';

                if (data.processos && data.processos.length > 0) {
                    data.processos.forEach(processo => {
                        const option = document.createElement('option');
                        option.value = processo.id;
                        // Formato: "2025/00004 - Licenciamento"
                        option.textContent = processo.texto_completo || `${processo.numero} - ${processo.tipo}`;
                        
                        if (processoSelecionado && processo.id == processoSelecionado) {
                            option.selected = true;
                        }
                        
                        processoSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Nenhum processo ativo encontrado';
                    option.disabled = true;
                    processoSelect.appendChild(option);
                }
            } catch (error) {
                console.error('Erro ao buscar processos:', error);
                alert('Erro ao buscar processos do estabelecimento');
            } finally {
                // Esconde loading
                processoLoading.classList.add('hidden');
                processoSelect.disabled = false;
            }
        }
    });
</script>
@endpush
