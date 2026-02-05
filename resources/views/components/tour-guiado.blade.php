{{-- Tour Guiado para Novos Usuários --}}
@props(['forceShow' => false])

<div x-data="tourGuiado()" 
     x-show="mostrarTour" 
     x-cloak
     @keydown.escape.window="fecharTour()"
     class="fixed inset-0 z-[9999]">
    
    {{-- Overlay escuro com recorte para destacar elemento --}}
    <div class="absolute inset-0 bg-black/60 transition-all duration-300"
         @click="proximoPasso()"></div>
    
    {{-- Spotlight/Destaque no elemento atual --}}
    <template x-if="elementoAtual">
        <div class="absolute bg-transparent ring-4 ring-blue-400 ring-opacity-75 rounded-lg pointer-events-none transition-all duration-300 animate-pulse"
             :style="posicaoDestaque"></div>
    </template>
    
    {{-- Card do Robô Assistente --}}
    <div class="absolute transition-all duration-300 z-[10000]"
         :style="posicaoCard"
         :class="{ 'animate-bounce-slow': passoAtual === 0 }">
        <div class="bg-white rounded-2xl shadow-2xl border-2 border-blue-200 max-w-sm overflow-hidden">
            {{-- Header com Robô --}}
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3 flex items-center gap-3">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg animate-bounce-slow">
                    <span class="text-2xl">🤖</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-white font-bold text-sm">Assistente InfoVISA</h3>
                    <p class="text-blue-100 text-xs">Vou te ajudar a começar!</p>
                </div>
                <button @click="fecharTour()" 
                        class="text-white/80 hover:text-white p-1 rounded-full hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            {{-- Conteúdo do Passo --}}
            <div class="p-4">
                {{-- Indicador de Passos --}}
                <div class="flex items-center justify-center gap-1.5 mb-3">
                    <template x-for="(passo, index) in passos" :key="index">
                        <div class="h-1.5 rounded-full transition-all duration-300"
                             :class="index === passoAtual ? 'w-6 bg-blue-500' : 'w-1.5 bg-gray-300'"></div>
                    </template>
                </div>
                
                {{-- Mensagem do Robô --}}
                <div class="bg-gray-50 rounded-xl p-3 mb-4 relative">
                    {{-- Seta do balão --}}
                    <div class="absolute -top-2 left-6 w-4 h-4 bg-gray-50 rotate-45"></div>
                    <p class="text-sm text-gray-700 leading-relaxed" x-html="passos[passoAtual]?.mensagem"></p>
                </div>
                
                {{-- Botões de Navegação --}}
                <div class="flex items-center justify-between gap-2">
                    <button x-show="passoAtual > 0"
                            @click="passoAnterior()"
                            class="flex items-center gap-1 px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Voltar
                    </button>
                    
                    <div class="flex items-center gap-2 ml-auto">
                        <button @click="fecharTour()" 
                                class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                            Pular Tour
                        </button>
                        
                        <button @click="proximoPasso()"
                                class="flex items-center gap-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-md">
                            <span x-text="passoAtual === passos.length - 1 ? 'Concluir' : 'Próximo'"></span>
                            <svg x-show="passoAtual < passos.length - 1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <svg x-show="passoAtual === passos.length - 1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            {{-- Footer --}}
            <div class="bg-gray-50 px-4 py-2 border-t border-gray-200">
                <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer">
                    <input type="checkbox" x-model="naoMostrarNovamente" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Não mostrar novamente
                </label>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes bounce-slow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-bounce-slow {
        animation: bounce-slow 2s ease-in-out infinite;
    }
</style>

