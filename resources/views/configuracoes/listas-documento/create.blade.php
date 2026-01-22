@extends('layouts.admin')

@section('title', 'Nova Lista de Documentos')
@section('page-title', 'Nova Lista de Documentos')

@section('content')
<div class="max-w-8xl mx-auto" x-data="listaDocumentoForm()">
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.listas-documento.index') }}" 
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <ul class="list-disc list-inside text-sm text-red-800">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.configuracoes.listas-documento.store') }}" method="POST">
        @csrf

        {{-- Informações Básicas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4">Informações Básicas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="tipo_processo_id" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Processo *</label>
                    <select name="tipo_processo_id" id="tipo_processo_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione o tipo de processo...</option>
                        @foreach($tiposProcesso as $tp)
                        <option value="{{ $tp->id }}" {{ old('tipo_processo_id') == $tp->id ? 'selected' : '' }}>
                            {{ $tp->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome da Lista *</label>
                    <input type="text" name="nome" id="nome" value="{{ old('nome') }}" required
                           placeholder="Ex: Documentos para Restaurantes - Estado"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="2"
                              placeholder="Descreva o propósito desta lista..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('descricao') }}</textarea>
                </div>

                <div>
                    <label for="escopo" class="block text-sm font-medium text-gray-700 mb-1">Escopo *</label>
                    <select name="escopo" id="escopo" x-model="escopo" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="estadual">Estadual</option>
                        <option value="municipal">Municipal</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Estadual: aplica-se a todos. Municipal: apenas para o município selecionado.</p>
                </div>

                <div x-show="escopo === 'municipal'" x-transition>
                    <label for="municipio_id" class="block text-sm font-medium text-gray-700 mb-1">Município *</label>
                    <select name="municipio_id" id="municipio_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione o município...</option>
                        @foreach($municipios as $municipio)
                        <option value="{{ $municipio->id }}" {{ old('municipio_id') == $municipio->id ? 'selected' : '' }}>
                            {{ $municipio->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="ativo" id="ativo" value="1" {{ old('ativo', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="ativo" class="text-sm text-gray-700">Lista Ativa</label>
                </div>
            </div>
        </div>

        {{-- Atividades --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4">Atividades Vinculadas *</h3>
            <p class="text-xs text-gray-500 mb-4">Selecione as atividades que exigirão esta lista de documentos</p>
            
            @if($tiposServico->isEmpty())
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">Nenhum tipo de serviço cadastrado. <a href="{{ route('admin.configuracoes.tipos-servico.create') }}" class="underline">Cadastre primeiro</a>.</p>
            </div>
            @else
            <div class="space-y-4">
                @foreach($tiposServico as $tipoServico)
                @if($tipoServico->atividadesAtivas->isNotEmpty())
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        {{ $tipoServico->nome }}
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($tipoServico->atividadesAtivas as $atividade)
                        <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="atividades[]" value="{{ $atividade->id }}"
                                   {{ in_array($atividade->id, old('atividades', [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm text-gray-700">{{ $atividade->nome }}</span>
                            @if($atividade->codigo_cnae)
                            <span class="text-xs text-gray-400 font-mono">({{ $atividade->codigo_cnae }})</span>
                            @endif
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
        </div>

        {{-- Documentos Obrigatórios --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4">Documentos Exigidos *</h3>
            <p class="text-xs text-gray-500 mb-4">Selecione os documentos que serão exigidos e defina se são obrigatórios ou opcionais</p>
            <p class="text-xs text-blue-600 mb-4">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Documentos comuns (aplicados automaticamente a todos os serviços) não aparecem nesta lista.
            </p>
            
            @if($tiposDocumento->isEmpty())
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">Nenhum tipo de documento específico cadastrado. <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-documento']) }}" class="underline">Cadastre primeiro</a>.</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($tiposDocumento as $doc)
                <div class="border border-gray-200 rounded-lg p-4" x-data="{ selecionado: {{ in_array($doc->id, old('documentos_selecionados', [])) ? 'true' : 'false' }} }">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" 
                               x-model="selecionado"
                               name="documentos_selecionados[]"
                               value="{{ $doc->id }}"
                               {{ in_array($doc->id, old('documentos_selecionados', [])) ? 'checked' : '' }}
                               @change="if(!selecionado) { $refs.obrigatorio_{{ $doc->id }}.checked = true; $refs.observacao_{{ $doc->id }}.value = ''; }"
                               class="w-4 h-4 mt-1 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">{{ $doc->nome }}</span>
                                <div class="flex items-center gap-4" x-show="selecionado">
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="documento_{{ $doc->id }}_obrigatorio" value="1" checked
                                               x-ref="obrigatorio_{{ $doc->id }}"
                                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <span class="text-xs text-gray-600">Obrigatório</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="documento_{{ $doc->id }}_obrigatorio" value="0"
                                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <span class="text-xs text-gray-600">Opcional</span>
                                    </label>
                                </div>
                            </div>
                            @if($doc->descricao)
                            <p class="text-xs text-gray-500 mt-1">{{ $doc->descricao }}</p>
                            @endif
                            <div x-show="selecionado" x-transition class="mt-2">
                                <input type="text" name="documento_{{ $doc->id }}_observacao" 
                                       x-ref="observacao_{{ $doc->id }}"
                                       value="{{ old('documento_'.$doc->id.'_observacao') }}"
                                       placeholder="Observação específica para este documento (opcional)"
                                       class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.configuracoes.listas-documento.index') }}" 
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                Criar Lista
            </button>
        </div>
    </form>
</div>

<script>
function listaDocumentoForm() {
    return {
        escopo: '{{ old('escopo', 'estadual') }}',
        
        // Debug function to check form data before submit
        debugForm() {
            const form = document.querySelector('form');
            const formData = new FormData(form);
            
            console.log('=== FORM DEBUG ===');
            console.log('Documentos selecionados:', formData.getAll('documentos_selecionados[]'));
            console.log('Atividades:', formData.getAll('atividades[]'));
            console.log('Todos os dados:');
            for (let [key, value] of formData.entries()) {
                console.log(key, ':', value);
            }
            console.log('==================');
        }
    }
}

// Add form submit debugging
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const formData = new FormData(form);
            const documentos = formData.getAll('documentos_selecionados[]');
            const atividades = formData.getAll('atividades[]');
            
            console.log('Form submitting with:');
            console.log('Documentos:', documentos);
            console.log('Atividades:', atividades);
            
            if (documentos.length === 0) {
                alert('ERRO: Nenhum documento selecionado!');
                e.preventDefault();
                return false;
            }
            
            if (atividades.length === 0) {
                alert('ERRO: Nenhuma atividade selecionada!');
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>
@endsection
