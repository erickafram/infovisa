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

<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Olá, {{ Str::words(auth('interno')->user()->nome, 1, '') }}!</h1>
            <p class="text-xs text-gray-500">{{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM') }}</p>
        </div>
        @if($docsAtrasados > 0)
        <a href="{{ route('admin.documentos-pendentes.index') }}" class="flex items-center gap-2 px-3 py-1.5 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ $docsAtrasados }} atrasado(s)
        </a>
        @endif
    </div>

    {{-- Mini Cards --}}
    <div class="grid grid-cols-4 gap-3">
        <a href="{{ route('admin.assinatura.pendentes') }}" class="bg-white rounded-lg border border-gray-200 p-3 hover:border-amber-300 hover:shadow transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-200 transition flex-shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-lg font-bold text-gray-900">{{ $stats['documentos_pendentes_assinatura'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 truncate">Assinaturas</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.documentos-pendentes.index') }}" class="bg-white rounded-lg border {{ $docsAtrasados > 0 ? 'border-red-300 bg-red-50/50' : 'border-gray-200' }} p-3 hover:border-purple-300 hover:shadow transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 {{ $docsAtrasados > 0 ? 'bg-red-100' : 'bg-purple-100' }} rounded-lg flex items-center justify-center transition flex-shrink-0">
                    <svg class="w-4 h-4 {{ $docsAtrasados > 0 ? 'text-red-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-lg font-bold {{ $docsAtrasados > 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $stats['total_pendentes_aprovacao'] ?? 0 }}</p>
                    <p class="text-xs {{ $docsAtrasados > 0 ? 'text-red-600' : 'text-gray-500' }} truncate">Aprovações</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.documentos.index', ['status' => 'com_prazos']) }}" class="bg-white rounded-lg border border-gray-200 p-3 hover:border-red-300 hover:shadow transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center group-hover:bg-red-200 transition flex-shrink-0">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-lg font-bold text-gray-900">{{ $stats['documentos_vencendo'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 truncate">Prazos</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="bg-white rounded-lg border border-gray-200 p-3 hover:border-cyan-300 hover:shadow transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center group-hover:bg-cyan-200 transition flex-shrink-0">
                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-lg font-bold text-gray-900">{{ $stats['estabelecimentos_pendentes'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 truncate">Cadastros</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Layout Principal --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        {{-- Coluna Principal --}}
        <div class="xl:col-span-2 space-y-4">
            
            {{-- Minhas Tarefas com Paginação AJAX --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden" x-data="tarefasPaginadas()">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Minhas Tarefas
                    </h2>
                    <span class="text-xs text-gray-500" x-text="total > 0 ? total + ' pendente(s)' : ''"></span>
                </div>
                
                <div class="divide-y divide-gray-100 min-h-[200px]">
                    {{-- Loading --}}
                    <template x-if="loading">
                        <div class="px-4 py-8 text-center">
                            <svg class="animate-spin h-6 w-6 text-blue-500 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-gray-500 mt-2">Carregando...</p>
                        </div>
                    </template>

                    {{-- Lista de Tarefas --}}
                    <template x-if="!loading && tarefas.length > 0">
                        <div>
                            <template x-for="tarefa in tarefas" :key="tarefa.tipo + '-' + (tarefa.id || tarefa.processo_id)">
                                <a :href="tarefa.url" class="block px-4 py-2.5 hover:bg-gray-50 transition" :class="tarefa.atrasado ? 'bg-red-50/50' : ''">
                                    <div class="flex items-center gap-3">
                                        {{-- Ícone --}}
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 relative"
                                             :class="tarefa.tipo === 'assinatura' ? 'bg-amber-100' : (tarefa.tipo === 'os' ? (tarefa.atrasado ? 'bg-red-100' : 'bg-blue-100') : (tarefa.atrasado ? 'bg-red-100' : 'bg-purple-100'))">
                                            <template x-if="tarefa.tipo === 'assinatura'">
                                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </template>
                                            <template x-if="tarefa.tipo === 'os'">
                                                <svg class="w-4 h-4" :class="tarefa.atrasado ? 'text-red-600' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                            </template>
                                            <template x-if="tarefa.tipo === 'aprovacao'">
                                                <svg class="w-4 h-4" :class="tarefa.atrasado ? 'text-red-600' : 'text-purple-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </template>
                                            <template x-if="tarefa.total > 1">
                                                <span class="absolute -top-1 -right-1 w-4 h-4 text-white text-[9px] font-bold rounded-full flex items-center justify-center"
                                                      :class="tarefa.atrasado ? 'bg-red-500' : 'bg-purple-500'" x-text="tarefa.total"></span>
                                            </template>
                                        </div>
                                        {{-- Conteúdo --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-medium text-gray-900 truncate" x-text="tarefa.titulo"></p>
                                                <template x-if="tarefa.tipo === 'os'">
                                                    <span class="px-1.5 py-0.5 text-[10px] font-medium rounded bg-blue-100 text-blue-700">OS</span>
                                                </template>
                                                <template x-if="tarefa.total > 1">
                                                    <span class="text-xs text-gray-400" x-text="'+' + (tarefa.total - 1)"></span>
                                                </template>
                                            </div>
                                            <p class="text-xs text-gray-500 truncate" x-text="tarefa.subtitulo"></p>
                                        </div>
                                        {{-- Badge/Ação --}}
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            <template x-if="tarefa.tipo === 'assinatura'">
                                                <span class="px-2.5 py-1 bg-amber-500 text-white text-xs font-medium rounded hover:bg-amber-600 transition">Assinar</span>
                                            </template>
                                            <template x-if="tarefa.tipo !== 'assinatura'">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                                      :class="getBadgeClass(tarefa)" x-text="getBadgeText(tarefa)"></span>
                                            </template>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </div>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </template>

                    {{-- Vazio --}}
                    <template x-if="!loading && tarefas.length === 0">
                        <div class="px-4 py-6 text-center">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-sm text-gray-500">Nenhuma tarefa pendente</p>
                        </div>
                    </template>
                </div>

                {{-- Paginação --}}
                <template x-if="lastPage > 1">
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-500">Página <span x-text="currentPage"></span> de <span x-text="lastPage"></span></span>
                        <div class="flex gap-1">
                            <button @click="prevPage()" :disabled="currentPage <= 1" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button @click="nextPage()" :disabled="currentPage >= lastPage" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Processos Atribuídos com Paginação AJAX --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden" x-data="processosAtribuidos()">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Processos Atribuídos
                    </h2>
                    <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 text-xs font-medium rounded-full" x-text="total"></span>
                </div>
                
                <div class="divide-y divide-gray-100 min-h-[150px]">
                    {{-- Loading --}}
                    <template x-if="loading">
                        <div class="px-4 py-6 text-center">
                            <svg class="animate-spin h-5 w-5 text-cyan-500 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </template>

                    {{-- Lista --}}
                    <template x-if="!loading && processos.length > 0">
                        <div>
                            <template x-for="proc in processos" :key="proc.id">
                                <a :href="proc.url" class="block px-4 py-2.5 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                             :class="proc.is_meu_direto ? 'bg-cyan-100' : 'bg-blue-100'">
                                            <svg class="w-4 h-4" :class="proc.is_meu_direto ? 'text-cyan-600' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-medium text-gray-900" x-text="proc.numero_processo"></p>
                                                <span class="px-1.5 py-0.5 text-[10px] font-medium rounded"
                                                      :class="proc.is_meu_direto ? 'bg-cyan-100 text-cyan-700' : 'bg-blue-100 text-blue-700'"
                                                      x-text="proc.is_meu_direto ? 'Meu' : 'Setor'"></span>
                                            </div>
                                            <p class="text-xs text-gray-500 truncate" x-text="proc.estabelecimento"></p>
                                        </div>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full"
                                              :class="getStatusClass(proc.status)" x-text="proc.status_nome"></span>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </template>

                    {{-- Vazio --}}
                    <template x-if="!loading && processos.length === 0">
                        <div class="px-4 py-6 text-center text-xs text-gray-400">Nenhum processo atribuído</div>
                    </template>
                </div>

                {{-- Paginação --}}
                <template x-if="lastPage > 1">
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-500">Página <span x-text="currentPage"></span> de <span x-text="lastPage"></span></span>
                        <div class="flex gap-1">
                            <button @click="prevPage()" :disabled="currentPage <= 1" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button @click="nextPage()" :disabled="currentPage >= lastPage" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Grid Cadastros e Prazos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Novos Cadastros --}}
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                        <h2 class="font-semibold text-gray-900 text-sm">Novos Cadastros</h2>
                        @if(count($estabelecimentos_pendentes ?? []) > 0)
                        <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 text-xs font-medium rounded-full">{{ count($estabelecimentos_pendentes) }}</span>
                        @endif
                    </div>
                    <div class="divide-y divide-gray-100 max-h-[180px] overflow-y-auto">
                        @forelse(($estabelecimentos_pendentes ?? collect())->take(4) as $est)
                        <a href="{{ route('admin.estabelecimentos.show', $est) }}" class="block px-4 py-2 hover:bg-gray-50 transition">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $est->nome_fantasia ?? $est->razao_social }}</p>
                            <p class="text-xs text-gray-500">{{ $est->cidade ?? '-' }} • {{ $est->created_at->diffForHumans() }}</p>
                        </a>
                        @empty
                        <div class="px-4 py-4 text-center text-xs text-gray-400">Nenhum cadastro pendente</div>
                        @endforelse
                    </div>
                    @if(count($estabelecimentos_pendentes ?? []) > 4)
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100">
                        <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="text-xs text-cyan-600 hover:text-cyan-700 font-medium">Ver todos →</a>
                    </div>
                    @endif
                </div>

                {{-- Prazos --}}
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                        <h2 class="font-semibold text-gray-900 text-sm">Prazos Próximos</h2>
                        <a href="{{ route('admin.documentos.index', ['status' => 'com_prazos']) }}" class="text-xs text-red-600 hover:text-red-700">Ver agenda</a>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-[180px] overflow-y-auto">
                        @forelse(($documentos_vencendo ?? collect())->take(4) as $doc)
                        @php
                            $diasFaltando = $doc->dias_faltando;
                            $isVencido = $diasFaltando < 0;
                            $isHoje = $diasFaltando == 0;
                        @endphp
                        <a href="{{ route('admin.documentos.show', $doc->id) }}" class="block px-4 py-2 hover:bg-gray-50 transition {{ $isVencido ? 'bg-red-50/50' : '' }}">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $doc->tipoDocumento->nome ?? 'Documento' }}</p>
                                    <p class="text-xs text-gray-500">{{ $doc->numero_documento }}</p>
                                </div>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full ml-2 {{ $isVencido ? 'bg-red-100 text-red-700' : ($isHoje ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $isVencido ? 'Vencido' : ($isHoje ? 'Hoje' : $diasFaltando . 'd') }}
                                </span>
                            </div>
                        </a>
                        @empty
                        <div class="px-4 py-4 text-center text-xs text-gray-400">Nenhum prazo próximo</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Coluna Lateral --}}
        <div class="space-y-4">
            {{-- Processos Monitorados --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                    <h2 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Monitorando
                    </h2>
                    <a href="{{ route('admin.processos.index-geral') }}" class="text-xs text-gray-500 hover:text-gray-700">Ver todos</a>
                </div>
                <div class="divide-y divide-gray-100 max-h-[180px] overflow-y-auto">
                    @forelse(($processos_acompanhados ?? collect())->take(5) as $proc)
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$proc->estabelecimento_id, $proc->id]) }}" class="block px-4 py-2 hover:bg-gray-50 transition">
                        <p class="text-sm font-medium text-gray-900">{{ $proc->numero_processo }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $proc->estabelecimento->nome_fantasia ?? 'Sem nome' }}</p>
                    </a>
                    @empty
                    <div class="px-4 py-4 text-center text-xs text-gray-400">Nenhum processo monitorado</div>
                    @endforelse
                </div>
            </div>

            {{-- Ações Rápidas --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg overflow-hidden" x-data="atalhosRapidos()">
                <div class="px-4 py-2.5 border-b border-blue-400/30 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-white">Ações Rápidas</h3>
                    <button @click="abrirModal()" class="text-blue-200 hover:text-white transition" title="Adicionar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </button>
                </div>
                <div class="divide-y divide-blue-400/20">
                    @forelse($atalhos_rapidos ?? [] as $atalho)
                    <div class="group relative">
                        <a href="{{ $atalho->url }}" class="flex items-center gap-3 px-4 py-2 hover:bg-blue-400/20 transition">
                            <span class="text-sm text-white">{{ $atalho->titulo }}</span>
                        </a>
                        <div class="absolute right-2 top-1/2 -translate-y-1/2 hidden group-hover:flex gap-1">
                            <button @click.prevent="editarAtalho({{ $atalho->id }}, '{{ addslashes($atalho->titulo) }}', '{{ addslashes($atalho->url) }}', '{{ $atalho->icone }}')" class="p-1 text-blue-200 hover:text-white"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                            <button @click.prevent="excluirAtalho({{ $atalho->id }})" class="p-1 text-blue-200 hover:text-red-300"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </div>
                    @empty
                    <a href="{{ route('admin.documentos-pendentes.index') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-blue-400/20 transition"><span class="text-sm text-white">Documentos Pendentes</span></a>
                    <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-blue-400/20 transition"><span class="text-sm text-white">Estabelecimentos Pendentes</span></a>
                    <a href="{{ route('admin.assinatura.pendentes') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-blue-400/20 transition"><span class="text-sm text-white">Assinaturas Pendentes</span></a>
                    @endforelse
                </div>

                {{-- Modal Atalho --}}
                <div x-show="modalAberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5)">
                    <div @click.away="fecharModal()" class="bg-white rounded-xl shadow-xl w-full max-w-md text-gray-900">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="font-semibold text-lg" x-text="editandoId ? 'Editar Atalho' : 'Novo Atalho'"></h3>
                            <button @click="fecharModal()" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        <form @submit.prevent="salvarAtalho()" class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Página</label>
                                <select x-model="form.url" @change="atualizarTituloPadrao()" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Selecione...</option>
                                    <optgroup label="Dashboard"><option value="{{ route('admin.dashboard') }}">Dashboard</option></optgroup>
                                    <optgroup label="Estabelecimentos">
                                        <option value="{{ route('admin.estabelecimentos.index') }}">Todos</option>
                                        <option value="{{ route('admin.estabelecimentos.pendentes') }}">Pendentes</option>
                                    </optgroup>
                                    <optgroup label="Processos">
                                        <option value="{{ route('admin.processos.index-geral') }}">Todos</option>
                                        <option value="{{ route('admin.documentos-pendentes.index') }}">Docs Pendentes</option>
                                    </optgroup>
                                    <optgroup label="Documentos">
                                        <option value="{{ route('admin.documentos.index') }}">Todos</option>
                                        <option value="{{ route('admin.assinatura.pendentes') }}">Assinaturas</option>
                                    </optgroup>
                                    <optgroup label="OS">
                                        <option value="{{ route('admin.ordens-servico.index') }}">Todas</option>
                                        <option value="{{ route('admin.ordens-servico.create') }}">Nova OS</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                <input type="text" x-model="form.titulo" required maxlength="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="fecharModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancelar</button>
                                <button type="submit" :disabled="salvando" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    <span x-show="!salvando" x-text="editandoId ? 'Salvar' : 'Adicionar'"></span>
                                    <span x-show="salvando">Salvando...</span>
                                </button>
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
        tarefas: [],
        loading: true,
        currentPage: 1,
        lastPage: 1,
        total: 0,
        
        init() {
            this.loadTarefas();
        },
        
        async loadTarefas() {
            this.loading = true;
            try {
                const response = await fetch(`{{ route('admin.dashboard.tarefas') }}?page=${this.currentPage}`);
                const data = await response.json();
                this.tarefas = data.data;
                this.currentPage = data.current_page;
                this.lastPage = data.last_page;
                this.total = data.total;
            } catch (e) {
                console.error('Erro ao carregar tarefas:', e);
            }
            this.loading = false;
        },
        
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadTarefas();
            }
        },
        
        nextPage() {
            if (this.currentPage < this.lastPage) {
                this.currentPage++;
                this.loadTarefas();
            }
        },
        
        getBadgeClass(tarefa) {
            if (tarefa.atrasado) return 'bg-red-100 text-red-700';
            if (tarefa.dias_restantes === 0) return 'bg-orange-100 text-orange-700';
            if (tarefa.dias_restantes !== null && tarefa.dias_restantes <= 3) return 'bg-amber-100 text-amber-700';
            if (tarefa.dias_restantes === null) return 'bg-gray-100 text-gray-600';
            return 'bg-green-100 text-green-700';
        },
        
        getBadgeText(tarefa) {
            if (tarefa.atrasado) {
                if (tarefa.tipo === 'aprovacao') return (tarefa.dias_pendente - 5) + 'd atrasado';
                return Math.abs(tarefa.dias_restantes) + 'd atrasado';
            }
            if (tarefa.dias_restantes === 0) return 'Hoje';
            if (tarefa.dias_restantes === null) return '-';
            return tarefa.dias_restantes + 'd';
        }
    }
}

function processosAtribuidos() {
    return {
        processos: [],
        loading: true,
        currentPage: 1,
        lastPage: 1,
        total: 0,
        
        init() {
            this.loadProcessos();
        },
        
        async loadProcessos() {
            this.loading = true;
            try {
                const response = await fetch(`{{ route('admin.dashboard.processos-atribuidos') }}?page=${this.currentPage}`);
                const data = await response.json();
                this.processos = data.data;
                this.currentPage = data.current_page;
                this.lastPage = data.last_page;
                this.total = data.total;
            } catch (e) {
                console.error('Erro ao carregar processos:', e);
            }
            this.loading = false;
        },
        
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadProcessos();
            }
        },
        
        nextPage() {
            if (this.currentPage < this.lastPage) {
                this.currentPage++;
                this.loadProcessos();
            }
        },
        
        getStatusClass(status) {
            const classes = {
                'aberto': 'bg-blue-100 text-blue-700',
                'em_analise': 'bg-yellow-100 text-yellow-700',
                'pendente': 'bg-orange-100 text-orange-700',
            };
            return classes[status] || 'bg-gray-100 text-gray-700';
        }
    }
}

function atalhosRapidos() {
    return {
        modalAberto: false,
        editandoId: null,
        salvando: false,
        form: { titulo: '', url: '', icone: 'link' },
        paginas: {
            '{{ route('admin.dashboard') }}': 'Dashboard',
            '{{ route('admin.estabelecimentos.index') }}': 'Estabelecimentos',
            '{{ route('admin.estabelecimentos.pendentes') }}': 'Estabelecimentos Pendentes',
            '{{ route('admin.processos.index-geral') }}': 'Processos',
            '{{ route('admin.documentos-pendentes.index') }}': 'Docs Pendentes',
            '{{ route('admin.documentos.index') }}': 'Documentos',
            '{{ route('admin.assinatura.pendentes') }}': 'Assinaturas',
            '{{ route('admin.ordens-servico.index') }}': 'Ordens de Serviço',
            '{{ route('admin.ordens-servico.create') }}': 'Nova OS',
        },
        abrirModal() { this.editandoId = null; this.form = { titulo: '', url: '', icone: 'link' }; this.modalAberto = true; },
        editarAtalho(id, titulo, url, icone) { this.editandoId = id; this.form = { titulo, url, icone }; this.modalAberto = true; },
        fecharModal() { this.modalAberto = false; this.editandoId = null; },
        atualizarTituloPadrao() { if (!this.editandoId && this.form.url && this.paginas[this.form.url]) this.form.titulo = this.paginas[this.form.url]; },
        async salvarAtalho() {
            this.salvando = true;
            try {
                const url = this.editandoId ? `/admin/atalhos-rapidos/${this.editandoId}` : '/admin/atalhos-rapidos';
                const response = await fetch(url, {
                    method: this.editandoId ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(this.form)
                });
                const data = await response.json();
                if (data.success) window.location.reload();
                else alert(data.error || 'Erro');
            } catch (e) { alert('Erro ao salvar'); }
            this.salvando = false;
        },
        async excluirAtalho(id) {
            if (!confirm('Remover?')) return;
            try {
                const response = await fetch(`/admin/atalhos-rapidos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                const data = await response.json();
                if (data.success) window.location.reload();
            } catch (e) { alert('Erro'); }
        }
    }
}
</script>
@endsection
