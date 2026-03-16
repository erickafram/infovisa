@extends('layouts.admin')

@section('title', 'Processos Sob Minha Responsabilidade')
@section('page-title', 'Processos Sob Minha Responsabilidade')

@section('content')
<div class="max-w-8xl mx-auto" x-data="processosResponsabilidade()" x-init="init()">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Processos sob minha responsabilidade</h1>
            <p class="text-[11px] text-gray-400">Lista completa dos processos atribuídos diretamente a você</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-[11px] text-gray-500 hover:text-gray-700 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-2.5 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white flex items-center justify-between">
            <span class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wider">Responsabilidade Direta</span>
            <span class="text-[10px] text-gray-400" x-text="total + ' processo(s)'"></span>
        </div>

        <template x-if="loading">
            <div class="p-8 text-center">
                <svg class="animate-spin h-5 w-5 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
        </template>

        <template x-if="!loading && processos.length === 0">
            <div class="p-10 text-center">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-700">Nenhum processo sob sua responsabilidade</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Quando houver tramitação direta para você, os processos aparecerão aqui.</p>
            </div>
        </template>

        <template x-if="!loading && processos.length > 0">
            <div class="divide-y divide-gray-50">
                <template x-for="p in processos" :key="'proc-' + p.id">
                    <a :href="p.url" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50/80 transition" :class="p.prazo && p.prazo.vencido ? 'bg-red-50/30' : (p.prazo && p.prazo.proximo ? 'bg-amber-50/20' : '')">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-[13px] font-semibold text-gray-900" x-text="p.numero_processo"></p>
                                <template x-if="p.docs_total > 0">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-medium" x-text="'Docs ' + p.docs_enviados + '/' + p.docs_total"></span>
                                </template>
                                <template x-if="p.prazo">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium"
                                          :class="p.prazo.vencido ? 'bg-red-100 text-red-700' : (p.prazo.proximo ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700')"
                                          x-text="p.prazo.vencido ? 'Prazo vencido' : ('Prazo ' + Math.abs(p.prazo.dias_restantes) + 'd')"></span>
                                </template>
                            </div>
                            <p class="text-sm text-gray-800 truncate mt-0.5" x-text="p.estabelecimento"></p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                                <template x-if="p.recebido_em_humano">
                                    <p class="text-[11px] text-sky-700" :title="p.recebido_em">Recebido em <span x-text="p.recebido_em"></span> (<span x-text="p.recebido_em_humano"></span>)</p>
                                </template>
                                <template x-if="!p.recebido_em_humano && p.aguardando_ciencia">
                                    <p class="text-[11px] text-amber-600" :title="p.tramitado_em">Tramitado em <span x-text="p.tramitado_em"></span> (aguardando ciência)</p>
                                </template>
                                <template x-if="p.tramitado_em && p.recebido_em_humano">
                                    <p class="text-[11px] text-gray-400">Tramitado em <span x-text="p.tramitado_em"></span></p>
                                </template>
                            </div>
                        </div>

                        <div class="flex-shrink-0 text-right">
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full"
                                  :class="getStatusClass(p.status)"
                                  x-text="p.status_nome"></span>
                        </div>
                    </a>
                </template>
            </div>
        </template>

        <template x-if="!loading && lastPage > 1">
            <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <span class="text-[11px] text-gray-400">
                    <span x-text="((currentPage - 1) * perPage) + 1"></span>–<span x-text="Math.min(currentPage * perPage, total)"></span> de <span x-text="total"></span>
                </span>
                <div class="flex items-center gap-1">
                    <button @click="prevPage()" :disabled="currentPage <= 1"
                            class="p-1.5 rounded-lg text-gray-400 hover:bg-white hover:text-gray-600 transition disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-[11px] text-gray-500 px-1.5" x-text="currentPage + '/' + lastPage"></span>
                    <button @click="nextPage()" :disabled="currentPage >= lastPage"
                            class="p-1.5 rounded-lg text-gray-400 hover:bg-white hover:text-gray-600 transition disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function processosResponsabilidade() {
    return {
        processos: [],
        loading: true,
        currentPage: 1,
        lastPage: 1,
        total: 0,
        perPage: 20,

        init() { this.load(); },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    escopo: 'meu_direto',
                });
                const response = await fetch(`{{ route('admin.dashboard.processos-atribuidos') }}?${params}`);
                const data = await response.json();
                this.processos = data.data;
                this.currentPage = data.current_page;
                this.lastPage = data.last_page;
                this.total = data.total;
                this.perPage = data.per_page;
            } catch (error) {
                console.error(error);
            }
            this.loading = false;
        },

        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.load(); } },
        nextPage() { if (this.currentPage < this.lastPage) { this.currentPage++; this.load(); } },

        getStatusClass(status) {
            if (status === 'aberto') return 'bg-blue-100 text-blue-700';
            if (status === 'parado') return 'bg-red-100 text-red-700';
            return 'bg-gray-100 text-gray-600';
        }
    }
}
</script>
@endsection