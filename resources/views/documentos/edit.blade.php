@extends('layouts.admin')

@section('title', 'Editar Rascunho')

@section('content')
{{-- Script de Edição Colaborativa --}}
<script src="{{ asset('js/edicao-colaborativa.js') }}"></script>

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
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3">
            <div class="px-3 py-2 bg-gradient-to-r from-blue-50 to-white border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <span class="flex items-center justify-center w-4 h-4 bg-blue-600 text-white rounded-full text-xs font-bold">1</span>
                    Tipo de Documento
                </h2>
            </div>
            <div class="p-3">
                <input type="hidden" name="tipo_documento_id" value="{{ $documento->tipo_documento_id }}">
                <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                    {{ $documento->tipoDocumento->nome }}
                </div>
                <p class="text-xs text-amber-600 mt-1.5 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    O tipo de documento não pode ser alterado após a criação
                </p>
            </div>
        </div>

        {{-- Seção: Editor de Conteúdo --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3">
            <div class="px-3 py-2 bg-gradient-to-r from-green-50 to-white border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <span class="flex items-center justify-center w-4 h-4 bg-green-600 text-white rounded-full text-xs font-bold">2</span>
                    Conteúdo do Documento
                </h2>
            </div>
            <div class="p-3">
                
                <!-- Toolbar do Editor -->
                <div class="border border-gray-300 rounded-t-lg bg-gray-50 p-1.5 space-y-1.5">
                    {{-- Primeira linha: Desfazer, Títulos e Formatação básica --}}
                    <div class="flex items-center gap-1 flex-wrap">
                        {{-- Desfazer/Refazer --}}
                        <button type="button" onclick="document.execCommand('undo')" class="p-1.5 hover:bg-gray-200 rounded" title="Desfazer (Ctrl+Z)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                        </button>
                        <button type="button" onclick="document.execCommand('redo')" class="p-1.5 hover:bg-gray-200 rounded" title="Refazer (Ctrl+Y)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/>
                            </svg>
                        </button>

                        <div class="w-px h-6 bg-gray-300 mx-1"></div>

                        {{-- Títulos --}}
                        <select onchange="document.execCommand('formatBlock', false, this.value); this.value=''" class="text-xs px-2 py-1 border border-gray-300 rounded hover:bg-gray-100" title="Estilo">
                            <option value="">Estilo</option>
                            <option value="h1">Título 1</option>
                            <option value="h2">Título 2</option>
                            <option value="h3">Título 3</option>
                            <option value="h4">Título 4</option>
                            <option value="p">Parágrafo</option>
                        </select>

                        {{-- Tamanho da fonte --}}
                        <select onchange="document.execCommand('fontSize', false, this.value); this.value=''" class="text-xs px-2 py-1 border border-gray-300 rounded hover:bg-gray-100" title="Tamanho">
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
                        <select onchange="document.execCommand('fontName', false, this.value); this.value=''" class="text-xs px-2 py-1 border border-gray-300 rounded hover:bg-gray-100" title="Fonte">
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
                        <button type="button" onclick="document.execCommand('subscript')" class="p-1.5 hover:bg-gray-200 rounded text-xs" title="Subscrito">
                            X<sub>2</sub>
                        </button>
                        <button type="button" onclick="document.execCommand('superscript')" class="p-1.5 hover:bg-gray-200 rounded text-xs" title="Sobrescrito">
                            X<sup>2</sup>
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

                        {{-- Tabela --}}
                        <button type="button" @click="inserirTabela()" class="p-1.5 hover:bg-gray-200 rounded" title="Inserir tabela">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        {{-- Linha horizontal --}}
                        <button type="button" onclick="document.execCommand('insertHorizontalRule')" class="p-1.5 hover:bg-gray-200 rounded" title="Inserir linha horizontal">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div class="w-px h-6 bg-gray-300 mx-1"></div>

                        <button type="button" onclick="document.execCommand('removeFormat')" class="p-1.5 hover:bg-gray-200 rounded" title="Limpar formatação">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <div class="relative">
                            <button type="button" @click="verificarOrtografia()" class="p-1.5 hover:bg-green-200 rounded text-green-600" title="Verificar ortografia">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                            <span x-show="contadorErros > 0" 
                                  x-text="contadorErros"
                                  class="absolute -top-1 -right-1 flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-500 rounded-full border-2 border-white"></span>
                        </div>

                        <button type="button" @click="limparTudo()" class="p-1.5 hover:bg-red-200 rounded text-red-600" title="Limpar tudo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    
                    <span x-show="salvandoAuto" class="ml-auto text-xs text-green-600 flex items-center gap-1">
                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Salvando...
                    </span>
                </div>

                <!-- Editor -->
                <div id="editor" 
                     contenteditable="true"
                     @input="conteudo = $el.innerHTML; salvarAutomaticamente(); verificarErrosTempoReal()"
                     @paste="handlePaste($event)"
                     class="min-h-[280px] max-h-[380px] overflow-y-auto p-3 border border-t-0 border-gray-300 rounded-b-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                     style="font-family: 'Times New Roman', serif; font-size: 13px; line-height: 1.5;">
                    <p>Selecione um tipo de documento para carregar o modelo ou digite o conteúdo do documento aqui...</p>
                </div>
                <textarea name="conteudo" x-model="conteudo" class="sr-only" required></textarea>

            </div>
        </div>

        {{-- Seção: Histórico de Versões (Completo com Restaurar) --}}
        @if($documento->versoes->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3" x-data="{ historicoAberto: false }">
            <div class="px-3 py-2 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200 cursor-pointer" @click="historicoAberto = !historicoAberto">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-4 h-4 bg-orange-600 text-white rounded-full text-xs font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        Histórico de Versões
                        <span class="px-1.5 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">{{ $documento->versoes->count() }}</span>
                    </h2>
                    <svg class="w-4 h-4 text-gray-500 transition-transform" :class="historicoAberto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
            <div x-show="historicoAberto" x-collapse>
                <div class="p-3">
                    <div class="space-y-2">
                        @foreach($documento->versoes->sortByDesc('versao') as $versao)
                        <div class="border border-gray-200 rounded-lg p-2.5 hover:bg-gray-50 transition-colors" x-data="{ mostrarConteudo: false }">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded">
                                            Versão {{ $versao->versao }}
                                        </span>
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span class="font-medium text-gray-900">{{ $versao->usuarioInterno->nome }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-gray-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $versao->created_at->format('d/m/Y H:i') }}
                                            <span class="text-gray-400">•</span>
                                            {{ $versao->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-1.5">
                                    <button type="button" 
                                            @click="mostrarConteudo = !mostrarConteudo"
                                            class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                                        <span x-show="!mostrarConteudo">Ver</span>
                                        <span x-show="mostrarConteudo">Ocultar</span>
                                    </button>
                                    
                                    <form action="{{ route('admin.documentos.restaurarVersao', [$documento->id, $versao->id]) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Tem certeza que deseja restaurar esta versão? O conteúdo atual será substituído.')">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Restaurar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div x-show="mostrarConteudo" x-collapse class="mt-2 pt-2 border-t border-gray-200">
                                <div class="bg-gray-50 rounded p-2 max-h-40 overflow-y-auto text-xs" style="font-family: 'Times New Roman', serif;">
                                    {!! $versao->conteudo !!}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Seção: Assinaturas Digitais --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3">
            <div class="px-3 py-2 bg-gradient-to-r from-purple-50 to-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-4 h-4 bg-purple-600 text-white rounded-full text-xs font-bold">3</span>
                        Assinaturas Digitais
                    </h2>
                    <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Obrigatório</span>
                </div>
            </div>
            <div class="p-3">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-1">
                    @foreach($usuariosInternos as $usuario)
                        <label class="flex items-start p-2 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-300 hover:bg-purple-50 transition-all group">
                            <input type="checkbox" 
                                   name="assinaturas[]" 
                                   value="{{ $usuario->id }}"
                                   {{ $documento->assinaturas->contains('usuario_interno_id', $usuario->id) ? 'checked' : '' }}
                                   class="mt-0.5 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <div class="ml-2 flex-1">
                                <div class="text-xs font-semibold text-gray-900 group-hover:text-purple-900">
                                    {{ $usuario->nome }}
                                    @if($usuario->id == auth('interno')->id())
                                        <span class="ml-1 px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full font-medium">Você</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $usuario->cpf_formatado }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

        {{-- Botões de Ação --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex gap-2 w-full sm:w-auto">
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
        tipoSelecionado: {{ $documento->tipo_documento_id }},
        sigiloso: {{ $documento->sigiloso ? 'true' : 'false' }},
        conteudo: '',
        modelos: [],
        salvandoAuto: false,
        ultimoSalvo: '',
        timeoutSalvar: null,
        contadorErros: 0,
        timeoutVerificacao: null,
        chaveLocalStorage: 'documento_rascunho_{{ request()->get("processo_id", "novo") }}',

        init() {
            // Carrega conteúdo do documento
            this.tipoSelecionado = {{ $documento->tipo_documento_id ?? 'null' }};
            this.conteudo = {!! json_encode($documento->conteudo) !!};
            document.getElementById('editor').innerHTML = this.conteudo;
            
            // Inicializa sistema de edição colaborativa
            this.edicaoColaborativa = new EdicaoColaborativa(
                {{ $documento->id }},
                '{{ auth("interno")->user()->nome }}'
            );
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

        inserirTabela() {
            const linhas = prompt('Número de linhas:', '3');
            const colunas = prompt('Número de colunas:', '3');
            
            if (!linhas || !colunas) return;
            
            let tabela = '<table border="1" style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
            for (let i = 0; i < parseInt(linhas); i++) {
                tabela += '<tr>';
                for (let j = 0; j < parseInt(colunas); j++) {
                    tabela += '<td style="border: 1px solid #ddd; padding: 8px;">&nbsp;</td>';
                }
                tabela += '</tr>';
            }
            tabela += '</table><p>&nbsp;</p>';
            
            document.execCommand('insertHTML', false, tabela);
            this.conteudo = document.getElementById('editor').innerHTML;
            this.salvarAutomaticamente();
        },

        limparTudo() {
            if (confirm('Tem certeza que deseja limpar todo o conteúdo? Esta ação não pode ser desfeita.')) {
                document.getElementById('editor').innerHTML = '<p><br></p>';
                this.conteudo = '<p><br></p>';
                this.salvarAutomaticamente();
            }
        },

        async carregarModelos(tipoId) {
            if (!tipoId) return;
            
            try {
                const response = await fetch(`/admin/documentos/modelos/${tipoId}`);
                
                if (!response.ok) {
                    console.warn('Nenhum modelo encontrado para este tipo de documento');
                    return;
                }
                
                this.modelos = await response.json();
                
                // Se houver modelos, carrega o primeiro automaticamente e preenche o editor
                if (this.modelos && this.modelos.length > 0) {
                    this.conteudo = this.modelos[0].conteudo;
                    const editor = document.getElementById('editor');
                    if (editor) {
                        editor.innerHTML = this.conteudo;
                        this.salvarAutomaticamente();
                    }
                } else {
                    console.log('Nenhum modelo disponível para este tipo de documento');
                }
            } catch (error) {
                console.error('Erro ao carregar modelos:', error);
                // Não exibe erro ao usuário, apenas loga no console
            }
        },

        contarPalavras() {
            const texto = this.conteudo.replace(/<[^>]*>/g, '').trim();
            return texto.split(/\s+/).filter(word => word.length > 0).length;
        },

        verificarErrosTempoReal() {
            clearTimeout(this.timeoutVerificacao);
            
            this.timeoutVerificacao = setTimeout(async () => {
                const editor = document.getElementById('editor');
                const texto = editor.innerText || editor.textContent;
                
                if (!texto.trim() || texto.length < 10) {
                    this.contadorErros = 0;
                    return;
                }

                try {
                    const response = await fetch('https://api.languagetool.org/v2/check', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            text: texto,
                            language: 'pt-BR',
                            enabledOnly: 'false'
                        })
                    });

                    const data = await response.json();
                    this.contadorErros = data.matches ? data.matches.length : 0;
                } catch (error) {
                    console.error('Erro na verificação em tempo real:', error);
                    this.contadorErros = 0;
                }
            }, 2000);
        },

        async verificarOrtografia() {
            const editor = document.getElementById('editor');
            const texto = editor.innerText || editor.textContent;
            
            if (!texto.trim()) {
                alert('Digite algum texto para verificar a ortografia.');
                return;
            }

            const btnVerificar = event.target.closest('button');
            const originalHTML = btnVerificar.innerHTML;
            btnVerificar.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            btnVerificar.disabled = true;

            try {
                const response = await fetch('https://api.languagetool.org/v2/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        text: texto,
                        language: 'pt-BR',
                        enabledOnly: 'false'
                    })
                });

                const data = await response.json();
                
                if (data.matches && data.matches.length > 0) {
                    let errosHTML = '<div style="max-height: 400px; overflow-y: auto;"><ul style="list-style: none; padding: 0;" id="lista-erros">';
                    
                    data.matches.forEach((erro, index) => {
                        const palavraErrada = texto.substring(erro.offset, erro.offset + erro.length);
                        const sugestoes = erro.replacements.slice(0, 3);
                        
                        errosHTML += `
                            <li id="erro-${index}" style="padding: 12px; margin-bottom: 10px; border-left: 3px solid #ef4444; background: #fef2f2; border-radius: 4px; transition: all 0.3s;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <strong style="color: #dc2626; font-size: 15px;">${palavraErrada}</strong>
                                        <p style="margin: 5px 0; font-size: 14px; color: #374151;">${erro.message}</p>
                                        ${sugestoes.length > 0 ? `
                                            <div style="margin-top: 8px;">
                                                <p style="margin: 0 0 5px 0; font-size: 12px; color: #6b7280; font-weight: 600;">Clique para substituir:</p>
                                                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                                    ${sugestoes.map(s => `
                                                        <button 
                                                            onclick="substituirPalavra('${palavraErrada.replace(/'/g, "\\'")}', '${s.value.replace(/'/g, "\\'")}', ${index})"
                                                            style="padding: 6px 12px; background: #059669; color: white; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: 500; transition: all 0.2s;"
                                                            onmouseover="this.style.background='#047857'"
                                                            onmouseout="this.style.background='#059669'">
                                                            ${s.value}
                                                        </button>
                                                    `).join('')}
                                                </div>
                                            </div>
                                        ` : '<p style="margin: 5px 0; font-size: 13px; color: #6b7280; font-style: italic;">Sem sugestões disponíveis</p>'}
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                    
                    errosHTML += '</ul></div>';
                    
                    const modal = document.createElement('div');
                    modal.id = 'modal-ortografia';
                    modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
                    modal.innerHTML = `
                        <div style="background: white; border-radius: 12px; max-width: 650px; width: 90%; max-height: 80vh; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                            <div style="background: linear-gradient(to right, #ef4444, #dc2626); padding: 20px; color: white;">
                                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Verificação Ortográfica</h3>
                                <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Encontrados <span id="contador-erros">${data.matches.length}</span> possíveis erros</p>
                            </div>
                            <div style="padding: 20px;">
                                ${errosHTML}
                            </div>
                            <div style="padding: 15px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                                <span id="status-correcoes" style="font-size: 13px; color: #059669; font-weight: 500;"></span>
                                <button onclick="this.closest('[style*=fixed]').remove()" style="padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                                    Fechar
                                </button>
                            </div>
                        </div>
                    `;
                    
                    window.substituirPalavra = (palavraErrada, sugestao, index) => {
                        const editor = document.getElementById('editor');
                        let conteudo = editor.innerHTML;
                        const regex = new RegExp(`\\b${palavraErrada.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'gi');
                        let substituido = false;
                        conteudo = conteudo.replace(regex, (match) => {
                            if (!substituido) {
                                substituido = true;
                                return sugestao;
                            }
                            return match;
                        });
                        editor.innerHTML = conteudo;
                        this.conteudo = conteudo;
                        this.salvarAutomaticamente();
                        
                        const erroItem = document.getElementById(`erro-${index}`);
                        if (erroItem) {
                            erroItem.style.borderLeftColor = '#059669';
                            erroItem.style.background = '#d1fae5';
                            erroItem.innerHTML = `
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <svg style="width: 24px; height: 24px; color: #059669; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <strong style="color: #065f46;">${palavraErrada}</strong> → <strong style="color: #059669;">${sugestao}</strong>
                                        <p style="margin: 5px 0 0 0; font-size: 13px; color: #047857;">✓ Substituído com sucesso!</p>
                                    </div>
                                </div>
                            `;
                            const errosRestantes = document.querySelectorAll('#lista-erros li[style*="border-left: 3px solid rgb(239, 68, 68)"]').length;
                            document.getElementById('contador-erros').textContent = errosRestantes;
                            const totalCorrigidos = data.matches.length - errosRestantes;
                            document.getElementById('status-correcoes').textContent = 
                                totalCorrigidos > 0 ? `✓ ${totalCorrigidos} correção(ões) aplicada(s)` : '';
                        }
                    };
                    
                    document.body.appendChild(modal);
                    modal.onclick = (e) => {
                        if (e.target === modal) modal.remove();
                    };
                } else {
                    alert('✓ Nenhum erro encontrado! Seu texto está correto.');
                }
            } catch (error) {
                console.error('Erro ao verificar ortografia:', error);
                alert('Erro ao verificar ortografia. Verifique sua conexão com a internet.');
            } finally {
                btnVerificar.innerHTML = originalHTML;
                btnVerificar.disabled = false;
            }
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
