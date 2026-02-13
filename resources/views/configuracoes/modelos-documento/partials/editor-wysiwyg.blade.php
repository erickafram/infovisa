{{-- Editor WYSIWYG para Modelos de Documento --}}
<div x-data="modeloEditor()" class="editor-container">
    {{-- Campo hidden para o conteúdo --}}
    <input type="hidden" name="conteudo" x-model="conteudo">
    
    {{-- Toolbar do Editor --}}
    <div class="border border-gray-300 rounded-t-lg bg-gradient-to-b from-gray-50 to-gray-100 p-2 space-y-2 shadow-sm">
        {{-- Primeira linha: Desfazer, Títulos e Formatação básica --}}
        <div class="flex items-center gap-1.5 flex-wrap">
            {{-- Desfazer/Refazer --}}
            <button type="button" @click="execCommand('undo')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Desfazer (Ctrl+Z)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
            </button>
            <button type="button" @click="execCommand('redo')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Refazer (Ctrl+Y)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/>
                </svg>
            </button>

            <div class="w-px h-7 bg-gray-300 mx-1"></div>

            {{-- Títulos --}}
            <select @change="execCommand('formatBlock', $event.target.value); $event.target.value=''" class="text-sm px-3 py-1.5 border border-gray-300 rounded hover:bg-white hover:shadow transition-all font-medium" title="Estilo">
                <option value="">Estilo</option>
                <option value="h1">Título 1</option>
                <option value="h2">Título 2</option>
                <option value="h3">Título 3</option>
                <option value="h4">Título 4</option>
                <option value="p">Parágrafo</option>
            </select>

            {{-- Tamanho da fonte --}}
            <select @change="execCommand('fontSize', $event.target.value); $event.target.value=''" class="text-sm px-3 py-1.5 border border-gray-300 rounded hover:bg-white hover:shadow transition-all" title="Tamanho">
                <option value="">Tamanho</option>
                <option value="1">8pt</option>
                <option value="2" selected>10pt (Padrão)</option>
                <option value="3">12pt</option>
                <option value="4">14pt</option>
                <option value="5">16pt</option>
                <option value="6">18pt</option>
            </select>

            {{-- Fonte --}}
            <select @change="execCommand('fontName', $event.target.value); $event.target.value=''" class="text-sm px-3 py-1.5 border border-gray-300 rounded hover:bg-white hover:shadow transition-all" title="Fonte">
                <option value="">Fonte</option>
                <option value="Arial">Arial</option>
                <option value="Times New Roman">Times New Roman</option>
                <option value="Courier New">Courier New</option>
                <option value="Georgia">Georgia</option>
                <option value="Verdana">Verdana</option>
                <option value="Tahoma">Tahoma</option>
            </select>

            <div class="w-px h-7 bg-gray-300 mx-1"></div>

            <button type="button" @click="execCommand('bold')" class="p-2 hover:bg-white hover:shadow rounded font-bold text-sm transition-all" title="Negrito">B</button>
            <button type="button" @click="execCommand('italic')" class="p-2 hover:bg-white hover:shadow rounded italic text-sm transition-all" title="Itálico">I</button>
            <button type="button" @click="execCommand('underline')" class="p-2 hover:bg-white hover:shadow rounded underline text-sm transition-all" title="Sublinhado">U</button>
            <button type="button" @click="execCommand('strikeThrough')" class="p-2 hover:bg-white hover:shadow rounded line-through text-sm transition-all" title="Tachado">S</button>

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
                    <button type="button" @click="execCommand('foreColor', '#000000'); showColorPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #000000"></button>
                    <button type="button" @click="execCommand('foreColor', '#FF0000'); showColorPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #FF0000"></button>
                    <button type="button" @click="execCommand('foreColor', '#00FF00'); showColorPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #00FF00"></button>
                    <button type="button" @click="execCommand('foreColor', '#0000FF'); showColorPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #0000FF"></button>
                    <button type="button" @click="execCommand('foreColor', '#FFFF00'); showColorPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #FFFF00"></button>
                    <button type="button" @click="execCommand('foreColor', '#FF00FF'); showColorPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #FF00FF"></button>
                    <button type="button" @click="execCommand('foreColor', '#00FFFF'); showColorPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #00FFFF"></button>
                    <button type="button" @click="execCommand('foreColor', '#808080'); showColorPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #808080"></button>
                </div>
            </div>

            {{-- Cor de fundo --}}
            <div class="relative" x-data="{ showBgPicker: false }">
                <button type="button" @click="showBgPicker = !showBgPicker" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Cor de fundo">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div x-show="showBgPicker" @click.away="showBgPicker = false" class="absolute z-10 mt-1 p-2 bg-white border border-gray-300 rounded-lg shadow-lg grid grid-cols-8 gap-1" style="display: none;">
                    <button type="button" @click="execCommand('backColor', 'transparent'); showBgPicker = false" class="w-6 h-6 rounded border border-gray-300 bg-white"></button>
                    <button type="button" @click="execCommand('backColor', '#FFFF00'); showBgPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #FFFF00"></button>
                    <button type="button" @click="execCommand('backColor', '#00FF00'); showBgPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #00FF00"></button>
                    <button type="button" @click="execCommand('backColor', '#00FFFF'); showBgPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #00FFFF"></button>
                    <button type="button" @click="execCommand('backColor', '#FFC0CB'); showBgPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #FFC0CB"></button>
                    <button type="button" @click="execCommand('backColor', '#FFA500'); showBgPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #FFA500"></button>
                    <button type="button" @click="execCommand('backColor', '#E0E0E0'); showBgPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #E0E0E0"></button>
                    <button type="button" @click="execCommand('backColor', '#D3D3D3'); showBgPicker = false" class="w-6 h-6 rounded border border-gray-300" style="background: #D3D3D3"></button>
                </div>
            </div>
        </div>

        {{-- Segunda linha: Alinhamento, listas e inserções --}}
        <div class="flex items-center gap-1.5 flex-wrap">
            <button type="button" @click="execCommand('justifyLeft')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Alinhar à esquerda">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm0 4h10v2H2V8zm0 4h16v2H2v-2zm0 4h10v2H2v-2z"/></svg>
            </button>
            <button type="button" @click="execCommand('justifyCenter')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Centralizar">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm3 4h10v2H5V8zm-3 4h16v2H2v-2zm3 4h10v2H5v-2z"/></svg>
            </button>
            <button type="button" @click="execCommand('justifyRight')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Alinhar à direita">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm6 4h10v2H8V8zm-6 4h16v2H2v-2zm6 4h10v2H8v-2z"/></svg>
            </button>
            <button type="button" @click="execCommand('justifyFull')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Justificar">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v2H2V4zm0 4h16v2H2V8zm0 4h16v2H2v-2zm0 4h16v2H2v-2z"/></svg>
            </button>

            <div class="w-px h-7 bg-gray-300 mx-1"></div>

            <button type="button" @click="execCommand('insertUnorderedList')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Lista">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h2v2H3V4zm4 0h10v2H7V4zM3 9h2v2H3V9zm4 0h10v2H7V9zm-4 5h2v2H3v-2zm4 0h10v2H7v-2z"/></svg>
            </button>
            <button type="button" @click="execCommand('insertOrderedList')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Lista numerada">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h1v3H3V4zm0 5h1v3H3V9zm0 5h1v3H3v-3zm4-9h10v2H7V5zm0 5h10v2H7v-2zm0 5h10v2H7v-2z"/></svg>
            </button>

            <div class="w-px h-7 bg-gray-300 mx-1"></div>

            <button type="button" @click="execCommand('indent')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Aumentar recuo">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h14v2H3V4zm0 4h14v2H3V8zm0 4h14v2H3v-2zm0 4h14v2H3v-2zM1 8l3 3-3 3V8z"/></svg>
            </button>
            <button type="button" @click="execCommand('outdent')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Diminuir recuo">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h14v2H3V4zm0 4h14v2H3V8zm0 4h14v2H3v-2zm0 4h14v2H3v-2zM7 8L4 11l3 3V8z"/></svg>
            </button>

            <div class="w-px h-7 bg-gray-300 mx-1"></div>

            {{-- Inserir Tabela --}}
            <button type="button" @click="inserirTabela()" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Inserir tabela">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"/>
                </svg>
            </button>

            {{-- Linha horizontal --}}
            <button type="button" @click="execCommand('insertHorizontalRule')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Inserir linha horizontal">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                </svg>
            </button>

            <div class="w-px h-7 bg-gray-300 mx-1"></div>

            {{-- Limpar formatação --}}
            <button type="button" @click="execCommand('removeFormat')" class="p-2 hover:bg-white hover:shadow rounded transition-all" title="Limpar formatação">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="w-px h-7 bg-gray-300 mx-1"></div>

            {{-- Inserir Variável --}}
            <div class="relative" x-data="{ showVars: false }">
                <button type="button" @click="showVars = !showVars" class="px-3 py-1.5 text-sm bg-amber-100 hover:bg-amber-200 text-amber-800 rounded transition-all flex items-center gap-1" title="Inserir variável">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Variáveis
                </button>
                <div x-show="showVars" @click.away="showVars = false" class="absolute z-20 mt-1 w-80 bg-white border border-gray-300 rounded-lg shadow-xl p-2 max-h-80 overflow-y-auto" style="display: none;">
                    <p class="text-xs text-gray-500 mb-2 px-2 font-medium">Clique para inserir no documento:</p>
                    
                    {{-- Estabelecimento --}}
                    <div class="mb-2">
                        <p class="text-xs font-bold text-gray-700 px-2 py-1 bg-gray-100 rounded">📍 Estabelecimento</p>
                        <div class="space-y-0.5 mt-1">
                            <button type="button" @click="inserirVariavel('{estabelecimento_nome}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{estabelecimento_nome}</span>
                                <span class="text-gray-500 text-xs ml-1">- Nome/Fantasia</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{estabelecimento_razao_social}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{estabelecimento_razao_social}</span>
                                <span class="text-gray-500 text-xs ml-1">- Razão Social</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{estabelecimento_cnpj}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{estabelecimento_cnpj}</span>
                                <span class="text-gray-500 text-xs ml-1">- CNPJ</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{estabelecimento_cpf}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{estabelecimento_cpf}</span>
                                <span class="text-gray-500 text-xs ml-1">- CPF</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{estabelecimento_endereco}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{estabelecimento_endereco}</span>
                                <span class="text-gray-500 text-xs ml-1">- Endereço completo</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{estabelecimento_bairro}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{estabelecimento_bairro}</span>
                                <span class="text-gray-500 text-xs ml-1">- Bairro</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{estabelecimento_cidade}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{estabelecimento_cidade}</span>
                                <span class="text-gray-500 text-xs ml-1">- Cidade</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{municipio}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{municipio}</span>
                                <span class="text-gray-500 text-xs ml-1">- Município</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{estabelecimento_telefone}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{estabelecimento_telefone}</span>
                                <span class="text-gray-500 text-xs ml-1">- Telefone</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{estabelecimento_email}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{estabelecimento_email}</span>
                                <span class="text-gray-500 text-xs ml-1">- E-mail</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{atividades}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{atividades}</span>
                                <span class="text-gray-500 text-xs ml-1">- Lista de atividades</span>
                            </button>
                        </div>
                    </div>

                    {{-- Responsável Técnico --}}
                    <div class="mb-2">
                        <p class="text-xs font-bold text-gray-700 px-2 py-1 bg-gray-100 rounded">👤 Responsável Técnico</p>
                        <div class="space-y-0.5 mt-1">
                            <button type="button" @click="inserirVariavel('{responsavel_nome}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{responsavel_nome}</span>
                                <span class="text-gray-500 text-xs ml-1">- Nome</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{responsavel_cpf}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{responsavel_cpf}</span>
                                <span class="text-gray-500 text-xs ml-1">- CPF</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{responsavel_email}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{responsavel_email}</span>
                                <span class="text-gray-500 text-xs ml-1">- E-mail</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{responsavel_conselho}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{responsavel_conselho}</span>
                                <span class="text-gray-500 text-xs ml-1">- Nº Conselho</span>
                            </button>
                        </div>
                    </div>

                    {{-- Processo --}}
                    <div class="mb-2">
                        <p class="text-xs font-bold text-gray-700 px-2 py-1 bg-gray-100 rounded">📄 Processo</p>
                        <div class="space-y-0.5 mt-1">
                            <button type="button" @click="inserirVariavel('{processo_numero}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{processo_numero}</span>
                                <span class="text-gray-500 text-xs ml-1">- Número</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{processo_tipo}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{processo_tipo}</span>
                                <span class="text-gray-500 text-xs ml-1">- Tipo</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{processo_data_criacao}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{processo_data_criacao}</span>
                                <span class="text-gray-500 text-xs ml-1">- Data de criação</span>
                            </button>
                        </div>
                    </div>

                    {{-- Data --}}
                    <div>
                        <p class="text-xs font-bold text-gray-700 px-2 py-1 bg-gray-100 rounded">📅 Data</p>
                        <div class="space-y-0.5 mt-1">
                            <button type="button" @click="inserirVariavel('{data_atual}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{data_atual}</span>
                                <span class="text-gray-500 text-xs ml-1">- Data atual (dd/mm/aaaa)</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{data_extenso}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{data_extenso}</span>
                                <span class="text-gray-500 text-xs ml-1">- Data por extenso</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{data_extenso_maiusculo}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{data_extenso_maiusculo}</span>
                                <span class="text-gray-500 text-xs ml-1">- Data por extenso MAIÚSCULO</span>
                            </button>
                            <button type="button" @click="inserirVariavel('{ano_atual}'); showVars = false" class="w-full text-left px-2 py-1 text-sm hover:bg-amber-50 rounded">
                                <span class="font-mono text-amber-600 text-xs">{ano_atual}</span>
                                <span class="text-gray-500 text-xs ml-1">- Ano atual</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Área do Editor --}}
    <div id="editor-conteudo"
         x-ref="editor"
         contenteditable="true"
         @input="atualizarConteudo()"
         @paste="handlePaste($event)"
         class="border border-t-0 border-gray-300 rounded-b-lg p-4 min-h-[400px] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 prose max-w-none"
         style="font-family: Arial, sans-serif; font-size: 10pt; line-height: 1.6; color: #000;">
    </div>
    
    @error('conteudo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    
    <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Use o botão "Variáveis" para inserir campos dinâmicos que serão substituídos automaticamente ao gerar o documento.
    </p>
</div>

<script>
function modeloEditor() {
    return {
        conteudo: @json(old('conteudo', $conteudoInicial ?? '')),
        
        init() {
            // Carrega o conteúdo inicial no editor
            this.$nextTick(() => {
                if (this.conteudo) {
                    this.$refs.editor.innerHTML = this.conteudo;
                }
            });
        },
        
        execCommand(command, value = null) {
            this.$refs.editor.focus();
            document.execCommand(command, false, value);
            this.atualizarConteudo();
        },
        
        atualizarConteudo() {
            this.conteudo = this.$refs.editor.innerHTML;
        },
        
        inserirVariavel(variavel) {
            this.$refs.editor.focus();
            // Insere variável sem fundo amarelo, mantendo a formatação do texto atual
            document.execCommand('insertHTML', false, `<span style="font-family: inherit; font-size: inherit; color: inherit;">${variavel}</span>`);
            this.atualizarConteudo();
        },
        
        inserirTabela() {
            const linhas = prompt('Número de linhas:', '3');
            const colunas = prompt('Número de colunas:', '3');
            
            if (linhas && colunas) {
                let html = '<table style="width: 100%; border-collapse: collapse; margin: 10px 0;">';
                for (let i = 0; i < parseInt(linhas); i++) {
                    html += '<tr>';
                    for (let j = 0; j < parseInt(colunas); j++) {
                        html += '<td style="border: 1px solid #ccc; padding: 8px;">&nbsp;</td>';
                    }
                    html += '</tr>';
                }
                html += '</table>';
                
                this.$refs.editor.focus();
                document.execCommand('insertHTML', false, html);
                this.atualizarConteudo();
            }
        },
        
        handlePaste(event) {
            // Permite colar mantendo formatação básica
            event.preventDefault();
            const text = event.clipboardData.getData('text/html') || event.clipboardData.getData('text/plain');
            document.execCommand('insertHTML', false, text);
            this.atualizarConteudo();
        }
    }
}
</script>

<style>
#editor-conteudo table {
    width: 100%;
    border-collapse: collapse;
    margin: 10px 0;
}
#editor-conteudo td, #editor-conteudo th {
    border: 1px solid #ccc;
    padding: 8px;
}
#editor-conteudo:focus {
    outline: none;
}
</style>
