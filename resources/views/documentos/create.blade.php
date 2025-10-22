@extends('layouts.admin')

@section('title', 'Criar Novo Documento')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6" x-data="documentoEditor()">
    @if(isset($processo))
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium text-blue-900">
                    Processo: <strong>{{ $processo->numero_processo }}</strong> - {{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}
                </span>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.documentos.store') }}" @submit="handleSubmit">
        @csrf
        
        @if(isset($processo))
            <input type="hidden" name="processo_id" value="{{ $processo->id }}">
        @endif

        <!-- Card Principal -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <!-- Header -->
            <div class="flex items-center gap-2 mb-6">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h2 class="text-xl font-semibold text-gray-900">Criar Novo Documento</h2>
            </div>

            <!-- Tipo de Documento e Sigiloso -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Tipo de Documento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Documento
                    </label>
                    <select name="tipo_documento_id" 
                            x-model="tipoSelecionado"
                            @change="carregarModelos($event.target.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Selecione o tipo de documento</option>
                        @foreach($tiposDocumento as $tipo)
                            <option value="{{ $tipo->id }}">{{ strtoupper($tipo->nome) }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Selecione o tipo para carregar um modelo predefinido
                    </p>
                </div>

                <!-- Documento Sigiloso -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Documento Sigiloso
                    </label>
                    <select name="sigiloso" 
                            x-model="sigiloso"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>
            </div>

            <!-- Conteúdo do Documento -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Conteúdo do Documento
                </label>
                
                <!-- Toolbar do Editor -->
                <div class="border border-gray-300 rounded-t-lg bg-gray-50 p-2 flex items-center gap-1 flex-wrap">
                    <button type="button" onclick="document.execCommand('bold')" class="p-1.5 hover:bg-gray-200 rounded" title="Negrito">
                        <strong>B</strong>
                    </button>
                    <button type="button" onclick="document.execCommand('italic')" class="p-1.5 hover:bg-gray-200 rounded" title="Itálico">
                        <em>I</em>
                    </button>
                    <button type="button" onclick="document.execCommand('underline')" class="p-1.5 hover:bg-gray-200 rounded" title="Sublinhado">
                        <u>U</u>
                    </button>
                    <div class="w-px h-6 bg-gray-300 mx-1"></div>
                    <button type="button" onclick="document.execCommand('justifyLeft')" class="p-1.5 hover:bg-gray-200 rounded" title="Alinhar à esquerda">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm0 4h10v2H2V8zm0 4h16v2H2v-2zm0 4h10v2H2v-2z"/></svg>
                    </button>
                    <button type="button" onclick="document.execCommand('justifyCenter')" class="p-1.5 hover:bg-gray-200 rounded" title="Centralizar">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm3 4h10v2H5V8zm-3 4h16v2H2v-2zm3 4h10v2H5v-2z"/></svg>
                    </button>
                    <button type="button" onclick="document.execCommand('justifyRight')" class="p-1.5 hover:bg-gray-200 rounded" title="Alinhar à direita">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm6 4h10v2H8V8zm-6 4h16v2H2v-2zm6 4h10v2H8v-2z"/></svg>
                    </button>
                    <button type="button" onclick="document.execCommand('justifyFull')" class="p-1.5 hover:bg-gray-200 rounded" title="Justificar">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm0 4h16v2H2V8zm0 4h16v2H2v-2zm0 4h16v2H2v-2z"/></svg>
                    </button>
                    <div class="w-px h-6 bg-gray-300 mx-1"></div>
                    <button type="button" onclick="document.execCommand('insertUnorderedList')" class="p-1.5 hover:bg-gray-200 rounded" title="Lista">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h2v2H3V4zm4 0h10v2H7V4zM3 9h2v2H3V9zm4 0h10v2H7V9zm-4 5h2v2H3v-2zm4 0h10v2H7v-2z"/></svg>
                    </button>
                    <button type="button" onclick="document.execCommand('insertOrderedList')" class="p-1.5 hover:bg-gray-200 rounded" title="Lista numerada">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h1v3H3V4zm0 5h1v3H3V9zm0 5h1v3H3v-3zm4-9h10v2H7V5zm0 5h10v2H7v-2zm0 5h10v2H7v-2z"/></svg>
                    </button>
                    
                    <div class="ml-auto text-xs text-gray-500">
                        <span x-text="contarPalavras() + ' palavras'"></span>
                    </div>
                    
                    <button type="button" 
                            @click="previsualizar = !previsualizar"
                            class="ml-2 px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                        <span x-show="!previsualizar">👁️ Pré-visualizar</span>
                        <span x-show="previsualizar">✏️ Editar</span>
                    </button>
                </div>

                <!-- Editor -->
                <div x-show="!previsualizar">
                    <div id="editor" 
                         contenteditable="true"
                         @input="conteudo = $el.innerHTML"
                         class="min-h-[400px] max-h-[600px] overflow-y-auto p-4 border border-t-0 border-gray-300 rounded-b-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                         style="font-family: 'Times New Roman', serif; font-size: 14px; line-height: 1.6;">
                        <p>Selecione um tipo de documento para carregar o modelo ou digite o conteúdo do documento aqui...</p>
                    </div>
                    <textarea name="conteudo" x-model="conteudo" class="sr-only" required></textarea>
                </div>

                <!-- Pré-visualização -->
                <div x-show="previsualizar" 
                     class="min-h-[400px] p-4 border border-t-0 border-gray-300 rounded-b-lg bg-white"
                     style="font-family: 'Times New Roman', serif; font-size: 14px; line-height: 1.6;"
                     x-html="conteudo"></div>

                <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Pré-visualizar documento
                </p>
            </div>

            <!-- Definir Assinaturas Digitais -->
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900">Definir Assinaturas Digitais</h3>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full font-medium">Obrigatório</span>
                </div>

                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg mb-3">
                    <p class="text-sm text-blue-900 flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Selecione os usuários que devem assinar digitalmente este documento.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-2">
                    @foreach($usuariosInternos as $usuario)
                        <label class="flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" 
                                   name="assinaturas[]" 
                                   value="{{ $usuario->id }}"
                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <div class="ml-3 flex-1">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $usuario->nome }}
                                    @if($usuario->id == auth('interno')->id())
                                        <span class="text-xs text-blue-600 font-normal">(Você)</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $usuario->cpf }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="flex gap-3">
                    @if(isset($processo))
                        <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            ← Voltar
                        </a>
                    @else
                        <a href="{{ route('admin.documentos.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            ← Voltar
                        </a>
                    @endif

                    <button type="button" 
                            @click="previsualizar = !previsualizar"
                            class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                        👁️ Pré-visualizar
                    </button>
                </div>

                <div class="flex gap-3">
                    <button type="submit" 
                            name="acao" 
                            value="rascunho"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        💾 Salvar como Rascunho
                    </button>
                    
                    <button type="submit" 
                            name="acao" 
                            value="finalizar"
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        ✅ Finalizar Documento
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function documentoEditor() {
    return {
        tipoSelecionado: null,
        sigiloso: false,
        conteudo: '<p>Selecione um tipo de documento para carregar o modelo ou digite o conteúdo do documento aqui...</p>',
        modelos: [],
        previsualizar: false,

        async carregarModelos(tipoId) {
            if (!tipoId) return;
            
            try {
                const response = await fetch(`/admin/documentos/modelos/${tipoId}`);
                this.modelos = await response.json();
                
                // Se houver modelos, carrega o primeiro automaticamente
                if (this.modelos.length > 0) {
                    this.conteudo = this.modelos[0].conteudo;
                    document.getElementById('editor').innerHTML = this.conteudo;
                }
            } catch (error) {
                console.error('Erro ao carregar modelos:', error);
            }
        },

        contarPalavras() {
            const texto = this.conteudo.replace(/<[^>]*>/g, '').trim();
            return texto.split(/\s+/).filter(word => word.length > 0).length;
        },

        handleSubmit(event) {
            const assinaturas = document.querySelectorAll('input[name="assinaturas[]"]:checked');
            if (assinaturas.length === 0) {
                event.preventDefault();
                alert('Selecione pelo menos um usuário para assinar o documento!');
                return false;
            }
        }
    }
}
</script>
@endsection
