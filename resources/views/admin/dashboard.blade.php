@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $docsAtrasados = 0;
    foreach($documentos_pendentes_aprovacao ?? [] as $doc) {
        if ((int) $doc->created_at->diffInDays(now()) > 5) $docsAtrasados++;
    }
    foreach($respostas_pendentes_aprovacao ?? [] as $resp) {
        if ((int) $resp->created_at->diffInDays(now()) > 5) $docsAtrasados++;
    }
@endphp

<div class="space-y-3">
    {{-- Header + Mini Cards inline --}}
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Olá, {{ Str::words(auth('interno')->user()->nome, 1, '') }}!</h1>
                <p class="text-xs text-gray-400">{{ now()->locale('pt_BR')->isoFormat('ddd, D MMM') }}</p>
            </div>
            @if($docsAtrasados > 0)
            <a href="{{ route('admin.documentos-pendentes.index') }}" class="flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ $docsAtrasados }} atrasado
            </a>
            @endif
        </div>
        
        {{-- Mini badges --}}
        <div class="flex items-center gap-1.5">
            <a href="{{ route('admin.assinatura.pendentes') }}" class="flex items-center gap-1 px-2 py-1 bg-amber-50 border border-amber-200 rounded hover:bg-amber-100" title="Assinaturas">
                <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                <span class="text-xs font-semibold text-amber-700">{{ $stats['documentos_pendentes_assinatura'] ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.documentos-pendentes.index') }}" class="flex items-center gap-1 px-2 py-1 {{ $docsAtrasados > 0 ? 'bg-red-50 border-red-200' : 'bg-purple-50 border-purple-200' }} border rounded hover:opacity-80" title="Aprovações">
                <svg class="w-3 h-3 {{ $docsAtrasados > 0 ? 'text-red-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-xs font-semibold {{ $docsAtrasados > 0 ? 'text-red-700' : 'text-purple-700' }}">{{ $stats['total_pendentes_aprovacao'] ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.documentos.index', ['status' => 'com_prazos']) }}" class="flex items-center gap-1 px-2 py-1 bg-orange-50 border border-orange-200 rounded hover:bg-orange-100" title="Prazos">
                <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-xs font-semibold text-orange-700">{{ $stats['documentos_vencendo'] ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="flex items-center gap-1 px-2 py-1 bg-cyan-50 border border-cyan-200 rounded hover:bg-cyan-100" title="Cadastros">
                <svg class="w-3 h-3 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span class="text-xs font-semibold text-cyan-700">{{ $stats['estabelecimentos_pendentes'] ?? 0 }}</span>
            </a>
        </div>
    </div>

    {{-- Layout 3 colunas --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        
        {{-- Coluna 1: Minhas Tarefas --}}
        <div class="bg-white rounded border border-gray-200" x-data="tarefasPaginadas()">
            <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-700">Minhas Tarefas</span>
                <span class="text-xs text-gray-400" x-text="total > 0 ? total + ' itens' : ''"></span>
            </div>
            <div class="divide-y divide-gray-50 min-h-[120px] max-h-[280px] overflow-y-auto">
                <template x-if="loading">
                    <div class="p-4 text-center"><svg class="animate-spin h-4 w-4 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>
                </template>
                <template x-if="!loading && tarefas.length > 0">
                    <div>
                        <template x-for="t in tarefas" :key="t.tipo + (t.id || t.processo_id)">
                            <a :href="t.url" class="flex items-center gap-2 px-3 py-1.5 hover:bg-gray-50" :class="t.atrasado ? 'bg-red-50/50' : ''">
                                <div class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0" :class="t.tipo === 'assinatura' ? 'bg-amber-100' : (t.tipo === 'os' ? 'bg-blue-100' : (t.atrasado ? 'bg-red-100' : 'bg-purple-100'))">
                                    <template x-if="t.tipo === 'assinatura'"><svg class="w-2.5 h-2.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></template>
                                    <template x-if="t.tipo === 'os'"><svg class="w-2.5 h-2.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></template>
                                    <template x-if="t.tipo === 'aprovacao'"><svg class="w-2.5 h-2.5" :class="t.atrasado ? 'text-red-600' : 'text-purple-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-800 truncate" x-text="t.titulo"></p>
                                    <p class="text-sm text-gray-400 truncate" x-text="t.subtitulo"></p>
                                </div>
                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded" :class="getBadgeClass(t)" x-text="getBadgeText(t)"></span>
                            </a>
                        </template>
                    </div>
                </template>
                <template x-if="!loading && tarefas.length === 0">
                    <div class="p-4 text-center text-xs text-gray-400">Nenhuma tarefa</div>
                </template>
            </div>
            <template x-if="lastPage > 1">
                <div class="px-3 py-1.5 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[10px] text-gray-400"><span x-text="currentPage"></span>/<span x-text="lastPage"></span></span>
                    <div class="flex gap-0.5">
                        <button @click="prevPage()" :disabled="currentPage <= 1" class="p-1 rounded hover:bg-gray-100 disabled:opacity-30"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                        <button @click="nextPage()" :disabled="currentPage >= lastPage" class="p-1 rounded hover:bg-gray-100 disabled:opacity-30"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Coluna 2: Processos Tramitados para Mim --}}
        <div class="bg-white rounded border border-gray-200" x-data="processosAtribuidos()">
            <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-700">Meus Processos</span>
                <span class="text-xs px-1.5 py-0.5 bg-cyan-100 text-cyan-700 rounded" x-text="total"></span>
            </div>
            <div class="divide-y divide-gray-50 min-h-[120px] max-h-[280px] overflow-y-auto">
                <template x-if="loading">
                    <div class="p-4 text-center"><svg class="animate-spin h-4 w-4 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>
                </template>
                <template x-if="!loading && processos.length > 0">
                    <div>
                        <template x-for="p in processos" :key="p.id">
                            <a :href="p.url" class="flex items-center gap-2 px-3 py-1.5 hover:bg-gray-50">
                                <div class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0" :class="p.is_meu_direto ? 'bg-cyan-100' : 'bg-blue-100'">
                                    <svg class="w-2.5 h-2.5" :class="p.is_meu_direto ? 'text-cyan-600' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-800 flex items-center gap-1.5">
                                        <span x-text="p.numero_processo"></span>
                                        <template x-if="p.prazo">
                                            <span class="text-[9px] font-medium px-1 py-0.5 rounded flex items-center gap-0.5"
                                                  :class="p.prazo.vencido ? 'bg-red-100 text-red-700' : (p.prazo.proximo ? 'bg-amber-100 text-amber-700' : 'bg-cyan-100 text-cyan-700')">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span x-text="p.prazo.data"></span>
                                                <span x-show="p.prazo.vencido" class="font-bold">(Vencido)</span>
                                            </span>
                                        </template>
                                    </p>
                                    <p class="text-sm text-gray-400 truncate" x-text="p.estabelecimento"></p>
                                </div>
                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded" :class="getStatusClass(p.status)" x-text="p.status_nome"></span>
                            </a>
                        </template>
                    </div>
                </template>
                <template x-if="!loading && processos.length === 0">
                    <div class="p-4 text-center text-xs text-gray-400">Nenhum processo</div>
                </template>
            </div>
            <template x-if="lastPage > 1">
                <div class="px-3 py-1.5 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[10px] text-gray-400"><span x-text="currentPage"></span>/<span x-text="lastPage"></span></span>
                    <div class="flex gap-0.5">
                        <button @click="prevPage()" :disabled="currentPage <= 1" class="p-1 rounded hover:bg-gray-100 disabled:opacity-30"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                        <button @click="nextPage()" :disabled="currentPage >= lastPage" class="p-1 rounded hover:bg-gray-100 disabled:opacity-30"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Coluna 3: Atalhos + Monitorando --}}
        <div class="space-y-3">
            {{-- Atalhos Rápidos --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded" x-data="atalhosRapidos()">
                <div class="px-3 py-2 border-b border-blue-400/30 flex items-center justify-between">
                    <span class="text-xs font-semibold text-white">Atalhos</span>
                    <button @click="abrirModal()" class="text-blue-200 hover:text-white"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg></button>
                </div>
                <div class="divide-y divide-blue-400/20">
                    @forelse($atalhos_rapidos ?? [] as $atalho)
                    <div class="group relative">
                        <a href="{{ $atalho->url }}" class="block px-3 py-1.5 hover:bg-blue-400/20"><span class="text-xs text-white">{{ $atalho->titulo }}</span></a>
                        <div class="absolute right-2 top-1/2 -translate-y-1/2 hidden group-hover:flex gap-0.5">
                            <button @click.prevent="editarAtalho({{ $atalho->id }}, '{{ addslashes($atalho->titulo) }}', '{{ addslashes($atalho->url) }}', '{{ $atalho->icone }}')" class="p-0.5 text-blue-200 hover:text-white"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                            <button @click.prevent="excluirAtalho({{ $atalho->id }})" class="p-0.5 text-blue-200 hover:text-red-300"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </div>
                    @empty
                    <a href="{{ route('admin.documentos-pendentes.index') }}" class="block px-3 py-1.5 hover:bg-blue-400/20"><span class="text-xs text-white">Docs Pendentes</span></a>
                    <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="block px-3 py-1.5 hover:bg-blue-400/20"><span class="text-xs text-white">Estabelecimentos</span></a>
                    <a href="{{ route('admin.ordens-servico.index') }}" class="block px-3 py-1.5 hover:bg-blue-400/20"><span class="text-xs text-white">Ordens de Serviço</span></a>
                    @endforelse
                </div>
                {{-- Modal --}}
                <div x-show="modalAberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.4)">
                    <div @click.away="fecharModal()" class="bg-white rounded-lg shadow-xl w-full max-w-sm">
                        <div class="px-4 py-3 border-b flex items-center justify-between">
                            <span class="text-base font-semibold" x-text="editandoId ? 'Editar' : 'Novo Atalho'"></span>
                            <button @click="fecharModal()" class="text-gray-400 hover:text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form @submit.prevent="salvarAtalho()" class="p-4 space-y-3">
                            <div>
                                <label class="text-xs font-medium text-gray-600">Página</label>
                                <select x-model="form.url" @change="atualizarTituloPadrao()" required class="w-full mt-1 px-2 py-1.5 text-sm border rounded focus:ring-1 focus:ring-blue-500">
                                    <option value="">Selecione...</option>
                                    <option value="{{ route('admin.dashboard') }}">Dashboard</option>
                                    <option value="{{ route('admin.estabelecimentos.index') }}">Estabelecimentos</option>
                                    <option value="{{ route('admin.estabelecimentos.pendentes') }}">Estab. Pendentes</option>
                                    <option value="{{ route('admin.processos.index-geral') }}">Processos</option>
                                    <option value="{{ route('admin.documentos-pendentes.index') }}">Docs Pendentes</option>
                                    <option value="{{ route('admin.documentos.index') }}">Documentos</option>
                                    <option value="{{ route('admin.assinatura.pendentes') }}">Assinaturas</option>
                                    <option value="{{ route('admin.ordens-servico.index') }}">Ordens de Serviço</option>
                                    <option value="{{ route('admin.ordens-servico.create') }}">Nova OS</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600">Título</label>
                                <input type="text" x-model="form.titulo" required maxlength="50" class="w-full mt-1 px-2 py-1.5 text-sm border rounded focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button type="button" @click="fecharModal()" class="flex-1 px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancelar</button>
                                <button type="submit" :disabled="salvando" class="flex-1 px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50" x-text="salvando ? '...' : 'Salvar'"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Monitorando --}}
            <div class="bg-white rounded border border-gray-200">
                <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">Monitorando</span>
                    <a href="{{ route('admin.processos.index-geral') }}" class="text-xs text-gray-400 hover:text-gray-600">ver todos</a>
                </div>
                <div class="divide-y divide-gray-50 max-h-[140px] overflow-y-auto">
                    @forelse(($processos_acompanhados ?? collect())->take(5) as $proc)
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$proc->estabelecimento_id, $proc->id]) }}" class="block px-3 py-1.5 hover:bg-gray-50">
                        <p class="text-xs font-medium text-gray-800">{{ $proc->numero_processo }}</p>
                        <p class="text-sm text-gray-400 truncate">{{ $proc->estabelecimento->nome_fantasia ?? '-' }}</p>
                    </a>
                    @empty
                    <div class="p-3 text-center text-xs text-gray-400">Nenhum</div>
                    @endforelse
                </div>
            </div>

            {{-- Cadastros Pendentes --}}
            @if(count($estabelecimentos_pendentes ?? []) > 0)
            <div class="bg-white rounded border border-gray-200">
                <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">Novos Cadastros</span>
                    <span class="text-xs px-1.5 py-0.5 bg-cyan-100 text-cyan-700 rounded">{{ count($estabelecimentos_pendentes) }}</span>
                </div>
                <div class="divide-y divide-gray-50 max-h-[100px] overflow-y-auto">
                    @foreach(($estabelecimentos_pendentes ?? collect())->take(3) as $est)
                    <a href="{{ route('admin.estabelecimentos.show', $est) }}" class="block px-3 py-1.5 hover:bg-gray-50">
                        <p class="text-xs font-medium text-gray-800 truncate">{{ $est->nome_fantasia ?? $est->razao_social }}</p>
                        <p class="text-sm text-gray-400">{{ $est->cidade ?? '-' }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function tarefasPaginadas() {
    return {
        tarefas: [], loading: true, currentPage: 1, lastPage: 1, total: 0,
        init() { this.load(); },
        async load() {
            this.loading = true;
            try {
                const r = await fetch(`{{ route('admin.dashboard.tarefas') }}?page=${this.currentPage}`);
                const d = await r.json();
                this.tarefas = d.data; this.currentPage = d.current_page; this.lastPage = d.last_page; this.total = d.total;
            } catch(e) { console.error(e); }
            this.loading = false;
        },
        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.load(); } },
        nextPage() { if (this.currentPage < this.lastPage) { this.currentPage++; this.load(); } },
        getBadgeClass(t) {
            if (t.atrasado) return 'bg-red-100 text-red-700';
            if (t.dias_restantes === 0) return 'bg-orange-100 text-orange-700';
            if (t.dias_restantes !== null && t.dias_restantes <= 3) return 'bg-amber-100 text-amber-700';
            if (t.dias_restantes === null) return 'bg-gray-100 text-gray-600';
            return 'bg-green-100 text-green-700';
        },
        getBadgeText(t) {
            if (t.tipo === 'assinatura') return 'Assinar';
            if (t.atrasado) return t.tipo === 'aprovacao' ? (t.dias_pendente - 5) + 'd' : Math.abs(t.dias_restantes) + 'd';
            if (t.dias_restantes === 0) return 'Hoje';
            if (t.dias_restantes === null) return '-';
            return t.dias_restantes + 'd';
        }
    }
}
function processosAtribuidos() {
    return {
        processos: [], loading: true, currentPage: 1, lastPage: 1, total: 0,
        init() { this.load(); },
        async load() {
            this.loading = true;
            try {
                const r = await fetch(`{{ route('admin.dashboard.processos-atribuidos') }}?page=${this.currentPage}`);
                const d = await r.json();
                this.processos = d.data; this.currentPage = d.current_page; this.lastPage = d.last_page; this.total = d.total;
            } catch(e) { console.error(e); }
            this.loading = false;
        },
        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.load(); } },
        nextPage() { if (this.currentPage < this.lastPage) { this.currentPage++; this.load(); } },
        getStatusClass(s) {
            return { 'aberto': 'bg-blue-100 text-blue-700', 'em_analise': 'bg-yellow-100 text-yellow-700', 'pendente': 'bg-orange-100 text-orange-700' }[s] || 'bg-gray-100 text-gray-700';
        }
    }
}
function atalhosRapidos() {
    return {
        modalAberto: false, editandoId: null, salvando: false, form: { titulo: '', url: '', icone: 'link' },
        paginas: {
            '{{ route('admin.dashboard') }}': 'Dashboard', '{{ route('admin.estabelecimentos.index') }}': 'Estabelecimentos',
            '{{ route('admin.estabelecimentos.pendentes') }}': 'Estab. Pendentes', '{{ route('admin.processos.index-geral') }}': 'Processos',
            '{{ route('admin.documentos-pendentes.index') }}': 'Docs Pendentes', '{{ route('admin.documentos.index') }}': 'Documentos',
            '{{ route('admin.assinatura.pendentes') }}': 'Assinaturas', '{{ route('admin.ordens-servico.index') }}': 'Ordens de Serviço',
            '{{ route('admin.ordens-servico.create') }}': 'Nova OS',
        },
        abrirModal() { this.editandoId = null; this.form = { titulo: '', url: '', icone: 'link' }; this.modalAberto = true; },
        editarAtalho(id, titulo, url, icone) { this.editandoId = id; this.form = { titulo, url, icone }; this.modalAberto = true; },
        fecharModal() { this.modalAberto = false; },
        atualizarTituloPadrao() { if (!this.editandoId && this.form.url && this.paginas[this.form.url]) this.form.titulo = this.paginas[this.form.url]; },
        async salvarAtalho() {
            this.salvando = true;
            try {
                const url = this.editandoId ? `/admin/atalhos-rapidos/${this.editandoId}` : '/admin/atalhos-rapidos';
                const r = await fetch(url, { method: this.editandoId ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(this.form) });
                const d = await r.json();
                if (d.success) window.location.reload(); else alert(d.error || 'Erro');
            } catch(e) { alert('Erro'); }
            this.salvando = false;
        },
        async excluirAtalho(id) {
            if (!confirm('Remover?')) return;
            try {
                const r = await fetch(`/admin/atalhos-rapidos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                const d = await r.json();
                if (d.success) window.location.reload();
            } catch(e) { alert('Erro'); }
        }
    }
}
</script>
@endsection
