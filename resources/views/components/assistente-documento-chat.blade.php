{{-- Assistente IA para Documentos PDF --}}
<style>
/* Estilos do chat de documentos */
.assistente-documento-mensagem {
    font-size: 0.8125rem; /* 13px - fonte menor e mais compacta */
    line-height: 1.6;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.assistente-documento-mensagem p {
    margin-bottom: 0.75rem;
    line-height: 1.6;
    color: #374151;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.assistente-documento-mensagem strong {
    font-weight: 600;
    color: #1f2937;
}

.assistente-documento-mensagem ul,
.assistente-documento-mensagem ol {
    margin-left: 1.25rem;
    margin-bottom: 0.75rem;
    padding-left: 0.25rem;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.assistente-documento-mensagem li {
    margin-bottom: 0.5rem;
    line-height: 1.6;
    color: #374151;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.assistente-documento-mensagem code {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.75rem;
    border: 1px solid #e5e7eb;
}

.assistente-documento-mensagem h3 {
    color: #7c3aed;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.assistente-documento-mensagem h4 {
    color: #6b7280;
    margin-bottom: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 600;
}

/* Melhorias no layout das mensagens */
.assistente-documento-mensagem {
    max-width: 95%; /* Mais espaço para texto */
}

/* Destaque para categorias em roxo */
.assistente-documento-mensagem .categoria {
    color: #7c3aed;
    font-weight: 600;
    font-size: 0.8125rem;
    margin-bottom: 0.75rem;
    display: block;
}

/* Melhorias adicionais para legibilidade */
.assistente-documento-mensagem br {
    line-height: 1.8;
}

/* Espaçamento melhor entre parágrafos */
.assistente-documento-mensagem p:first-child {
    margin-top: 0;
}

.assistente-documento-mensagem p:last-child {
    margin-bottom: 0;
}

/* Esconde elementos antes da inicialização do Alpine.js */
[x-cloak] {
    display: none !important;
}
</style>

<div x-data="assistenteDocumento()" x-init="init()" class="fixed bottom-6 right-6" style="z-index: 10000; width: 380px;" x-cloak>
    {{-- Botão Flutuante (quando minimizado) --}}
    <button x-show="!chatAberto && documentosCarregados.length > 0"
            @click="chatAberto = true"
            x-transition.duration.300ms
            class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 p-3 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span class="text-xs font-medium">Assistente</span>
    </button>
    
    {{-- Janela de Chat do Documento --}}
    <div x-show="chatAberto"
         x-transition.duration.300ms
         class="bg-white rounded-2xl shadow-2xl border-2 border-purple-200 overflow-hidden flex flex-col"
         style="height: 550px;">
        
        {{-- Cabeçalho --}}
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-base">Assistente de Documento</h3>
                    <p class="text-[10px] text-white/80" x-show="documentosCarregados.length > 0" x-text="documentosCarregados.length === 1 ? `📄 ${documentosCarregados[0].nome_documento}` : `📄 ${documentosCarregados.length} documentos carregados`"></p>
                    <div class="flex items-center mt-1" x-show="documentosCarregados.length > 0">
                        <label class="relative inline-flex items-center cursor-pointer" title="Ativa conhecimento geral da IA (pode estar desatualizado)">
                            <input type="checkbox" x-model="buscarInternet" @change="mostrarAvisoBuscaInternet()" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ms-2 text-xs font-medium text-white/90">Conhecimento geral</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                {{-- Botão Adicionar Documentos --}}
                <button @click="mostrarSeletorDocumentos = !mostrarSeletorDocumentos" 
                        x-show="documentosCarregados.length > 0"
                        title="Adicionar mais documentos"
                        class="hover:bg-white/20 rounded-full p-1.5 transition-colors">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
                
                {{-- Botão Limpar Conversa --}}
                <button @click="limparConversa()" 
                        title="Limpar conversa"
                        class="hover:bg-white/20 rounded-full p-1.5 transition-colors">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
                
                {{-- Botão Minimizar --}}
                <button @click="chatAberto = false" 
                        title="Minimizar"
                        class="hover:bg-white/20 rounded-full p-1.5 transition-colors">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                {{-- Botão Fechar --}}
                <button @click="fecharChat()" 
                        title="Fechar assistente"
                        class="hover:bg-white/20 rounded-full p-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Seletor de Documentos --}}
        <div x-show="mostrarSeletorDocumentos" 
             x-transition
             class="border-b border-gray-200 bg-white p-3 max-h-48 overflow-y-auto">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-semibold text-gray-700">Selecionar Documentos do Processo</h4>
                <button @click="mostrarSeletorDocumentos = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="space-y-1">
                <template x-for="doc in documentosDisponiveis" :key="doc.id">
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input type="checkbox" 
                               :value="doc.id"
                               :checked="documentosSelecionados.includes(doc.id)"
                               @change="toggleDocumento(doc.id)"
                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-xs text-gray-700 flex-1" x-text="doc.nome"></span>
                        <span class="text-[10px] text-gray-500" x-text="doc.tamanho"></span>
                    </label>
                </template>
            </div>
            <button @click="carregarDocumentosSelecionados()" 
                    :disabled="documentosSelecionados.length === 0 || carregandoMultiplos"
                    class="mt-3 w-full px-3 py-2 bg-purple-600 text-white text-xs font-medium rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <svg x-show="!carregandoMultiplos" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <div x-show="carregandoMultiplos" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                <span x-text="carregandoMultiplos ? 'Carregando...' : `Carregar ${documentosSelecionados.length} documento(s)`"></span>
            </button>
        </div>
        
        {{-- Área de Mensagens --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50" x-ref="mensagensContainer">
            <template x-for="(mensagem, index) in mensagens" :key="index">
                <div :class="mensagem.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="mensagem.role === 'user' 
                                ? 'bg-purple-600 text-white rounded-2xl rounded-br-sm px-3 py-2.5 max-w-[95%] shadow-md' 
                                : 'bg-white text-gray-800 rounded-2xl rounded-bl-sm px-3 py-2.5 max-w-[95%] shadow-md border border-gray-200'">
                        <div class="assistente-documento-mensagem" x-html="formatarMensagem(mensagem.content)"></div>
                        <div class="text-[10px] mt-1.5 opacity-70" x-text="mensagem.time"></div>
                    </div>
                </div>
            </template>
            
            {{-- Loading --}}
            <div x-show="carregando" class="flex justify-start">
                <div class="bg-white text-gray-800 rounded-2xl rounded-bl-sm px-3 py-2.5 shadow-md border border-gray-200">
                    <div class="flex items-center gap-2">
                        <div class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-purple-600"></div>
                        <span class="text-xs">Analisando documento...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input de Mensagem --}}
        <div class="border-t border-gray-200 p-3 bg-white">
            <form @submit.prevent="enviarMensagem()" class="flex gap-2">
                <textarea x-model="mensagemAtual"
                          placeholder="Pergunte sobre o documento..."
                          class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm resize-none"
                          rows="2"
                          :disabled="carregando"
                          @keydown="handleKeyDown($event)"></textarea>
                <button type="submit" 
                        :disabled="!mensagemAtual.trim() || carregando"
                        class="bg-purple-600 text-white p-2 rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function assistenteDocumento() {
    return {
        chatAberto: false,
        mensagens: [],
        mensagemAtual: '',
        carregando: false,
        buscarInternet: false,
        documentosCarregados: [],
        documentosDisponiveis: [],
        documentosSelecionados: [],
        mostrarSeletorDocumentos: false,
        carregandoMultiplos: false,
        processoId: null,
        estabelecimentoId: null,
        
        init() {
            // Escuta evento de documento carregado
            window.addEventListener('documento-carregado', (event) => {
                // Adiciona documento aos carregados
                this.documentosCarregados = [{
                    documento_id: event.detail.documento_id,
                    nome_documento: event.detail.nome_documento,
                    conteudo: event.detail.conteudo,
                    total_caracteres: event.detail.total_caracteres
                }];
                
                // Salva IDs do processo
                this.processoId = event.detail.processo_id;
                this.estabelecimentoId = event.detail.estabelecimento_id;
                
                // Busca documentos disponíveis do processo
                this.buscarDocumentosDisponiveis();
                
                // NÃO abre o chat automaticamente - usuário deve clicar no botão Assistente
                // this.chatAberto = true;
                
                // Limpa mensagens anteriores
                this.mensagens = [];
                
                // Adiciona mensagem de boas-vindas
                this.mensagens.push({
                    role: 'assistant',
                    content: `📄 **Documento carregado com sucesso!**\n\n` +
                             `**${event.detail.nome_documento}**\n` +
                             `${event.detail.total_caracteres.toLocaleString('pt-BR')} caracteres extraídos\n\n` +
                             `✅ **Pronto para responder suas perguntas!**\n\n` +
                             `💡 **Dica:** Clique no botão **+** no cabeçalho para adicionar mais documentos à análise.\n\n` +
                             `**Sugestões:**\n` +
                             `- O que esse documento fala?\n` +
                             `- Qual o resumo?\n` +
                             `- Quais os pontos principais?\n` +
                             `- Me explique o conteúdo`,
                    time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                });
                
                this.scrollToBottom();
            });
            
            // Escuta evento de fechamento do modal PDF
            window.addEventListener('pdf-modal-fechado', () => {
                this.fecharChat();
            });
        },
        
        async enviarMensagem() {
            if (!this.mensagemAtual.trim() || this.carregando) return;
            
            if (this.documentosCarregados.length === 0) {
                alert('Nenhum documento carregado!');
                return;
            }
            
            const mensagem = this.mensagemAtual.trim();
            this.mensagemAtual = '';
            
            // Reseta altura do textarea
            this.$nextTick(() => {
                const textarea = this.$el.querySelector('textarea');
                if (textarea) {
                    textarea.style.height = 'auto';
                }
            });
            
            // Adiciona mensagem do usuário
            this.mensagens.push({
                role: 'user',
                content: mensagem,
                time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
            });
            
            this.scrollToBottom();
            this.carregando = true;
            
            try {
                // Prepara histórico (últimas 10 mensagens)
                const history = this.mensagens.slice(-10).map(msg => ({
                    role: msg.role,
                    content: msg.content
                }));
                
                const response = await fetch('{{ route("admin.ia.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message: mensagem,
                        history: history.slice(0, -1),
                        documentos_contexto: this.documentosCarregados.map(doc => ({
                            nome: doc.nome_documento,
                            conteudo: doc.conteudo,
                            buscar_internet: this.buscarInternet
                        })),
                        // Fallback para compatibilidade
                        documento_contexto: this.documentosCarregados.length === 1 ? {
                            nome: this.documentosCarregados[0].nome_documento,
                            conteudo: this.documentosCarregados[0].conteudo,
                            buscar_internet: this.buscarInternet
                        } : null,
                        buscar_internet: this.buscarInternet
                    })
                });
                
                const data = await response.json();
                
                if (data.response) { // CORRIGIDO: era 'resposta', agora é 'response'
                    this.mensagens.push({
                        role: 'assistant',
                        content: data.response,
                        time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    });
                } else {
                    throw new Error('Resposta inválida');
                }
            } catch (error) {
                console.error('Erro ao enviar mensagem:', error);
                this.mensagens.push({
                    role: 'assistant',
                    content: '❌ Desculpe, ocorreu um erro ao processar sua pergunta. Tente novamente.',
                    time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                });
            } finally {
                this.carregando = false;
                this.scrollToBottom();
            }
        },
        
        formatarMensagem(texto) {
            // Converte markdown básico para HTML
            return texto
                // Converte URLs em links clicáveis (antes de outras conversões)
                .replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" class="text-blue-600 hover:text-blue-800 underline">$1</a>')
                // Converte asteriscos simples em negrito (ex: *texto*)
                .replace(/\*(.+?)\*/g, '<strong>$1</strong>')
                // Detecta categorias (início com **Sobre [Categoria]:**)
                .replace(/^\*\*Sobre (.+?):\*\*/gm, '<span class="categoria">**Sobre $1:**</span>')
                // Converte negrito (duplo asterisco)
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                // Converte quebras de linha
                .replace(/\n/g, '<br>')
                // Converte listas
                .replace(/^- (.+)$/gm, '<li>$1</li>')
                // Agrupa itens de lista
                .replace(/(<li>.*<\/li>)/s, '<ul>$&</ul>')
                // Converte código inline
                .replace(/`(.+?)`/g, '<code>$1</code>');
        },
        
        handleKeyDown(event) {
            // Shift+Enter: quebra linha sem enviar
            if (event.key === 'Enter' && event.shiftKey) {
                event.preventDefault();
                // Insere quebra de linha no textarea
                const start = event.target.selectionStart;
                const end = event.target.selectionEnd;
                const value = event.target.value;
                
                this.mensagemAtual = value.substring(0, start) + '\n' + value.substring(end);
                
                // Ajusta altura do textarea
                this.$nextTick(() => {
                    event.target.style.height = 'auto';
                    event.target.style.height = event.target.scrollHeight + 'px';
                    // Mantém cursor na posição correta
                    event.target.selectionStart = event.target.selectionEnd = start + 1;
                });
            }
            
            // Enter sem Shift: envia mensagem
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                this.enviarMensagem();
            }
        },
        
        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.mensagensContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },
        
        limparConversa() {
            if (confirm('Deseja limpar toda a conversa sobre este(s) documento(s)?')) {
                this.mensagens = [];
                if (this.documentosCarregados.length > 0) {
                    const nomes = this.documentosCarregados.map(d => d.nome_documento).join(', ');
                    this.mensagens.push({
                        role: 'assistant',
                        content: `📄 **${this.documentosCarregados.length} documento(s) carregado(s)**\n\nConversa limpa! Pode fazer novas perguntas sobre os documentos.`,
                        time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    });
                }
            }
        },
        
        fecharChat() {
            this.chatAberto = false;
            // Dispara evento para notificar que o chat de documento fechou
            window.dispatchEvent(new CustomEvent('documento-chat-fechado'));
            // Limpa documentos após 1 segundo (permite reabrir rapidamente)
            setTimeout(() => {
                if (!this.chatAberto) {
                    this.documentosCarregados = [];
                    this.documentosDisponiveis = [];
                    this.documentosSelecionados = [];
                    this.mensagens = [];
                    this.mostrarSeletorDocumentos = false;
                }
            }, 1000);
        },
        
        async buscarDocumentosDisponiveis() {
            if (!this.processoId || !this.estabelecimentoId) return;
            
            try {
                const baseUrl = window.APP_BASE_URL || '';
                const response = await fetch(`${baseUrl}/admin/ia/documentos-processo/${this.estabelecimentoId}/${this.processoId}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    console.log('Documentos retornados:', data.documentos);
                    console.log('Documentos carregados:', this.documentosCarregados);
                    
                    // Remove documentos já carregados da lista
                    const idsCarregados = this.documentosCarregados.map(d => d.documento_id);
                    console.log('IDs carregados:', idsCarregados);
                    
                    this.documentosDisponiveis = data.documentos.filter(doc => !idsCarregados.includes(doc.id));
                    console.log('Documentos disponíveis após filtro:', this.documentosDisponiveis);
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
            
            // Limita a 3 documentos no total para evitar exceder limite de tokens
            const totalAposCarregar = this.documentosCarregados.length + this.documentosSelecionados.length;
            if (totalAposCarregar > 3) {
                alert('⚠️ Limite atingido!\n\nVocê pode carregar no máximo 3 documentos por vez para evitar exceder o limite de processamento da IA.\n\nAtualmente você tem ' + this.documentosCarregados.length + ' documento(s) carregado(s).');
                return;
            }
            
            this.carregandoMultiplos = true;
            
            try {
                const response = await fetch(window.APP_URL + '/admin/ia/extrair-multiplos-pdfs', {
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
                            conteudo: doc.conteudo,
                            total_caracteres: doc.total_caracteres
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
                        content: `✅ **${data.documentos.length} documento(s) adicionado(s) à análise!**\n\n` +
                                 `Total de documentos carregados: ${this.documentosCarregados.length}\n\n` +
                                 `Agora posso responder perguntas considerando todos os documentos carregados.`,
                        time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    });
                    
                    this.scrollToBottom();
                } else {
                    alert(data.message || 'Erro ao carregar documentos');
                }
            } catch (error) {
                console.error('Erro ao carregar documentos:', error);
                alert('Erro ao carregar documentos. Tente novamente.');
            } finally {
                this.carregandoMultiplos = false;
            }
        },
        
        mostrarAvisoBuscaInternet() {
            // Removido aviso de texto conforme solicitado
            // O toggle visual já é suficiente
        }
    }
}
</script>
