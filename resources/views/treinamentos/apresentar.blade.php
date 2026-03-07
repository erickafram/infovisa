<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $apresentacao->titulo }} — Apresentação</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { margin: 0; overflow: hidden; font-family: 'Segoe UI', 'Inter', system-ui, sans-serif; }
        .slide-enter { animation: slideIn 0.4s cubic-bezier(0.22, 1, 0.36, 1); }
        @keyframes slideIn { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }
        .bar-animate { transition: width 0.8s cubic-bezier(0.22, 1, 0.36, 1); }
        .thumbnail-strip::-webkit-scrollbar { width: 6px; }
        .thumbnail-strip::-webkit-scrollbar-track { background: transparent; }
        .thumbnail-strip::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }
        .slide-html-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5rem auto; }
        .slide-html-content h1, .slide-html-content h2, .slide-html-content h3 { color: #fff; margin-bottom: 0.5rem; }
        .slide-html-content p { margin-bottom: 0.5rem; }
        .slide-html-content ul, .slide-html-content ol { text-align: left; margin: 0.5rem auto; }
        .slide-html-content li { margin-bottom: 0.25rem; }
        .slide-html-content a { color: #93c5fd; text-decoration: underline; }
        .slide-html-content blockquote { border-left: 4px solid rgba(255,255,255,0.3); padding-left: 1rem; font-style: italic; opacity: 0.9; }
        .slide-html-content table { border-collapse: collapse; margin: 0.5rem auto; }
        .slide-html-content th, .slide-html-content td { border: 1px solid rgba(255,255,255,0.2); padding: 0.5rem 1rem; }
    </style>
</head>
<body class="bg-gray-950">

<div x-data="apresentacao(@js($slidesPayload))" x-init="init()" @keydown.window="handleKey($event)" x-cloak class="relative h-screen w-screen select-none">

    {{-- Sidebar de slides (toggle) --}}
    <div x-show="showSidebar" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900/95 backdrop-blur-xl border-r border-white/10 flex flex-col">

        <div class="px-4 py-4 border-b border-white/10">
            <p class="text-xs font-semibold text-blue-400 uppercase tracking-widest">{{ $apresentacao->evento->titulo }}</p>
            <h2 class="text-sm font-bold text-white mt-1 truncate">{{ $apresentacao->titulo }}</h2>
            <p class="text-xs text-gray-400 mt-1"><span x-text="currentIndex + 1"></span> / <span x-text="slides.length"></span></p>
        </div>

        <div class="flex-1 overflow-y-auto thumbnail-strip p-3 space-y-2">
            <template x-for="(slide, idx) in slides" :key="slide.id">
                <button @click="goTo(idx); showSidebar = false" class="w-full rounded-lg p-3 text-left transition-all duration-150"
                        :class="currentIndex === idx 
                            ? 'bg-blue-600 text-white ring-2 ring-blue-400' 
                            : 'bg-white/5 text-gray-300 hover:bg-white/10'">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-md text-xs font-bold"
                              :class="currentIndex === idx ? 'bg-white/20' : 'bg-white/10'" x-text="idx + 1"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold truncate" x-text="slide.titulo"></p>
                            <p class="text-[10px] mt-0.5 opacity-60" x-text="slide.tipo === 'pergunta' ? '❓ Pergunta' : '📄 Conteúdo'"></p>
                        </div>
                    </div>
                </button>
            </template>
        </div>

        <div class="p-3 border-t border-white/10">
            <button @click="showSidebar = false" class="w-full rounded-lg bg-white/10 px-3 py-2 text-xs font-medium text-white hover:bg-white/20 transition">Fechar painel</button>
        </div>
    </div>

    {{-- Overlay quando sidebar aberta --}}
    <div x-show="showSidebar" @click="showSidebar = false" class="fixed inset-0 z-40 bg-black/40"></div>

    {{-- Slide principal - tela inteira --}}
    <div class="h-screen w-screen flex flex-col">

        {{-- Área do slide --}}
        <div class="flex-1 relative overflow-hidden" @click="handleSlideClick($event)">
            <template x-if="currentSlide && currentSlide.tipo !== 'pergunta'">
                <div :key="'slide-' + currentIndex" class="slide-enter absolute inset-0 flex flex-col items-center justify-center p-8 md:p-16"
                     :style="`background: ${getSlideBackground()}`">
                    <div class="max-w-5xl w-full text-center space-y-8">
                        <div class="flex items-center justify-center gap-3 text-white/60">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-sm font-bold text-white" x-text="currentIndex + 1"></span>
                            <span class="text-xs uppercase tracking-[0.3em] font-medium">{{ $apresentacao->titulo }}</span>
                        </div>
                        <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white leading-tight" x-text="currentSlide.titulo"></h1>
                        <p x-show="currentSlide.conteudo" class="text-lg md:text-2xl text-white/80 leading-relaxed max-w-3xl mx-auto slide-html-content" x-html="currentSlide.conteudo"></p>
                    </div>
                </div>
            </template>

            {{-- Slide de PERGUNTA --}}
            <template x-if="currentSlide && currentSlide.tipo === 'pergunta'">
                <div :key="'slide-' + currentIndex" class="slide-enter absolute inset-0 flex"
                     :style="`background: ${getSlideBackground()}`">

                    {{-- Lado esquerdo: Pergunta + Resultados --}}
                    <div class="flex-1 flex flex-col items-center justify-center p-8 md:p-12">
                        <div class="max-w-2xl w-full space-y-6">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 backdrop-blur px-3 py-1 text-xs font-semibold text-white">
                                    ❓ Enquete interativa
                                </span>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                      :class="currentSlide.pergunta_ativa ? 'bg-emerald-500/20 text-emerald-300' : 'bg-gray-500/20 text-gray-400'"
                                      x-text="currentSlide.pergunta_ativa ? 'Ativa' : 'Inativa'"></span>
                            </div>

                            <h2 class="text-3xl md:text-5xl font-bold text-white leading-tight" x-text="currentSlide.titulo"></h2>

                            {{-- Barras de resultado --}}
                            <div class="space-y-3 mt-8">
                                <template x-for="(item, oi) in currentSlide.estatisticas.opcoes" :key="item.id">
                                    <div class="group">
                                        <div class="rounded-xl bg-white/10 backdrop-blur p-4 hover:bg-white/15 transition">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-base md:text-lg font-semibold text-white flex items-center gap-2">
                                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15 text-xs font-bold" x-text="['A','B','C','D','E','F','G','H'][oi] || (oi+1)"></span>
                                                    <span x-text="item.texto"></span>
                                                </span>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-2xl font-bold text-white" x-text="item.percentual + '%'"></span>
                                                    <span class="text-xs text-white/50" x-text="'(' + item.quantidade + ')'"></span>
                                                </div>
                                            </div>
                                            <div class="h-3 rounded-full bg-white/10 overflow-hidden">
                                                <div class="bar-animate h-full rounded-full" :class="getBarColor(oi)" :style="`width: ${item.percentual}%`"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="flex items-center gap-4 pt-4 text-white/50 text-sm">
                                <span>Total: <strong class="text-white" x-text="currentSlide.estatisticas.total_respostas"></strong> respostas</span>
                                <span class="flex items-center gap-1">
                                    <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span></span>
                                    Atualizando em tempo real
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Lado direito: QR Code --}}
                    <div class="hidden lg:flex w-80 xl:w-96 flex-col items-center justify-center bg-white/5 backdrop-blur border-l border-white/10 p-8">
                        <div class="bg-white rounded-2xl p-6 shadow-2xl">
                            <img :src="`data:image/png;base64,${currentSlide.qr_code_base64}`" alt="QR Code" class="w-52 h-52 xl:w-64 xl:h-64 object-contain">
                        </div>
                        <p class="mt-5 text-base font-semibold text-white text-center">Escaneie para votar</p>
                        <p class="mt-1 text-xs text-white/40 text-center break-all max-w-full px-4" x-text="currentSlide.pergunta_url"></p>
                    </div>
                </div>
            </template>

            {{-- Slide vazio --}}
            <template x-if="!currentSlide">
                <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-900 to-gray-800">
                    <div class="text-center text-white/50">
                        <p class="text-2xl font-bold">Nenhum slide cadastrado</p>
                        <p class="mt-2 text-sm">Adicione slides na tela de gerenciamento</p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Barra inferior de controles --}}
        <div class="h-14 bg-gray-900/90 backdrop-blur-xl border-t border-white/10 flex items-center justify-between px-4 z-30"
             x-show="showControls" x-transition>

            <div class="flex items-center gap-2">
                <button @click="showSidebar = !showSidebar" class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-medium text-white hover:bg-white/20 transition" title="Painel de slides (S)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    Slides
                </button>
                <a href="{{ route('admin.treinamentos.apresentacoes.show', $apresentacao) }}" class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-medium text-white hover:bg-white/20 transition" title="Sair da apresentação">
                    Sair
                </a>
            </div>

            <div class="flex items-center gap-3">
                <button @click="prev()" :disabled="currentIndex === 0"
                        class="rounded-lg bg-white/10 px-4 py-1.5 text-sm font-medium text-white hover:bg-white/20 transition disabled:opacity-30 disabled:cursor-not-allowed">◀ Anterior</button>
                <span class="text-sm font-bold text-white/70"><span x-text="currentIndex + 1"></span> / <span x-text="slides.length"></span></span>
                <button @click="next()" :disabled="currentIndex >= slides.length - 1"
                        class="rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 transition disabled:opacity-30 disabled:cursor-not-allowed">Próximo ▶</button>
            </div>

            <div class="flex items-center gap-2">
                <button @click="toggleFullscreen()" class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-medium text-white hover:bg-white/20 transition" title="Tela cheia (F)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Indicador de progresso --}}
    <div class="fixed top-0 left-0 right-0 h-1 z-50 bg-white/5">
        <div class="h-full bg-blue-500 transition-all duration-500" :style="`width: ${slides.length > 1 ? (currentIndex / (slides.length - 1)) * 100 : 100}%`"></div>
    </div>
</div>

<script>
    const SLIDE_COLORS = [
        'linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0c4a6e 100%)',
        'linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%)',
        'linear-gradient(135deg, #0f172a 0%, #312e81 50%, #1e1b4b 100%)',
        'linear-gradient(135deg, #0c0c1d 0%, #1b2838 50%, #2d1b69 100%)',
        'linear-gradient(135deg, #0f172a 0%, #064e3b 50%, #065f46 100%)',
        'linear-gradient(135deg, #1a0a2e 0%, #2d1f4e 50%, #4a1d6e 100%)',
    ];
    const BAR_COLORS = ['bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-purple-500', 'bg-cyan-500', 'bg-orange-500', 'bg-pink-500'];

    function apresentacao(slidesPayload) {
        return {
            slides: slidesPayload || [],
            currentIndex: 0,
            showSidebar: false,
            showControls: true,
            controlsTimer: null,
            pollingHandle: null,

            init() {
                this.startPolling();
                this.resetControlsTimer();
            },

            get currentSlide() {
                return this.slides[this.currentIndex] || null;
            },

            goTo(idx) { this.currentIndex = idx; },
            prev() { if (this.currentIndex > 0) this.currentIndex--; },
            next() { if (this.currentIndex < this.slides.length - 1) this.currentIndex++; },

            handleKey(e) {
                if (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'PageDown') { e.preventDefault(); this.next(); }
                else if (e.key === 'ArrowLeft' || e.key === 'PageUp') { e.preventDefault(); this.prev(); }
                else if (e.key === 'Home') { this.currentIndex = 0; }
                else if (e.key === 'End') { this.currentIndex = Math.max(0, this.slides.length - 1); }
                else if (e.key === 's' || e.key === 'S') { this.showSidebar = !this.showSidebar; }
                else if (e.key === 'f' || e.key === 'F') { this.toggleFullscreen(); }
                else if (e.key === 'Escape') { this.showSidebar = false; }
                this.resetControlsTimer();
            },

            handleSlideClick(e) {
                const w = window.innerWidth;
                if (e.clientX > w * 0.7) this.next();
                else if (e.clientX < w * 0.3) this.prev();
                this.resetControlsTimer();
            },

            getSlideBackground() {
                return SLIDE_COLORS[this.currentIndex % SLIDE_COLORS.length];
            },

            getBarColor(index) { return BAR_COLORS[index % BAR_COLORS.length]; },

            toggleFullscreen() {
                if (!document.fullscreenElement) document.documentElement.requestFullscreen();
                else document.exitFullscreen();
            },

            resetControlsTimer() {
                this.showControls = true;
                clearTimeout(this.controlsTimer);
                this.controlsTimer = setTimeout(() => { this.showControls = false; }, 5000);
            },

            startPolling() {
                this.refreshStats();
                this.pollingHandle = setInterval(() => this.refreshStats(), 5000);
            },

            async refreshStats() {
                for (const slide of this.slides) {
                    if (slide.tipo !== 'pergunta' || !slide.resultados_url) continue;
                    try {
                        const r = await fetch(slide.resultados_url, { headers: { 'Accept': 'application/json' } });
                        if (r.ok) slide.estatisticas = await r.json();
                    } catch (e) { /* silently retry next cycle */ }
                }
            },

            destroy() { clearInterval(this.pollingHandle); clearTimeout(this.controlsTimer); }
        };
    }
</script>
</body>
</html>