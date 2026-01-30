@php
    // Verifica se o Chat Interno está ativo nas configurações
    $chatInternoAtivo = \App\Models\ConfiguracaoSistema::where('chave', 'chat_interno_ativo')->first();
    $chatAtivo = $chatInternoAtivo && $chatInternoAtivo->valor === 'true';
@endphp

@if($chatAtivo)
{{-- Chat Interno - Estilo WhatsApp - Otimizado --}}
<div x-data="chatInterno()" x-init="init()" class="fixed bottom-6 z-40" style="right: 100px;">
    
    {{-- Botão do Chat --}}
    <button @click="toggleChat()" 
            class="relative w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center"
            :class="{ 'ring-4 ring-green-200': isOpen }">
        <svg x-show="!isOpen" class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <svg x-show="isOpen" x-cloak class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span x-show="(totalNaoLidas + suporteNaoLidos) > 0 && !isOpen" x-cloak
              class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center animate-pulse"
              x-text="(totalNaoLidas + suporteNaoLidos) > 9 ? '9+' : (totalNaoLidas + suporteNaoLidos)"></span>
    </button>

    {{-- Janela do Chat --}}
    <div x-show="isOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="absolute bottom-16 right-0 w-96 h-[520px] bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3 flex items-center gap-3 flex-shrink-0">
            <button x-show="conversaAtual || suporteAberto" @click="voltarLista()" class="p-1 hover:bg-white/20 rounded-full transition">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
            {{-- Header Lista --}}
            <div x-show="!conversaAtual && !suporteAberto" class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold">Chat Interno</h3>
                    <p class="text-white/70 text-xs" x-text="usuariosOnline + ' online'"></p>
                </div>
            </div>

            {{-- Header Suporte --}}
            <div x-show="suporteAberto" class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold">Suporte InfoVISA</h3>
                    <p class="text-white/70 text-xs">Avisos do sistema</p>
                </div>
            </div>
            
            {{-- Header Conversa --}}
            <div x-show="conversaAtual && !suporteAberto" class="flex items-center gap-3 flex-1">
                <div class="relative">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-sm" x-text="conversaAtual?.iniciais || ''"></span>
                    </div>
                    <span x-show="conversaAtual?.online" class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-green-600 rounded-full"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-white font-semibold truncate" x-text="conversaAtual?.nome || ''"></h3>
                    <p class="text-white/70 text-xs" x-text="(conversaAtual?.tipo || '') + (conversaAtual?.municipio ? ' • ' + conversaAtual.municipio : '')"></p>
                </div>
            </div>
        </div>

        {{-- Conteúdo --}}
        <div class="flex-1 overflow-hidden flex flex-col">
            {{-- Lista de Conversas/Usuários --}}
            <div x-show="!conversaAtual && !suporteAberto" class="flex-1 overflow-y-auto flex flex-col">
                {{-- Suporte InfoVISA (fixo no topo) --}}
                <button @click="abrirSuporte()" class="w-full p-3 flex items-center gap-3 hover:bg-gray-50 transition text-left border-b border-gray-100 bg-green-50">
                    <div class="relative flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-gray-900">Suporte InfoVISA</span>
                        <p class="text-sm text-gray-500">Avisos e comunicados</p>
                    </div>
                    <span x-show="suporteNaoLidos > 0" class="w-5 h-5 bg-green-500 text-white text-xs font-bold rounded-full flex items-center justify-center" x-text="suporteNaoLidos"></span>
                </button>

                {{-- Tabs --}}
                <div class="flex border-b border-gray-200 bg-gray-50 flex-shrink-0">
                    <button @click="tab = 'conversas'" :class="tab === 'conversas' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500'" class="flex-1 py-2 text-sm font-medium border-b-2 transition">Conversas</button>
                    <button @click="tab = 'usuarios'; carregarUsuarios()" :class="tab === 'usuarios' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500'" class="flex-1 py-2 text-sm font-medium border-b-2 transition">Usuários</button>
                </div>

                {{-- Busca de Usuários --}}
                <div x-show="tab === 'usuarios'" class="p-2 border-b border-gray-100 flex-shrink-0">
                    <div class="relative">
                        <input type="text" x-model="buscaUsuario" @input.debounce.200ms="buscarUsuarios()" placeholder="Buscar usuário..." class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-full focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Lista de Conversas --}}
                <div x-show="tab === 'conversas'" class="flex-1 overflow-y-auto divide-y divide-gray-100">
                    <div x-show="loading && conversas.length === 0" class="p-8 text-center text-gray-500">
                        <svg class="w-8 h-8 mx-auto text-gray-300 animate-spin mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <p class="text-sm">Carregando...</p>
                    </div>
                    <div x-show="!loading && conversas.length === 0" class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <p class="text-sm">Nenhuma conversa ainda</p>
                    </div>
                    <template x-for="conv in conversas" :key="conv.id">
                        <button @click="abrirConversa(conv)" class="w-full p-3 flex items-center gap-3 hover:bg-gray-50 transition text-left">
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-gray-400 to-gray-500 rounded-full flex items-center justify-center"><span class="text-white font-semibold" x-text="conv.iniciais"></span></div>
                                <span x-show="conv.online" class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between"><span class="font-medium text-gray-900 truncate" x-text="conv.nome"></span><span class="text-xs text-gray-400" x-text="conv.ultima_mensagem?.data || ''"></span></div>
                                <div class="flex items-center justify-between mt-0.5"><p class="text-sm text-gray-500 truncate" x-text="conv.ultima_mensagem?.conteudo || 'Sem mensagens'"></p><span x-show="conv.nao_lidas > 0" class="ml-2 w-5 h-5 bg-green-500 text-white text-xs font-bold rounded-full flex items-center justify-center" x-text="conv.nao_lidas"></span></div>
                                <p class="text-xs text-gray-400 mt-0.5" x-text="conv.tipo"></p>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Lista de Usuários --}}
                <div x-show="tab === 'usuarios'" class="flex-1 overflow-y-auto divide-y divide-gray-100">
                    <div x-show="loading && usuarios.length === 0" class="p-8 text-center text-gray-500">
                        <svg class="w-8 h-8 mx-auto text-gray-300 animate-spin mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <p class="text-sm">Carregando...</p>
                    </div>
                    <template x-for="usuario in usuarios" :key="usuario.id">
                        <button @click="iniciarConversa(usuario)" class="w-full p-3 flex items-center gap-3 hover:bg-gray-50 transition text-left">
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-500 rounded-full flex items-center justify-center"><span class="text-white font-semibold" x-text="usuario.iniciais"></span></div>
                                <span x-show="usuario.online" class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-gray-900 truncate block" x-text="usuario.nome"></span>
                                <p class="text-xs text-gray-500" x-text="usuario.tipo + (usuario.municipio ? ' • ' + usuario.municipio : '')"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Mensagens do Suporte --}}
            <div x-show="suporteAberto" class="flex-1 flex flex-col overflow-hidden">
                <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-[#e5ddd5]">
                    <template x-for="msg in suporteMensagens" :key="msg.id">
                        <div class="flex justify-start">
                            <div class="bg-white max-w-[85%] px-3 py-2 rounded-lg shadow">
                                <div x-show="msg.tipo === 'texto'" class="text-sm whitespace-pre-wrap break-words text-gray-800" x-text="msg.conteudo"></div>
                                <img x-show="msg.tipo === 'imagem'" :src="msg.arquivo_url" class="max-w-full rounded">
                                <a x-show="msg.tipo === 'arquivo'" :href="msg.arquivo_url" target="_blank" class="flex items-center gap-2 text-blue-600 hover:underline text-sm" x-text="'📎 ' + (msg.arquivo_nome || 'Arquivo')"></a>
                                <p class="text-[10px] text-gray-500 text-right mt-1" x-text="msg.data"></p>
                            </div>
                        </div>
                    </template>
                    <div x-show="suporteMensagens.length === 0" class="text-center text-gray-500 py-8"><p class="text-sm">Nenhum aviso do suporte</p></div>
                </div>
                <div class="p-3 bg-gray-100 border-t text-center text-xs text-gray-500">Este canal é apenas para avisos. Não é possível enviar mensagens.</div>
            </div>

            {{-- Área de Mensagens --}}
            <div x-show="conversaAtual && !suporteAberto" class="flex-1 flex flex-col overflow-hidden">
                <div x-ref="mensagensContainer" class="flex-1 overflow-y-auto p-3 space-y-2 bg-[#e5ddd5]">
                    <div x-show="loadingMensagens" class="text-center py-4">
                        <svg class="w-6 h-6 mx-auto text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    <template x-for="msg in mensagens" :key="msg.id">
                        <div :class="msg.minha ? 'flex justify-end' : 'flex justify-start'" class="group">
                            <div :class="msg.minha ? 'bg-[#dcf8c6]' : 'bg-white'" class="max-w-[80%] px-3 py-2 rounded-lg shadow relative">
                                <button x-show="msg.minha && msg.pode_deletar && !msg.deletada" @click="apagarMensagem(msg.id)" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-xs hover:bg-red-600" title="Apagar (até 30 min)">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                <div x-show="msg.deletada" class="text-sm italic text-gray-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    Mensagem apagada
                                </div>
                                <div x-show="!msg.deletada">
                                    <div x-show="msg.tipo === 'texto'" class="text-sm whitespace-pre-wrap break-words text-gray-800" x-text="msg.conteudo"></div>
                                    <img x-show="msg.tipo === 'imagem'" :src="msg.arquivo_url" class="max-w-full rounded cursor-pointer" @click="window.open(msg.arquivo_url, '_blank')">
                                    <audio x-show="msg.tipo === 'audio'" :src="msg.arquivo_url" controls class="max-w-full"></audio>
                                    <a x-show="msg.tipo === 'arquivo'" :href="msg.arquivo_url" target="_blank" class="flex items-center gap-2 text-blue-600 hover:underline text-sm" x-text="'📎 ' + (msg.arquivo_nome || 'Arquivo')"></a>
                                </div>
                                <div class="flex items-center justify-end gap-1 mt-1">
                                    <span class="text-[10px] text-gray-500" x-text="msg.data"></span>
                                    <span x-show="msg.minha && !msg.deletada" class="flex items-center">
                                        <svg x-show="msg.lida" class="w-4 h-4 text-[#53bdeb]" viewBox="0 0 16 15" fill="currentColor"><path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.88a.32.32 0 0 1-.484.032l-.358-.325a.32.32 0 0 0-.484.032l-.378.48a.418.418 0 0 0 .036.54l1.32 1.267a.32.32 0 0 0 .484-.034l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.88a.32.32 0 0 1-.484.032L1.892 7.77a.366.366 0 0 0-.516.005l-.423.433a.364.364 0 0 0 .006.514l3.255 3.185a.32.32 0 0 0 .484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"/></svg>
                                        <svg x-show="!msg.lida && msg.entregue" class="w-4 h-4 text-gray-400" viewBox="0 0 16 15" fill="currentColor"><path d="M15.01 3.316l-.478-.372a.365.365 0 0 0-.51.063L8.666 9.88a.32.32 0 0 1-.484.032l-.358-.325a.32.32 0 0 0-.484.032l-.378.48a.418.418 0 0 0 .036.54l1.32 1.267a.32.32 0 0 0 .484-.034l6.272-8.048a.366.366 0 0 0-.064-.512zm-4.1 0l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.88a.32.32 0 0 1-.484.032L1.892 7.77a.366.366 0 0 0-.516.005l-.423.433a.364.364 0 0 0 .006.514l3.255 3.185a.32.32 0 0 0 .484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"/></svg>
                                        <svg x-show="!msg.lida && !msg.entregue" class="w-4 h-4 text-gray-400" viewBox="0 0 16 15" fill="currentColor"><path d="M10.91 3.316l-.478-.372a.365.365 0 0 0-.51.063L4.566 9.88a.32.32 0 0 1-.484.032L1.892 7.77a.366.366 0 0 0-.516.005l-.423.433a.364.364 0 0 0 .006.514l3.255 3.185a.32.32 0 0 0 .484-.033l6.272-8.048a.365.365 0 0 0-.063-.51z"/></svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Input de Mensagem --}}
                <div class="p-3 bg-gray-100 border-t border-gray-200 flex-shrink-0">
                    <form @submit.prevent="enviarMensagem()" class="flex items-end gap-2">
                        <label class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-200 rounded-full cursor-pointer transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <input type="file" class="hidden" @change="enviarArquivo($event)" accept="image/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx">
                        </label>
                        <div class="flex-1">
                            <input type="text" x-model="novaMensagem" @keydown.enter.prevent="enviarMensagem()" placeholder="Digite uma mensagem..." class="w-full px-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm bg-white">
                        </div>
                        <button type="submit" :disabled="!novaMensagem.trim() || enviando" class="p-2 bg-green-500 text-white rounded-full hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <svg x-show="!enviando" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <svg x-show="enviando" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function chatInterno() {
    return {
        isOpen: false,
        tab: 'conversas',
        conversas: [],
        usuarios: [],
        mensagens: [],
        conversaAtual: null,
        novaMensagem: '',
        totalNaoLidas: 0,
        usuariosOnline: 0,
        pollingInterval: null,
        ultimaMensagemId: 0,
        buscaUsuario: '',
        suporteAberto: false,
        suporteMensagens: [],
        suporteNaoLidos: 0,
        loading: false,
        loadingMensagens: false,
        enviando: false,
        cache: { conversas: null, conversasTime: 0 },

        init() {
            this.carregarConversas();
            this.pollingInterval = setInterval(() => this.verificarNovas(), 1500);
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) this.carregarConversas();
        },

        async carregarConversas() {
            const now = Date.now();
            if (this.cache.conversas && (now - this.cache.conversasTime) < 1000) return;
            this.loading = true;
            try {
                const r = await fetch('{{ route("admin.chat.conversas") }}');
                const data = await r.json();
                this.conversas = data;
                this.totalNaoLidas = data.reduce((t, c) => t + c.nao_lidas, 0);
                this.cache.conversas = data;
                this.cache.conversasTime = now;
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        async carregarUsuarios() {
            this.loading = true;
            try {
                const r = await fetch('{{ route("admin.chat.usuarios") }}');
                const data = await r.json();
                this.usuarios = data;
                this.usuariosOnline = data.filter(u => u.online).length;
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        async buscarUsuarios() {
            try {
                const url = this.buscaUsuario ? `{{ route("admin.chat.usuarios.buscar") }}?q=${encodeURIComponent(this.buscaUsuario)}` : '{{ route("admin.chat.usuarios") }}';
                const r = await fetch(url);
                this.usuarios = await r.json();
            } catch (e) { console.error(e); }
        },

        async abrirConversa(conv) {
            this.suporteAberto = false;
            this.conversaAtual = { id: conv.id, usuario_id: conv.usuario_id, nome: conv.nome, iniciais: conv.iniciais, tipo: conv.tipo, municipio: conv.municipio, online: conv.online };
            await this.carregarMensagens(conv.usuario_id);
        },

        async iniciarConversa(usuario) {
            this.suporteAberto = false;
            this.conversaAtual = { usuario_id: usuario.id, nome: usuario.nome, iniciais: usuario.iniciais, tipo: usuario.tipo, municipio: usuario.municipio, online: usuario.online };
            await this.carregarMensagens(usuario.id);
        },

        async carregarMensagens(usuarioId) {
            this.loadingMensagens = true;
            try {
                const r = await fetch(`{{ url('/admin/chat/mensagens') }}/${usuarioId}`);
                const data = await r.json();
                this.mensagens = data.mensagens || [];
                if (this.conversaAtual) this.conversaAtual.id = data.conversa_id;
                if (this.mensagens.length > 0) this.ultimaMensagemId = this.mensagens[this.mensagens.length - 1].id;
                this.$nextTick(() => this.scrollToBottom());
                this.carregarConversas();
            } catch (e) { console.error(e); }
            this.loadingMensagens = false;
        },

        async abrirSuporte() {
            this.conversaAtual = null;
            this.suporteAberto = true;
            try {
                const r = await fetch('{{ route("admin.chat.suporte.mensagens") }}');
                const data = await r.json();
                this.suporteMensagens = data.mensagens || [];
                this.suporteNaoLidos = 0;
            } catch (e) { console.error(e); }
        },

        async verificarSuporteNaoLidos() {
            try {
                const r = await fetch('{{ route("admin.chat.suporte.nao-lidos") }}');
                const data = await r.json();
                this.suporteNaoLidos = data.nao_lidos || 0;
            } catch (e) { console.error(e); }
        },

        voltarLista() {
            this.conversaAtual = null;
            this.suporteAberto = false;
            this.mensagens = [];
            this.ultimaMensagemId = 0;
            this.carregarConversas();
            this.verificarSuporteNaoLidos();
        },

        async enviarMensagem() {
            if (!this.novaMensagem.trim() || !this.conversaAtual || this.enviando) return;
            const texto = this.novaMensagem.trim();
            this.novaMensagem = '';
            this.enviando = true;
            const tempId = 'temp_' + Date.now();
            const msgTemp = { id: tempId, conteudo: texto, tipo: 'texto', minha: true, data: new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'}), lida: false, entregue: false, deletada: false, pode_deletar: true };
            this.mensagens.push(msgTemp);
            this.$nextTick(() => this.scrollToBottom());
            try {
                const fd = new FormData();
                fd.append('usuario_id', this.conversaAtual.usuario_id);
                fd.append('conteudo', texto);
                const r = await fetch('{{ route("admin.chat.enviar") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: fd });
                const data = await r.json();
                const idx = this.mensagens.findIndex(m => m.id === tempId);
                if (idx !== -1 && data.success && data.mensagem) {
                    this.mensagens[idx] = data.mensagem;
                    this.ultimaMensagemId = data.mensagem.id;
                }
            } catch (e) { console.error(e); this.mensagens = this.mensagens.filter(m => m.id !== tempId); }
            this.enviando = false;
        },

        async enviarArquivo(event) {
            const file = event.target.files[0];
            if (!file || !this.conversaAtual) return;
            this.enviando = true;
            try {
                const fd = new FormData();
                fd.append('usuario_id', this.conversaAtual.usuario_id);
                fd.append('arquivo', file);
                const r = await fetch('{{ route("admin.chat.enviar") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: fd });
                const data = await r.json();
                if (data.success && data.mensagem) {
                    this.mensagens.push(data.mensagem);
                    this.ultimaMensagemId = data.mensagem.id;
                    this.$nextTick(() => this.scrollToBottom());
                }
            } catch (e) { console.error(e); }
            this.enviando = false;
            event.target.value = '';
        },

        async apagarMensagem(msgId) {
            if (!confirm('Apagar esta mensagem para todos?')) return;
            try {
                const r = await fetch(`{{ url('/admin/chat/mensagem') }}/${msgId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                const data = await r.json();
                if (data.success) {
                    const idx = this.mensagens.findIndex(m => m.id === msgId);
                    if (idx !== -1) { this.mensagens[idx].deletada = true; this.mensagens[idx].conteudo = null; this.mensagens[idx].pode_deletar = false; }
                } else { alert(data.error || 'Erro ao apagar mensagem'); }
            } catch (e) { console.error(e); alert('Erro ao apagar mensagem'); }
        },

        async verificarNovas() {
            try {
                const params = new URLSearchParams({ ultima_id: this.ultimaMensagemId });
                
                // Se está em conversa, adiciona parâmetros
                if (this.isOpen && this.conversaAtual) {
                    params.append('usuario_id', this.conversaAtual.usuario_id);
                    // Envia IDs das mensagens para verificar deletadas (máx 50)
                    const msgIds = this.mensagens.filter(m => !m.deletada && typeof m.id === 'number').slice(-50).map(m => m.id);
                    if (msgIds.length > 0) {
                        msgIds.forEach(id => params.append('msg_ids[]', id));
                    }
                }
                
                const r = await fetch(`{{ route("admin.chat.verificar-novas") }}?${params}`);
                const data = await r.json();
                
                // Atualiza contadores (sempre)
                this.totalNaoLidas = data.total_nao_lidas || 0;
                if (data.suporte_nao_lidos !== undefined) {
                    this.suporteNaoLidos = data.suporte_nao_lidos;
                }
                
                // Se chat fechado, para aqui
                if (!this.isOpen) return;
                
                // Se em conversa, processa mensagens
                if (this.conversaAtual) {
                    if (data.outro_online !== undefined) this.conversaAtual.online = data.outro_online;
                    
                    if (data.novas_mensagens && data.novas_mensagens.length > 0) {
                        for (const msg of data.novas_mensagens) {
                            if (!this.mensagens.find(m => m.id === msg.id)) this.mensagens.push(msg);
                        }
                        this.ultimaMensagemId = data.novas_mensagens[data.novas_mensagens.length - 1].id;
                        this.$nextTick(() => this.scrollToBottom());
                    }
                    
                    if (data.mensagens_lidas && data.mensagens_lidas.length > 0) {
                        for (let i = 0; i < this.mensagens.length; i++) {
                            if (this.mensagens[i].minha && data.mensagens_lidas.includes(this.mensagens[i].id)) {
                                this.mensagens[i].lida = true;
                                this.mensagens[i].entregue = true;
                            }
                        }
                    }
                    
                    if (data.mensagens_deletadas && data.mensagens_deletadas.length > 0) {
                        for (let i = 0; i < this.mensagens.length; i++) {
                            if (data.mensagens_deletadas.includes(this.mensagens[i].id) && !this.mensagens[i].deletada) {
                                this.mensagens[i].deletada = true;
                                this.mensagens[i].tipo = 'deletada';
                                this.mensagens[i].conteudo = null;
                                this.mensagens[i].arquivo_url = null;
                                this.mensagens[i].pode_deletar = false;
                            }
                        }
                    }
                }
            } catch (e) { /* silencioso */ }
        },

        scrollToBottom() {
            const container = this.$refs.mensagensContainer;
            if (container) container.scrollTop = container.scrollHeight;
        }
    };
}
</script>@endif