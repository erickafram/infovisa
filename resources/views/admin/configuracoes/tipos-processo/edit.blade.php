@extends('layouts.admin')

@section('title', 'Editar Tipo de Processo')
@section('page-title', 'Editar Tipo de Processo')

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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Editar Tipo de Processo</h1>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $tipoProcesso->nome }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('admin.configuracoes.tipos-processo.update', $tipoProcesso->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

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

            <div class="space-y-6">
                {{-- Nome --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Tipo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nome" 
                           value="{{ old('nome', $tipoProcesso->nome) }}"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror"
                           placeholder="Ex: Licenciamento Sanitário">
                    @error('nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Código --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Código <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="codigo" 
                           value="{{ old('codigo', $tipoProcesso->codigo) }}"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('codigo') border-red-500 @enderror"
                           placeholder="Ex: licenciamento">
                    <p class="mt-1 text-xs text-gray-500">Código único para identificação interna (sem espaços, apenas letras minúsculas e underscores)</p>
                    @error('codigo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição
                    </label>
                    <textarea name="descricao" 
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none @error('descricao') border-red-500 @enderror"
                              placeholder="Descreva brevemente este tipo de processo...">{{ old('descricao', $tipoProcesso->descricao) }}</textarea>
                    @error('descricao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ordem --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Ordem de Exibição
                    </label>
                    <input type="number" 
                           name="ordem" 
                           value="{{ old('ordem', $tipoProcesso->ordem) }}"
                           min="0"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('ordem') border-red-500 @enderror"
                           placeholder="0">
                    <p class="mt-1 text-xs text-gray-500">Ordem em que aparecerá nas listagens (menor número aparece primeiro)</p>
                    @error('ordem')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Card Competência e Descentralização --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden" x-data="{ competencia: '{{ old('competencia', $tipoProcesso->competencia ?? 'municipal') }}' }">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900">Competência e Descentralização</h3>
                </div>
            </div>
            <div class="p-6">

            <div class="space-y-6">
                {{-- Competência --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Competência <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="radio" 
                                       name="competencia" 
                                       id="competencia_municipal"
                                       value="municipal"
                                       x-model="competencia"
                                       {{ old('competencia', $tipoProcesso->competencia ?? 'municipal') === 'municipal' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            </div>
                            <div class="ml-3">
                                <label for="competencia_municipal" class="font-medium text-gray-700">🏢 Somente Municipal</label>
                                <p class="text-sm text-gray-500">Apenas municípios podem criar este tipo de processo</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="radio" 
                                       name="competencia" 
                                       id="competencia_estadual"
                                       value="estadual"
                                       x-model="competencia"
                                       {{ old('competencia', $tipoProcesso->competencia) === 'estadual' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            </div>
                            <div class="ml-3">
                                <label for="competencia_estadual" class="font-medium text-gray-700">🏛️ Estadual (com seleção de municípios descentralizados)</label>
                                <p class="text-sm text-gray-500">Apenas o estado pode criar, mas municípios descentralizados também podem (selecione abaixo)</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="radio" 
                                       name="competencia" 
                                       id="competencia_estadual_exclusivo"
                                       value="estadual_exclusivo"
                                       x-model="competencia"
                                       {{ old('competencia', $tipoProcesso->competencia) === 'estadual_exclusivo' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            </div>
                            <div class="ml-3">
                                <label for="competencia_estadual_exclusivo" class="font-medium text-gray-700">🏛️ Somente Estadual</label>
                                <p class="text-sm text-gray-500">Apenas o estado pode criar este tipo de processo (sem exceções)</p>
                            </div>
                        </div>
                    </div>
                    @error('competencia')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Municípios Descentralizados (apenas para estadual) --}}
                <div x-show="competencia === 'estadual'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Municípios Descentralizados
                    </label>
                    <select name="municipios_descentralizados[]" 
                            multiple
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            size="8">
                        @foreach($municipios as $municipio)
                            <option value="{{ $municipio->nome }}" 
                                    {{ in_array($municipio->nome, old('municipios_descentralizados', $tipoProcesso->municipios_descentralizados ?? [])) ? 'selected' : '' }}>
                                {{ $municipio->nome }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Selecione os municípios que terão permissão para criar este tipo de processo (mesmo sendo estadual). 
                        Segure Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplos.
                    </p>
                </div>
            </div>
        </div>

        {{-- Card Configurações --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900">Configurações</h3>
                </div>
            </div>
            <div class="p-6">

            <div class="space-y-4">
                {{-- Processo Anual --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               name="anual" 
                               id="anual"
                               {{ old('anual', $tipoProcesso->anual) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </div>
                    <div class="ml-3">
                        <label for="anual" class="font-medium text-gray-700">Processo Anual</label>
                        <p class="text-sm text-gray-500">Apenas um processo deste tipo pode ser aberto por estabelecimento por ano</p>
                    </div>
                </div>

                {{-- Usuário Externo Pode Abrir --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               name="usuario_externo_pode_abrir" 
                               id="usuario_externo_pode_abrir"
                               {{ old('usuario_externo_pode_abrir', $tipoProcesso->usuario_externo_pode_abrir) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </div>
                    <div class="ml-3">
                        <label for="usuario_externo_pode_abrir" class="font-medium text-gray-700">Usuário Externo Pode Abrir</label>
                        <p class="text-sm text-gray-500">Permite que usuários externos (empresas) possam abrir este tipo de processo</p>
                    </div>
                </div>

                {{-- Usuário Externo Pode Visualizar --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               name="usuario_externo_pode_visualizar" 
                               id="usuario_externo_pode_visualizar"
                               {{ old('usuario_externo_pode_visualizar', $tipoProcesso->usuario_externo_pode_visualizar ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </div>
                    <div class="ml-3">
                        <label for="usuario_externo_pode_visualizar" class="font-medium text-gray-700">Usuário Externo Pode Visualizar</label>
                        <p class="text-sm text-gray-500">Permite que usuários externos (empresas) possam visualizar este processo quando aberto por usuário interno. Desmarque para processos internos como Descentralização, Denúncia, etc.</p>
                    </div>
                </div>

                {{-- Ativo --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               name="ativo" 
                               id="ativo"
                               {{ old('ativo', $tipoProcesso->ativo) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </div>
                    <div class="ml-3">
                        <label for="ativo" class="font-medium text-gray-700">Ativo</label>
                        <p class="text-sm text-gray-500">Tipo de processo disponível para uso no sistema</p>
                    </div>
                </div>
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
                Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection
