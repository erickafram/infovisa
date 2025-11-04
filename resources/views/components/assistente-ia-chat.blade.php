{{-- Componente de Chat da IA - Fixo no canto direito --}}
@php
    $iaAtiva = \App\Models\ConfiguracaoSistema::where('chave', 'ia_ativa')->value('valor');
    
    // Busca categorias POPs ativas para sugestões
    $categoriasPops = \App\Models\CategoriaPop::ativas()
        ->ordenadas()
        ->limit(4)
        ->get(['id', 'nome', 'cor']);
    
    // Busca nome do usuário logado
    $usuarioLogado = auth('interno')->user();
    $nomeUsuario = $usuarioLogado ? $usuarioLogado->nome : 'Usuário';
    $primeiroNome = explode(' ', $nomeUsuario)[0]; // Pega apenas o primeiro nome
@endphp

@if($iaAtiva === 'true')
<style>
.transition-none {
    transition: none !important;
    animation: none !important;
}

/* Formatação das respostas da IA - MELHORADA */
.formatted-response {
    font-size: 0.9375rem; /* 15px - maior que antes */
    line-height: 1.7; /* Mais espaçamento entre linhas */
    color: #1f2937; /* Cor mais escura para melhor contraste */
}

.formatted-response p {
    margin-bottom: 0.75rem; /* Mais espaço entre parágrafos */
}

.formatted-response p:last-child {
    margin-bottom: 0;
}

.formatted-response strong {
    font-weight: 700; /* Mais negrito */
    color: #111827; /* Ainda mais escuro */
}

.formatted-response ol {
    list-style-type: decimal;
    margin-left: 1.5rem;
    margin-top: 0.75rem;
    margin-bottom: 0.75rem;
}

.formatted-response ul {
    list-style-type: disc;
    margin-left: 1.5rem;
    margin-top: 0.75rem;
    margin-bottom: 0.75rem;
}

.formatted-response li {
    margin-bottom: 0.5rem; /* Mais espaço entre itens */
    line-height: 1.7;
}

.formatted-response li:last-child {
    margin-bottom: 0;
}

.formatted-response code {
    background-color: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    color: #374151;
    font-weight: 500;
}

.formatted-response .section-title {
    font-weight: 700;
    color: #111827;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    font-size: 1rem; /* Títulos um pouco maiores */
}

.formatted-response .section-title:first-child {
    margin-top: 0;
}

.formatted-response .highlight {
    background-color: #fef3c7;
    padding: 0.25rem 0.375rem;
    border-radius: 0.25rem;
}

