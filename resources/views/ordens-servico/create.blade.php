@extends('layouts.admin')

@section('title', 'Nova Ordem de Serviço')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.ordens-servico.index') }}" 
           class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Nova Ordem de Serviço</h1>
    </div>

    {{-- Formulário --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form method="POST" action="{{ route('admin.ordens-servico.store') }}" class="divide-y divide-gray-200">
            @csrf

            {{-- Estabelecimento e Processo --}}
            <div class="p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Estabelecimento e Processo</h2>
                
                <div class="grid grid-cols-1 gap-4">
                    {{-- Estabelecimento --}}
                    <div>
                        <label for="estabelecimento_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Estabelecimento <span class="text-gray-500">(Opcional)</span>
                        </label>
                        <select name="estabelecimento_id" 
                                id="estabelecimento_id" 
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white @error('estabelecimento_id') border-red-500 @enderror">
                            <option value="">Sem estabelecimento (vincular depois)</option>
                            @foreach($estabelecimentos as $estabelecimento)
                            <option value="{{ $estabelecimento->id }}" {{ old('estabelecimento_id') == $estabelecimento->id ? 'selected' : '' }}>
                                {{ $estabelecimento->nome_fantasia }} - {{ $estabelecimento->razao_social }}
                            </option>
                            @endforeach
                        </select>
                        @error('estabelecimento_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Processo --}}
                    <div id="processo-container">
                        <label for="processo_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Processo Vinculado <span class="text-gray-500">(Opcional - apenas se houver estabelecimento)</span>
                        </label>
                        <select name="processo_id" 
                                id="processo_id" 
                                disabled
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100 @error('processo_id') border-red-500 @enderror">
                            <option value="">Selecione primeiro um estabelecimento</option>
                        </select>
                        @error('processo_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-2">
                            <span id="processo-info" class="hidden text-xs text-blue-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span id="processo-count"></span>
                            </span>
                            <span id="processo-sem-processo" class="hidden text-xs text-amber-600 flex items-center gap-1 bg-amber-50 p-2 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Este estabelecimento não possui processos ativos. Crie um processo antes de criar a ordem de serviço.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tipos de Ação --}}
            <div class="p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Tipos de Ação</h2>
                
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

            {{-- Técnicos Responsáveis --}}
            <div class="p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Técnicos Responsáveis</h2>
                
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

            {{-- Período e Observações --}}
            <div class="p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Período e Observações</h2>
                
                <div class="space-y-4">

                    {{-- Período de Execução --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Período de Execução (Opcional)
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Data de Início --}}
                            <div>
                                <label for="data_inicio" class="block text-xs font-medium text-gray-600 mb-1">
                                    Data de Início
                                </label>
                                <input type="date" 
                                       id="data_inicio" 
                                       name="data_inicio" 
                                       value="{{ old('data_inicio') }}"
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_inicio') border-red-500 @enderror">
                                @error('data_inicio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Data Fim --}}
                            <div>
                                <label for="data_fim" class="block text-xs font-medium text-gray-600 mb-1">
                                    Data de Término
                                </label>
                                <input type="date" 
                                       id="data_fim" 
                                       name="data_fim" 
                                       value="{{ old('data_fim') }}"
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
                            As datas podem ser preenchidas durante a execução da ordem de serviço.
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
            <div class="flex items-center justify-end gap-3 p-6 bg-gray-50">
                <a href="{{ route('admin.ordens-servico.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition">
                    Salvar Ordem de Serviço
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        const estabelecimentoSelect = document.getElementById('estabelecimento_id');
        const processoSelect = document.getElementById('processo_id');
        const processoInfo = document.getElementById('processo-info');
        const processoCount = document.getElementById('processo-count');
        const processoSemProcesso = document.getElementById('processo-sem-processo');
        const submitButton = document.querySelector('button[type="submit"]');

        estabelecimentoSelect.addEventListener('change', function() {
            const estabelecimentoId = this.value;
            
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
