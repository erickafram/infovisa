@extends('layouts.admin')

@section('title', 'Nova Ordem de Serviço')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.ordens-servico.index') }}" 
               class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Nova Ordem de Serviço</h1>
                <p class="text-sm text-gray-600 mt-1">Cadastre uma nova ordem de serviço no sistema</p>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.ordens-servico.store') }}" class="space-y-6">
            @csrf

            {{-- Dados Principais --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Dados Principais
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Estabelecimento --}}
                    <div class="md:col-span-2">
                        <label for="estabelecimento_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Estabelecimento <span class="text-red-500">*</span>
                        </label>
                        <select name="estabelecimento_id" 
                                id="estabelecimento_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('estabelecimento_id') border-red-500 @enderror">
                            <option value="">Selecione um estabelecimento</option>
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
                            <option value="{{ $tipoAcao->id }}" {{ in_array($tipoAcao->id, old('tipos_acao_ids', [])) ? 'selected' : '' }}>
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
                            <option value="{{ $tecnico->id }}" {{ in_array($tecnico->id, old('tecnicos_ids', [])) ? 'selected' : '' }}>
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
                            Data de Início
                        </label>
                        <input type="date" 
                               id="data_inicio" 
                               name="data_inicio" 
                               value="{{ old('data_inicio') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_inicio') border-red-500 @enderror">
                        @error('data_inicio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Data Fim --}}
                    <div>
                        <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">
                            Data Fim
                        </label>
                        <input type="date" 
                               id="data_fim" 
                               name="data_fim" 
                               value="{{ old('data_fim') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_fim') border-red-500 @enderror">
                        @error('data_fim')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Pode ser no mesmo dia que a data de início</p>
                    </div>

                    {{-- Prioridade --}}
                    <div>
                        <label for="prioridade" class="block text-sm font-medium text-gray-700 mb-1">
                            Prioridade <span class="text-red-500">*</span>
                        </label>
                        <select id="prioridade" 
                                name="prioridade" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('prioridade') border-red-500 @enderror">
                            <option value="baixa" {{ old('prioridade') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                            <option value="media" {{ old('prioridade', 'media') == 'media' ? 'selected' : '' }}>Média</option>
                            <option value="alta" {{ old('prioridade') == 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="urgente" {{ old('prioridade') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                        </select>
                        @error('prioridade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('observacoes') border-red-500 @enderror">{{ old('observacoes') }}</textarea>
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
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
@endpush