/* Categoria destacada no início da resposta */
.formatted-response h3,
.formatted-response h4 {
    font-weight: 700;
    color: #7c3aed; /* Roxo para categorias */
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

/* Esconde elementos antes da inicialização do Alpine.js */
[x-cloak] {
    display: none !important;
}
</style>
<div x-data="assistenteIA()" x-init="init()" class="fixed bottom-6 right-6" style="z-index: 10000;" x-cloak>
    {{-- Botão Flutuante --}}
    <button @click="toggleChat()" 
            x-show="!chatAberto"
            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full p-4 shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-300 flex items-center gap-3 group">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        <span class="font-semibold hidden group-hover:inline-block">Assistente IA</span>
    </button>

    {{-- Janela de Chat --}}
    <div x-show="chatAberto"
         x-transition.duration.300ms
         :class="[
             maximizado ? 'fixed inset-4 w-auto h-auto' : 'relative'
         ]"
         :style="maximizado ? '' : 'width: 380px; height: 500px;'"
         class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col transition-all duration-300">
        
        {{-- Cabeçalho --}}
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-3 flex items-center justify-between">
            <div class="flex items-center gap-2 flex-1 min-w-0">
                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-base">Olá, {{ $primeiroNome }}! 👋</h3>
                    <p class="text-[10px] text-white/80">Assistente InfoVisa - Sempre pronto para ajudar</p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button @click="limparConversa()" 
                        x-show="mensagens.length > 0"
                        title="Limpar conversa"
                        class="hover:bg-white/20 rounded-full p-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
                <button @click="toggleMaximizar()" 
                        :title="maximizado ? 'Minimizar' : 'Maximizar'"
                        class="hover:bg-white/20 rounded-full p-1.5 transition-colors">
                    <svg x-show="!maximizado" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    <svg x-show="maximizado" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"/>
                    </svg>
                </button>
                <button @click="toggleChat()" class="hover:bg-white/20 rounded-full p-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Área de Mensagens --}}
        <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-gray-50" x-ref="messagesContainer">
            {{-- Mensagem de Boas-vindas --}}
            <div x-show="mensagens.length === 0" class="py-3">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                    </svg>
                </div>
                <h4 class="font-bold text-sm text-gray-900 mb-1 text-center">Olá, {{ $primeiroNome }}! 👋</h4>
                <p class="text-xs text-gray-600 mb-3 text-center">Como posso ajudar você hoje?</p>
                
                {{-- Sugestões sobre o Sistema --}}
                <div class="mb-4">
                    <h5 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Sobre o Sistema
                    </h5>
                    <div class="space-y-1.5">
                        <button @click="enviarSugestao('Como abrir um processo?')" 
                                class="w-full text-left px-3 py-1.5 bg-white hover:bg-blue-50 border border-gray-200 rounded-lg text-xs text-gray-700 transition-colors">
                            💼 Como abrir um processo?
                        </button>
                        <button @click="enviarSugestao('Quantos estabelecimentos tenho?')" 
                                class="w-full text-left px-3 py-1.5 bg-white hover:bg-blue-50 border border-gray-200 rounded-lg text-xs text-gray-700 transition-colors">
                            📊 Quantos estabelecimentos tenho?
                        </button>
                        <button @click="enviarSugestao('Como criar um documento digital?')" 
                                class="w-full text-left px-3 py-1.5 bg-white hover:bg-blue-50 border border-gray-200 rounded-lg text-xs text-gray-700 transition-colors">
                            📄 Como criar um documento digital?
                        </button>
                    </div>
                </div>

                @if($categoriasPops->isNotEmpty())
                {{-- Sugestões sobre POPs por Categoria --}}
                <div>
                    <h5 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Normas e Procedimentos
                    </h5>
                    <div class="space-y-1.5">
                        @foreach($categoriasPops as $categoria)
                        <button @click="enviarSugestao('Quais são as normas sobre {{ strtolower($categoria->nome) }}?')" 
                                class="w-full text-left px-3 py-1.5 bg-white hover:bg-purple-50 border border-gray-200 rounded-lg text-xs text-gray-700 transition-colors flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $categoria->cor }}"></span>
                            <span class="truncate">{{ $categoria->nome }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Mensagens --}}
            <template x-for="(msg, index) in mensagens" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' 
                        ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl rounded-tr-none px-3 py-2 max-w-[85%]' 
                        : 'bg-white text-gray-900 rounded-2xl rounded-tl-none px-4 py-3 max-w-[90%] shadow-sm border border-gray-200'">
                        <div :class="msg.role === 'user' ? 'text-xs leading-relaxed' : 'formatted-response'" x-html="formatarMensagem(msg.content, msg.role)"></div>
                        <p class="text-[10px] mt-1.5 opacity-60" x-text="msg.time"></p>
                    </div>
                </div>
            </template>

            {{-- Loading --}}
            <div x-show="carregando" class="flex justify-start">
                <div class="bg-white text-gray-900 rounded-2xl rounded-tl-none px-4 py-3 shadow-sm border border-gray-200">
                    <div class="flex gap-2">
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input de Mensagem --}}
        <div class="p-3 bg-white border-t border-gray-200">
            <form @submit.prevent="enviarMensagem()" class="flex gap-2">
                <input type="text" 
                       x-model="mensagemAtual"
                       :disabled="carregando"
                       placeholder="Digite sua pergunta... (máx. 2000 caracteres)"
                       maxlength="2000"
                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                <button type="submit" 
                        :disabled="!mensagemAtual.trim() || carregando"
                        class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-3 py-2 rounded-lg hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function assistenteIA() {
    // Carrega estados ANTES da inicialização para evitar animação
    const estadoChat = localStorage.getItem('ia_chat_aberto');
    const estadoMaximizado = localStorage.getItem('ia_chat_maximizado');
    const historico = localStorage.getItem('ia_chat_history');
    
    let mensagensIniciais = [];
    if (historico) {
        try {
            mensagensIniciais = JSON.parse(historico);
        } catch (e) {
            console.error('Erro ao carregar histórico:', e);
        }
    }
    
    return {
        chatAberto: estadoChat === 'true',
        maximizado: estadoMaximizado === 'true',
        mensagens: mensagensIniciais,
        mensagemAtual: '',
        carregando: false,
        inicializado: false,
        
        init() {
            // Marca como inicializado e rola para o final se chat estiver aberto
            this.$nextTick(() => {
                this.inicializado = true;
                if (this.chatAberto && this.mensagens.length > 0) {
                    // Aguarda um pouco mais para garantir que o DOM está pronto
                    setTimeout(() => {
                        this.scrollToBottomInstant();
                    }, 50);
                }
            });

            // REMOVIDO: Lógica de documento movida para assistente-documento-chat.blade.php
        },
        
        toggleChat() {
            this.chatAberto = !this.chatAberto;
            // Salva estado no localStorage
            localStorage.setItem('ia_chat_aberto', this.chatAberto ? 'true' : 'false');
            if (this.chatAberto) {
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            }
        },
        
        toggleMaximizar() {
            this.maximizado = !this.maximizado;
            // Salva estado no localStorage
            localStorage.setItem('ia_chat_maximizado', this.maximizado ? 'true' : 'false');
            // Rola para o final após maximizar/minimizar
            this.$nextTick(() => {
                this.scrollToBottom();
            });
        },
        
        enviarSugestao(texto) {
            this.mensagemAtual = texto;
            this.enviarMensagem();
        },
        
        async enviarMensagem() {
            if (!this.mensagemAtual.trim() || this.carregando) return;
            
            const mensagem = this.mensagemAtual.trim();
            this.mensagemAtual = '';
            
            // Adiciona mensagem do usuário
            this.mensagens.push({
                role: 'user',
                content: mensagem,
                time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
            });
            
            this.salvarHistorico();
            this.scrollToBottom();
            this.carregando = true;
            
            try {
                // Prepara histórico para enviar à API
                const history = this.mensagens.slice(-10).map(msg => ({
                    role: msg.role,
                    content: msg.content
                }));
                
                // Prepara payload
                const payload = {
                    message: mensagem,
                    history: history.slice(0, -1) // Remove a última (que é a atual)
                };

                const response = await fetch('{{ route('admin.ia.chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.mensagens.push({
                        role: 'assistant',
                        content: data.response, // CORRIGIDO: era 'message', agora é 'response'
                        time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    });
                } else {
                    this.mensagens.push({
                        role: 'assistant',
                        content: data.response || 'Desculpe, ocorreu um erro. Tente novamente.',
                        time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    });
                }
            } catch (error) {
                console.error('Erro ao enviar mensagem:', error);
                
                // Tenta extrair mensagem de erro mais específica
                let errorMessage = 'Desculpe, não consegui processar sua mensagem. Verifique sua conexão e tente novamente.';
                
                if (error.response) {
                    // Erro de validação (422) ou outro erro HTTP
                    if (error.response.status === 422) {
                        errorMessage = 'Sua pergunta é muito longa. Por favor, reduza o tamanho da mensagem (máximo 2000 caracteres).';
                    }
                }
                
                this.mensagens.push({
                    role: 'assistant',
                    content: errorMessage,
                    time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                });
            } finally {
                this.carregando = false;
                this.salvarHistorico();
                this.scrollToBottom();
            }
        },
        
        salvarHistorico() {
            // Mantém apenas as últimas 50 mensagens
            const historico = this.mensagens.slice(-50);
            localStorage.setItem('ia_chat_history', JSON.stringify(historico));
        },
        
        scrollToBottom() {
            this.$nextTick(() => {
                setTimeout(() => {
                    const container = this.$refs.messagesContainer;
                    if (container) {
                        container.scrollTo({
                            top: container.scrollHeight,
                            behavior: 'smooth'
                        });
                    }
                }, 100);
            });
        },
        
        scrollToBottomInstant() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },
        
        limparConversa() {
            if (confirm('Deseja realmente limpar toda a conversa? Esta ação não pode ser desfeita.')) {
                this.mensagens = [];
                localStorage.removeItem('ia_chat_history');
            }
        },
        
        formatarMensagem(content, role) {
            if (role === 'user') {
                // Mensagens do usuário não precisam formatação especial
                return this.escapeHtml(content);
            }
            
            // Formata mensagens da IA
            let formatted = content;
            
            // Escapa HTML primeiro
            formatted = this.escapeHtml(formatted);
            
            // Converte **texto** em <strong>texto</strong>
            formatted = formatted.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            
            // Converte listas numeradas (1. item)
            formatted = formatted.replace(/^(\d+)\.\s+(.+)$/gm, '<li>$2</li>');
            
            // Envolve listas numeradas em <ol>
            formatted = formatted.replace(/(<li>.*<\/li>\n?)+/g, function(match) {
                return '<ol>' + match + '</ol>';
            });
            
            // Converte listas com marcadores (- item ou • item)
            formatted = formatted.replace(/^[-•]\s+(.+)$/gm, '<li>$1</li>');
            
            // Envolve listas com marcadores em <ul> (que não estão em <ol>)
            formatted = formatted.replace(/(<li>.*<\/li>\n?)+/g, function(match) {
                if (!match.includes('<ol>')) {
                    return '<ul>' + match + '</ul>';
                }
                return match;
            });
            
            // Converte `código` em <code>código</code>
            formatted = formatted.replace(/`(.+?)`/g, '<code>$1</code>');
            
            // Converte quebras de linha em parágrafos
            const paragraphs = formatted.split('\n\n');
            formatted = paragraphs.map(p => {
                p = p.trim();
                if (p && !p.startsWith('<ol>') && !p.startsWith('<ul>')) {
                    return '<p>' + p.replace(/\n/g, '<br>') + '</p>';
                }
                return p;
            }).join('');
            
            return formatted;
        },
        
        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    }
}
</script>
@endif
