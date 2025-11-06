@extends('layouts.admin')

@section('title', 'Novo Tipo de Processo')
@section('page-title', 'Novo Tipo de Processo')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.configuracoes.tipos-processo.index') }}" 
           class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Novo Tipo de Processo</h1>
            <p class="text-xs text-gray-500 mt-0.5">Adicione um novo tipo de processo ao sistema</p>
        </div>
    </div>

    {{-- Erros de Validação --}}
    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 p-3 rounded-lg">
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-xs font-medium text-red-800 mb-1">Erro ao criar tipo de processo</p>
                    <ul class="text-xs text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('admin.configuracoes.tipos-processo.store') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Card Principal --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-100">Informações Básicas</h3>

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
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5" x-data="{ competencia: '{{ old('competencia', 'municipal') }}', municipiosSelecionados: {{ json_encode(old('municipios_descentralizados', [])) }} }">
            <h3 class="text-sm font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-100">Competência</h3>

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
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-100">Configurações</h3>

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
        <div class="flex items-center justify-end gap-2 pt-2">
            <a href="{{ route('admin.configuracoes.tipos-processo.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                Criar Tipo de Processo
            </button>
        </div>
    </form>
</div>
@endsection
