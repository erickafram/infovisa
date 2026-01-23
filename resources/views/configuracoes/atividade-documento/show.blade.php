@extends('layouts.admin')

@section('title', 'Documentos da Atividade')
@section('page-title', 'Gerenciar Documentos da Atividade')

@section('content')
<div class="max-w-6xl mx-auto" x-data="atividadeDocumentoForm()">
    {{-- Voltar --}}
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.atividade-documento.index', ['tab' => 'atividades']) }}" 
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Atividades
        </a>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <p class="text-sm text-red-800">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Informações da Atividade --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-xl bg-cyan-100 text-cyan-600">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-900">{{ $atividade->nome }}</h2>
                <div class="flex items-center gap-3 mt-2">
                    @if($atividade->codigo_cnae)
                    <span class="px-3 py-1 text-sm font-mono bg-gray-100 text-gray-700 rounded-lg">
                        CNAE: {{ $atividade->codigo_cnae_formatado }}
                    </span>
                    @endif
                    @if($atividade->tipoServico)
                    <span class="px-3 py-1 text-sm bg-violet-100 text-violet-700 rounded-lg">
                        {{ $atividade->tipoServico->nome }}
                    </span>
                    @endif
                </div>
                @if($atividade->descricao)
                <p class="text-sm text-gray-600 mt-3">{{ $atividade->descricao }}</p>
                @endif
            </div>
            <div class="text-right">
                <span class="text-3xl font-bold text-cyan-600">{{ $atividade->documentosObrigatorios->count() }}</span>
                <p class="text-sm text-gray-500">documento(s)</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Documentos Vinculados --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase">Documentos Vinculados</h3>
                    <p class="text-xs text-gray-500 mt-1">Arraste para reordenar. Clique para editar.</p>
                </div>

                <form action="{{ route('admin.configuracoes.atividade-documento.update', $atividade) }}" method="POST" id="formDocumentos">
                    @csrf
                    @method('PUT')

                    <div class="p-4">
                        @if($atividade->documentosObrigatorios->isEmpty())
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-500">Nenhum documento vinculado a esta atividade.</p>
                            <p class="text-sm text-gray-400 mt-1">Use o painel ao lado para adicionar documentos.</p>
                        </div>
                        @else
                        <div class="space-y-3" id="listaDocumentos">
                            @foreach($atividade->documentosObrigatorios as $index => $doc)
                            <div class="documento-item border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow" 
                                 data-id="{{ $doc->id }}">
                                <input type="hidden" name="documentos[{{ $index }}][id]" value="{{ $doc->id }}">
                                
                                <div class="flex items-start gap-4">
                                    {{-- Handle para arrastar --}}
                                    <div class="cursor-move text-gray-400 hover:text-gray-600 mt-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-sm font-medium text-gray-900">{{ $doc->nome }}</span>
                                                <span class="text-xs text-gray-500 ml-2">({{ $doc->nomenclatura }})</span>
                                            </div>
                                            <button type="button" 
                                                    onclick="removerDocumento({{ $doc->id }})"
                                                    class="p-1 text-red-500 hover:bg-red-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="mt-3 flex items-center gap-4">
                                            <label class="flex items-center gap-2">
                                                <input type="radio" 
                                                       name="documentos[{{ $index }}][obrigatorio]" 
                                                       value="1" 
                                                       {{ $doc->pivot->obrigatorio ? 'checked' : '' }}
                                                       class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                                                <span class="text-sm text-gray-700">Obrigatório</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="radio" 
                                                       name="documentos[{{ $index }}][obrigatorio]" 
                                                       value="0" 
                                                       {{ !$doc->pivot->obrigatorio ? 'checked' : '' }}
                                                       class="w-4 h-4 text-yellow-600 border-gray-300 focus:ring-yellow-500">
                                                <span class="text-sm text-gray-700">Opcional</span>
                                            </label>
                                        </div>

                                        <div class="mt-3">
                                            <input type="text" 
                                                   name="documentos[{{ $index }}][observacao]" 
                                                   value="{{ $doc->pivot->observacao }}"
                                                   placeholder="Observação específica para esta atividade (opcional)"
                                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    @if($atividade->documentosObrigatorios->isNotEmpty())
                    <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                        <button type="submit" 
                                class="px-6 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 transition-colors">
                            Salvar Alterações
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Painel Lateral: Adicionar Documento --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-4">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase">Adicionar Documento</h3>
                </div>

                <form action="{{ route('admin.configuracoes.atividade-documento.adicionar', $atividade) }}" method="POST" class="p-4">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Documento</label>
                            <select name="tipo_documento_obrigatorio_id" required
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                                <option value="">Selecione...</option>
                                @foreach($documentosDisponiveis as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="obrigatorio" value="1" checked
                                           class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                                    <span class="text-sm text-gray-700">Obrigatório</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="obrigatorio" value="0"
                                           class="w-4 h-4 text-yellow-600 border-gray-300 focus:ring-yellow-500">
                                    <span class="text-sm text-gray-700">Opcional</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observação</label>
                            <input type="text" name="observacao" 
                                   placeholder="Observação específica (opcional)"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                        </div>

                        <button type="submit" 
                                class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                            Adicionar Documento
                        </button>
                    </div>
                </form>

                {{-- Copiar de outra atividade --}}
                <div class="p-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Copiar de outra atividade</h4>
                    <form action="{{ route('admin.configuracoes.atividade-documento.copiar', $atividade) }}" method="POST">
                        @csrf
                        <div class="space-y-3">
                            <select name="atividade_origem_id" required
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                                <option value="">Selecione a atividade...</option>
                                @foreach(\App\Models\Atividade::where('id', '!=', $atividade->id)->where('ativo', true)->withCount('documentosObrigatorios')->having('documentos_obrigatorios_count', '>', 0)->orderBy('nome')->get() as $outraAtividade)
                                <option value="{{ $outraAtividade->id }}">
                                    {{ $outraAtividade->nome }} ({{ $outraAtividade->documentos_obrigatorios_count }} docs)
                                </option>
                                @endforeach
                            </select>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="substituir" value="1"
                                       class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <span class="text-sm text-gray-700">Substituir documentos existentes</span>
                            </label>
                            <button type="submit" 
                                    class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                Copiar Documentos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function atividadeDocumentoForm() {
    return {
        init() {
            // Inicializar sortable se necessário
        }
    }
}

function removerDocumento(docId) {
    if (confirm('Remover este documento da atividade?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.configuracoes.atividade-documento.remover", [$atividade->id, ""]) }}/' + docId;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
