@extends('layouts.admin')

@section('title', 'Editar Modelo de Documento')
@section('page-title', 'Editar Modelo de Documento')

@section('content')
<div class="max-w-4xl mx-auto">
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
            <span class="text-gray-900 font-medium">Editar: {{ $modeloDocumento->nome }}</span>
        </nav>
    </div>

    {{-- Formulário --}}
    <form method="POST" action="{{ route('admin.configuracoes.modelos-documento.update', $modeloDocumento->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Card Principal --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Informações do Modelo</h3>

            <div class="space-y-6">
                {{-- Nome --}}
                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Modelo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nome" 
                           id="nome" 
                           value="{{ old('nome', $modeloDocumento->nome) }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror"
                           placeholder="Ex: Alvará Sanitário">
                    @error('nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipo de Documento --}}
                <div>
                    <label for="tipo_documento" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Documento <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo_documento" 
                            id="tipo_documento" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tipo_documento') border-red-500 @enderror">
                        <option value="">Selecione o tipo</option>
                        <option value="alvara" {{ old('tipo_documento', $modeloDocumento->tipo_documento) == 'alvara' ? 'selected' : '' }}>Alvará</option>
                        <option value="memorando" {{ old('tipo_documento', $modeloDocumento->tipo_documento) == 'memorando' ? 'selected' : '' }}>Memorando</option>
                        <option value="notificacao" {{ old('tipo_documento', $modeloDocumento->tipo_documento) == 'notificacao' ? 'selected' : '' }}>Notificação</option>
                        <option value="oficio" {{ old('tipo_documento', $modeloDocumento->tipo_documento) == 'oficio' ? 'selected' : '' }}>Ofício</option>
                        <option value="termo" {{ old('tipo_documento', $modeloDocumento->tipo_documento) == 'termo' ? 'selected' : '' }}>Termo</option>
                        <option value="relatorio" {{ old('tipo_documento', $modeloDocumento->tipo_documento) == 'relatorio' ? 'selected' : '' }}>Relatório</option>
                        <option value="declaracao" {{ old('tipo_documento', $modeloDocumento->tipo_documento) == 'declaracao' ? 'selected' : '' }}>Declaração</option>
                        <option value="atestado" {{ old('tipo_documento', $modeloDocumento->tipo_documento) == 'atestado' ? 'selected' : '' }}>Atestado</option>
                    </select>
                    @error('tipo_documento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Código --}}
                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                        Código (opcional)
                    </label>
                    <input type="text" 
                           name="codigo" 
                           id="codigo" 
                           value="{{ old('codigo', $modeloDocumento->codigo) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('codigo') border-red-500 @enderror"
                           placeholder="Ex: alvara_sanitario">
                    <p class="mt-1 text-xs text-gray-500">Se não informado, será gerado automaticamente a partir do nome</p>
                    @error('codigo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição --}}
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição
                    </label>
                    <textarea name="descricao" 
                              id="descricao" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror"
                              placeholder="Descreva o propósito deste modelo">{{ old('descricao', $modeloDocumento->descricao) }}</textarea>
                    @error('descricao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Conteúdo --}}
                <div>
                    <label for="conteudo" class="block text-sm font-medium text-gray-700 mb-2">
                        Conteúdo do Modelo <span class="text-red-500">*</span>
                    </label>
                    <textarea name="conteudo" 
                              id="conteudo" 
                              rows="15"
                              required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm @error('conteudo') border-red-500 @enderror"
                              placeholder="Digite o conteúdo HTML do documento...">{{ old('conteudo', $modeloDocumento->conteudo) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Use variáveis como: {estabelecimento_nome}, {processo_numero}, {data_atual}</p>
                    @error('conteudo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Grid: Ordem e Status --}}
                <div class="grid grid-cols-2 gap-6">
                    {{-- Ordem --}}
                    <div>
                        <label for="ordem" class="block text-sm font-medium text-gray-700 mb-2">
                            Ordem de Exibição
                        </label>
                        <input type="number" 
                               name="ordem" 
                               id="ordem" 
                               value="{{ old('ordem', $modeloDocumento->ordem) }}"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('ordem') border-red-500 @enderror">
                        @error('ordem')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="ativo" class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <div class="flex items-center h-10">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="ativo" 
                                       id="ativo" 
                                       value="1"
                                       {{ old('ativo', $modeloDocumento->ativo) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-700">Ativo</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.configuracoes.modelos-documento.index') }}" 
               class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection
