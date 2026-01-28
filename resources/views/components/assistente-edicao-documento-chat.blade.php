{{-- Assistente IA para Edição/Criação de Documentos --}}
<style>
/* Estilos do chat de edição */
.assistente-edicao-mensagem {
    font-size: 0.75rem;
    line-height: 1.5;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.assistente-edicao-mensagem p {
    margin-bottom: 0.5rem;
    line-height: 1.5;
    color: #374151;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.assistente-edicao-mensagem strong {
    font-weight: 600;
    color: #1f2937;
}

.assistente-edicao-mensagem ul,
.assistente-edicao-mensagem ol {
    margin-left: 1.25rem;
    margin-bottom: 0.75rem;
    padding-left: 0.25rem;
}

.assistente-edicao-mensagem li {
    margin-bottom: 0.375rem;
    line-height: 1.5;
    color: #374151;
}

[x-cloak] {
    display: none !important;
}
</style>

<div x-data="assistenteEdicaoDocumento()" x-init="init()" x-cloak>
    {{-- Botão Flutuante (só mostra quando chat está fechado) - NO CANTO --}}
    <div x-show="!chatAberto && !minimizado" class="fixed bottom-6 right-6" style="z-index: 10000;">
        <button type="button"
                @click="toggleChat()"
                class="bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-full pl-4 pr-5 py-3 shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-start gap-2"
                style="min-width: 140px;">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span class="text-sm font-medium whitespace-nowrap">Redação</span>
        </button>
    </div>

    {{-- Janela do Chat (minimizado) - MAIS CENTRALIZADO --}}
    <div x-show="minimizado"
         x-transition.duration.300ms
         class="fixed bottom-6 right-20 bg-white rounded-lg shadow-xl border-2 border-green-200 flex flex-col"
         style="z-index: 10000; height: 60px; width: 200px;">
        
        {{-- Header Minimizado --}}
        <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white px-3 py-3 rounded-t-lg flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <h3 class="font-semibold text-xs">Redação</h3>
            </div>
            <div class="flex items-center gap-1">
                {{-- Botão Maximizar --}}
                <button type="button"
                        @click="maximizarChat()" 
                        title="Maximizar"
                        class="hover:bg-white/20 rounded-full p-1 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                </button>
                
                {{-- Botão Fechar --}}
                <button type="button"
                        @click="fecharChat()" 
                        class="hover:bg-white/20 rounded-full p-1 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Janela do Chat (normal) - MAIS CENTRALIZADO --}}
    <div x-show="chatAberto && !minimizado"
         x-transition.duration.300ms
         class="fixed bottom-6 right-20 bg-white rounded-lg shadow-2xl border-2 border-green-200 flex flex-col"
         style="z-index: 10000; height: 450px; width: 340px;">

        {{-- Header Normal --}}
        <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white px-4 py-3 rounded-t-lg flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <h3 class="font-semibold text-sm">Assistente de Redação</h3>
        </div>
        <div class="flex items-center gap-2">
            {{-- Botão Minimizar --}}
            <button type="button"
                    @click="minimizarChat()" 
                    title="Minimizar"
                    class="hover:bg-white/20 rounded-full p-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </button>
                
                {{-- Botão Adicionar Documentos --}}
                <button type="button"
                        @click="mostrarSeletorDocumentos = !mostrarSeletorDocumentos" 
                        title="Adicionar documentos do processo"
                        class="hover:bg-white/20 rounded-full p-1.5 transition-colors relative">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span x-show="documentosCarregados.length > 0" 
                          class="absolute -top-1 -right-1 bg-white text-green-600 text-[9px] font-bold w-3.5 h-3.5 flex items-center justify-center rounded-full border border-green-600"
                          x-text="documentosCarregados.length"></span>
                </button>

                {{-- Toggle Conhecimento Geral --}}
                <div class="flex items-center gap-1.5 bg-white/20 rounded-full px-2 py-1">
                    <button type="button"
                            @click="toggleConhecimentoGeral()" 
                            class="relative w-9 h-5 bg-gray-200 rounded-full transition-colors duration-200"
                            :class="conhecimentoGeral ? 'bg-blue-600' : 'bg-gray-200'"
                            title="Ativar/Desativar busca na internet">
                        <span class="absolute top-[2px] start-[2px] bg-white border-gray-300 border rounded-full h-4 w-4 transition-transform duration-200"
                              :class="conhecimentoGeral ? 'translate-x-full' : 'translate-x-0'"></span>
                    </button>
                    <span class="text-xs cursor-pointer" @click="toggleConhecimentoGeral()">
                        🌐 Web
                    </span>
                </div>
                
                {{-- Botão Limpar --}}
                <button type="button"
                        @click="limparConversa()" 
                        x-show="mensagens.length > 0"
                        title="Limpar conversa"
                        class="hover:bg-white/20 rounded-full p-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                
                {{-- Botão Fechar --}}
                <button type="button"
                        @click="toggleChat()" 
                        class="hover:bg-white/20 rounded-full p-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Seletor de Documentos --}}
        <div x-show="mostrarSeletorDocumentos" 
             x-transition
             class="border-b border-gray-200 bg-white p-3 max-h-48 overflow-y-auto shadow-inner">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-xs font-bold text-gray-700 uppercase">Selecionar Documentos do Processo</h4>
                <button @click="mostrarSeletorDocumentos = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div x-show="documentosDisponiveis.length === 0" class="text-center py-4 text-gray-500 text-xs">
                <p x-show="!processoId">Não foi possível identificar o processo atual.</p>
                <p x-show="processoId">Nenhum documento disponível.</p>
            </div>

            <div class="space-y-1">
                <template x-for="doc in documentosDisponiveis" :key="doc.id">
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer border border-transparent hover:border-gray-200 transition-colors">
                        <input type="checkbox" 
                               :value="doc.id"
                               :checked="documentosSelecionados.includes(doc.id)"
                               @change="toggleDocumento(doc.id)"
                               class="rounded border-gray-300 text-green-600 focus:ring-green-500 w-4 h-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-700 font-medium truncate" x-text="doc.nome"></p>
                            <p class="text-[10px] text-gray-500" x-text="doc.tamanho"></p>
                        </div>
                    </label>
                </template>
            </div>
            
            <div class="mt-3 flex justify-end" x-show="documentosDisponiveis.length > 0">
                <button @click="carregarDocumentosSelecionados()" 
                        :disabled="documentosSelecionados.length === 0 || carregandoMultiplos"
                        class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-1.5 px-3 rounded disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1">
                    <span x-show="carregandoMultiplos" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                    <span x-text="carregandoMultiplos ? 'Carregando...' : 'Carregar Selecionados'"></span>
                </button>
            </div>
        </div>
        
        {{-- Lista de Documentos Carregados (Badge) --}}
        <div x-show="documentosCarregados.length > 0 && !mostrarSeletorDocumentos" class="bg-green-50 px-3 py-1.5 border-b border-green-100 flex flex-col gap-1">
            <div class="flex items-center gap-2 overflow-x-auto whitespace-nowrap">
                <span class="text-[10px] font-bold text-green-700 uppercase flex-shrink-0">Contexto:</span>
                <template x-for="doc in documentosCarregados" :key="doc.documento_id">
                    <span class="inline-flex items-center gap-1 bg-white border border-green-200 rounded px-1.5 py-0.5 text-[10px] text-green-800" :title="doc.nome_documento">
                        <span class="truncate max-w-[100px]" x-text="doc.nome_documento"></span>
                        <button @click="removerDocumento(doc.documento_id)" class="hover:text-red-500 font-bold">×</button>
                    </span>
                </template>
            </div>
            
            {{-- Alerta de Limite de Caracteres --}}
            <div x-show="totalCaracteres > 15000" class="text-[9px] text-amber-600 flex items-center gap-1 font-medium">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span x-text="totalCaracteres > 20000 ? 'Limite excedido! A IA analisará apenas o início dos documentos.' : 'Muitos documentos! A análise pode ser parcial.'"></span>
            </div>
        </div>

        {{-- Mensagens --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3" x-ref="mensagensContainer">
            {{-- Mensagem de boas-vindas --}}
            <div x-show="mensagens.length === 0" class="text-center text-gray-500 text-sm py-8">
                <svg class="w-12 h-12 mx-auto mb-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <p class="font-medium mb-2">✍️ Assistente de Redação</p>
                <p class="text-xs text-gray-400 px-4">
                    Estou aqui para ajudar você a escrever melhor!<br>
                    Posso corrigir português, melhorar textos, sugerir redações e muito mais.
                </p>
                <div class="mt-4 text-xs text-left bg-green-50 p-3 rounded-lg mx-4">
                    <p class="font-semibold text-green-700 mb-2">💡 Exemplos de perguntas:</p>
                    <ul class="space-y-1 text-gray-600">
                        <li>• "Corrija este texto: [seu texto]"</li>
                        <li>• "Melhore a redação deste parágrafo"</li>
                        <li>• "Como posso escrever isso de forma mais formal?"</li>
                        <li>• "Sugira um texto para notificar o estabelecimento"</li>
                    </ul>
                </div>
            </div>

            {{-- Lista de mensagens --}}
            <template x-for="(msg, index) in mensagens" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end mb-4' : 'flex justify-start mb-4'">
                    
                    {{-- Avatar Assistente (só aparece nas mensagens da IA) --}}
                    <div x-show="msg.role !== 'user'" class="flex-shrink-0 mr-2 mt-1">
                        <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    <div :class="msg.role === 'user' 
                                ? 'bg-gradient-to-br from-green-600 to-green-700 text-white rounded-2xl rounded-br-none px-5 py-3.5 max-w-[85%] shadow-sm' 
                                : 'bg-white border border-gray-100 text-gray-800 rounded-2xl rounded-bl-none px-5 py-4 max-w-[90%] shadow-sm assistente-edicao-mensagem relative'">
                        
                        {{-- Conteúdo da Mensagem --}}
                        <div x-html="formatarMensagemComBotao(msg.content, index)" class="prose prose-sm max-w-none" :class="msg.role === 'user' ? 'prose-invert' : ''"></div>
                        
                        {{-- Hora da Mensagem --}}
                        <div class="text-[10px] mt-1.5 flex items-center gap-1" :class="msg.role === 'user' ? 'text-green-100 justify-end' : 'text-gray-400 justify-start'">
                            <svg x-show="msg.role === 'user'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span x-text="msg.time"></span>
                        </div>
                    </div>

                    {{-- Avatar Usuário (só aparece nas mensagens do User) --}}
                    <div x-show="msg.role === 'user'" class="flex-shrink-0 ml-2 mt-1">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Loading --}}
            <div x-show="carregando" class="flex justify-start">
                <div class="bg-gray-100 rounded-lg px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex gap-1">
                            <div class="w-2 h-2 bg-green-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-green-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-green-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                        <span class="text-xs text-gray-500">Pensando...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-200 p-3">
            <form @submit.prevent="enviarMensagem()" class="flex gap-2">
                <input type="text" 
                       x-model="mensagemAtual"
                       placeholder="Digite sua pergunta..."
                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       :disabled="carregando">
                <button type="submit" 
                        :disabled="!mensagemAtual.trim() || carregando"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function assistenteEdicaoDocumento() {
    // Carrega estados ANTES da inicialização para evitar animação
    const estadoSalvo = localStorage.getItem('assistente_edicao_aberto');
    const estadoMinimizado = localStorage.getItem('assistente_edicao_minimizado');
    
    return {
        chatAberto: estadoSalvo === 'true',
        minimizado: estadoMinimizado === 'true',
        mensagens: [],
        mensagemAtual: '',
        textoEditor: '',
        carregando: false,
        conhecimentoGeral: false,
        processoId: null,
        estabelecimentoId: null,
        documentosDisponiveis: [],
        documentosSelecionados: [],
        documentosCarregados: [],
        mostrarSeletorDocumentos: false,
        carregandoMultiplos: false,
        
        get totalCaracteres() {
            return this.documentosCarregados.reduce((total, doc) => total + (doc.conteudo ? doc.conteudo.length : 0), 0);
        },
        
        init() {
            // Inicia minimizado
            this.minimizado = true;
            
            // Tenta pegar IDs da URL ou de meta tags (se disponíveis na página)
            const urlParams = new URLSearchParams(window.location.search);
            this.processoId = urlParams.get('processo_id') || document.querySelector('meta[name="processo-id"]')?.content;
            this.estabelecimentoId = urlParams.get('estabelecimento_id') || document.querySelector('meta[name="estabelecimento-id"]')?.content;
            
            // Se tiver IDs, busca documentos
            if (this.processoId && this.estabelecimentoId) {
                this.buscarDocumentosDisponiveis();
            }
            
            // Recupera histórico do localStorage se houver (opcional)
            const salvo = localStorage.getItem('chat_edicao_historico');
            if (salvo) {
                // Implementar recuperação se desejado
            }
            
            // Escuta eventos de abertura
            window.addEventListener('abrir-chat-edicao', () => {
                this.chatAberto = true;
                this.minimizado = false;
                this.$nextTick(() => this.scrollToBottom());
            });
            
            this.monitorarEditor();
        },
        
        async buscarDocumentosDisponiveis() {
            console.log('Assistente Redação: Buscando documentos...', { processo: this.processoId, estabelecimento: this.estabelecimentoId });
            
            if (!this.processoId || !this.estabelecimentoId) {
                console.warn('Assistente Redação: IDs ausentes');
                return;
            }
            
            try {
                const baseUrl = window.APP_BASE_URL || '';
                const url = `${baseUrl}/admin/ia/documentos-processo/${this.estabelecimentoId}/${this.processoId}`;
                console.log('Assistente Redação: URL busca', url);
                
                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                console.log('Assistente Redação: Resposta busca', data);
                
                if (data.success) {
                    // Remove documentos já carregados da lista
                    const idsCarregados = this.documentosCarregados.map(d => d.documento_id);
                    this.documentosDisponiveis = data.documentos.filter(doc => !idsCarregados.includes(doc.id));
                }
            } catch (error) {
                console.error('Erro ao buscar documentos:', error);
            }
        },
        
        toggleDocumento(docId) {
            const index = this.documentosSelecionados.indexOf(docId);
            if (index > -1) {
                this.documentosSelecionados.splice(index, 1);
            } else {
                this.documentosSelecionados.push(docId);
            }
        },
        
        async carregarDocumentosSelecionados() {
            if (this.documentosSelecionados.length === 0) return;
            
            // Limita a 3 documentos no total
            const totalAposCarregar = this.documentosCarregados.length + this.documentosSelecionados.length;
            if (totalAposCarregar > 3) {
                alert('⚠️ Limite atingido!\n\nVocê pode carregar no máximo 3 documentos por vez.');
                return;
            }
            
            this.carregandoMultiplos = true;
            
            try {
                const response = await fetch('/admin/ia/extrair-multiplos-pdfs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        documento_ids: this.documentosSelecionados,
                        processo_id: this.processoId,
                        estabelecimento_id: this.estabelecimentoId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Adiciona novos documentos aos já carregados
                    data.documentos.forEach(doc => {
                        this.documentosCarregados.push({
                            documento_id: doc.documento_id,
                            nome_documento: doc.nome_documento,
                            conteudo: doc.conteudo
                        });
                    });
                    
                    // Atualiza lista de disponíveis
                    await this.buscarDocumentosDisponiveis();
                    
                    // Limpa seleção
                    this.documentosSelecionados = [];
                    this.mostrarSeletorDocumentos = false;
                    
                    // Adiciona mensagem informativa
                    this.mensagens.push({
                        role: 'assistant',
                        content: `✅ **${data.documentos.length} documento(s) carregado(s)!**\n\nAgora posso usar o conteúdo destes arquivos para auxiliar na redação.`,
                        time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    });
                    
                    this.$nextTick(() => this.scrollToBottom());
                } else {
                    alert(data.message || 'Erro ao carregar documentos');
                }
            } catch (error) {
                console.error('Erro ao carregar documentos:', error);
                alert('Erro ao carregar documentos.');
            } finally {
                this.carregandoMultiplos = false;
            }
        },
        
        removerDocumento(docId) {
            this.documentosCarregados = this.documentosCarregados.filter(d => d.documento_id !== docId);
            this.buscarDocumentosDisponiveis(); // Devolve para a lista de disponíveis
        },
        
        toggleChat() {
            this.chatAberto = !this.chatAberto;
            this.minimizado = false;
            localStorage.setItem('assistente_edicao_aberto', this.chatAberto ? 'true' : 'false');
            localStorage.setItem('assistente_edicao_minimizado', 'false');
            
            if (this.chatAberto) {
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        
        minimizarChat() {
            this.chatAberto = false;
            this.minimizado = true;
            localStorage.setItem('assistente_edicao_aberto', 'false');
            localStorage.setItem('assistente_edicao_minimizado', 'true');
        },
        
        maximizarChat() {
            this.chatAberto = true;
            this.minimizado = false;
            localStorage.setItem('assistente_edicao_aberto', 'true');
            localStorage.setItem('assistente_edicao_minimizado', 'false');
            this.$nextTick(() => this.scrollToBottom());
        },
        
        fecharChat() {
            this.chatAberto = false;
            this.minimizado = false;
            localStorage.setItem('assistente_edicao_aberto', 'false');
            localStorage.setItem('assistente_edicao_minimizado', 'false');
        },
        
        toggleConhecimentoGeral() {
            // Mostra feedback visual quando ativa/desativa
            this.conhecimentoGeral = !this.conhecimentoGeral;
            
            if (this.conhecimentoGeral) {
                this.mensagens.push({
                    role: 'assistant',
                    content: '🌐 **Busca na internet ativada!**\n\nAgora posso pesquisar informações sobre:\n- Modelos de documentos oficiais\n- Exemplos de notificações e ofícios\n- Boas práticas de redação oficial\n- Legislação e normas aplicáveis',
                    time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                });
                this.$nextTick(() => this.scrollToBottom());
            } else {
                this.mensagens.push({
                    role: 'assistant',
                    content: '📚 **Busca na internet desativada.**\n\nAgora vou usar apenas meu conhecimento para ajudar na redação.',
                    time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                });
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        
        monitorarEditor() {
            // Monitora o editor de conteúdo com contenteditable
            const intervalo = setInterval(() => {
                const editorConteudo = document.querySelector('#editor');
                if (editorConteudo) {
                    // Pega o texto puro do editor (sem HTML)
                    this.textoEditor = editorConteudo.innerText || editorConteudo.textContent || '';
                }
            }, 1000);
        },
        
        obterDadosEstabelecimento() {
            // Tenta capturar dados do estabelecimento da página
            const dados = {
                nome: null,
                cnpj: null,
                telefone: null,
                endereco: null,
                processo_numero: null,
                processo_tipo: null
            };
            
            // Tenta pegar da URL (parâmetros)
            const urlParams = new URLSearchParams(window.location.search);
            const processoId = urlParams.get('processo_id');
            
            // Tenta pegar do DOM (se estiver na página de processo/estabelecimento)
            const nomeElement = document.querySelector('[data-estabelecimento-nome]') || 
                               document.querySelector('h1, h2, h3')?.textContent?.includes('SUPERMERCADO') ? 
                               document.querySelector('h1, h2, h3') : null;
            
            // Busca por padrões comuns na página
            const textosPagina = document.body.innerText;
            
            // Tenta extrair CNPJ
            const cnpjMatch = textosPagina.match(/(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2})/);
            if (cnpjMatch) dados.cnpj = cnpjMatch[1];
            
            // Tenta extrair telefone
            const telefoneMatch = textosPagina.match(/\((\d{2})\)\s*(\d{4,5})-(\d{4})/);
            if (telefoneMatch) dados.telefone = telefoneMatch[0];
            
            // Tenta extrair número do processo
            const processoMatch = textosPagina.match(/(\d{4}\/\d{5})/);
            if (processoMatch) dados.processo_numero = processoMatch[1];
            
            // Tenta extrair nome do estabelecimento
            const nomeMatch = textosPagina.match(/Nome do Estabelecimento[\s\n]+([A-Z\s]+)/i) ||
                             textosPagina.match(/SUPERMERCADO [A-Z]+/);
            if (nomeMatch) dados.nome = nomeMatch[1]?.trim() || nomeMatch[0]?.trim();
            
            return dados;
        },
        
        async enviarMensagem() {
            if (!this.mensagemAtual.trim() || this.carregando) return;
            
            const mensagem = this.mensagemAtual.trim();
            this.mensagemAtual = '';
            
            // Adiciona mensagem do usuário
            this.mensagens.push({
                role: 'user',
                content: this.escapeHtml(mensagem),
                time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
            });
            
            this.$nextTick(() => this.scrollToBottom());
            
            this.carregando = true;
            
            // Se busca web está ativa, mostra feedback
            if (this.conhecimentoGeral) {
                this.mensagens.push({
                    role: 'assistant',
                    content: '🔍 **Pesquisando na internet...**\n\nAguarde enquanto busco informações em tempo real na web.',
                    time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                });
                this.$nextTick(() => this.scrollToBottom());
            }
            
            // Captura dados do estabelecimento da página
            const dadosEstabelecimento = this.obterDadosEstabelecimento();
            
            try {
                const response = await fetch('/admin/ia/chat-edicao-documento', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        mensagem: mensagem,
                        historico: this.mensagens.slice(-10).map(msg => ({
                            role: msg.role,
                            content: msg.content.length > 2000 ? msg.content.substring(0, 2000) + '... [truncado]' : msg.content
                        })),
                        texto_atual: this.textoEditor,
                        conhecimento_geral: this.conhecimentoGeral,
                        dados_estabelecimento: dadosEstabelecimento,
                        documentos_contexto: this.documentosCarregados.map(doc => ({
                            nome_documento: doc.nome_documento,
                            conteudo: doc.conteudo
                        }))
                    })
                });
                
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.error || `Erro ${response.status}: Falha ao comunicar com servidor`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (data.resposta) {
                    // Remove mensagem de "Pesquisando..." se existir
                    if (this.conhecimentoGeral && this.mensagens.length > 0) {
                        const ultimaMensagem = this.mensagens[this.mensagens.length - 1];
                        if (ultimaMensagem.role === 'assistant' && ultimaMensagem.content.includes('🔍 **Pesquisando na internet')) {
                            this.mensagens.pop();
                        }
                    }
                    
                    this.mensagens.push({
                        role: 'assistant',
                        content: this.formatarMensagem(data.resposta),
                        time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    });
                } else {
                    throw new Error('Resposta inválida da IA');
                }
            } catch (error) {
                console.error('Erro ao enviar mensagem:', error);
                
                // Remove mensagem de "Pesquisando..." se existir
                if (this.conhecimentoGeral && this.mensagens.length > 0) {
                    const ultimaMensagem = this.mensagens[this.mensagens.length - 1];
                    if (ultimaMensagem.role === 'assistant' && ultimaMensagem.content.includes('🔍 **Pesquisando na internet')) {
                        this.mensagens.pop();
                    }
                }
                
                this.mensagens.push({
                    role: 'assistant',
                    content: `❌ ${error.message || 'Desculpe, ocorreu um erro. Tente novamente.'}`,
                    time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                });
            } finally {
                this.carregando = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        
        limparConversa() {
            if (confirm('Deseja limpar toda a conversa?')) {
                this.mensagens = [];
            }
        },
        
        scrollToBottom() {
            const container = this.$refs.mensagensContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },
        
        formatarMensagem(texto) {
            // Converte markdown básico para HTML
            let html = texto;
            
            // Negrito
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            
            // Listas
            html = html.replace(/^- (.+)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
            
            // Quebras de linha
            html = html.replace(/\n/g, '<br>');
            
            return html;
        },
        
        formatarMensagemComBotao(texto, index) {
            // Primeiro, verifica se há múltiplos blocos TEXTO_CORRIGIDO (para estruturação completa)
            const regexTextosCorrigidos = /```TEXTO_CORRIGIDO(?:\n|<br>)?([\s\S]*?)```/g;
            const matchesTextosCorrigidos = [...texto.matchAll(regexTextosCorrigidos)];
            
            if (matchesTextosCorrigidos.length > 1) {
                // Múltiplos blocos TEXTO_CORRIGIDO encontrados - junta tudo em um único texto
                let mensagemSemCodigo = texto;
                let textoCompleto = [];
                
                matchesTextosCorrigidos.forEach(match => {
                    mensagemSemCodigo = mensagemSemCodigo.replace(match[0], '');
                    let textoCorrigido = match[1].trim();
                    textoCorrigido = textoCorrigido.replace(/<br\s*\/?>/gi, '\n');
                    if (textoCorrigido) {
                        textoCompleto.push(textoCorrigido);
                    }
                });
                
                // Junta todos os blocos com quebra de linha dupla
                let textoFinal = textoCompleto.join('\n\n');
                
                // Formata a mensagem
                let html = this.formatarMensagem(mensagemSemCodigo);
                
                // Adiciona card único com todo o texto estruturado
                html += `
                    <div class="mt-3 p-3 bg-white border-2 border-blue-500 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-blue-700">✅ TEXTO ESTRUTURADO COMPLETO:</span>
                        </div>
                        <div class="text-sm text-gray-800 mb-3 p-2 bg-gray-50 rounded max-h-60 overflow-y-auto" style="white-space: pre-wrap;">${this.escapeHtml(textoFinal)}</div>
                        <button type="button"
                                onclick="window.aplicarCorrecaoNoEditor('${this.escapeForAttribute(textoFinal)}')"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            📝 Aplicar Texto Completo no Editor
                        </button>
                    </div>
                `;
                
                return html;
            }
            
            // Detecta se há múltiplos parágrafos corrigidos (PARAGRAFO_1, PARAGRAFO_2, etc)
            // Aceita com ou sem quebras de linha entre os backticks
            const regexParagrafos = /```PARAGRAFO_(\d+)(?:\n|<br>)?([\s\S]*?)```/g;
            const matchesParagrafos = [...texto.matchAll(regexParagrafos)];
            
            if (matchesParagrafos.length > 0) {
                // Múltiplos parágrafos encontrados
                let mensagemSemCodigo = texto;
                matchesParagrafos.forEach(match => {
                    mensagemSemCodigo = mensagemSemCodigo.replace(match[0], '');
                });
                
                // Formata a mensagem (correções realizadas)
                let html = this.formatarMensagem(mensagemSemCodigo);
                
                // Adiciona cards para cada parágrafo
                matchesParagrafos.forEach(match => {
                    const numParagrafo = match[1];
                    let textoCorrigido = match[2].trim();
                    textoCorrigido = textoCorrigido.replace(/<br\s*\/?>/gi, '\n');
                    
                    // Se não tem erros, mostra mensagem diferente
                    if (textoCorrigido === 'SEM_ERROS') {
                        html += `
                            <div class="mt-3 p-3 bg-gray-50 border-2 border-gray-300 rounded-lg">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-bold text-gray-600">📄 Parágrafo ${numParagrafo}:</span>
                                    <span class="text-xs text-green-600 font-semibold">✓ Sem erros</span>
                                </div>
                            </div>
                        `;
                    } else {
                        // Tem erros, mostra botão para aplicar
                        html += `
                            <div class="mt-3 p-3 bg-white border-2 border-green-500 rounded-lg" id="paragrafo-card-${numParagrafo}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-green-700">✅ Parágrafo ${numParagrafo} corrigido:</span>
                                    <span id="status-paragrafo-${numParagrafo}" class="text-xs font-semibold text-gray-400" style="display: none;">
                                        ✓ Aplicado
                                    </span>
                                </div>
                                <div class="text-sm text-gray-800 mb-3 p-2 bg-gray-50 rounded" style="white-space: pre-wrap;">${this.escapeHtml(textoCorrigido)}</div>
                                <button type="button"
                                        id="btn-paragrafo-${numParagrafo}"
                                        onclick="window.aplicarParagrafoNoEditor(${numParagrafo}, '${this.escapeForAttribute(textoCorrigido)}')"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <span id="texto-btn-paragrafo-${numParagrafo}">📝 Aplicar Parágrafo ${numParagrafo}</span>
                                </button>
                            </div>
                        `;
                    }
                });
                
                return html;
            }
            
            // Se não encontrou múltiplos parágrafos, tenta formato único (TEXTO_CORRIGIDO)
            // Aceita com ou sem quebras de linha entre os backticks
            let regex = /```TEXTO_CORRIGIDO(?:\n|<br>)?([\s\S]*?)```/;
            let match = texto.match(regex);
            
            // Se não encontrar, tenta formato alternativo: TEXTO_CORRIGIDO seguido de quebra e ```
            if (!match) {
                regex = /TEXTO_CORRIGIDO(?:\n|<br>)```(?:\n|<br>)([\s\S]*?)```/;
                match = texto.match(regex);
            }
            
            // Se não encontrar, tenta apenas com ``` no início
            if (!match) {
                regex = /^```(?:\n|<br>)?([\s\S]*?)```/;
                match = texto.match(regex);
            }
            
            if (match) {
                // Texto único corrigido
                let textoCorrigido = match[1].trim();
                textoCorrigido = textoCorrigido.replace(/<br\s*\/?>/gi, '\n');
                
                let mensagemSemCodigo = texto.replace(match[0], '');
                let html = this.formatarMensagem(mensagemSemCodigo);
                
                html += `
                    <div class="mt-3 p-3 bg-white border-2 border-green-500 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-green-700">✅ TEXTO CORRIGIDO:</span>
                        </div>
                        <div class="text-sm text-gray-800 mb-3 p-2 bg-gray-50 rounded" style="white-space: pre-wrap;">${this.escapeHtml(textoCorrigido)}</div>
                        <button type="button"
                                onclick="window.aplicarCorrecaoNoEditor('${this.escapeForAttribute(textoCorrigido)}')"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            📝 Aplicar no Editor
                        </button>
                    </div>
                `;
                
                return html;
            }
            
            // Se não tem correção, formata normalmente
            return this.formatarMensagem(texto);
        },
        
        escapeForAttribute(text) {
            return text
                .replace(/\\/g, '\\\\')
                .replace(/'/g, "\\'")
                .replace(/"/g, '\\"')
                .replace(/\n/g, '\\n')
                .replace(/\r/g, '\\r');
        },
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
}
</script>

<script>
// Função global para aplicar correção completa no editor
window.aplicarCorrecaoNoEditor = function(textoCorrigido) {
    const editor = document.querySelector('#editor');
    
    if (editor) {
        // Substitui o conteúdo do editor
        editor.innerHTML = textoCorrigido.replace(/\n/g, '<br>');
        
        // Dispara evento de input para atualizar o Alpine.js
        editor.dispatchEvent(new Event('input', { bubbles: true }));
        
        // Feedback visual
        editor.style.transition = 'background-color 0.3s';
        editor.style.backgroundColor = '#d1fae5'; // Verde claro
        
        setTimeout(() => {
            editor.style.backgroundColor = '';
        }, 1000);
        
        // Alerta de sucesso
        alert('✅ Texto aplicado no editor com sucesso!');
        
        // Foca no editor
        editor.focus();
    } else {
        alert('❌ Editor não encontrado!');
    }
};

// Função global para aplicar parágrafo específico no editor
window.aplicarParagrafoNoEditor = function(numParagrafo, textoCorrigido) {
    const editor = document.querySelector('#editor');
    
    if (editor) {
        // Pega o conteúdo atual do editor
        let conteudoAtual = editor.innerHTML;
        
        // Remove tags HTML para trabalhar com texto puro
        let textoPuro = conteudoAtual
            .replace(/<br\s*\/?>/gi, '\n')
            .replace(/<\/div>/gi, '\n')
            .replace(/<div[^>]*>/gi, '')
            .replace(/<[^>]+>/g, '')
            .trim();
        
        // Separa em parágrafos de várias formas:
        // 1. Por quebras de linha duplas
        let paragrafos = textoPuro.split(/\n\s*\n/);
        
        // 2. Se não encontrou, tenta por ponto final seguido de espaço e letra maiúscula
        if (paragrafos.length === 1) {
            // Regex para detectar fim de frase: ponto/exclamação/interrogação + espaço + letra maiúscula
            paragrafos = textoPuro.split(/([.!?])\s+(?=[A-ZÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ])/);
            
            // Reconstrói os parágrafos com a pontuação
            let paragrafosReconstruidos = [];
            for (let i = 0; i < paragrafos.length; i += 2) {
                if (paragrafos[i]) {
                    let paragrafo = paragrafos[i];
                    if (paragrafos[i + 1]) {
                        paragrafo += paragrafos[i + 1]; // Adiciona a pontuação de volta
                    }
                    paragrafosReconstruidos.push(paragrafo.trim());
                }
            }
            paragrafos = paragrafosReconstruidos;
        }
        
        // 3. Se ainda não encontrou, tenta por quebra de linha simples
        if (paragrafos.length === 1) {
            paragrafos = textoPuro.split(/\n/);
        }
        
        // Verifica se o número do parágrafo é válido
        const index = numParagrafo - 1;
        if (index >= 0 && index < paragrafos.length) {
            // Substitui o parágrafo específico
            paragrafos[index] = textoCorrigido.trim();
            
            // Reconstrói o conteúdo (junta com quebra de linha dupla)
            editor.innerHTML = paragrafos.join('<br><br>');
            
            // Dispara evento de input
            editor.dispatchEvent(new Event('input', { bubbles: true }));
            
            // Feedback visual no editor
            editor.style.transition = 'background-color 0.3s';
            editor.style.backgroundColor = '#d1fae5';
            
            setTimeout(() => {
                editor.style.backgroundColor = '';
            }, 1000);
            
            // Atualiza visual do botão e card
            const btn = document.getElementById(`btn-paragrafo-${numParagrafo}`);
            const card = document.getElementById(`paragrafo-card-${numParagrafo}`);
            const status = document.getElementById(`status-paragrafo-${numParagrafo}`);
            const textoBotao = document.getElementById(`texto-btn-paragrafo-${numParagrafo}`);
            
            if (btn && card && status && textoBotao) {
                // Muda cor do botão para verde mais escuro (já aplicado)
                btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                btn.classList.add('bg-green-700', 'hover:bg-green-800');
                
                // Adiciona borda verde mais grossa no card
                card.classList.remove('border-green-500');
                card.classList.add('border-green-600', 'border-4');
                
                // Mostra status "Aplicado"
                status.style.display = 'inline';
                status.classList.remove('text-gray-400');
                status.classList.add('text-green-600');
                
                // Muda texto do botão
                textoBotao.innerHTML = '✓ Aplicado - Clique para reaplicar';
                
                // Animação de pulso no status
                status.style.animation = 'pulse 0.5s ease-in-out';
            }
            
            // Alerta de sucesso
            alert(`✅ Parágrafo ${numParagrafo} aplicado com sucesso!`);
            
            // Foca no editor
            editor.focus();
        } else {
            alert(`❌ Parágrafo ${numParagrafo} não encontrado!`);
        }
    } else {
        alert('❌ Editor não encontrado!');
    }
};
</script>

