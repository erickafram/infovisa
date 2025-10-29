@extends('layouts.admin')

@section('title', 'Novo Documento POP')
@section('page-title', 'Novo Documento POP')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">Adicionar Novo Documento POP</h2>
            <p class="mt-1 text-sm text-gray-600">Faça upload de documentos de procedimentos operacionais padrão</p>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.configuracoes.documentos-pops.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Título --}}
            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                    Título Base <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="titulo" 
                       id="titulo" 
                       value="{{ old('titulo') }}"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Ex: Procedimento para Inspeção Sanitária">
                <p class="mt-1 text-xs text-gray-500">
                    <strong>Nota:</strong> Ao enviar múltiplos arquivos, o nome de cada arquivo será usado como título individual. 
                    Este título base será usado apenas se você enviar um único arquivo.
                </p>
            </div>

            {{-- Descrição --}}
            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">
                    Descrição
                </label>
                <textarea name="descricao" 
                          id="descricao" 
                          rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Descreva brevemente o conteúdo do documento...">{{ old('descricao') }}</textarea>
            </div>

            {{-- Categorias --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Categorias
                </label>
                @if($categorias->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg text-sm">
                        Nenhuma categoria cadastrada. <a href="{{ route('admin.configuracoes.categorias-pops.create') }}" class="underline font-medium">Criar primeira categoria</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($categorias as $categoria)
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="checkbox" 
                                       name="categorias[]" 
                                       value="{{ $categoria->id }}"
                                       {{ in_array($categoria->id, old('categorias', [])) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-3 flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $categoria->cor }}"></span>
                                    <span class="text-sm font-medium text-gray-900">{{ $categoria->nome }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Selecione uma ou mais categorias para classificar este documento</p>
                @endif
            </div>

            {{-- Arquivos (Múltiplos) --}}
            <div x-data="{ files: [] }">
                <label for="arquivos" class="block text-sm font-medium text-gray-700 mb-1">
                    Arquivos <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                    <div class="space-y-1 text-center w-full">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="arquivos" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Selecione um ou mais arquivos</span>
                                <input id="arquivos" 
                                       name="arquivos[]" 
                                       type="file" 
                                       multiple
                                       required
                                       accept=".pdf,.doc,.docx,.txt"
                                       class="sr-only"
                                       @change="files = Array.from($event.target.files)">
                            </label>
                            <p class="pl-1">ou arraste e solte</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF, DOC, DOCX ou TXT até 10MB cada</p>
                    </div>
                </div>
                
                {{-- Preview dos arquivos selecionados --}}
                <div x-show="files.length > 0" class="mt-4 space-y-2">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-700">
                            <span x-text="files.length"></span> arquivo(s) selecionado(s)
                        </p>
                        <button type="button" 
                                @click="files = []; document.getElementById('arquivos').value = ''"
                                class="text-sm text-red-600 hover:text-red-800">
                            Limpar todos
                        </button>
                    </div>
                    <div class="max-h-48 overflow-y-auto space-y-2 bg-gray-50 rounded-lg p-3">
                        <template x-for="(file, index) in files" :key="index">
                            <div class="flex items-center justify-between p-2 bg-white rounded border border-gray-200">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                                        <p class="text-xs text-gray-500" x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                                    </div>
                                </div>
                                <button type="button" 
                                        @click="files.splice(index, 1); updateFileInput()"
                                        class="ml-2 text-red-600 hover:text-red-800 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Disponível para IA --}}
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="disponivel_ia" 
                               name="disponivel_ia" 
                               type="checkbox" 
                               value="1"
                               {{ old('disponivel_ia') ? 'checked' : '' }}
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    </div>
                    <div class="ml-3">
                        <label for="disponivel_ia" class="font-medium text-gray-900 text-sm">
                            Disponibilizar para Assistente IA
                        </label>
                        <p class="text-sm text-gray-600 mt-1">
                            Marque esta opção se deseja que o Assistente IA possa analisar e responder perguntas sobre o conteúdo deste documento. 
                            O conteúdo será extraído e indexado automaticamente.
                        </p>
                        <div class="mt-2 text-xs text-purple-700 bg-purple-100 px-3 py-2 rounded">
                            <strong>Nota:</strong> Documentos marcados para IA terão seu conteúdo extraído e armazenado para consulta automática pelo assistente.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.configuracoes.documentos-pops.index') }}" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Salvar Documento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
