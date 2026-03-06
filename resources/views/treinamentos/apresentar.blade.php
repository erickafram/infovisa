@extends('layouts.admin')

@section('title', 'Modo apresentador')
@section('page-title', 'Modo apresentador')

@section('content')
<div x-data="treinamentoApresentador(@js($slidesPayload))" x-init="init()" class="grid gap-6 xl:grid-cols-[320px,1fr]">
    <aside class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <p class="text-sm font-medium text-blue-600">{{ $apresentacao->evento->titulo }}</p>
            <h2 class="text-lg font-bold text-gray-900">{{ $apresentacao->titulo }}</h2>
        </div>
        <div class="space-y-2 p-4">
            <template x-for="(slide, index) in slides" :key="slide.id">
                <button type="button" @click="setSlide(index)" class="w-full rounded-xl border px-4 py-3 text-left transition" :class="currentIndex === index ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide opacity-70">Slide <span x-text="slide.ordem"></span></p>
                            <p class="mt-1 text-sm font-semibold" x-text="slide.titulo"></p>
                        </div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-gray-600 shadow-sm" x-text="slide.perguntas.length"></span>
                    </div>
                </button>
            </template>
        </div>
    </aside>

    <section class="space-y-6">
        <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600">Slide atual</p>
                    <h3 class="text-3xl font-bold text-gray-900" x-text="currentSlide ? currentSlide.titulo : 'Sem slides cadastrados'"></h3>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="previousSlide()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Anterior</button>
                    <button type="button" @click="nextSlide()" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Próximo</button>
                </div>
            </div>

            <div class="mt-8 min-h-[240px] rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-blue-900 p-8 text-white shadow-inner">
                <div class="mx-auto max-w-4xl space-y-6">
                    <div class="flex items-center gap-3 text-blue-100">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-lg font-bold" x-text="currentSlide ? currentSlide.ordem : ''"></span>
                        <p class="text-sm uppercase tracking-[0.25em]">Treinamento interativo</p>
                    </div>
                    <h4 class="text-4xl font-bold leading-tight" x-text="currentSlide ? currentSlide.titulo : ''"></h4>
                    <p class="whitespace-pre-line text-lg leading-8 text-slate-100" x-text="currentSlide && currentSlide.conteudo ? currentSlide.conteudo : 'Adicione conteúdo ao slide para orientar a apresentação.'"></p>
                </div>
            </div>
        </div>

        <template x-if="currentSlide && currentSlide.perguntas.length">
            <div class="space-y-4">
                <template x-for="pergunta in currentSlide.perguntas" :key="pergunta.id">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h4 class="text-lg font-semibold text-gray-900" x-text="pergunta.enunciado"></h4>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="pergunta.ativa ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700'" x-text="pergunta.ativa ? 'Ativa' : 'Inativa'"></span>
                                </div>
                                <p class="mt-2 break-all text-sm text-blue-700" x-text="pergunta.url"></p>
                                <div class="mt-5 grid gap-4 md:grid-cols-2">
                                    <template x-for="item in pergunta.estatisticas.opcoes" :key="item.id">
                                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                            <div class="flex items-center justify-between gap-3">
                                                <p class="text-sm font-semibold text-gray-900" x-text="item.texto"></p>
                                                <span class="text-sm font-bold text-blue-700" x-text="item.percentual + '%' "></span>
                                            </div>
                                            <div class="mt-3 h-3 overflow-hidden rounded-full bg-gray-200">
                                                <div class="h-full rounded-full bg-blue-600 transition-all duration-500" :style="`width: ${item.percentual}%`"></div>
                                            </div>
                                            <p class="mt-2 text-xs text-gray-500"><span x-text="item.quantidade"></span> resposta(s)</p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="xl:w-64">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                    <img :src="`data:image/png;base64,${pergunta.qr_code_base64}`" alt="QR Code" class="mx-auto h-52 w-52 object-contain">
                                    <p class="mt-3 text-center text-sm text-gray-500">Escaneie para responder</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="currentSlide && !currentSlide.perguntas.length">
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-sm text-gray-500 shadow-sm">
                Este slide não possui perguntas interativas.
            </div>
        </template>
    </section>
</div>
@endsection

@push('scripts')
<script>
    function treinamentoApresentador(slidesPayload) {
        return {
            slides: slidesPayload || [],
            currentIndex: 0,
            pollingHandle: null,
            init() {
                this.startPolling();
            },
            get currentSlide() {
                return this.slides[this.currentIndex] || null;
            },
            setSlide(index) {
                this.currentIndex = index;
            },
            previousSlide() {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                }
            },
            nextSlide() {
                if (this.currentIndex < this.slides.length - 1) {
                    this.currentIndex++;
                }
            },
            startPolling() {
                this.refreshStats();
                this.pollingHandle = setInterval(() => this.refreshStats(), 8000);
            },
            async refreshStats() {
                const slide = this.currentSlide;
                if (!slide || !slide.perguntas.length) {
                    return;
                }

                for (const pergunta of slide.perguntas) {
                    try {
                        const response = await fetch(pergunta.resultados_url, {
                            headers: { 'Accept': 'application/json' }
                        });

                        if (!response.ok) {
                            continue;
                        }

                        pergunta.estatisticas = await response.json();
                    } catch (error) {
                        console.error('Falha ao atualizar estatísticas da pergunta', error);
                    }
                }
            }
        };
    }
</script>
@endpush