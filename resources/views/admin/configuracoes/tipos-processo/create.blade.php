@extends('layouts.admin')

@section('title', 'Novo Tipo de Processo')
@section('page-title', 'Novo Tipo de Processo')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('admin.configuracoes.tipos-processo.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border-2 border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-blue-300 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-1">
                    <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Novo Tipo de Processo</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Adicione um novo tipo de processo ao sistema</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Erros de Validação --}}
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-red-800 mb-2">Erro ao criar tipo de processo</h3>
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="flex items-start gap-2">
                                <span class="text-red-500 mt-0.5">•</span>
                                <span>{{ $error }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('admin.configuracoes.tipos-processo.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Card Principal --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900">Informações Básicas</h3>
                </div>
            </div>
            <div class="p-6">

            <div class="space-y-4">
                {{-- Nome e Código --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">
                            Nome do Tipo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="nome" 
                               value="{{ old('nome') }}"
                               required
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror"
                               placeholder="Ex: Licenciamento Sanitário">
                        @error('nome')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">
                            Código <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="codigo" 
                               value="{{ old('codigo') }}"
                               required
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('codigo') border-red-500 @enderror"
                               placeholder="Ex: licenciamento">
                        @error('codigo')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Descrição --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">
                        Descrição
                    </label>
                    <textarea name="descricao" 
                              rows="2"
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none @error('descricao') border-red-500 @enderror"
                              placeholder="Descreva brevemente este tipo de processo...">{{ old('descricao') }}</textarea>
                    @error('descricao')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ordem --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">
                        Ordem de Exibição
                    </label>
                    <input type="number" 
                           name="ordem" 
                           value="{{ old('ordem', 0) }}"
                           min="0"
                           class="w-32 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('ordem') border-red-500 @enderror"
                           placeholder="0">
                    <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro</p>
                    @error('ordem')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Card Competência e Descentralização --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden" x-data="{ competencia: '{{ old('competencia', 'municipal') }}', municipiosSelecionados: {{ json_encode(old('municipios_descentralizados', [])) }} }">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900">Competência</h3>
                </div>
            </div>
            <div class="p-6">

            <div class="space-y-4">
                {{-- Competência --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-2">
                        Tipo de Competência <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="radio" 
                                   name="competencia" 
                                   id="competencia_municipal"
                                   value="municipal"
                                   x-model="competencia"
                                   {{ old('competencia', 'municipal') === 'municipal' ? 'checked' : '' }}
                                   class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3 flex-1">
                                <span class="text-sm font-medium text-gray-900">🏢 Somente Municipal</span>
                                <p class="text-xs text-gray-500 mt-0.5">Apenas municípios podem criar este tipo de processo</p>
                            </div>
                        </label>

                        <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="radio" 
                                   name="competencia" 
                                   id="competencia_estadual"
                                   value="estadual"
                                   x-model="competencia"
                                   {{ old('competencia') === 'estadual' ? 'checked' : '' }}
                                   class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3 flex-1">
                                <span class="text-sm font-medium text-gray-900">🏛️ Estadual (com municípios descentralizados)</span>
                                <p class="text-xs text-gray-500 mt-0.5">Estado pode criar, e municípios descentralizados também (selecione abaixo)</p>
                            </div>
                        </label>

                        <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="radio" 
                                   name="competencia" 
                                   id="competencia_estadual_exclusivo"
                                   value="estadual_exclusivo"
                                   x-model="competencia"
                                   {{ old('competencia') === 'estadual_exclusivo' ? 'checked' : '' }}
                                   class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3 flex-1">
                                <span class="text-sm font-medium text-gray-900">🏛️ Somente Estadual</span>
                                <p class="text-xs text-gray-500 mt-0.5">Apenas o estado pode criar (sem exceções)</p>
                            </div>
                        </label>
                    </div>
                    @error('competencia')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Municípios Descentralizados (apenas para estadual) --}}
                <div x-show="competencia === 'estadual'" x-cloak class="pt-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">
                        Municípios Descentralizados
                    </label>
                    <select name="municipios_descentralizados[]" 
                            multiple
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            size="6">
                        @foreach($municipios as $municipio)
                            <option value="{{ $municipio->nome }}" 
                                    {{ in_array($municipio->nome, old('municipios_descentralizados', [])) ? 'selected' : '' }}>
                                {{ $municipio->nome }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Segure Ctrl/Cmd para selecionar múltiplos municípios
                    </p>
                </div>
            </div>
        </div>

        {{-- Card Configurações --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900">Configurações</h3>
                </div>
            </div>
            <div class="p-6">

            <div class="space-y-3">
                {{-- Processo Anual --}}
                <label class="flex items-start gap-3 p-2.5 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors">
                    <input type="checkbox" 
                           name="anual" 
                           id="anual"
                           {{ old('anual') ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-900">Processo Anual</span>
                        <p class="text-xs text-gray-500 mt-0.5">Apenas um processo por estabelecimento por ano</p>
                    </div>
                </label>

                {{-- Usuário Externo Pode Abrir --}}
                <label class="flex items-start gap-3 p-2.5 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors">
                    <input type="checkbox" 
                           name="usuario_externo_pode_abrir" 
                           id="usuario_externo_pode_abrir"
                           {{ old('usuario_externo_pode_abrir') ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-900">Usuário Externo Pode Abrir</span>
                        <p class="text-xs text-gray-500 mt-0.5">Empresas podem abrir este tipo de processo</p>
                    </div>
                </label>

                {{-- Usuário Externo Pode Visualizar --}}
                <label class="flex items-start gap-3 p-2.5 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors">
                    <input type="checkbox" 
                           name="usuario_externo_pode_visualizar" 
                           id="usuario_externo_pode_visualizar"
                           {{ old('usuario_externo_pode_visualizar', true) ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-900">Usuário Externo Pode Visualizar</span>
                        <p class="text-xs text-gray-500 mt-0.5">Empresas podem visualizar processos abertos por usuário interno</p>
                    </div>
                </label>

                {{-- Ativo --}}
                <label class="flex items-start gap-3 p-2.5 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors">
                    <input type="checkbox" 
                           name="ativo" 
                           id="ativo"
                           {{ old('ativo', true) ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-900">Ativo</span>
                        <p class="text-xs text-gray-500 mt-0.5">Disponível para uso no sistema</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.configuracoes.tipos-processo.index') }}"
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
                Criar Tipo de Processo
            </button>
        </div>
    </form>
</div>
@endsection
