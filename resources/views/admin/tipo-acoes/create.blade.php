@extends('layouts.admin')

@section('title', 'Nova Ação')
@section('page-title', 'Nova Ação')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        {{-- Header --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">Cadastrar Tipo de Ação</h2>
            <p class="mt-1 text-sm text-gray-600">Preencha os dados da nova ação</p>
        </div>

        {{-- Form --}}
        <form action="{{ route('admin.configuracoes.tipo-acoes.store') }}" method="POST">
            @csrf

            {{-- Descrição --}}
            <div class="mb-5">
                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                    Descrição <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="descricao"
                       name="descricao" 
                       value="{{ old('descricao') }}"
                       required
                       maxlength="255"
                       placeholder="Ex: Inspeção Sanitária em Estabelecimento"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror">
                @error('descricao')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Código do Procedimento --}}
            <div class="mb-5">
                <label for="codigo_procedimento" class="block text-sm font-medium text-gray-700 mb-2">
                    Código do Procedimento <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="codigo_procedimento"
                       name="codigo_procedimento" 
                       value="{{ old('codigo_procedimento') }}"
                       required
                       maxlength="255"
                       placeholder="Ex: 0301010012"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono @error('codigo_procedimento') border-red-500 @enderror">
                @error('codigo_procedimento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Código único que identifica o procedimento</p>
            </div>

            {{-- Competência --}}
            <div class="mb-5">
                <label for="competencia" class="block text-sm font-medium text-gray-700 mb-2">
                    Competência <span class="text-red-500">*</span>
                </label>
                <select id="competencia"
                        name="competencia" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('competencia') border-red-500 @enderror">
                    <option value="">Selecione...</option>
                    <option value="estadual" {{ old('competencia') === 'estadual' ? 'selected' : '' }}>Estadual</option>
                    <option value="municipal" {{ old('competencia') === 'municipal' ? 'selected' : '' }}>Municipal</option>
                    <option value="ambos" {{ old('competencia') === 'ambos' ? 'selected' : '' }}>Ambos (Estadual e Municipal)</option>
                </select>
                @error('competencia')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Checkboxes --}}
            <div class="mb-6 space-y-3">
                {{-- Atividade SIA --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="atividade_sia" 
                               name="atividade_sia" 
                               type="checkbox"
                               value="1"
                               {{ old('atividade_sia') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </div>
                    <div class="ml-3">
                        <label for="atividade_sia" class="font-medium text-gray-700 text-sm">
                            Atividade SIA
                        </label>
                        <p class="text-xs text-gray-500">Marque se esta ação faz parte do Sistema de Informação Ambulatorial</p>
                    </div>
                </div>

                {{-- Ativo --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="ativo" 
                               name="ativo" 
                               type="checkbox"
                               value="1"
                               checked
                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </div>
                    <div class="ml-3">
                        <label for="ativo" class="font-medium text-gray-700 text-sm">
                            Ativo
                        </label>
                        <p class="text-xs text-gray-500">Ações ativas podem ser utilizadas no sistema</p>
                    </div>
                </div>
            </div>

            {{-- Info Box --}}
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium mb-1">Sobre os Tipos de Ações</p>
                        <p>As ações representam procedimentos realizados pela vigilância sanitária e podem ser vinculadas a processos, ordens de serviço e outros módulos do sistema.</p>
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.configuracoes.tipo-acoes.index') }}" 
                   class="flex-1 px-4 py-3 text-center text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="flex-1 px-4 py-3 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    Cadastrar Ação
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
