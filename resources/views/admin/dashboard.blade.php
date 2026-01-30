@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $docsAtrasados = 0;
    // Prazo de 5 dias aplica-se APENAS a processos de licenciamento
    foreach($documentos_pendentes_aprovacao ?? [] as $doc) {
        if ($doc->processo && $doc->processo->tipo === 'licenciamento') {
            if ((int) $doc->created_at->diffInDays(now()) > 5) $docsAtrasados++;
        }
    }
    foreach($respostas_pendentes_aprovacao ?? [] as $resp) {
        if ($resp->documentoDigital && $resp->documentoDigital->processo && $resp->documentoDigital->processo->tipo === 'licenciamento') {
            if ((int) $resp->created_at->diffInDays(now()) > 5) $docsAtrasados++;
        }
    }
@endphp

<div class="space-y-6">
    {{-- Modal de Data de Nascimento (se não preenchida) --}}
    @if(!auth('interno')->user()->data_nascimento)
    <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            {{-- Modal --}}
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="{{ route('admin.perfil.atualizar-nascimento') }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                                <svg class="h-7 w-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                                Complete seu cadastro
                            </h3>
                            <p class="text-sm text-gray-500 mt-2">
                                Por favor, informe sua data de nascimento para continuar.
                            </p>
                        </div>
                        
                        <div class="mt-5">
                            <label for="data_nascimento_modal" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Nascimento <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="data_nascimento_modal" 
                                   name="data_nascimento" 
                                   required
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-lg">
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4">
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Salvar e Continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Olá, {{ Str::words(auth('interno')->user()->nome, 1, '') }}!</h1>
            <p class="text-sm text-gray-500">{{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM') }}</p>
        </div>
        @if($docsAtrasados > 0)
        <a href="{{ route('admin.documentos-pendentes.index') }}" class="flex items-center gap-2 px-3 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 transition">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $docsAtrasados }} documento(s) atrasado(s)
        </a>
        @endif
    </div>

    {{-- Avisos do Sistema --}}
    @if(isset($avisos_sistema) && $avisos_sistema->count() > 0)
    <div class="space-y-2">
        @foreach($avisos_sistema as $aviso)
        <div class="flex items-start gap-3 p-3 rounded-lg border {{ $aviso->tipo_color }}">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $aviso->tipo_icone }}"/>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium">{{ $aviso->titulo }}</p>
                <p class="text-xs mt-0.5 opacity-80">{{ $aviso->mensagem }}</p>
                @if($aviso->link)
                <a href="{{ $aviso->link }}" target="_blank" class="inline-flex items-center gap-1 text-xs mt-1 underline hover:opacity-80">
                    {{ $aviso->link }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('admin.documentos-pendentes.index') }}" class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_pendentes_aprovacao'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Documentos Pendentes</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['estabelecimentos_pendentes'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Estabelecimentos Pendentes</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.assinatura.pendentes') }}" class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['documentos_pendentes_assinatura'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Assinaturas Pendentes</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.processos.index-geral') }}" class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['processos_abertos'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Todos os Processos</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Layout Principal --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Coluna 1: Minhas Tarefas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100" x-data="tarefasPaginadas()">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Minhas Tarefas</h3>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400" x-show="total > 0" x-text="total + ' itens'"></span>
                    <a href="{{ route('admin.dashboard.todas-tarefas') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">ver todos</a>
                </div>
            </div>
            <div class="divide-y divide-gray-50 min-h-[200px] max-h-[320px] overflow-y-auto">
                <template x-if="loading">
                    <div class="p-8 text-center">
                        <svg class="animate-spin h-6 w-6 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                </template>
                <template x-if="!loading && tarefas.length > 0">
                    <div>
                        <template x-for="t in tarefas" :key="t.tipo + (t.id || t.processo_id)">
                            <a :href="t.url" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition" :class="t.atrasado ? 'bg-red-50/50' : ''">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :class="t.tipo === 'assinatura' ? 'bg-amber-100' : (t.tipo === 'os' ? 'bg-blue-100' : (t.tipo === 'resposta' ? (t.atrasado ? 'bg-red-100' : 'bg-green-100') : (t.atrasado ? 'bg-red-100' : 'bg-purple-100')))">
                                    <template x-if="t.tipo === 'assinatura'"><svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></template>
                                    <template x-if="t.tipo === 'os'"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></template>
                                    <template x-if="t.tipo === 'resposta'"><svg class="w-4 h-4" :class="t.atrasado ? 'text-red-600' : 'text-green-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg></template>
                                    <template x-if="t.tipo === 'aprovacao'"><svg class="w-4 h-4" :class="t.atrasado ? 'text-red-600' : 'text-purple-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <template x-if="t.tipo_processo">
                                        <span class="text-[10px] px-1.5 py-0.5 rounded mb-0.5 inline-block"
                                              :class="t.is_licenciamento ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                                              x-text="t.tipo_processo"></span>
                                    </template>
                                    <p class="text-sm font-medium text-gray-900 truncate" x-text="t.titulo"></p>
                                    <p class="text-xs text-gray-500 truncate" x-text="t.subtitulo"></p>
                                </div>
                                <span class="text-xs font-medium px-2 py-1 rounded-full" :class="getBadgeClass(t)" x-text="getBadgeText(t)"></span>
                            </a>
                        </template>
                    </div>
                </template>
                <template x-if="!loading && tarefas.length === 0">
                    <div class="p-8 text-center text-sm text-gray-400">Nenhuma tarefa pendente</div>
                </template>
            </div>
            <template x-if="total > 10">
                <div class="px-4 py-2 border-t border-gray-100 text-center">
                    <a href="{{ route('admin.dashboard.todas-tarefas') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        Ver todas as <span x-text="total"></span> tarefas →
                    </a>
                </div>
            </template>
        </div>

        {{-- Coluna 2: Meus Processos --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100" x-data="processosAtribuidos()">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Meus Processos</h3>
                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-full font-medium" x-text="total"></span>
            </div>
            <div class="divide-y divide-gray-50 min-h-[200px] max-h-[320px] overflow-y-auto">
                <template x-if="loading">
                    <div class="p-8 text-center">
                        <svg class="animate-spin h-6 w-6 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                </template>
                <template x-if="!loading && processos.length > 0">
                    <div>
                        <template x-for="p in processos" :key="p.id">
                            <a :href="p.url" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :class="p.is_meu_direto ? 'bg-blue-100' : 'bg-blue-100'">
                                    <svg class="w-4 h-4" :class="p.is_meu_direto ? 'text-blue-600' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        <span x-text="p.numero_processo"></span>
                                        <template x-if="p.prazo">
                                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full flex items-center gap-1"
                                                  :class="p.prazo.vencido ? 'bg-red-100 text-red-700' : (p.prazo.proximo ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700')">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span x-text="p.prazo.data"></span>
                                            </span>
                                        </template>
                                    </p>
                                    <p class="text-xs text-gray-500 truncate" x-text="p.estabelecimento"></p>
                                </div>
                                <span class="text-xs font-medium px-2 py-1 rounded-full" :class="getStatusClass(p.status)" x-text="p.status_nome"></span>
                            </a>
                        </template>
                    </div>
                </template>
                <template x-if="!loading && processos.length === 0">
                    <div class="p-8 text-center text-sm text-gray-400">Nenhum processo atribuído</div>
                </template>
            </div>
            <template x-if="lastPage > 1">
                <div class="px-4 py-2 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-gray-400">Página <span x-text="currentPage"></span> de <span x-text="lastPage"></span></span>
                    <div class="flex gap-1">
                        <button @click="prevPage()" :disabled="currentPage <= 1" class="p-1.5 rounded-lg hover:bg-gray-100 disabled:opacity-30 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                        <button @click="nextPage()" :disabled="currentPage >= lastPage" class="p-1.5 rounded-lg hover:bg-gray-100 disabled:opacity-30 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Coluna 3: Monitorando + Cadastros Pendentes + Atalhos --}}
        <div class="space-y-6">
            {{-- Monitorando --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Monitorando</h3>
                    <a href="{{ route('admin.processos.index-geral') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">ver todos</a>
                </div>
                <div class="divide-y divide-gray-50 max-h-[180px] overflow-y-auto">
                    @forelse(($processos_acompanhados ?? collect())->take(5) as $proc)
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$proc->estabelecimento_id, $proc->id]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $proc->numero_processo }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $proc->estabelecimento->nome_fantasia ?? $proc->estabelecimento->razao_social ?? '-' }}</p>
                        </div>
                    </a>
                    @empty
                    <div class="p-6 text-center text-sm text-gray-400">Nenhum processo monitorado</div>
                    @endforelse
                </div>
            </div>

            {{-- Cadastros Pendentes --}}
            @if(count($estabelecimentos_pendentes ?? []) > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Cadastros Pendentes</h3>
                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-full font-medium">{{ count($estabelecimentos_pendentes) }}</span>
                </div>
                <div class="divide-y divide-gray-50 max-h-[140px] overflow-y-auto">
                    @foreach(($estabelecimentos_pendentes ?? collect())->take(3) as $est)
                    <a href="{{ route('admin.estabelecimentos.show', $est) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $est->nome_fantasia ?? $est->razao_social }}</p>
                            <p class="text-xs text-gray-500">{{ $est->cidade ?? '-' }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Atalhos Rápidos --}}
            <div class="bg-blue-600 rounded-xl shadow-sm" x-data="atalhosRapidos()">
                <div class="px-3 py-2 border-b border-white/20 flex items-center justify-between">
                    <h3 class="font-semibold text-white text-sm">Atalhos</h3>
                    <button @click="abrirModal()" class="text-white/70 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </button>
                </div>
                <div class="divide-y divide-white/10 max-h-48 overflow-y-auto">
                    @forelse($atalhos_rapidos ?? [] as $atalho)
                    <div class="group relative">
                        <a href="{{ $atalho->url }}" class="flex items-center gap-2 px-3 py-2 hover:bg-white/10 transition">
                            <div class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            </div>
                            <span class="text-xs text-white font-medium truncate">{{ $atalho->titulo }}</span>
                        </a>
                        <div class="absolute right-2 top-1/2 -translate-y-1/2 hidden group-hover:flex gap-1">
                            <button @click.prevent="editarAtalho({{ $atalho->id }}, '{{ addslashes($atalho->titulo) }}', '{{ addslashes($atalho->url) }}', '{{ $atalho->icone }}')" class="p-1 rounded-lg text-white/70 hover:text-white hover:bg-white/20 transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <button @click.prevent="excluirAtalho({{ $atalho->id }})" class="p-1 rounded-lg text-white/70 hover:text-red-300 hover:bg-white/20 transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    @empty
                    <a href="{{ route('admin.documentos-pendentes.index') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-white/10 transition">
                        <div class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-xs text-white font-medium truncate">Documentos Pendentes</span>
                    </a>
                    <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-white/10 transition">
                        <div class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span class="text-xs text-white font-medium truncate">Estabelecimentos Pendentes</span>
                    </a>
                    <a href="{{ route('admin.ordens-servico.index') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-white/10 transition">
                        <div class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <span class="text-xs text-white font-medium truncate">Ordens de Serviço</span>
                    </a>
                    @endforelse
                </div>

                {{-- Modal Atalhos --}}
                <div x-show="modalAberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5)">
                    <div @click.away="fecharModal()" class="bg-white rounded-xl shadow-2xl w-full max-w-sm">
                        <div class="px-5 py-4 border-b flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-gray-900" x-text="editandoId ? 'Editar Atalho' : 'Novo Atalho'"></h4>
                            <button @click="fecharModal()" class="text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <form @submit.prevent="salvarAtalho()" class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Página</label>
                                <select x-model="form.url" @change="atualizarTituloPadrao()" required class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" x-model="form.titulo" required maxlength="50" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="fecharModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancelar</button>
                                <button type="submit" :disabled="salvando" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition" x-text="salvando ? 'Salvando...' : 'Salvar'"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
                const r = await fetch(`{{ route('admin.dashboard.tarefas') }}?page=${this.currentPage}&per_page=10`);
                const d = await r.json();
                this.tarefas = d.data.slice(0, 10); this.currentPage = d.current_page; this.lastPage = Math.min(d.last_page, 1); this.total = d.total;
            } catch(e) { console.error(e); }
            this.loading = false;
        },
        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.load(); } },
        nextPage() { if (this.currentPage < this.lastPage) { this.currentPage++; this.load(); } },
        getBadgeClass(t) {
            // Se não é licenciamento, não tem prazo - mostra cinza
            if (t.is_licenciamento === false) return 'bg-gray-100 text-gray-600';
            if (t.atrasado) return 'bg-red-100 text-red-700';
            if (t.dias_restantes === 0) return 'bg-orange-100 text-orange-700';
            if (t.dias_restantes !== null && t.dias_restantes <= 3) return 'bg-amber-100 text-amber-700';
            if (t.dias_restantes === null) return 'bg-gray-100 text-gray-600';
            return 'bg-green-100 text-green-700';
        },
        getBadgeText(t) {
            if (t.tipo === 'assinatura') return 'Assinar';
            // Se não é licenciamento, mostra "Verificar" (sem prazo)
            if (t.is_licenciamento === false) return 'Verificar';
            if (t.tipo === 'resposta') {
                if (t.atrasado) return (t.dias_pendente - 5) + 'd';
                if (t.dias_restantes === 0) return 'Hoje';
                if (t.dias_restantes === null) return 'Verificar';
                return t.dias_restantes + 'd';
            }
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
                const url = this.editandoId ? `${window.APP_URL}/admin/atalhos-rapidos/${this.editandoId}` : `${window.APP_URL}/admin/atalhos-rapidos`;
                const r = await fetch(url, { method: this.editandoId ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(this.form) });
                const d = await r.json();
                if (d.success) window.location.reload(); else alert(d.error || 'Erro');
            } catch(e) { alert('Erro'); }
            this.salvando = false;
        },
        async excluirAtalho(id) {
            if (!confirm('Remover este atalho?')) return;
            try {
                const r = await fetch(`${window.APP_URL}/admin/atalhos-rapidos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                const d = await r.json();
                if (d.success) window.location.reload();
            } catch(e) { alert('Erro'); }
        }
    }
}
</script>
@endsection
