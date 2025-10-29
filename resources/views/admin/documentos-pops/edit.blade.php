@extends('layouts.admin')

@section('title', 'Editar Documento POP')
@section('page-title', 'Editar Documento POP')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">Editar Documento POP</h2>
            <p class="mt-1 text-sm text-gray-600">Atualize as informações do documento</p>
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

        <form action="{{ route('admin.configuracoes.documentos-pops.update', $documentoPop) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Título --}}
            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                    Título <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="titulo" 
                       id="titulo" 
                       value="{{ old('titulo', $documentoPop->titulo) }}"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Ex: Procedimento para Inspeção Sanitária">
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
                          placeholder="Descreva brevemente o conteúdo do documento...">{{ old('descricao', $documentoPop->descricao) }}</textarea>
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
                                       {{ in_array($categoria->id, old('categorias', $categoriasSelecionadas)) ? 'checked' : '' }}
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

            {{-- Arquivo Atual --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo Atual</label>
                <div class="flex items-center gap-3">
                    @if($documentoPop->extensao === 'pdf')
                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">{{ $documentoPop->arquivo_nome }}</div>
                        <div class="text-xs text-gray-500">{{ $documentoPop->tamanho_formatado }}</div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.configuracoes.documentos-pops.visualizar', $documentoPop) }}" 
                           target="_blank"
                           class="text-sm text-blue-600 hover:text-blue-900">
                            Visualizar
                        </a>
                        <a href="{{ route('admin.configuracoes.documentos-pops.download', $documentoPop) }}" 
                           class="text-sm text-indigo-600 hover:text-indigo-900">
                            Download
                        </a>
                    </div>
                </div>
            </div>

            {{-- Substituir Arquivo --}}
            <div>
                <label for="arquivo" class="block text-sm font-medium text-gray-700 mb-1">
                    Substituir Arquivo (opcional)
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="arquivo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Selecione um arquivo</span>
                                <input id="arquivo" 
                                       name="arquivo" 
                                       type="file" 
                                       accept=".pdf,.doc,.docx,.txt"
                                       class="sr-only"
                                       onchange="updateFileName(this)">
                            </label>
                            <p class="pl-1">ou arraste e solte</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF, DOC, DOCX ou TXT até 10MB</p>
                        <p id="file-name" class="text-sm text-gray-700 font-medium mt-2"></p>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">Deixe em branco para manter o arquivo atual</p>
            </div>

            {{-- Disponível para IA --}}
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="disponivel_ia" 
                               name="disponivel_ia" 
                               type="checkbox" 
                               value="1"
                               {{ old('disponivel_ia', $documentoPop->disponivel_ia) ? 'checked' : '' }}
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    </div>
                    <div class="ml-3">
                        <label for="disponivel_ia" class="font-medium text-gray-900 text-sm">
                            Disponibilizar para Assistente IA
                        </label>
                        <p class="text-sm text-gray-600 mt-1">
                            Marque esta opção se deseja que o Assistente IA possa analisar e responder perguntas sobre o conteúdo deste documento.
                        </p>
                        @if($documentoPop->disponivel_ia && $documentoPop->isIndexado())
                            <div class="mt-2 text-xs text-green-700 bg-green-100 px-3 py-2 rounded flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Status:</strong> Documento indexado em {{ $documentoPop->indexado_em->format('d/m/Y H:i') }}</span>
                            </div>
                        @elseif($documentoPop->disponivel_ia && !$documentoPop->isIndexado())
                            <div class="mt-2 text-xs text-yellow-700 bg-yellow-100 px-3 py-2 rounded">
                                <strong>Atenção:</strong> Documento marcado para IA mas não indexado. Será indexado ao salvar.
                            </div>
                        @endif
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
                    Atualizar Documento
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileName = input.files[0]?.name;
    const fileNameElement = document.getElementById('file-name');
    if (fileName) {
        fileNameElement.textContent = '📄 ' + fileName;
    } else {
        fileNameElement.textContent = '';
    }
}
</script>
@endsection
