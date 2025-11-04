{{-- Assistente IA para Documentos PDF --}}
<style>
/* Estilos do chat de documentos */
.assistente-documento-mensagem {
    font-size: 0.8125rem; /* 13px - fonte menor */
    line-height: 1.5;
}

.assistente-documento-mensagem p {
    margin-bottom: 0.5rem;
    line-height: 1.5;
    color: #374151;
}

.assistente-documento-mensagem strong {
    font-weight: 600;
    color: #1f2937;
}

.assistente-documento-mensagem ul,
.assistente-documento-mensagem ol {
    margin-left: 1.25rem;
    margin-bottom: 0.5rem;
    padding-left: 0.25rem;
}

.assistente-documento-mensagem li {
    margin-bottom: 0.375rem;
    line-height: 1.5;
    color: #374151;
}

.assistente-documento-mensagem code {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.75rem;
}

.assistente-documento-mensagem h3 {
    color: #7c3aed;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.assistente-documento-mensagem h4 {
    color: #6b7280;
    margin-bottom: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 600;
}

/* Esconde elementos antes da inicialização do Alpine.js */
[x-cloak] {
    display: none !important;
}
</style>

<div x-data="assistenteDocumento()" x-init="init()" class="fixed bottom-6 left-6" style="z-index: 10000; width: 420px;" x-cloak>
    {{-- Botão Flutuante (quando minimizado) --}}
    <button x-show="!chatAberto && documentoCarregado"
            @click="chatAberto = true"
            x-transition.duration.300ms
            class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 p-4 flex items-center gap-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span class="text-sm font-medium pr-2">Assistente de Documento</span>
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
                    <p class="text-[10px] text-white/80" x-show="documentoCarregado" x-text="documentoCarregado ? `📄 ${documentoCarregado.nome_documento}` : ''"></p>
                </div>
            </div>
            <div class="flex items-center gap-1">
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

        {{-- Área de Mensagens --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50" x-ref="mensagensContainer">
            <template x-for="(mensagem, index) in mensagens" :key="index">
                <div :class="mensagem.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="mensagem.role === 'user' 
                                ? 'bg-purple-600 text-white rounded-2xl rounded-br-sm px-3 py-2.5 max-w-[85%] shadow-md' 
                                : 'bg-white text-gray-800 rounded-2xl rounded-bl-sm px-3 py-2.5 max-w-[90%] shadow-md border border-gray-200'">
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
                <input type="text" 
                       x-model="mensagemAtual"
                       placeholder="Pergunte sobre o documento..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                       :disabled="carregando">
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
        documentoCarregado: null,
        nomeDocumento: '',
        
        init() {
            // Escuta evento de documento carregado
            window.addEventListener('documento-carregado', (event) => {
                this.documentoCarregado = {
                    documento_id: event.detail.documento_id,
                    nome_documento: event.detail.nome_documento,
                    conteudo: event.detail.conteudo,
                    total_caracteres: event.detail.total_caracteres
                };
                
                this.nomeDocumento = event.detail.nome_documento;
                
                // Abre o chat automaticamente
                this.chatAberto = true;
                
                // Limpa mensagens anteriores
                this.mensagens = [];
                
                // Adiciona mensagem de boas-vindas
                this.mensagens.push({
                    role: 'assistant',
                    content: `📄 **Documento carregado com sucesso!**\n\n` +
                             `**${event.detail.nome_documento}**\n` +
                             `${event.detail.total_caracteres.toLocaleString('pt-BR')} caracteres extraídos\n\n` +
                             `✅ **Pronto para responder suas perguntas!**\n\n` +
                             `**Sugestões:**\n` +
                             `- O que esse documento fala?\n` +
                             `- Qual o resumo?\n` +
                             `- Quais os pontos principais?\n` +
                             `- Me explique o conteúdo`,
                    time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                });
                
                this.scrollToBottom();
            });
        },
        
        async enviarMensagem() {
            if (!this.mensagemAtual.trim() || this.carregando) return;
            
            if (!this.documentoCarregado) {
                alert('Nenhum documento carregado!');
                return;
            }
            
            const mensagem = this.mensagemAtual.trim();
            this.mensagemAtual = '';
            
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
                        documento_contexto: {
                            nome: this.documentoCarregado.nome_documento, // Nome do documento
                            conteudo: this.documentoCarregado.conteudo    // Conteúdo extraído
                        }
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
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>')
                .replace(/^- (.+)$/gm, '<li>$1</li>')
                .replace(/(<li>.*<\/li>)/s, '<ul>$&</ul>');
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
            if (confirm('Deseja limpar toda a conversa sobre este documento?')) {
                this.mensagens = [];
                if (this.documentoCarregado) {
                    this.mensagens.push({
                        role: 'assistant',
                        content: `📄 **${this.nomeDocumento}**\n\nConversa limpa! Pode fazer novas perguntas sobre o documento.`,
                        time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    });
                }
            }
        },
        
        fecharChat() {
            this.chatAberto = false;
            // Limpa documento após 1 segundo (permite reabrir rapidamente)
            setTimeout(() => {
                if (!this.chatAberto) {
                    this.documentoCarregado = null;
                    this.nomeDocumento = '';
                    this.mensagens = [];
                }
            }, 1000);
        }
    }
}
</script>
