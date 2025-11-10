@extends('layouts.admin')

@section('title', 'Editar Ordem de Serviço')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">
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
                        <select name="tipos_acao_ids[]" 
                                id="tipos_acao_ids" 
                                multiple
                                required
                                class="w-full">
                            @foreach($tiposAcao as $tipoAcao)
                            <option value="{{ $tipoAcao->id }}" {{ in_array($tipoAcao->id, old('tipos_acao_ids', $ordemServico->tipos_acao_ids ?? [])) ? 'selected' : '' }}>
                                {{ $tipoAcao->descricao }}
                            </option>
                            @endforeach
                        </select>
                        @error('tipos_acao_ids')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Técnicos (Múltiplos) --}}
                    <div class="md:col-span-2">
                        <label for="tecnicos_ids" class="block text-sm font-medium text-gray-700 mb-1">
                            Técnicos Responsáveis <span class="text-red-500">*</span>
                        </label>
                        <select name="tecnicos_ids[]" 
                                id="tecnicos_ids" 
                                multiple
                                required
                                class="w-full">
                            @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" {{ in_array($tecnico->id, old('tecnicos_ids', $ordemServico->tecnicos_ids ?? [])) ? 'selected' : '' }}>
                                {{ $tecnico->nome }}
                            </option>
                            @endforeach
                        </select>
                        @error('tecnicos_ids')
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
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
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
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
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

        // Inicializa Choices.js para Tipos de Ação
        const tiposAcaoSelect = new Choices('#tipos_acao_ids', {
            removeItemButton: true,
            searchEnabled: true,
            searchPlaceholderValue: 'Pesquisar tipos de ação...',
            noResultsText: 'Nenhum tipo de ação encontrado',
            noChoicesText: 'Nenhum tipo de ação disponível',
            itemSelectText: 'Clique para selecionar',
            placeholder: true,
            placeholderValue: 'Selecione os tipos de ação',
            maxItemCount: -1,
            shouldSort: false
        });

        // Inicializa Choices.js para Técnicos
        const tecnicosSelect = new Choices('#tecnicos_ids', {
            removeItemButton: true,
            searchEnabled: true,
            searchPlaceholderValue: 'Pesquisar técnicos...',
            noResultsText: 'Nenhum técnico encontrado',
            noChoicesText: 'Nenhum técnico disponível',
            itemSelectText: 'Clique para selecionar',
            placeholder: true,
            placeholderValue: 'Selecione os técnicos responsáveis',
            maxItemCount: -1,
            shouldSort: false
        });

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
                const response = await fetch(`/admin/ordens-servico/estabelecimento/${estabelecimentoId}/processos`);
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
