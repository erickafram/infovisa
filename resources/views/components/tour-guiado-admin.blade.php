{{-- Tour Guiado para Admin Dashboard --}}
@props(['forceShow' => false])

<div x-data="tourGuiadoAdmin()" 
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
    
    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/40 transition-all duration-500"
         @click="proximoPasso()"></div>
    
    {{-- Card do Assistente --}}
    <div class="absolute transition-all duration-500 ease-out z-[10000]"
         :style="posicaoCard"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <div class="relative w-80">
            <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden">
                
                {{-- Header compacto --}}
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 flex items-center gap-3">
                    <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center shadow">
                        <span class="text-xl">🤖</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-white font-semibold text-sm truncate">Guia do Sistema</h3>
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
                
                {{-- Corpo --}}
                <div class="p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xl" x-text="passos[passoAtual]?.icone"></span>
                        <h4 class="font-semibold text-gray-800 text-sm" x-text="passos[passoAtual]?.titulo"></h4>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-2.5 mb-2">
                        <p class="text-xs text-gray-600 leading-relaxed" x-html="passos[passoAtual]?.mensagem"></p>
                    </div>
                    
                    <div x-show="passos[passoAtual]?.dica" class="flex items-start gap-1.5 text-[11px] text-amber-700 bg-amber-50 rounded p-2">
                        <span>💡</span>
                        <p x-text="passos[passoAtual]?.dica"></p>
                    </div>
                </div>
                
                {{-- Footer --}}
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
                            :class="passoAtual === passos.length - 1 ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700'">
                        <span x-text="passoAtual === passos.length - 1 ? 'Concluir ✓' : 'Próximo →'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function tourGuiadoAdmin() {
    return {
        mostrarTour: false,
        passoAtual: 0,
        elementoAtual: null,
        
        passos: [
            {
                elemento: null,
                icone: '👋',
                titulo: 'Bem-vindo ao Painel!',
                mensagem: `Olá! Vou te mostrar como a <strong>Dashboard</strong> está organizada.<br><br>
                           São <strong>3 colunas</strong>: o que é pra você, o que é do seu setor e acompanhamento geral.`,
                dica: 'O tour leva menos de 1 minuto.',
                posicao: 'centro'
            },
            {
                elemento: '#tour-stats-cards',
                icone: '📊',
                titulo: 'Resumo Rápido',
                mensagem: `Os 3 cards no topo mostram seus números principais:<br><br>
                           • <strong>Para Mim</strong> — suas OS e assinaturas pendentes<br>
                           • <strong>Meu Setor</strong> — aprovações e processos do setor<br>
                           • <strong>Cadastros Pendentes</strong> — estabelecimentos aguardando aprovação`,
                dica: 'A barra colorida no rodapé de cada card detalha a composição do total.',
                posicao: 'baixo'
            },
            {
                elemento: '#tour-minhas-tarefas',
                icone: '👤',
                titulo: 'Para Mim',
                mensagem: `Esta coluna mostra tudo que depende <strong>diretamente de você</strong>:<br><br>
                           • <strong class="text-blue-600">Ordens de Serviço</strong> — OS em andamento atribuídas a você<br>
                           • <strong class="text-amber-600">Assinaturas Pendentes</strong> — documentos aguardando sua assinatura<br>
                           • <strong class="text-indigo-600">Meus Processos</strong> — processos onde você é o responsável atual`,
                dica: 'Itens em vermelho estão atrasados (mais de 5 dias sem movimentação).',
                posicao: 'direita'
            },
            {
                elemento: '#tour-processos-setor',
                icone: '🏢',
                titulo: 'Demandas do Setor',
                mensagem: `Aqui ficam as demandas do <strong>seu setor como um todo</strong>:<br><br>
                           • <strong class="text-purple-600">Docs para Aprovar</strong> — documentos aguardando análise do setor<br>
                           • <strong class="text-emerald-600">Respostas para Analisar</strong> — respostas de notificações recebidas<br>
                           • <strong class="text-teal-600">Processos do Setor</strong> — todos os processos atribuídos ao setor`,
                dica: 'Útil para coordenadores distribuírem demandas entre a equipe.',
                posicao: 'esquerda'
            },
            {
                elemento: '#tour-monitorando',
                icone: '👁️',
                titulo: 'Monitorando',
                mensagem: `Processos que você escolheu <strong>acompanhar</strong>, mesmo que não sejam do seu setor.<br><br>
                           Use o botão <strong>"Acompanhar"</strong> na página do processo para adicioná-lo aqui.`,
                dica: 'Você recebe notificações sempre que houver movimentação nesses processos.',
                posicao: 'esquerda'
            },
            {
                elemento: '#tour-cadastros-pendentes',
                icone: '🏗️',
                titulo: 'Cadastros Pendentes',
                mensagem: `Lista de <strong>estabelecimentos</strong> que solicitaram cadastro e aguardam aprovação.<br><br>
                           Clique em cada um para revisar os dados e aprovar ou solicitar correções.`,
                dica: 'Verifique CNPJ, endereço e atividades antes de aprovar.',
                posicao: 'cima'
            },
            {
                elemento: '#tour-atalhos',
                icone: '⚡',
                titulo: 'Atalhos Rápidos',
                mensagem: `Crie <strong>atalhos personalizados</strong> para as páginas que você mais usa.<br><br>
                           Clique no <strong>+</strong> para adicionar. Você pode salvar até links com filtros aplicados!`,
                dica: 'Passe o mouse sobre um atalho para editar ou excluir.',
                posicao: 'cima'
            },
            {
                elemento: null,
                icone: '🚀',
                titulo: 'Tudo Pronto!',
                mensagem: `Agora você conhece a dashboard!<br><br>
                           Use o <strong>menu lateral</strong> para acessar as demais funcionalidades do sistema.<br>
                           E lembre-se: o botão <strong>"Tour"</strong> no canto inferior repete este guia a qualquer momento.`,
                dica: null,
                posicao: 'centro'
            }
        ],
        
        init() {
            const tourVisto = localStorage.getItem('infovisa_tour_admin_visto');
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
                    this.elementoAtual.style.outline = '4px solid #6366f1';
                    this.elementoAtual.style.outlineOffset = '4px';
                    this.elementoAtual.style.boxShadow = '0 0 0 8px rgba(99,102,241,0.3), 0 0 30px rgba(99,102,241,0.4)';
                    this.elementoAtual.style.position = 'relative';
                    this.elementoAtual.style.zIndex = '9998';
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
            localStorage.setItem('infovisa_tour_admin_visto', 'true');
        },
        
        reiniciarTour() {
            localStorage.removeItem('infovisa_tour_admin_visto');
            this.passoAtual = 0;
            this.mostrarTour = true;
            this.atualizarPosicao();
        }
    }
}
</script>
