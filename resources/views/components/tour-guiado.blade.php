{{-- Tour Guiado para Novos Usuários - Layout Moderno --}}
@props(['forceShow' => false])

<div x-data="tourGuiado()" 
     x-show="mostrarTour" 
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="fecharTour()"
     class="fixed inset-0 z-[9999]">
    
    {{-- Overlay mais claro para ver o conteúdo --}}
    <div class="absolute inset-0 bg-black/40 transition-all duration-500"
         @click="proximoPasso()"></div>
    
    {{-- Card do Assistente --}}
    <div class="absolute transition-all duration-500 ease-out z-[10000]"
         :style="posicaoCard"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <div class="relative w-80">
            {{-- Card principal compacto --}}
            <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden">
                
                {{-- Header compacto --}}
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2.5 flex items-center gap-3">
                    <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center shadow">
                        <span class="text-xl">🤖</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-white font-semibold text-sm truncate">Guia InfoVISA</h3>
                        <div class="flex items-center gap-1.5">
                            <div class="flex-1 h-1 bg-white/30 rounded-full overflow-hidden">
                                <div class="h-full bg-white rounded-full transition-all" :style="`width: ${((passoAtual + 1) / passos.length) * 100}%`"></div>
                            </div>
                            <span class="text-white/70 text-[10px]" x-text="`${passoAtual + 1}/${passos.length}`"></span>
                        </div>
                    </div>
                    <button @click="fecharTour()" class="p-1 rounded hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                {{-- Corpo compacto --}}
                <div class="p-3">
                    {{-- Título com ícone --}}
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xl" x-text="passos[passoAtual]?.icone"></span>
                        <h4 class="font-semibold text-gray-800 text-sm" x-text="passos[passoAtual]?.titulo"></h4>
                    </div>
                    
                    {{-- Mensagem --}}
                    <div class="bg-gray-50 rounded-lg p-2.5 mb-2">
                        <p class="text-xs text-gray-600 leading-relaxed" x-html="passos[passoAtual]?.mensagem"></p>
                    </div>
                    
                    {{-- Dica --}}
                    <div x-show="passos[passoAtual]?.dica" class="flex items-start gap-1.5 text-[11px] text-amber-700 bg-amber-50 rounded p-2">
                        <span>💡</span>
                        <p x-text="passos[passoAtual]?.dica"></p>
                    </div>
                </div>
                
                {{-- Footer compacto --}}
                <div class="px-3 pb-3 flex items-center gap-2">
                    <button x-show="passoAtual > 0" @click="passoAnterior()"
                            class="px-2.5 py-1.5 text-xs text-gray-500 hover:bg-gray-100 rounded transition-colors">
                        ← Voltar
                    </button>
                    <div class="flex-1"></div>
                    <button @click="fecharTour()" class="px-2.5 py-1.5 text-xs text-gray-400 hover:text-gray-600">
                        Pular
                    </button>
                    <button @click="proximoPasso()"
                            class="px-4 py-1.5 text-xs font-semibold text-white rounded-lg transition-all"
                            :class="passoAtual === passos.length - 1 ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'">
                        <span x-text="passoAtual === passos.length - 1 ? 'Concluir ✓' : 'Próximo →'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }
    .animate-wiggle {
        animation: wiggle 0.5s ease-in-out infinite;
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
                icone: '👋',
                iconeBg: 'bg-gradient-to-br from-cyan-100 to-blue-100',
                titulo: 'Bem-vindo ao InfoVISA!',
                mensagem: `Olá! Sou o assistente virtual do sistema.<br><br>
                           Vou te mostrar as principais funcionalidades em <strong>poucos passos</strong>.`,
                dica: 'O tour leva menos de 1 minuto!',
                posicao: 'centro'
            },
            {
                elemento: '#tour-novo-cadastro',
                icone: '📝',
                iconeBg: 'bg-gradient-to-br from-green-100 to-emerald-100',
                titulo: 'Cadastre seu Estabelecimento',
                mensagem: `Clique aqui para cadastrar um <strong>novo estabelecimento</strong>.<br><br>
                           Você pode registrar empresas (CNPJ) ou autônomos (CPF).`,
                dica: 'Tenha em mãos: CNPJ/CPF, endereço completo e contato.',
                posicao: 'direita'
            },
            {
                elemento: '#tour-meus-estabelecimentos',
                icone: '🏢',
                iconeBg: 'bg-gradient-to-br from-blue-100 to-indigo-100',
                titulo: 'Seus Estabelecimentos',
                mensagem: `Visualize todos os seus estabelecimentos cadastrados.<br><br>
                           Acompanhe o <strong>status de aprovação</strong> e gerencie os dados.`,
                posicao: 'baixo'
            },
            {
                elemento: '#tour-meus-processos',
                icone: '📋',
                iconeBg: 'bg-gradient-to-br from-purple-100 to-violet-100',
                titulo: 'Processos de Licenciamento',
                mensagem: `Aqui você acessa seus processos sanitários.<br><br>
                           <strong>Envie documentos</strong> obrigatórios e acompanhe cada etapa.`,
                dica: 'Documentos devem ser em PDF, máximo 10MB.',
                posicao: 'baixo'
            },
            {
                elemento: '#tour-alertas',
                icone: '⚠️',
                iconeBg: 'bg-gradient-to-br from-amber-100 to-orange-100',
                titulo: 'Atenção às Pendências!',
                mensagem: `Esta área mostra itens que precisam da sua ação:<br><br>
                           • Documentos <strong>rejeitados</strong> para correção<br>
                           • Notificações com <strong>prazo</strong> definido<br>
                           • Novos documentos emitidos`,
                dica: 'Verifique diariamente para evitar problemas!',
                posicao: 'baixo'
            },
            {
                elemento: '#tour-estatisticas',
                icone: '📊',
                iconeBg: 'bg-gradient-to-br from-slate-100 to-gray-100',
                titulo: 'Resumo Geral',
                mensagem: `Veja rapidamente a situação dos seus:<br><br>
                           • Estabelecimentos cadastrados<br>
                           • Processos em andamento<br>
                           • Status geral do sistema`,
                posicao: 'cima'
            },
            {
                elemento: null,
                icone: '🚀',
                iconeBg: 'bg-gradient-to-br from-green-100 to-emerald-100',
                titulo: 'Tudo Pronto!',
                mensagem: `Você já conhece o básico do sistema!<br><br>
                           Qualquer dúvida, entre em contato com a <strong>Vigilância Sanitária</strong> do seu município.`,
                dica: 'Você pode rever este guia clicando em "Ver Tour Novamente".',
                posicao: 'centro'
            }
        ],
        
        init() {
            const tourVisto = localStorage.getItem('infovisa_tour_visto');
            const forceShow = {{ $forceShow ? 'true' : 'false' }};
            
            if (!tourVisto || forceShow) {
                setTimeout(() => {
                    this.mostrarTour = true;
                    this.atualizarPosicao();
                }, 800);
            }
        },
        
        atualizarPosicao() {
            // Remove destaque do elemento anterior
            if (this.elementoAtual) {
                this.elementoAtual.style.outline = '';
                this.elementoAtual.style.outlineOffset = '';
                this.elementoAtual.style.boxShadow = '';
                this.elementoAtual.style.position = '';
                this.elementoAtual.style.zIndex = '';
                this.elementoAtual.style.borderRadius = '';
            }
            
            const passo = this.passos[this.passoAtual];
            if (passo.elemento) {
                this.elementoAtual = document.querySelector(passo.elemento);
                if (this.elementoAtual) {
                    this.elementoAtual.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Aplica destaque diretamente no elemento
                    this.elementoAtual.style.outline = '4px solid #06b6d4';
                    this.elementoAtual.style.outlineOffset = '4px';
                    this.elementoAtual.style.boxShadow = '0 0 0 8px rgba(6,182,212,0.3), 0 0 30px rgba(6,182,212,0.4)';
                    this.elementoAtual.style.position = 'relative';
                    this.elementoAtual.style.zIndex = '10001';
                    this.elementoAtual.style.borderRadius = '12px';
                }
            } else {
                this.elementoAtual = null;
            }
        },
        
        get posicaoCard() {
            const passo = this.passos[this.passoAtual];
            
            if (!this.elementoAtual || passo.posicao === 'centro') {
                return 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
            }
            
            const rect = this.elementoAtual.getBoundingClientRect();
            const cardWidth = 320;
            const cardHeight = 280;
            const gap = 20;
            
            let top, left;
            
            switch (passo.posicao) {
                case 'direita':
                    top = rect.top + window.scrollY + (rect.height / 2) - (cardHeight / 2);
                    left = rect.right + gap;
                    if (left + cardWidth > window.innerWidth - 20) {
                        left = rect.left - cardWidth - gap;
                    }
                    break;
                case 'baixo':
                    top = rect.bottom + window.scrollY + gap;
                    left = rect.left + (rect.width / 2) - (cardWidth / 2);
                    if (left + cardWidth > window.innerWidth - 20) left = window.innerWidth - cardWidth - 20;
                    if (left < 20) left = 20;
                    break;
                case 'esquerda':
                    top = rect.top + window.scrollY + (rect.height / 2) - (cardHeight / 2);
                    left = rect.left - cardWidth - gap;
                    if (left < 20) left = rect.right + gap;
                    break;
                case 'cima':
                    top = rect.top + window.scrollY - cardHeight - gap;
                    left = rect.left + (rect.width / 2) - (cardWidth / 2);
                    if (left + cardWidth > window.innerWidth - 20) left = window.innerWidth - cardWidth - 20;
                    if (left < 20) left = 20;
                    if (top < 20) top = rect.bottom + window.scrollY + gap;
                    break;
                default:
                    return 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
            }
            
            top = Math.max(20, top);
            
            return `top: ${top}px; left: ${left}px;`;
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
            // Remove destaque do elemento
            if (this.elementoAtual) {
                this.elementoAtual.style.outline = '';
                this.elementoAtual.style.outlineOffset = '';
                this.elementoAtual.style.boxShadow = '';
                this.elementoAtual.style.position = '';
                this.elementoAtual.style.zIndex = '';
                this.elementoAtual.style.borderRadius = '';
            }
            this.mostrarTour = false;
            localStorage.setItem('infovisa_tour_visto', 'true');
        },
        
        reiniciarTour() {
            localStorage.removeItem('infovisa_tour_visto');
            this.passoAtual = 0;
            this.mostrarTour = true;
            this.atualizarPosicao();
        }
    }
}
</script>
