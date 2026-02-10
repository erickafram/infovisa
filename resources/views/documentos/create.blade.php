@extends('layouts.admin')

@section('title', 'Criar Novo Documento')

@php
    // Desativa o assistente IA principal nesta página (já tem o assistente de redação)
    $desativarAssistenteIA = true;
@endphp

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
@if(isset($processo))
    <meta name="processo-id" content="{{ $processo->id }}">
    <meta name="estabelecimento-id" content="{{ $processo->estabelecimento_id }}">
@endif

<div class="min-h-screen bg-gray-50" x-data="documentoEditor()" @keydown.escape="modalConfirmarFinalizacao = false">
    <div class="max-w-8xl mx-auto px-4 py-8">
        {{-- Mensagens de Erro --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-red-800 mb-2">Erro ao salvar documento:</h3>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Aviso de outro usuário editando --}}
        <div x-show="outroUsuarioEditando" 
             x-transition
             x-cloak
             class="mb-6 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg shadow-md">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-bold text-amber-800">Documento em edição por outro usuário</h3>
                    <p class="mt-1 text-sm text-amber-700">
                        <span class="font-semibold" x-text="nomeUsuarioEditando"></span> está editando este documento no momento.
                        <br>
                        <span class="text-amber-600">Aguarde o usuário finalizar as alterações antes de salvar para evitar conflitos.</span>
                    </p>
                    <div class="mt-2 flex items-center gap-2 text-xs text-amber-600">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Verificando a cada 10 segundos...
                    </div>
                </div>
            </div>
        </div>

        {{-- Mensagem de recuperação de dados --}}
        <div x-show="dadosRecuperados" 
             x-transition
             class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-800 mb-1">Rascunho recuperado</h3>
                        <p class="text-sm text-blue-700">Seus dados foram recuperados automaticamente. Continue editando de onde parou.</p>
                    </div>
                </div>
                <button @click="dadosRecuperados = false" class="text-blue-500 hover:text-blue-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

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
                <span class="text-gray-900 font-medium">Novo Documento</span>
            </div>
            
            @if(isset($processo))
                <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-medium">{{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}</span>
                </div>
            @endif
        </div>

    <form id="formDocumento" method="POST" action="{{ route('admin.documentos.store') }}" @submit="handleSubmit">
        @csrf
        
        @if(isset($processo))
            <input type="hidden" name="processo_id" value="{{ $processo->id }}">
        @endif
        
        {{-- Campo hidden para a ação (rascunho ou finalizar) --}}
        <input type="hidden" name="acao" id="inputAcao" value="rascunho">
        
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
                <select name="tipo_documento_id" 
                        x-model="tipoSelecionado"
                        @change="carregarModelos($event.target.value); atualizarAvisoPrazo($event.target.value); salvarAutomaticamente()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        required>
                    <option value="">Selecione o tipo de documento</option>
                    @foreach($tiposDocumento as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nome }}</option>
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
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3">
            <div class="px-3 py-2 bg-gradient-to-r from-green-50 to-white border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <span class="flex items-center justify-center w-4 h-4 bg-green-600 text-white rounded-full text-xs font-bold">2</span>
                    Conteúdo do Documento
                </h2>
            </div>
            <div class="p-3">
                
                <!-- Toolbar do Editor -->
                <div class="border border-gray-300 rounded-t-lg bg-gradient-to-b from-gray-50 to-gray-100 p-2 space-y-2 shadow-sm">
                    {{-- Primeira linha: Desfazer, Títulos e Formatação básica --}}
                    <div class="flex items-center gap-1.5 flex-wrap">
                        {{-- Desfazer/Refazer --}}
                        <button type="button" onclick="document.execCommand('undo')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Desfazer (Ctrl+Z)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                        </button>
                        <button type="button" onclick="document.execCommand('redo')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Refazer (Ctrl+Y)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/>
                            </svg>
                        </button>

                        <div class="w-px h-7 bg-gray-300 mx-1"></div>

                        {{-- Títulos --}}
                        <select onchange="document.execCommand('formatBlock', false, this.value); this.value=''" class="text-sm px-3 py-1.5 border border-gray-300 rounded hover:bg-white hover:shadow transition-all font-medium" title="Estilo">
                            <option value="">Estilo</option>
                            <option value="h1">Título 1</option>
                            <option value="h2">Título 2</option>
                            <option value="h3">Título 3</option>
                            <option value="h4">Título 4</option>
                            <option value="p">Parágrafo</option>
                        </select>

                        {{-- Tamanho da fonte --}}
                        <select onchange="document.execCommand('fontSize', false, this.value); this.value=''" class="text-sm px-3 py-1.5 border border-gray-300 rounded hover:bg-white hover:shadow transition-all" title="Tamanho">
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
                        <select onchange="document.execCommand('fontName', false, this.value); this.value=''" class="text-sm px-3 py-1.5 border border-gray-300 rounded hover:bg-white hover:shadow transition-all" title="Fonte">
                            <option value="">Fonte</option>
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Tahoma">Tahoma</option>
                        </select>

                        <div class="w-px h-7 bg-gray-300 mx-1"></div>

                        {{-- Botão Variáveis Dinâmicas --}}
                        <div class="relative" x-data="{ showVarsDropdown: false }">
                            <button type="button" @click="showVarsDropdown = !showVarsDropdown" class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-300 rounded hover:bg-amber-100 hover:shadow transition-all" title="Inserir variável dinâmica">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Variáveis
                            </button>
                            <div x-show="showVarsDropdown" @click.away="showVarsDropdown = false" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 class="absolute z-50 mt-1 w-80 bg-white border border-gray-300 rounded-lg shadow-xl p-2 max-h-80 overflow-y-auto" style="display: none;">
                                <p class="text-xs text-gray-500 mb-2 px-2 font-medium">Clique para inserir no documento:</p>
                                
                                {{-- Estabelecimento --}}
                                <div class="mb-2">
                                    <p class="text-xs font-bold text-gray-700 px-2 py-1 bg-gray-100 rounded">📍 Estabelecimento</p>
                                    <div class="space-y-0.5 mt-1">
                                        <button type="button" @click="inserirVariavel('{estabelecimento_nome}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{estabelecimento_nome}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Nome/Fantasia</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{estabelecimento_razao_social}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{estabelecimento_razao_social}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Razão Social</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{estabelecimento_cnpj}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{estabelecimento_cnpj}</span>
                                            <span class="text-gray-500 text-xs ml-1">- CNPJ</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{estabelecimento_cpf}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{estabelecimento_cpf}</span>
                                            <span class="text-gray-500 text-xs ml-1">- CPF</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{estabelecimento_endereco}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{estabelecimento_endereco}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Endereço completo</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{estabelecimento_bairro}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{estabelecimento_bairro}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Bairro</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{estabelecimento_cidade}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{estabelecimento_cidade}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Cidade</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{municipio}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{municipio}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Município</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{estabelecimento_telefone}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{estabelecimento_telefone}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Telefone</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{estabelecimento_email}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{estabelecimento_email}</span>
                                            <span class="text-gray-500 text-xs ml-1">- E-mail</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{atividades}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{atividades}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Lista de atividades</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Responsável Técnico --}}
                                <div class="mb-2">
                                    <p class="text-xs font-bold text-gray-700 px-2 py-1 bg-gray-100 rounded">👤 Responsável Técnico</p>
                                    <div class="space-y-0.5 mt-1">
                                        <button type="button" @click="inserirVariavel('{responsavel_nome}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{responsavel_nome}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Nome</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{responsavel_cpf}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{responsavel_cpf}</span>
                                            <span class="text-gray-500 text-xs ml-1">- CPF</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{responsavel_email}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{responsavel_email}</span>
                                            <span class="text-gray-500 text-xs ml-1">- E-mail</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{responsavel_conselho}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{responsavel_conselho}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Nº Conselho</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Processo --}}
                                <div class="mb-2">
                                    <p class="text-xs font-bold text-gray-700 px-2 py-1 bg-gray-100 rounded">📄 Processo</p>
                                    <div class="space-y-0.5 mt-1">
                                        <button type="button" @click="inserirVariavel('{processo_numero}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{processo_numero}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Número</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{processo_tipo}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{processo_tipo}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Tipo</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{processo_data_criacao}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{processo_data_criacao}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Data de criação</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Data --}}
                                <div>
                                    <p class="text-xs font-bold text-gray-700 px-2 py-1 bg-gray-100 rounded">📅 Data</p>
                                    <div class="space-y-0.5 mt-1">
                                        <button type="button" @click="inserirVariavel('{data_atual}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{data_atual}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Data atual (dd/mm/aaaa)</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{data_extenso}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{data_extenso}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Data por extenso</span>
                                        </button>
                                        <button type="button" @click="inserirVariavel('{ano_atual}'); showVarsDropdown = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                            <span class="font-mono text-amber-600 text-xs">{ano_atual}</span>
                                            <span class="text-gray-500 text-xs ml-1">- Ano atual</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="w-px h-7 bg-gray-300 mx-1"></div>

                        <button type="button" onclick="document.execCommand('bold')" class="p-2 hover:bg-white hover:shadow rounded font-bold text-sm transition-all" title="Negrito">
                            B
                        </button>
                        <button type="button" onclick="document.execCommand('italic')" class="p-2 hover:bg-white hover:shadow rounded italic text-sm transition-all" title="Itálico">
                            I
                        </button>
                        <button type="button" onclick="document.execCommand('underline')" class="p-2 hover:bg-white hover:shadow rounded underline text-sm transition-all" title="Sublinhado">
                            U
                        </button>
                        <button type="button" onclick="document.execCommand('strikeThrough')" class="p-2 hover:bg-white hover:shadow rounded line-through text-sm transition-all" title="Tachado">
                            S
                        </button>
                        <button type="button" onclick="document.execCommand('subscript')" class="p-2 hover:bg-white hover:shadow rounded text-sm transition-all" title="Subscrito">
                            X<sub>2</sub>
                        </button>
                        <button type="button" onclick="document.execCommand('superscript')" class="p-2 hover:bg-white hover:shadow rounded text-sm transition-all" title="Sobrescrito">
                            X<sup>2</sup>
                        </button>

                        <div class="w-px h-7 bg-gray-300 mx-1"></div>

                        {{-- Cor do texto --}}
                        <div class="relative" x-data="{ showColorPicker: false }">
                            <button type="button" @click="showColorPicker = !showColorPicker" class="p-2 hover:bg-white hover:shadow rounded flex items-center gap-1 transition-all" title="Cor do texto">
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
                            <button type="button" @click="showBgPicker = !showBgPicker" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Cor de fundo">
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
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <button type="button" onclick="document.execCommand('justifyLeft')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Alinhar à esquerda">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm0 4h10v2H2V8zm0 4h16v2H2v-2zm0 4h10v2H2v-2z"/></svg>
                        </button>
                        <button type="button" onclick="document.execCommand('justifyCenter')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Centralizar">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm3 4h10v2H5V8zm-3 4h16v2H2v-2zm3 4h10v2H5v-2z"/></svg>
                        </button>
                        <button type="button" onclick="document.execCommand('justifyRight')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Alinhar à direita">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm6 4h10v2H8V8zm-6 4h16v2H2v-2zm6 4h10v2H8v-2z"/></svg>
                        </button>
                        <button type="button" onclick="document.execCommand('justifyFull')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Justificar">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm0 4h16v2H2V8zm0 4h16v2H2v-2zm0 4h16v2H2v-2z"/></svg>
                        </button>

                        <div class="w-px h-7 bg-gray-300 mx-1"></div>

                        <button type="button" onclick="document.execCommand('insertUnorderedList')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Lista">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h2v2H3V4zm4 0h10v2H7V4zM3 9h2v2H3V9zm4 0h10v2H7V9zm-4 5h2v2H3v-2zm4 0h10v2H7v-2z"/></svg>
                        </button>
                        <button type="button" onclick="document.execCommand('insertOrderedList')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Lista numerada">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h1v3H3V4zm0 5h1v3H3V9zm0 5h1v3H3v-3zm4-9h10v2H7V5zm0 5h10v2H7v-2zm0 5h10v2H7v-2z"/></svg>
                        </button>

                        <div class="w-px h-7 bg-gray-300 mx-1"></div>

                        <button type="button" onclick="document.execCommand('indent')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Aumentar recuo">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h14v2H3V4zm0 4h14v2H3V8zm0 4h14v2H3v-2zm0 4h14v2H3v-2zM1 8l3 3-3 3V8z"/></svg>
                        </button>
                        <button type="button" onclick="document.execCommand('outdent')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Diminuir recuo">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h14v2H3V4zm0 4h14v2H3V8zm0 4h14v2H3v-2zm0 4h14v2H3v-2zM7 8L4 11l3 3V8z"/></svg>
                        </button>

                        <div class="w-px h-7 bg-gray-300 mx-1"></div>

                        {{-- Botão Inserir Imagem --}}
                        <button type="button" @click="$refs.imageInput.click()" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Inserir Imagem">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <input type="file" x-ref="imageInput" @change="inserirImagem($event)" accept="image/*" class="hidden">

                        <button type="button" onclick="document.execCommand('createLink', false, prompt('Digite a URL:'))" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Inserir link">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <button type="button" onclick="document.execCommand('unlink')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Remover link">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 6.707 6.293a1 1 0 00-1.414 1.414L8.586 11l-3.293 3.293a1 1 0 101.414 1.414L10 12.414l3.293 3.293a1 1 0 001.414-1.414L11.414 11l3.293-3.293z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div class="w-px h-7 bg-gray-300 mx-1"></div>

                        {{-- Tabela --}}
                        <button type="button" @click="inserirTabela()" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Inserir tabela">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        {{-- Linha horizontal --}}
                        <button type="button" onclick="document.execCommand('insertHorizontalRule')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Inserir linha horizontal">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div class="w-px h-7 bg-gray-300 mx-1"></div>

                        <button type="button" onclick="document.execCommand('removeFormat')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Limpar formatação">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <div class="relative">
                            <button type="button" @click="verificarOrtografia()" class="p-2 hover:bg-green-100 hover:shadow rounded text-green-600 transition-all" title="Verificar ortografia">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                            <span x-show="contadorErros > 0" 
                                  x-text="contadorErros"
                                  class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full border-2 border-white"></span>
                        </div>

                        <button type="button" @click="limparTudo()" class="p-2 hover:bg-red-100 hover:shadow rounded text-red-600 transition-all" title="Limpar tudo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    
                    <span x-show="salvandoAuto" class="ml-auto text-sm text-green-600 flex items-center gap-1.5 font-medium">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Salvando...
                    </span>
                </div>

                {{-- Logomarca do Documento --}}
                @if(isset($logomarca) && $logomarca)
                    <div class="border border-b-0 border-gray-300 bg-gradient-to-b from-blue-50 to-white p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset($logomarca) }}" 
                                 alt="Logomarca" 
                                 class="h-16 w-auto object-contain">
                            <div class="text-xs text-gray-600">
                                <p class="font-semibold text-gray-800">Logomarca do Documento</p>
                                <p class="text-gray-500">
                                    @if(isset($processo) && $processo->estabelecimento)
                                        @if($processo->estabelecimento->isCompetenciaEstadual())
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                                Competência Estadual
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                </svg>
                                                Município: {{ $processo->estabelecimento->municipioRelacionado->nome ?? 'N/A' }}
                                            </span>
                                        @endif
                                    @else
                                        Logomarca padrão
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 text-right">
                            <p class="font-medium">Esta logomarca aparecerá</p>
                            <p>no documento final</p>
                        </div>
                    </div>
                @endif

                <!-- Editor -->
                <div id="editor" 
                     contenteditable="true"
                     @input="conteudo = $el.innerHTML; salvarAutomaticamente(); verificarErrosTempoReal()"
                     @paste="handlePaste($event)"
                     class="min-h-[400px] max-h-[600px] overflow-y-auto p-6 border border-t-0 border-gray-300 rounded-b-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                     style="font-family: Arial, Helvetica, sans-serif; font-size: 12pt; line-height: 1.6;">
                    <p>Selecione um tipo de documento para carregar o modelo ou digite o conteúdo do documento aqui...</p>
                </div>
                <textarea name="conteudo" x-model="conteudo" class="sr-only" required></textarea>

            </div>
        </div>

        {{-- Seção: Prazo/Validade (Condicional) --}}
        <div x-show="temPrazo" 
             x-cloak
             class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3">
            <div class="px-3 py-2 bg-gradient-to-r from-blue-50 to-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-4 h-4 bg-blue-600 text-white rounded-full text-xs font-bold">3</span>
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Prazo/Validade do Documento
                    </h2>
                    <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">Obrigatório</span>
                </div>
            </div>
            <div class="p-3">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-3 rounded">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Este tipo de documento possui prazo de validade</p>
                            <p>Defina o prazo em dias. A data de vencimento será calculada automaticamente a partir da data de criação.</p>
                        </div>
                    </div>
                </div>

                {{-- Aviso sobre contagem de prazo (será exibido dinamicamente via JavaScript) --}}
                <div id="aviso-prazo-container" style="display: none;">
                    {{-- Aviso para documentos de notificação/fiscalização --}}
                    <div id="aviso-notificacao" class="bg-amber-50 border-l-4 border-amber-500 p-3 mb-3 rounded-lg" style="display: none;">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="text-sm text-amber-800">
                                <p class="font-semibold">📋 Contagem de Prazo (§1º)</p>
                                <p class="text-xs text-amber-700 mt-1">
                                    O prazo inicia quando o estabelecimento <strong>visualizar o documento</strong> ou após <strong>5 dias úteis da última assinatura</strong> (o que ocorrer primeiro).
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Aviso para documentos de licenciamento (Alvará, etc) --}}
                    <div id="aviso-licenciamento" class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-3 rounded-lg" style="display: none;">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold">📅 Prazo Fixo</p>
                                <p class="text-xs text-blue-700 mt-1">
                                    O prazo é contado a partir da <strong>data de criação</strong> do documento.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="prazo_dias" class="block text-sm font-medium text-gray-700 mb-2">
                            Prazo em Dias <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2 mb-3">
                            <input type="number" 
                                   name="prazo_dias" 
                                   id="prazo_dias"
                                   x-model="prazoDias"
                                   @input="calcularDataVencimento()"
                                   min="1"
                                   :required="temPrazo"
                                   class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Ex: 365">
                            <span class="text-sm text-gray-600">dias</span>
                        </div>
                        <p class="mb-3 text-xs text-gray-500">
                            Exemplos: Alvará (365 dias), Notificação (30 dias)
                        </p>

                        {{-- Tipo de Prazo --}}
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Prazo <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center cursor-pointer p-2 border border-gray-300 rounded-lg hover:bg-blue-50 transition">
                                <input type="radio" 
                                       name="tipo_prazo" 
                                       value="corridos" 
                                       x-model="tipoPrazo"
                                       @change="calcularDataVencimento()"
                                       :required="temPrazo"
                                       class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-xs text-gray-700">
                                    <strong>Dias Corridos</strong> - Todos os dias
                                </span>
                            </label>
                            <label class="flex items-center cursor-pointer p-2 border border-gray-300 rounded-lg hover:bg-blue-50 transition">
                                <input type="radio" 
                                       name="tipo_prazo" 
                                       value="uteis" 
                                       x-model="tipoPrazo"
                                       @change="calcularDataVencimento()"
                                       :required="temPrazo"
                                       class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-xs text-gray-700">
                                    <strong>Dias Úteis</strong> - Exclui finais de semana/feriados
                                </span>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            💡 Você pode ajustar considerando feriados municipais específicos
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-show="!isNotificacao">Data de Vencimento Prevista</span>
                            <span x-show="isNotificacao">Vencimento Estimado (5º dia útil)</span>
                        </label>
                        <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg" :class="isNotificacao ? 'bg-amber-50 border-amber-300' : 'bg-gray-50'">
                            <svg class="w-5 h-5" :class="isNotificacao ? 'text-amber-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm font-medium" :class="isNotificacao ? 'text-amber-800' : 'text-gray-900'" x-text="dataVencimentoFormatada || 'Informe o prazo'"></span>
                        </div>
                        <p class="mt-1 text-xs" :class="isNotificacao ? 'text-amber-600' : 'text-gray-500'">
                            <span x-show="!isNotificacao">Calculada automaticamente baseada no tipo de prazo</span>
                            <span x-show="isNotificacao">⚠️ Data estimada considerando início no 5º dia útil. Pode ser antecipada se o estabelecimento visualizar antes.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seção: Assinaturas Digitais --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3" x-data="buscaTecnicos()">
            <div class="px-3 py-2 bg-gradient-to-r from-purple-50 to-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-4 h-4 bg-purple-600 text-white rounded-full text-xs font-bold" x-text="temPrazo ? '4' : '3'"></span>
                        Assinaturas Digitais
                    </h2>
                    <div class="flex items-center gap-2">
                        <span x-show="selecionados.length > 0" class="px-1.5 py-0.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full" x-text="selecionados.length + ' selecionado(s)'"></span>
                        <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Obrigatório</span>
                    </div>
                </div>
            </div>
            <div class="p-3">
                {{-- Assinantes selecionados --}}
                <div x-show="selecionados.length > 0" class="mb-3 flex flex-wrap gap-1">
                    <template x-for="id in selecionados" :key="'sel-'+id">
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">
                            <span x-text="getNomeUsuario(id)"></span>
                            <button type="button" @click="toggleSelecionado(id)" class="ml-0.5 text-purple-600 hover:text-purple-900">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </span>
                    </template>
                </div>

                {{-- Hidden inputs para enviar os selecionados no form --}}
                <template x-for="id in selecionados" :key="'hidden-'+id">
                    <input type="hidden" name="assinaturas[]" :value="id">
                </template>

                {{-- Campo de Busca --}}
                <div class="mb-3">
                    <input type="text" 
                           x-model="busca"
                           @input="filtrarTecnicos()"
                           placeholder="Buscar técnico por nome, CPF ou email..."
                           class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-300 transition">
                    <div class="mt-1 flex gap-2 text-xs text-gray-500">
                        <span x-show="usuariosFiltrados.length > 0" class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="`${usuariosFiltrados.length} técnico(s) encontrado(s)`"></span>
                        </span>
                        <span x-show="usuariosFiltrados.length === 0 && busca" class="flex items-center gap-1 text-orange-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Nenhum técnico encontrado
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-1 border rounded-lg bg-gray-50">
                    <template x-for="usuario in usuariosFiltrados" :key="usuario.id">
                        <label class="flex items-start p-2 border-2 rounded-lg cursor-pointer hover:border-purple-300 hover:bg-purple-50 transition-all group bg-white"
                               :class="selecionados.includes(usuario.id) ? 'border-purple-400 bg-purple-50' : 'border-gray-200'">
                            <input type="checkbox" 
                                   :checked="selecionados.includes(usuario.id)"
                                   @change="toggleSelecionado(usuario.id)"
                                   class="mt-0.5 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <div class="ml-2 flex-1 min-w-0">
                                <div class="text-xs font-semibold text-gray-900 group-hover:text-purple-900 truncate">
                                    <span x-text="usuario.nome"></span>
                                    <template x-if="usuario.id == {{ auth('interno')->id() }}">
                                        <span class="ml-1 px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full font-medium">Você</span>
                                    </template>
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5 truncate" x-text="usuario.cpf"></div>
                            </div>
                        </label>
                    </template>

                    {{-- Fallback se não houver usuários --}}
                    <template x-if="usuariosFiltrados.length === 0 && !busca">
                        <div class="col-span-full text-center py-4 text-gray-500">
                            <p class="text-sm">Nenhum técnico disponível</p>
                        </div>
                    </template>
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
                    
                    {{-- Botão Pré-visualizar --}}
                    <button type="button" 
                            @click="modalPreview = true"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-blue-700 bg-blue-50 border-2 border-blue-200 rounded-lg hover:bg-blue-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Pré-visualizar
                    </button>
                </div>

                <div class="flex gap-2 w-full sm:w-auto">
                    <button type="submit" 
                            name="acao" 
                            value="rascunho"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Salvar Rascunho
                    </button>
                    
                    <button type="button"
                            @click="modalConfirmarFinalizacao = true"
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
    
    {{-- Modal de Pré-visualização --}}
    <div x-show="modalPreview" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div x-show="modalPreview"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="modalPreview = false"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            {{-- Modal Content --}}
            <div x-show="modalPreview"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-5xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                
                {{-- Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Pré-visualização do Documento
                        </h3>
                        <button @click="modalPreview = false" 
                                class="text-white hover:text-gray-200 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                    {{-- Informações do Documento --}}
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-1">Tipo de Documento</p>
                                <p class="text-sm text-gray-900" x-text="tipoSelecionado ? document.querySelector('select[name=tipo_documento_id] option[value=\'' + tipoSelecionado + '\']')?.text : 'Não selecionado'"></p>
                            </div>
                            @if(isset($processo))
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-1">Processo Vinculado</p>
                                <p class="text-sm text-gray-900">{{ $processo->numero_processo }}</p>
                            </div>
                            @endif
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-1">Documento Sigiloso</p>
                                <p class="text-sm text-gray-900" x-text="sigiloso ? 'Sim' : 'Não'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-1">Palavras</p>
                                <p class="text-sm text-gray-900" x-text="contarPalavras()"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Conteúdo do Documento --}}
                    <div class="prose prose-sm max-w-none bg-white p-6 rounded-lg border border-gray-200">
                        <div x-html="conteudo || '<p class=\'text-gray-400 italic\'>Nenhum conteúdo digitado ainda...</p>'"></div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button @click="modalPreview = false"
                            class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Confirmação para Finalizar Documento --}}
<div x-show="modalConfirmarFinalizacao" 
     class="fixed inset-0 z-50 flex items-center justify-center"
     style="display: none;">
    {{-- Backdrop --}}
    <div x-show="modalConfirmarFinalizacao"
         @click="modalConfirmarFinalizacao = false"
         class="absolute inset-0 bg-black bg-opacity-40"
         x-transition>
    </div>

    {{-- Modal Content --}}
    <div x-show="modalConfirmarFinalizacao"
         class="relative bg-white rounded-lg shadow-xl max-w-md mx-auto p-6"
         x-transition>
        
        {{-- Icon and Title --}}
        <div class="flex items-start gap-4 mb-4">
            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100">
                <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0-6a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">
                    Finalizar Documento
                </h3>
            </div>
        </div>

        {{-- Message --}}
        <p class="text-gray-700 text-sm mb-6 leading-relaxed">
            <span class="font-medium">Atenção:</span> O documento poderá ser editado desde que não tenha sido assinado por nenhuma pessoa. Após a primeira assinatura, não será mais possível fazer alterações.
        </p>
        <p class="text-gray-600 text-sm mb-6 leading-relaxed flex items-start gap-2">
            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span><strong>Dica:</strong> Se deseja continuar editando o documento, salve como <strong>Rascunho</strong>. Assim você poderá fazer alterações antes de finalizar e enviar para assinatura.</span>
        </p>

        {{-- Buttons --}}
        <div class="flex gap-3 justify-end">
            <button type="button" 
                    @click="modalConfirmarFinalizacao = false"
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </button>
            <button type="button"
                    @click="confirmarFinalizacao()"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 transition">
                Finalizar
            </button>
        </div>
    </div>
</div>

<script>
// Dados dos tipos de documento (prazo_notificacao)
const tiposDocumentoData = {

    @foreach($tiposDocumento as $tipo)
    {{ $tipo->id }}: {
        prazo_notificacao: {{ $tipo->prazo_notificacao ? 'true' : 'false' }},
        tem_prazo: {{ $tipo->tem_prazo ? 'true' : 'false' }},
        prazo_padrao_dias: {{ $tipo->prazo_padrao_dias ?? 'null' }}
    },
    @endforeach
};

// Função para busca de técnicos com filtro
function buscaTecnicos() {
    return {
        busca: '',
        selecionados: [],
        usuariosFiltrados: [
            @foreach($usuariosInternos as $usuario)
                {
                    id: {{ $usuario->id }},
                    nome: '{{ addslashes($usuario->nome) }}',
                    cpf: '{{ $usuario->cpf_formatado }}',
                    email: '{{ $usuario->email }}'
                },
            @endforeach
        ],
        usuariosOriginais: [
            @foreach($usuariosInternos as $usuario)
                {
                    id: {{ $usuario->id }},
                    nome: '{{ addslashes($usuario->nome) }}',
                    cpf: '{{ $usuario->cpf_formatado }}',
                    email: '{{ $usuario->email }}'
                },
            @endforeach
        ],

        toggleSelecionado(id) {
            const index = this.selecionados.indexOf(id);
            if (index === -1) {
                this.selecionados.push(id);
            } else {
                this.selecionados.splice(index, 1);
            }
        },

        getNomeUsuario(id) {
            const usuario = this.usuariosOriginais.find(u => u.id === id);
            return usuario ? usuario.nome : '';
        },

        filtrarTecnicos() {
            const termo = this.busca.toLowerCase().trim();
            
            if (!termo) {
                this.usuariosFiltrados = this.usuariosOriginais;
                return;
            }

            this.usuariosFiltrados = this.usuariosOriginais.filter(usuario => 
                usuario.nome.toLowerCase().includes(termo) ||
                usuario.cpf.toLowerCase().includes(termo) ||
                usuario.email.toLowerCase().includes(termo)
            );
        }
    };
}

function documentoEditor() {
    return {
        tipoSelecionado: null,
        sigiloso: false,
        conteudo: '',
        modelos: [],
        salvandoAuto: false,
        ultimoSalvo: '',
        timeoutSalvar: null,
        dadosRecuperados: false,
        modalPreview: false,
        modalConfirmarFinalizacao: false,
        confirmandoFinalizacao: false,
        contadorErros: 0,
        timeoutVerificacao: null,
        chaveLocalStorage: 'documento_rascunho_{{ request()->get("processo_id", "novo") }}',
        // Campos de prazo
        temPrazo: false,
        prazoDias: null,
        tipoPrazo: 'corridos',
        dataVencimentoFormatada: '',
        isNotificacao: false, // Se é documento de notificação/fiscalização (§1º)
        // Controle de edição simultânea
        outroUsuarioEditando: false,
        nomeUsuarioEditando: '',
        documentoId: {{ isset($documento) ? $documento->id : 'null' }},
        intervalEdicao: null,
        edicaoBloqueada: false,

        init() {
            // Inicia verificação de edição se for edição de documento existente
            if (this.documentoId) {
                this.iniciarVerificacaoEdicao();
            }
            
            // Libera edição quando a página for fechada
            window.addEventListener('beforeunload', () => {
                if (this.documentoId) {
                    this.liberarEdicao();
                }
            });
            
            // Tenta recuperar dados salvos do localStorage
            const dadosSalvos = localStorage.getItem(this.chaveLocalStorage);
            
            if (dadosSalvos) {
                try {
                    const dados = JSON.parse(dadosSalvos);
                    
                    // Recupera o conteúdo
                    if (dados.conteudo) {
                        this.conteudo = dados.conteudo;
                        document.getElementById('editor').innerHTML = this.conteudo;
                        this.dadosRecuperados = true;
                    }
                    
                    // Recupera o tipo de documento selecionado
                    if (dados.tipoSelecionado) {
                        this.tipoSelecionado = dados.tipoSelecionado;
                        // Aguarda o próximo tick para garantir que o select foi renderizado
                        this.$nextTick(() => {
                            const selectTipo = document.querySelector('select[name="tipo_documento_id"]');
                            if (selectTipo) {
                                selectTipo.value = dados.tipoSelecionado;
                            }
                        });
                    }
                    
                    console.log('Dados recuperados do localStorage:', dados);
                } catch (e) {
                    console.error('Erro ao recuperar dados do localStorage:', e);
                    // Se houver erro, inicia com editor vazio
                    this.conteudo = '<p>Selecione um tipo de documento para carregar o modelo ou digite o conteúdo do documento aqui...</p>';
                    document.getElementById('editor').innerHTML = this.conteudo;
                }
            } else {
                // Inicia com editor vazio
                this.conteudo = '<p>Selecione um tipo de documento para carregar o modelo ou digite o conteúdo do documento aqui...</p>';
                document.getElementById('editor').innerHTML = this.conteudo;
            }
        },

        // Métodos para controle de edição simultânea
        async iniciarVerificacaoEdicao() {
            // Primeiro, registra que estamos editando
            await this.registrarEdicao();
            
            // Inicia o polling a cada 10 segundos
            this.intervalEdicao = setInterval(() => {
                this.registrarEdicao();
            }, 10000);
        },

        async registrarEdicao() {
            if (!this.documentoId) return;
            
            try {
                const response = await fetch(`${window.APP_URL}/admin/documentos/${this.documentoId}/registrar-edicao`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.editando) {
                    this.outroUsuarioEditando = true;
                    this.nomeUsuarioEditando = data.usuario_nome;
                    this.edicaoBloqueada = true;
                } else {
                    this.outroUsuarioEditando = false;
                    this.nomeUsuarioEditando = '';
                    this.edicaoBloqueada = false;
                }
            } catch (error) {
                console.error('Erro ao registrar edição:', error);
            }
        },

        async verificarEdicao() {
            if (!this.documentoId) return;
            
            try {
                const response = await fetch(`${window.APP_URL}/admin/documentos/${this.documentoId}/verificar-edicao`);
                const data = await response.json();
                
                if (data.editando) {
                    this.outroUsuarioEditando = true;
                    this.nomeUsuarioEditando = data.usuario_nome;
                } else {
                    this.outroUsuarioEditando = false;
                    this.nomeUsuarioEditando = '';
                }
            } catch (error) {
                console.error('Erro ao verificar edição:', error);
            }
        },

        async liberarEdicao() {
            if (!this.documentoId) return;
            
            // Usa sendBeacon para garantir que a requisição seja enviada mesmo ao fechar a página
            const data = new FormData();
            data.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            navigator.sendBeacon(`/admin/documentos/${this.documentoId}/liberar-edicao`, data);
            
            if (this.intervalEdicao) {
                clearInterval(this.intervalEdicao);
            }
        },

        salvarAutomaticamente() {
            // Não salva se a modal de confirmação de finalização está aberta
            if (this.modalConfirmarFinalizacao) {
                return;
            }
            
            clearTimeout(this.timeoutSalvar);
            this.salvandoAuto = true;
            
            this.timeoutSalvar = setTimeout(() => {
                const dados = {
                    conteudo: this.conteudo,
                    tipoSelecionado: this.tipoSelecionado,
                    timestamp: Date.now()
                };
                localStorage.setItem(this.chaveLocalStorage, JSON.stringify(dados));
                this.salvandoAuto = false;
                this.ultimoSalvo = 'agora';
                console.log('Dados salvos automaticamente:', dados);
            }, 1000);
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
            }, 2000); // Aguarda 2 segundos após parar de digitar
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

        // Padroniza o conteúdo para Arial, removendo fontes e tamanhos inline
        padronizarFonte(html) {
            // Cria um elemento temporário para manipular o HTML
            const temp = document.createElement('div');
            temp.innerHTML = html;

            // Remove font-family e font-size inline de todos os elementos
            const elementos = temp.querySelectorAll('*');
            elementos.forEach(el => {
                if (el.style) {
                    el.style.removeProperty('font-family');
                    el.style.removeProperty('font-size');
                }
                // Remove tags <font> mantendo o conteúdo
                if (el.tagName === 'FONT') {
                    el.removeAttribute('face');
                    el.removeAttribute('size');
                }
            });

            return temp.innerHTML;
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

        // Inserir variável dinâmica no editor
        inserirVariavel(variavel) {
            const editor = document.getElementById('editor');
            editor.focus();
            
            // Insere a variável com destaque visual
            const variavelFormatada = `<span style="background-color: #fef3c7; color: #92400e; padding: 2px 4px; border-radius: 3px; font-family: monospace; font-size: 0.9em;">${variavel}</span>&nbsp;`;
            document.execCommand('insertHTML', false, variavelFormatada);
            
            this.conteudo = editor.innerHTML;
            this.salvarAutomaticamente();
        },

        limparTudo() {
            if (confirm('Tem certeza que deseja limpar todo o conteúdo? Esta ação não pode ser desfeita.')) {
                document.getElementById('editor').innerHTML = '<p><br></p>';
                this.conteudo = '<p><br></p>';
                this.salvarAutomaticamente();
            }
        },

        async verificarOrtografia() {
            const editor = document.getElementById('editor');
            const conteudoHTML = editor.innerHTML;
            const texto = editor.innerText || editor.textContent;
            
            if (!texto.trim()) {
                alert('Digite algum texto para verificar a ortografia.');
                return;
            }

            // Mostrar indicador de carregamento
            const btnVerificar = event.target.closest('button');
            const originalHTML = btnVerificar.innerHTML;
            btnVerificar.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            btnVerificar.disabled = true;

            try {
                // Usar API pública do LanguageTool
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
                    // Criar lista de erros com botões de substituição
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
                    
                    // Criar modal customizado
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
                    
                    // Função global para substituir palavra
                    window.substituirPalavra = (palavraErrada, sugestao, index) => {
                        const editor = document.getElementById('editor');
                        let conteudo = editor.innerHTML;
                        
                        // Criar regex para encontrar a palavra (case insensitive)
                        const regex = new RegExp(`\\b${palavraErrada.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'gi');
                        
                        // Substituir primeira ocorrência sem destaque (para não aparecer no PDF)
                        let substituido = false;
                        conteudo = conteudo.replace(regex, (match) => {
                            if (!substituido) {
                                substituido = true;
                                return sugestao;
                            }
                            return match;
                        });
                        
                        // Atualizar editor
                        editor.innerHTML = conteudo;
                        this.conteudo = conteudo;
                        this.salvarAutomaticamente();
                        
                        // Marcar erro como corrigido no modal
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
                            
                            // Atualizar contador
                            const errosRestantes = document.querySelectorAll('#lista-erros li[style*="border-left: 3px solid rgb(239, 68, 68)"]').length;
                            document.getElementById('contador-erros').textContent = errosRestantes;
                            
                            // Atualizar status
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
                // Restaurar botão
                btnVerificar.innerHTML = originalHTML;
                btnVerificar.disabled = false;
            }
        },

        async carregarModelos(tipoId) {
            if (!tipoId) {
                console.log('Tipo de documento não selecionado');
                return;
            }
            
            console.log('Carregando modelos para tipo:', tipoId);
            
            try {
                // Busca modelos - usa APP_URL para funcionar em subdiretórios
                const url = `${window.APP_URL}/admin/documentos/modelos/${tipoId}`;
                console.log('Fazendo requisição para:', url);
                
                const response = await fetch(url);
                console.log('Status da resposta:', response.status);
                
                if (!response.ok) {
                    console.warn('Nenhum modelo encontrado para este tipo de documento (Status:', response.status, ')');
                    return;
                }
                
                this.modelos = await response.json();
                console.log('Modelos carregados:', this.modelos);
                
                // Se houver modelos, carrega o primeiro automaticamente e preenche o editor
                if (this.modelos && this.modelos.length > 0) {
                    console.log('Carregando modelo:', this.modelos[0]);
                    this.conteudo = this.padronizarFonte(this.modelos[0].conteudo);
                    const editor = document.getElementById('editor');
                    if (editor) {
                        editor.innerHTML = this.conteudo;
                        console.log('Modelo carregado no editor com sucesso!');
                        this.salvarAutomaticamente();
                    } else {
                        console.error('Editor não encontrado no DOM');
                    }
                } else {
                    console.log('Nenhum modelo disponível para este tipo de documento');
                }

                // Busca informações de prazo do tipo
                await this.buscarPrazoTipo(tipoId);
                
            } catch (error) {
                console.error('Erro ao carregar modelos:', error);
            }
        },

        atualizarAvisoPrazo(tipoId) {
            if (!tipoId || !tiposDocumentoData[tipoId]) {
                // Esconde todos os avisos
                document.getElementById('aviso-prazo-container').style.display = 'none';
                document.getElementById('aviso-notificacao').style.display = 'none';
                document.getElementById('aviso-licenciamento').style.display = 'none';
                this.isNotificacao = false;
                return;
            }

            const tipoData = tiposDocumentoData[tipoId];
            
            // Mostra o container
            document.getElementById('aviso-prazo-container').style.display = 'block';
            
            // Mostra o aviso correto baseado no tipo
            if (tipoData.prazo_notificacao) {
                // Documento de notificação/fiscalização
                document.getElementById('aviso-notificacao').style.display = 'block';
                document.getElementById('aviso-licenciamento').style.display = 'none';
                this.isNotificacao = true;
            } else {
                // Documento de licenciamento (prazo fixo)
                document.getElementById('aviso-notificacao').style.display = 'none';
                document.getElementById('aviso-licenciamento').style.display = 'block';
                this.isNotificacao = false;
            }
            
            // Recalcula a data de vencimento com a nova informação
            this.calcularDataVencimento();
        },

        contarPalavras() {
            const texto = this.conteudo.replace(/<[^>]*>/g, '').trim();
            return texto.split(/\s+/).filter(word => word.length > 0).length;
        },

        confirmarFinalizacao() {
            // Fecha o modal
            this.modalConfirmarFinalizacao = false;
            this.confirmandoFinalizacao = true;
            
            // Define a ação como finalizar
            document.getElementById('inputAcao').value = 'finalizar';
            
            // Submete o formulário
            document.getElementById('formDocumento').submit();
        },

        handleSubmit(event) {
            // Debug: verifica o conteúdo
            console.log('Conteúdo:', this.conteudo);
            console.log('Tipo selecionado:', this.tipoSelecionado);
            
            const assinaturas = document.querySelectorAll('input[type="hidden"][name="assinaturas[]"]');
            console.log('Assinaturas selecionadas:', assinaturas.length);
            
            if (assinaturas.length === 0) {
                event.preventDefault();
                alert('Selecione pelo menos um usuário para assinar o documento!');
                return false;
            }
            
            if (!this.conteudo || this.conteudo.trim() === '' || this.conteudo === '<p>Selecione um tipo de documento para carregar o modelo ou digite o conteúdo do documento aqui...</p>') {
                event.preventDefault();
                alert('Digite o conteúdo do documento!');
                return false;
            }
            
            // Limpa o localStorage após enviar (tanto rascunho quanto finalizar)
            localStorage.removeItem(this.chaveLocalStorage);
            console.log('localStorage limpo após submissão');
        },

        // Busca informações de prazo do tipo de documento
        async buscarPrazoTipo(tipoId) {
            try {
                const response = await fetch(`${window.APP_URL}/admin/documentos/prazo-tipo/${tipoId}`);
                
                if (!response.ok) {
                    console.warn('Erro ao buscar prazo do tipo');
                    return;
                }
                
                const data = await response.json();
                console.log('Informações de prazo:', data);
                
                this.temPrazo = data.tem_prazo;
                
                // Se tem prazo, preenche os campos
                if (data.tem_prazo) {
                    // Preenche prazo padrão se existir
                    if (data.prazo_padrao_dias) {
                        this.prazoDias = data.prazo_padrao_dias;
                    } else {
                        this.prazoDias = null;
                    }
                    
                    // Preenche tipo de prazo
                    this.tipoPrazo = data.tipo_prazo || 'corridos';
                    
                    // Calcula data de vencimento se tiver prazo
                    if (this.prazoDias) {
                        this.calcularDataVencimento();
                    }
                } else {
                    // Limpa os campos se não tem prazo
                    this.prazoDias = null;
                    this.tipoPrazo = 'corridos';
                    this.dataVencimentoFormatada = '';
                }
                
            } catch (error) {
                console.error('Erro ao buscar prazo do tipo:', error);
            }
        },

        // Calcula a data de vencimento baseada no prazo em dias
        calcularDataVencimento() {
            if (!this.prazoDias || this.prazoDias < 1) {
                this.dataVencimentoFormatada = '';
                return;
            }

            const hoje = new Date();
            let dataInicioPrazo = new Date(hoje);
            
            // Para documentos de notificação (§1º), o prazo começa após 5 dias úteis
            // (ou antes, se o estabelecimento visualizar - mas isso não sabemos agora)
            if (this.isNotificacao) {
                // Adiciona 5 dias úteis para início do prazo
                let diasUteisContados = 0;
                while (diasUteisContados < 5) {
                    dataInicioPrazo.setDate(dataInicioPrazo.getDate() + 1);
                    const diaSemana = dataInicioPrazo.getDay();
                    if (diaSemana !== 0 && diaSemana !== 6) {
                        diasUteisContados++;
                    }
                }
            }
            
            let dataVencimento = new Date(dataInicioPrazo);
            
            if (this.tipoPrazo === 'corridos') {
                // Dias corridos: simplesmente adiciona os dias
                dataVencimento.setDate(dataInicioPrazo.getDate() + parseInt(this.prazoDias));
            } else {
                // Dias úteis: adiciona apenas dias úteis (segunda a sexta)
                let diasRestantes = parseInt(this.prazoDias);
                
                while (diasRestantes > 0) {
                    dataVencimento.setDate(dataVencimento.getDate() + 1);
                    const diaSemana = dataVencimento.getDay();
                    
                    // 0 = Domingo, 6 = Sábado
                    if (diaSemana !== 0 && diaSemana !== 6) {
                        diasRestantes--;
                    }
                }
            }

            // Formata a data em português
            const opcoes = { 
                day: '2-digit', 
                month: 'long', 
                year: 'numeric' 
            };
            this.dataVencimentoFormatada = dataVencimento.toLocaleDateString('pt-BR', opcoes);
        },
    }
}
</script>

{{-- Assistente de Edição de Documento --}}
@include('components.assistente-edicao-documento-chat')

@endsection
