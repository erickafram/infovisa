@extends('layouts.admin')

@section('title', 'Editar Rascunho')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="documentoEditor()">
    <div class="max-w-8xl mx-auto px-4 py-8">
        {{-- Header com Breadcrumb --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                @if(isset($processo))
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}" class="hover:text-blue-600 transition">
                        Processo {{ $processo->numero_processo }}
                    </a>
                @else
                    <a href="{{ route('admin.documentos.index') }}" class="hover:text-blue-600 transition">
                        Documentos
                    </a>
                @endif
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium">Editar Rascunho</span>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                Editar Rascunho
            </h1>
            
            @if(isset($processo))
                <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-medium">{{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}</span>
                </div>
            @endif
        </div>

    <form method="POST" action="{{ route('admin.documentos.update', $documento->id) }}" @submit="handleSubmit">
        @csrf
        @method('PUT')
        
        {{-- Campo hidden para o conteúdo do editor --}}
        <input type="hidden" name="conteudo" x-model="conteudo">

        {{-- Seção: Tipo de Documento --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-white border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <span class="flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs font-bold">1</span>
                    Tipo de Documento
                </h2>
            </div>
            <div class="p-6">
                <select name="tipo_documento_id" 
                        x-model="tipoSelecionado"
                        @change="carregarModelos($event.target.value)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base"
                        required>
                    <option value="">Selecione o tipo de documento</option>
                    @foreach($tiposDocumento as $tipo)
                        <option value="{{ $tipo->id }}" {{ $documento->tipo_documento_id == $tipo->id ? 'selected' : '' }}>{{ $tipo->nome }}</option>
                    @endforeach
                </select>
                <p class="text-sm text-gray-500 mt-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ao selecionar um tipo, o modelo predefinido será carregado automaticamente
                </p>
            </div>
        </div>

        {{-- Seção: Editor de Conteúdo --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-white border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <span class="flex items-center justify-center w-6 h-6 bg-green-600 text-white rounded-full text-xs font-bold">2</span>
                    Conteúdo do Documento
                </h2>
            </div>
            <div class="p-6">
                
                <!-- Toolbar do Editor -->
                <div class="border border-gray-300 rounded-t-lg bg-gray-50 p-2 space-y-2">
                    {{-- Primeira linha: Formatação básica --}}
                    <div class="flex items-center gap-1 flex-wrap">
                        {{-- Tamanho da fonte --}}
                        <select onchange="document.execCommand('fontSize', false, this.value)" class="text-xs px-2 py-1 border border-gray-300 rounded hover:bg-gray-100" title="Tamanho">
                            <option value="">Tamanho</option>
                            <option value="1">Muito pequeno</option>
                            <option value="2">Pequeno</option>
                            <option value="3">Normal</option>
                            <option value="4">Médio</option>
                            <option value="5">Grande</option>
                            <option value="6">Muito grande</option>
                            <option value="7">Enorme</option>
                        </select>

                        {{-- Fonte --}}
                        <select onchange="document.execCommand('fontName', false, this.value)" class="text-xs px-2 py-1 border border-gray-300 rounded hover:bg-gray-100" title="Fonte">
                            <option value="">Fonte</option>
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Tahoma">Tahoma</option>
                        </select>

                        <div class="w-px h-6 bg-gray-300 mx-1"></div>

                        <button type="button" onclick="document.execCommand('bold')" class="p-1.5 hover:bg-gray-200 rounded font-bold" title="Negrito">
                            B
                        </button>
                        <button type="button" onclick="document.execCommand('italic')" class="p-1.5 hover:bg-gray-200 rounded italic" title="Itálico">
                            I
                        </button>
                        <button type="button" onclick="document.execCommand('underline')" class="p-1.5 hover:bg-gray-200 rounded underline" title="Sublinhado">
                            U
                        </button>
                        <button type="button" onclick="document.execCommand('strikeThrough')" class="p-1.5 hover:bg-gray-200 rounded line-through" title="Tachado">
                            S
                        </button>

                        <div class="w-px h-6 bg-gray-300 mx-1"></div>

                        {{-- Cor do texto --}}
                        <div class="relative" x-data="{ showColorPicker: false }">
                            <button type="button" @click="showColorPicker = !showColorPicker" class="p-1.5 hover:bg-gray-200 rounded flex items-center gap-1" title="Cor do texto">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                </svg>
                                <span class="text-xs">A</span>
                            </button>
                            <div x-show="showColorPicker" @click.away="showColorPicker = false" class="absolute z-10 mt-1 p-2 bg-white border border-gray-300 rounded-lg shadow-lg grid grid-cols-8 gap-1" style="display: none;">
                                <button type="button" onclick="document.execCommand('foreColor', false, '#000000')" class="w-6 h-6 rounded border border-gray-300" style="background: #000000" title="Preto"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#FF0000')" class="w-6 h-6 rounded border border-gray-300" style="background: #FF0000" title="Vermelho"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#00FF00')" class="w-6 h-6 rounded border border-gray-300" style="background: #00FF00" title="Verde"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#0000FF')" class="w-6 h-6 rounded border border-gray-300" style="background: #0000FF" title="Azul"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#FFFF00')" class="w-6 h-6 rounded border border-gray-300" style="background: #FFFF00" title="Amarelo"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#FF00FF')" class="w-6 h-6 rounded border border-gray-300" style="background: #FF00FF" title="Magenta"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#00FFFF')" class="w-6 h-6 rounded border border-gray-300" style="background: #00FFFF" title="Ciano"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#FFFFFF')" class="w-6 h-6 rounded border border-gray-300" style="background: #FFFFFF" title="Branco"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#808080')" class="w-6 h-6 rounded border border-gray-300" style="background: #808080" title="Cinza"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#800000')" class="w-6 h-6 rounded border border-gray-300" style="background: #800000" title="Marrom"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#008000')" class="w-6 h-6 rounded border border-gray-300" style="background: #008000" title="Verde escuro"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#000080')" class="w-6 h-6 rounded border border-gray-300" style="background: #000080" title="Azul escuro"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#FFA500')" class="w-6 h-6 rounded border border-gray-300" style="background: #FFA500" title="Laranja"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#800080')" class="w-6 h-6 rounded border border-gray-300" style="background: #800080" title="Roxo"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#FFC0CB')" class="w-6 h-6 rounded border border-gray-300" style="background: #FFC0CB" title="Rosa"></button>
                                <button type="button" onclick="document.execCommand('foreColor', false, '#A52A2A')" class="w-6 h-6 rounded border border-gray-300" style="background: #A52A2A" title="Marrom escuro"></button>
                            </div>
                        </div>

                        {{-- Cor de fundo --}}
                        <div class="relative" x-data="{ showBgPicker: false }">
                            <button type="button" @click="showBgPicker = !showBgPicker" class="p-1.5 hover:bg-gray-200 rounded" title="Cor de fundo">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            <div x-show="showBgPicker" @click.away="showBgPicker = false" class="absolute z-10 mt-1 p-2 bg-white border border-gray-300 rounded-lg shadow-lg grid grid-cols-8 gap-1" style="display: none;">
                                <button type="button" onclick="document.execCommand('backColor', false, 'transparent')" class="w-6 h-6 rounded border border-gray-300 bg-white" title="Sem cor"></button>
                                <button type="button" onclick="document.execCommand('backColor', false, '#FFFF00')" class="w-6 h-6 rounded border border-gray-300" style="background: #FFFF00" title="Amarelo"></button>
                                <button type="button" onclick="document.execCommand('backColor', false, '#00FF00')" class="w-6 h-6 rounded border border-gray-300" style="background: #00FF00" title="Verde"></button>
                                <button type="button" onclick="document.execCommand('backColor', false, '#00FFFF')" class="w-6 h-6 rounded border border-gray-300" style="background: #00FFFF" title="Ciano"></button>
                                <button type="button" onclick="document.execCommand('backColor', false, '#FF00FF')" class="w-6 h-6 rounded border border-gray-300" style="background: #FF00FF" title="Magenta"></button>
                                <button type="button" onclick="document.execCommand('backColor', false, '#FFA500')" class="w-6 h-6 rounded border border-gray-300" style="background: #FFA500" title="Laranja"></button>
                                <button type="button" onclick="document.execCommand('backColor', false, '#FFC0CB')" class="w-6 h-6 rounded border border-gray-300" style="background: #FFC0CB" title="Rosa"></button>
                                <button type="button" onclick="document.execCommand('backColor', false, '#E0E0E0')" class="w-6 h-6 rounded border border-gray-300" style="background: #E0E0E0" title="Cinza claro"></button>
                            </div>
                        </div>
                    </div>

                    {{-- Segunda linha: Alinhamento e listas --}}
                    <div class="flex items-center gap-1 flex-wrap">
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

                        <div class="w-px h-6 bg-gray-300 mx-1"></div>

                        <button type="button" onclick="document.execCommand('indent')" class="p-1.5 hover:bg-gray-200 rounded" title="Aumentar recuo">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h14v2H3V4zm0 4h14v2H3V8zm0 4h14v2H3v-2zm0 4h14v2H3v-2zM1 8l3 3-3 3V8z"/></svg>
                        </button>
                        <button type="button" onclick="document.execCommand('outdent')" class="p-1.5 hover:bg-gray-200 rounded" title="Diminuir recuo">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h14v2H3V4zm0 4h14v2H3V8zm0 4h14v2H3v-2zm0 4h14v2H3v-2zM7 8L4 11l3 3V8z"/></svg>
                        </button>

                        <div class="w-px h-6 bg-gray-300 mx-1"></div>

                        {{-- Botão Inserir Imagem --}}
                        <button type="button" @click="$refs.imageInput.click()" class="p-1.5 hover:bg-gray-200 rounded" title="Inserir Imagem">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <input type="file" x-ref="imageInput" @change="inserirImagem($event)" accept="image/*" class="hidden">

                        <button type="button" onclick="document.execCommand('createLink', false, prompt('Digite a URL:'))" class="p-1.5 hover:bg-gray-200 rounded" title="Inserir link">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <button type="button" onclick="document.execCommand('unlink')" class="p-1.5 hover:bg-gray-200 rounded" title="Remover link">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 6.707 6.293a1 1 0 00-1.414 1.414L8.586 11l-3.293 3.293a1 1 0 101.414 1.414L10 12.414l3.293 3.293a1 1 0 001.414-1.414L11.414 11l3.293-3.293z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div class="w-px h-6 bg-gray-300 mx-1"></div>

                        <button type="button" onclick="document.execCommand('removeFormat')" class="p-1.5 hover:bg-gray-200 rounded text-xs" title="Limpar formatação">
                            🧹
                        </button>
                    
                    <div class="ml-auto flex items-center gap-3">
                        <span class="text-xs text-gray-500" x-text="contarPalavras() + ' palavras'"></span>
                        <span x-show="salvandoAuto" class="text-xs text-green-600 flex items-center gap-1">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Salvando...
                        </span>
                        <span x-show="!salvandoAuto && ultimoSalvo" class="text-xs text-gray-500">
                            Salvo <span x-text="ultimoSalvo"></span>
                        </span>
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
                         @input="conteudo = $el.innerHTML; salvarAutomaticamente()"
                         @paste="handlePaste($event)"
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

            </div>
        </div>

        {{-- Seção: Assinaturas Digitais --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 bg-purple-600 text-white rounded-full text-xs font-bold">3</span>
                        Assinaturas Digitais
                    </h2>
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Obrigatório</span>
                </div>
            </div>
            <div class="p-6">
                <div class="mb-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                    <p class="text-sm text-purple-900 flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>Selecione pelo menos um usuário interno para assinar digitalmente este documento.</span>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto p-1">
                    @foreach($usuariosInternos as $usuario)
                        <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-purple-300 hover:bg-purple-50 transition-all group">
                            <input type="checkbox" 
                                   name="assinaturas[]" 
                                   value="{{ $usuario->id }}"
                                   {{ $documento->assinaturas->contains('usuario_interno_id', $usuario->id) ? 'checked' : '' }}
                                   class="mt-1 h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <div class="ml-3 flex-1">
                                <div class="text-sm font-semibold text-gray-900 group-hover:text-purple-900">
                                    {{ $usuario->nome }}
                                    @if($usuario->id == auth('interno')->id())
                                        <span class="ml-1 px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full font-medium">Você</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">CPF: {{ $usuario->cpf }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

        {{-- Botões de Ação --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex gap-3 w-full sm:w-auto">
                    @if(isset($processo))
                        <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}" 
                           class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Voltar
                        </a>
                    @else
                        <a href="{{ route('admin.documentos.index') }}" 
                           class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Voltar
                        </a>
                    @endif

                    <button type="button" 
                            @click="previsualizar = !previsualizar"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-blue-700 bg-blue-50 border-2 border-blue-200 rounded-lg hover:bg-blue-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span x-text="previsualizar ? 'Editar' : 'Pré-visualizar'"></span>
                    </button>
                </div>

                <div class="flex gap-3 w-full sm:w-auto">
                    <button type="submit" 
                            name="acao" 
                            value="rascunho"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Salvar Rascunho
                    </button>
                    
                    <button type="submit" 
                            name="acao" 
                            value="finalizar"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Finalizar Documento
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
        conteudo: '',
        modelos: [],
        previsualizar: false,
        salvandoAuto: false,
        ultimoSalvo: '',
        timeoutSalvar: null,
        chaveLocalStorage: 'documento_rascunho_{{ request()->get("processo_id", "novo") }}',

        init() {
            // Carrega conteúdo do documento
            this.tipoSelecionado = {{ $documento->tipo_documento_id ?? 'null' }};
            this.conteudo = {!! json_encode($documento->conteudo) !!};
            document.getElementById('editor').innerHTML = this.conteudo;
        },

        salvarAutomaticamente() {
            clearTimeout(this.timeoutSalvar);
            this.salvandoAuto = true;
            
            this.timeoutSalvar = setTimeout(() => {
                const dados = {
                    conteudo: this.conteudo,
                    timestamp: Date.now()
                };
                localStorage.setItem(this.chaveLocalStorage, JSON.stringify(dados));
                this.salvandoAuto = false;
                this.ultimoSalvo = 'agora';
            }, 1000);
        },

        tempoDecorrido(timestamp) {
            const segundos = Math.floor((Date.now() - timestamp) / 1000);
            if (segundos < 60) return 'agora';
            const minutos = Math.floor(segundos / 60);
            if (minutos < 60) return minutos + ' min';
            const horas = Math.floor(minutos / 60);
            return horas + ' h';
        },

        inserirImagem(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            if (!file.type.startsWith('image/')) {
                alert('Por favor, selecione apenas arquivos de imagem.');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = `<img src="${e.target.result}" style="max-width: 100%; height: auto; margin: 10px 0;" />`;
                document.execCommand('insertHTML', false, img);
                this.conteudo = document.getElementById('editor').innerHTML;
                this.salvarAutomaticamente();
            };
            reader.readAsDataURL(file);
            
            // Limpa o input para permitir selecionar a mesma imagem novamente
            event.target.value = '';
        },

        handlePaste(event) {
            // Permite colar imagens
            const items = event.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    event.preventDefault();
                    const blob = items[i].getAsFile();
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = `<img src="${e.target.result}" style="max-width: 100%; height: auto; margin: 10px 0;" />`;
                        document.execCommand('insertHTML', false, img);
                        this.conteudo = document.getElementById('editor').innerHTML;
                        this.salvarAutomaticamente();
                    };
                    reader.readAsDataURL(blob);
                    break;
                }
            }
        },

        async carregarModelos(tipoId) {
            if (!tipoId) return;
            
            try {
                const response = await fetch(`/admin/documentos/modelos/${tipoId}`);
                this.modelos = await response.json();
                
                // Se houver modelos, carrega o primeiro automaticamente e preenche o editor
                if (this.modelos.length > 0) {
                    this.conteudo = this.modelos[0].conteudo;
                    document.getElementById('editor').innerHTML = this.conteudo;
                    this.salvarAutomaticamente();
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
            
            // Limpa o localStorage após enviar
            const acao = event.submitter.value;
            if (acao === 'finalizar') {
                localStorage.removeItem(this.chaveLocalStorage);
            }
        }
    }
}
</script>
@endsection
