@extends('layouts.admin')

@section('title', 'Novo Modelo de Documento')
@section('page-title', 'Novo Modelo de Documento')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Breadcrumb --}}
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-600">
            <a href="{{ route('admin.configuracoes.index') }}" class="hover:text-blue-600">Configurações</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('admin.configuracoes.modelos-documento.index') }}" class="hover:text-blue-600">Modelos de Documentos</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">Novo Modelo</span>
        </nav>
    </div>

    {{-- Formulário --}}
    <form method="POST" action="{{ route('admin.configuracoes.modelos-documento.store') }}" class="space-y-6">
        @csrf

        {{-- Card: Informações Básicas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs font-bold">1</span>
                Informações do Modelo
            </h3>

            <div class="space-y-6">
                {{-- Grid: Tipo e Ordem --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Tipo de Documento --}}
                    <div class="md:col-span-2">
                        <label for="tipo_documento_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Documento <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_documento_id" 
                                id="tipo_documento_id" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tipo_documento_id') border-red-500 @enderror">
                            <option value="">Selecione o tipo</option>
                            @foreach($tiposDocumento as $tipo)
                                <option value="{{ $tipo->id }}" {{ old('tipo_documento_id') == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_documento_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ordem --}}
                    <div>
                        <label for="ordem" class="block text-sm font-medium text-gray-700 mb-2">
                            Ordem
                        </label>
                        <input type="number" 
                               name="ordem" 
                               id="ordem" 
                               value="{{ old('ordem', 0) }}"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('ordem') border-red-500 @enderror">
                        @error('ordem')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Descrição --}}
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição
                    </label>
                    <textarea name="descricao" 
                              id="descricao" 
                              rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror"
                              placeholder="Descreva o propósito deste modelo (opcional)">{{ old('descricao') }}</textarea>
                    @error('descricao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label for="ativo" class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <div class="flex items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="ativo" 
                                   id="ativo" 
                                   value="1"
                                   {{ old('ativo', true) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Ativo</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Editor de Conteúdo --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="flex items-center justify-center w-6 h-6 bg-green-600 text-white rounded-full text-xs font-bold">2</span>
                Conteúdo do Modelo <span class="text-red-500">*</span>
            </h3>

            @php $conteudoInicial = old('conteudo', ''); @endphp
            @include('configuracoes.modelos-documento.partials.editor-wysiwyg')
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.configuracoes.modelos-documento.index') }}" 
               class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                Criar Modelo
            </button>
        </div>
    </form>
</div>
@endsection
