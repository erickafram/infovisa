@extends('layouts.admin')

@section('title', 'Editar Documento de Ajuda')
@section('page-title', 'Editar Documento de Ajuda')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Cabeçalho --}}
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.documentos-ajuda.index') }}" class="text-sm text-blue-600 hover:text-blue-700 flex items-center mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Documentos de Ajuda
        </a>
        <p class="text-gray-600 text-sm">Edite as informações do documento de ajuda</p>
    </div>

    {{-- Formulário --}}
    <form action="{{ route('admin.configuracoes.documentos-ajuda.update', $documento->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            {{-- Título --}}
            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">
                    Título <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="titulo" 
                       id="titulo" 
                       value="{{ old('titulo', $documento->titulo) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('titulo') border-red-500 @enderror"
                       placeholder="Ex: Manual do Sistema, Guia de Pagamento DARE..."
                       required>
                @error('titulo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Descrição --}}
            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">
                    Descrição
                </label>
                <textarea name="descricao" 
                          id="descricao" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror"
                          placeholder="Breve descrição do documento...">{{ old('descricao', $documento->descricao) }}</textarea>
                @error('descricao')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Escopo e Município --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="escopo_competencia" class="block text-sm font-medium text-gray-700 mb-1">
                        Exibir para <span class="text-red-500">*</span>
                    </label>
                    <select name="escopo_competencia"
                            id="escopo_competencia"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('escopo_competencia') border-red-500 @enderror"
                            required>
                        <option value="todos" @selected(old('escopo_competencia', $documento->escopo_competencia) === 'todos')>Todos os processos vinculados</option>
                        <option value="estadual" @selected(old('escopo_competencia', $documento->escopo_competencia) === 'estadual')>Somente processos de competência estadual</option>
                        <option value="municipal" @selected(old('escopo_competencia', $documento->escopo_competencia) === 'municipal')>Somente processos de competência municipal</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Use municipal quando o documento deve aparecer apenas para processos municipais.</p>
                    @error('escopo_competencia')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="municipio-field-wrapper" class="{{ old('escopo_competencia', $documento->escopo_competencia) === 'municipal' ? '' : 'hidden' }}">
                    <label for="municipio_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Município <span class="text-red-500">*</span>
                    </label>
                    <select name="municipio_id"
                            id="municipio_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('municipio_id') border-red-500 @enderror">
                        <option value="">Selecione o município</option>
                        @foreach($municipios as $municipio)
                        <option value="{{ $municipio->id }}" @selected((string) old('municipio_id', $documento->municipio_id) === (string) $municipio->id)>{{ $municipio->nome }}/{{ $municipio->uf }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Exemplo: um documento municipal de Dianópolis só aparecerá para processos municipais de Dianópolis.</p>
                    @error('municipio_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Arquivo Atual --}}
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo Atual</label>
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $documento->nome_original }}</p>
                        <p class="text-xs text-gray-500">{{ $documento->tamanho_formatado }}</p>
                    </div>
                    <a href="{{ route('admin.configuracoes.documentos-ajuda.visualizar', $documento->id) }}" 
                       target="_blank"
                       class="px-3 py-1.5 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                        Visualizar
                    </a>
                </div>
            </div>

            {{-- Novo Arquivo PDF (opcional) --}}
            <div>
                <label for="arquivo" class="block text-sm font-medium text-gray-700 mb-1">
                    Substituir Arquivo (opcional)
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors @error('arquivo') border-red-500 @enderror">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="arquivo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                <span>Selecione um novo arquivo</span>
                                <input id="arquivo" name="arquivo" type="file" class="sr-only" accept=".pdf">
                            </label>
                            <p class="pl-1">ou arraste e solte</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF até 10MB (deixe vazio para manter o atual)</p>
                    </div>
                </div>
                <p id="arquivo-nome" class="mt-2 text-sm text-gray-600 hidden"></p>
                @error('arquivo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tipos de Processo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tipos de Processo <span class="text-red-500">*</span>
                </label>
                <p class="text-xs text-gray-500 mb-3">Selecione em quais tipos de processo este documento será exibido</p>
                
                @php
                    $tiposSelecionados = old('tipos_processo', $documento->tiposProcesso->pluck('id')->toArray());
                @endphp
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto p-3 border border-gray-200 rounded-lg bg-gray-50">
                    @foreach($tiposProcesso as $tipo)
                    <label class="flex items-center gap-2 p-2 bg-white rounded border border-gray-200 hover:border-blue-300 cursor-pointer transition-colors">
                        <input type="checkbox" 
                               name="tipos_processo[]" 
                               value="{{ $tipo->id }}"
                               {{ in_array($tipo->id, $tiposSelecionados) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700">{{ $tipo->nome }}</span>
                    </label>
                    @endforeach
                </div>
                @error('tipos_processo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Ordem e Status --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="ordem" class="block text-sm font-medium text-gray-700 mb-1">
                        Ordem de Exibição
                    </label>
                    <input type="number" 
                           name="ordem" 
                           id="ordem" 
                           value="{{ old('ordem', $documento->ordem) }}"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Status
                    </label>
                    <label class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer">
                        <input type="checkbox" 
                               name="ativo" 
                               value="1"
                               {{ old('ativo', $documento->ativo) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Documento ativo</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.configuracoes.documentos-ajuda.index') }}" 
               class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Mostra o nome do arquivo selecionado
    document.getElementById('arquivo').addEventListener('change', function(e) {
        const nomeEl = document.getElementById('arquivo-nome');
        if (this.files.length > 0) {
            nomeEl.textContent = '📄 Novo arquivo: ' + this.files[0].name;
            nomeEl.classList.remove('hidden');
        } else {
            nomeEl.classList.add('hidden');
        }
    });

    const escopoCompetenciaSelect = document.getElementById('escopo_competencia');
    const municipioFieldWrapper = document.getElementById('municipio-field-wrapper');
    const municipioSelect = document.getElementById('municipio_id');

    function atualizarCampoMunicipioDocumentoAjuda() {
        const exibirMunicipio = escopoCompetenciaSelect.value === 'municipal';

        municipioFieldWrapper.classList.toggle('hidden', !exibirMunicipio);
        municipioSelect.required = exibirMunicipio;

        if (!exibirMunicipio) {
            municipioSelect.value = '';
        }
    }

    escopoCompetenciaSelect.addEventListener('change', atualizarCampoMunicipioDocumentoAjuda);
    atualizarCampoMunicipioDocumentoAjuda();
</script>
@endpush
@endsection