<script>
function tourGuiado() {
    return {
        mostrarTour: false,
        passoAtual: 0,
        naoMostrarNovamente: false,
        elementoAtual: null,
        
        passos: [
            {
                elemento: null,
                mensagem: `<strong>👋 Bem-vindo ao InfoVISA!</strong><br><br>
                           Sou seu assistente virtual e vou te guiar pelos principais recursos do sistema.<br><br>
                           <em>Clique em "Próximo" para começar o tour!</em>`,
                posicao: 'centro'
            },
            {
                elemento: '#tour-novo-cadastro',
                mensagem: `<strong>📝 Novo Cadastro</strong><br><br>
                           Para cadastrar seu estabelecimento, clique aqui em <strong>"Novo Cadastro"</strong>.<br><br>
                           Você pode cadastrar empresas (PJ) ou pessoas físicas (PF).`,
                posicao: 'direita'
            },
            {
                elemento: '#tour-meus-estabelecimentos',
                mensagem: `<strong>🏢 Meus Estabelecimentos</strong><br><br>
                           Aqui você visualiza todos os seus estabelecimentos cadastrados.<br><br>
                           Acompanhe o <strong>status de aprovação</strong> de cada um.`,
                posicao: 'baixo'
            },
            {
                elemento: '#tour-meus-processos',
                mensagem: `<strong>📋 Meus Processos</strong><br><br>
                           Acesse seus processos de licenciamento sanitário.<br><br>
                           <strong>Envie documentos</strong> e acompanhe o andamento de cada solicitação.`,
                posicao: 'baixo'
            },
            {
                elemento: '#tour-alertas',
                mensagem: `<strong>⚠️ Alertas e Pendências</strong><br><br>
                           Fique atento a esta área! Aqui aparecem:<br>
                           • Documentos <strong>rejeitados</strong> para correção<br>
                           • Notificações com <strong>prazo</strong> para responder<br>
                           • Novos documentos emitidos pela Vigilância`,
                posicao: 'baixo'
            },
            {
                elemento: '#tour-estatisticas',
                mensagem: `<strong>📊 Resumo Geral</strong><br><br>
                           Aqui você vê um resumo rápido dos seus:<br>
                           • Estabelecimentos cadastrados<br>
                           • Processos em andamento<br>
                           • Status geral`,
                posicao: 'direita'
            },
            {
                elemento: null,
                mensagem: `<strong>🎉 Tudo Pronto!</strong><br><br>
                           Agora você já conhece as principais funcionalidades do sistema.<br><br>
                           Se tiver dúvidas, clique no botão de <strong>ajuda</strong> ou entre em contato com a Vigilância Sanitária.<br><br>
                           <em>Bom trabalho!</em> 🚀`,
                posicao: 'centro'
            }
        ],
        
        init() {
            // Verifica se o usuário já viu o tour
            const tourVisto = localStorage.getItem('infovisa_tour_visto');
            const forceShow = {{ $forceShow ? 'true' : 'false' }};
            
            if (!tourVisto || forceShow) {
                // Aguarda um pouco para a página carregar
                setTimeout(() => {
                    this.mostrarTour = true;
                    this.atualizarPosicao();
                }, 1000);
            }
        },
        
        atualizarPosicao() {
            const passo = this.passos[this.passoAtual];
            if (passo.elemento) {
                this.elementoAtual = document.querySelector(passo.elemento);
                if (this.elementoAtual) {
                    this.elementoAtual.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                this.elementoAtual = null;
            }
        },
        
        get posicaoDestaque() {
            if (!this.elementoAtual) return 'display: none;';
            
            const rect = this.elementoAtual.getBoundingClientRect();
            const padding = 8;
            
            return `
                top: ${rect.top + window.scrollY - padding}px;
                left: ${rect.left - padding}px;
                width: ${rect.width + padding * 2}px;
                height: ${rect.height + padding * 2}px;
            `;
        },
        
        get posicaoCard() {
            const passo = this.passos[this.passoAtual];
            
            if (!this.elementoAtual || passo.posicao === 'centro') {
                return 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
            }
            
            const rect = this.elementoAtual.getBoundingClientRect();
            const cardWidth = 384; // max-w-sm = 384px
            const cardHeight = 300; // altura estimada
            const gap = 16;
            
            let top, left;
            
            switch (passo.posicao) {
                case 'direita':
                    top = rect.top + window.scrollY;
                    left = rect.right + gap;
                    // Se não couber na direita, coloca na esquerda
                    if (left + cardWidth > window.innerWidth) {
                        left = rect.left - cardWidth - gap;
                    }
                    break;
                case 'baixo':
                    top = rect.bottom + window.scrollY + gap;
                    left = rect.left;
                    // Ajusta se sair da tela
                    if (left + cardWidth > window.innerWidth) {
                        left = window.innerWidth - cardWidth - 20;
                    }
                    break;
                case 'esquerda':
                    top = rect.top + window.scrollY;
                    left = rect.left - cardWidth - gap;
                    break;
                case 'cima':
                    top = rect.top + window.scrollY - cardHeight - gap;
                    left = rect.left;
                    break;
                default:
                    return 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
            }
            
            return `top: ${top}px; left: ${Math.max(20, left)}px;`;
        },
        
        proximoPasso() {
            if (this.passoAtual < this.passos.length - 1) {
                this.passoAtual++;
                this.$nextTick(() => this.atualizarPosicao());
            } else {
                this.fecharTour();
            }
        },
        
        passoAnterior() {
            if (this.passoAtual > 0) {
                this.passoAtual--;
                this.$nextTick(() => this.atualizarPosicao());
            }
        },
        
        fecharTour() {
            this.mostrarTour = false;
            
            if (this.naoMostrarNovamente) {
                localStorage.setItem('infovisa_tour_visto', 'true');
            } else {
                // Marca como visto mesmo assim, para não aparecer toda vez
                localStorage.setItem('infovisa_tour_visto', 'true');
            }
        },
        
        // Método para reiniciar o tour (pode ser chamado de fora)
        reiniciarTour() {
            localStorage.removeItem('infovisa_tour_visto');
            this.passoAtual = 0;
            this.mostrarTour = true;
            this.atualizarPosicao();
        }
    }
}
</script>
