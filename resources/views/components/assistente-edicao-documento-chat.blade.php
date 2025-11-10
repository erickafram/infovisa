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

<div x-data="assistenteEdicaoDocumento()" x-init="init()" x-cloak class="fixed bottom-6 right-6" style="z-index: 10000; width: 340px;">
    {{-- Botão Flutuante (só mostra quando chat está fechado) --}}
    <button type="button"
            x-show="!chatAberto && !minimizado"
            @click="toggleChat()"
            class="bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-full pl-4 pr-5 py-3 shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-start gap-2"
            style="min-width: 140px;">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        <span class="text-sm font-medium whitespace-nowrap">Redação</span>
    </button>

    {{-- Janela do Chat (minimizado) --}}
    <div x-show="minimizado"
         x-transition.duration.300ms
         class="bg-white rounded-lg shadow-xl border-2 border-green-200 flex flex-col"
         style="height: 60px; width: 200px;">
        
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

    {{-- Janela do Chat (normal) --}}
    <div x-show="chatAberto && !minimizado"
         x-transition.duration.300ms
         class="bg-white rounded-lg shadow-2xl border-2 border-green-200 flex flex-col"
         style="height: 450px;">

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
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' 
                                ? 'bg-green-600 text-white rounded-lg px-4 py-3 max-w-[90%]' 
                                : 'bg-gray-100 text-gray-800 rounded-lg px-4 py-3 max-w-[90%] assistente-edicao-mensagem'">
                        <div x-html="formatarMensagemComBotao(msg.content, index)"></div>
                        <div class="text-xs mt-1 opacity-70" x-text="msg.time"></div>
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
        carregando: false,
        conhecimentoGeral: false,
        textoEditor: '',
        
        init() {
            // Monitora mudanças no editor de texto
            this.monitorarEditor();
            
            // Scroll para o final se chat estiver aberto
            if (this.chatAberto && !this.minimizado) {
                this.$nextTick(() => this.scrollToBottom());
            }
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
                        historico: this.mensagens.slice(-10),
                        texto_atual: this.textoEditor,
                        conhecimento_geral: this.conhecimentoGeral,
                        dados_estabelecimento: dadosEstabelecimento
                    })
                });
                
                const data = await response.json();
                
                if (data.resposta) {
                    this.mensagens.push({
                        role: 'assistant',
                        content: this.formatarMensagem(data.resposta),
                        time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                    });
                } else {
                    throw new Error('Resposta inválida');
                }
            } catch (error) {
                console.error('Erro ao enviar mensagem:', error);
                this.mensagens.push({
                    role: 'assistant',
                    content: '❌ Desculpe, ocorreu um erro. Tente novamente.',
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

