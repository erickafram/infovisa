@extends('layouts.admin')

@section('title', 'Todas as Tarefas')
@section('page-title', 'Todas as Tarefas')

@section('content')
<div class="max-w-8xl mx-auto" x-data="todasTarefas()" x-init="init()">
    
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    Todas as Tarefas
                </h1>
                <p class="mt-1 text-gray-600">Tarefas pessoais e demandas do setor em um só lugar</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar ao Dashboard
            </a>
        </div>
    </div>

    {{-- Cards resumo --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Card Para Mim --}}
        <button @click="filtro = 'para_mim'; currentPage = 1; load()" 
                class="relative overflow-hidden rounded-xl p-4 text-left transition-all border-2"
                :class="filtro === 'para_mim' ? 'border-blue-500 shadow-lg shadow-blue-100' : 'border-transparent bg-white shadow-sm hover:shadow-md'">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-10"></div>
            <div class="relative flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Para Mim</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-blue-600" x-text="contadores.para_mim || 0"></span>
                        <span class="text-xs text-gray-500">tarefas pessoais</span>
                    </div>
                </div>
            </div>
            <div class="relative mt-2 flex gap-3 text-xs text-gray-500">
                <span><span class="font-medium text-blue-600" x-text="contadores.os || 0"></span> OS</span>
                <span><span class="font-medium text-amber-600" x-text="contadores.assinatura || 0"></span> Assinaturas</span>
            </div>
        </button>

        {{-- Card Demandas do Setor --}}
        <button @click="filtro = 'setor'; currentPage = 1; load()" 
                class="relative overflow-hidden rounded-xl p-4 text-left transition-all border-2"
                :class="filtro === 'setor' ? 'border-purple-500 shadow-lg shadow-purple-100' : 'border-transparent bg-white shadow-sm hover:shadow-md'">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 opacity-10"></div>
            <div class="relative flex items-center gap-3">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Demandas do Setor</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-purple-600" x-text="contadores.setor || 0"></span>
                        <span class="text-xs text-gray-500">pendências</span>
                    </div>
                </div>
            </div>
            <div class="relative mt-2 flex gap-3 text-xs text-gray-500">
                <span><span class="font-medium text-purple-600" x-text="contadores.aprovacao || 0"></span> Aprovações</span>
                <span><span class="font-medium text-green-600" x-text="contadores.resposta || 0"></span> Respostas</span>
            </div>
        </button>

        {{-- Card Total --}}
        <button @click="filtro = 'todos'; currentPage = 1; load()" 
                class="relative overflow-hidden rounded-xl p-4 text-left transition-all border-2"
                :class="filtro === 'todos' ? 'border-gray-500 shadow-lg shadow-gray-100' : 'border-transparent bg-white shadow-sm hover:shadow-md'">
            <div class="absolute inset-0 bg-gradient-to-br from-gray-500 to-gray-600 opacity-10"></div>
            <div class="relative flex items-center gap-3">
                <div class="p-2 bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Todas</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-gray-700" x-text="contadores.total || 0"></span>
                        <span class="text-xs text-gray-500">no total</span>
                    </div>
                </div>
            </div>
            <div class="relative mt-2 text-xs text-gray-500">
                Visão completa de todas as pendências
            </div>
        </button>
    </div>

    {{-- Filtros detalhados --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-medium text-gray-700">Filtrar por tipo:</span>
            
            <button @click="filtro = 'todos'; currentPage = 1; load()" 
                    :class="filtro === 'todos' ? 'bg-gray-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                Todos
                <span class="px-1.5 py-0.5 rounded-full text-xs" :class="filtro === 'todos' ? 'bg-white/20' : 'bg-gray-200'" x-text="contadores.total || 0"></span>
            </button>

            <span class="text-gray-300">|</span>

            {{-- Grupo "Para Mim" --}}
            <span class="text-xs text-blue-600 font-semibold uppercase tracking-wider">Para Mim:</span>
            <button @click="filtro = 'para_mim'; currentPage = 1; load()" 
                    :class="filtro === 'para_mim' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                Todos
                <span class="px-1.5 py-0.5 rounded-full text-xs" :class="filtro === 'para_mim' ? 'bg-white/20' : 'bg-blue-100'" x-text="contadores.para_mim || 0"></span>
            </button>
            <button @click="filtro = 'os'; currentPage = 1; load()" 
                    :class="filtro === 'os' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                OS
                <span class="px-1.5 py-0.5 rounded-full text-xs" :class="filtro === 'os' ? 'bg-white/20' : 'bg-blue-100'" x-text="contadores.os || 0"></span>
            </button>
            <button @click="filtro = 'assinatura'; currentPage = 1; load()" 
                    :class="filtro === 'assinatura' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Assinaturas
                <span class="px-1.5 py-0.5 rounded-full text-xs" :class="filtro === 'assinatura' ? 'bg-white/20' : 'bg-amber-100'" x-text="contadores.assinatura || 0"></span>
            </button>
            
            <span class="text-gray-300">|</span>
            
            {{-- Grupo "Setor" --}}
            <span class="text-xs text-purple-600 font-semibold uppercase tracking-wider">Meu Setor:</span>
            <button @click="filtro = 'setor'; currentPage = 1; load()" 
                    :class="filtro === 'setor' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100'"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                Todos
                <span class="px-1.5 py-0.5 rounded-full text-xs" :class="filtro === 'setor' ? 'bg-white/20' : 'bg-purple-100'" x-text="contadores.setor || 0"></span>
            </button>
            <button @click="filtro = 'aprovacao'; currentPage = 1; load()" 
                    :class="filtro === 'aprovacao' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100'"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Aprovações
                <span class="px-1.5 py-0.5 rounded-full text-xs" :class="filtro === 'aprovacao' ? 'bg-white/20' : 'bg-purple-100'" x-text="contadores.aprovacao || 0"></span>
            </button>
            <button @click="filtro = 'resposta'; currentPage = 1; load()" 
                    :class="filtro === 'resposta' ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100'"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                Respostas
                <span class="px-1.5 py-0.5 rounded-full text-xs" :class="filtro === 'resposta' ? 'bg-white/20' : 'bg-green-100'" x-text="contadores.resposta || 0"></span>
            </button>
        </div>
    </div>

    {{-- Lista de Tarefas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Loading --}}
        <template x-if="loading">
            <div class="p-12 text-center">
                <svg class="animate-spin h-8 w-8 text-purple-600 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <p class="mt-3 text-gray-500">Carregando tarefas...</p>
            </div>
        </template>

        {{-- Lista Vazia --}}
        <template x-if="!loading && tarefas.length === 0">
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    <template x-if="filtro === 'para_mim'"><span>Nenhuma tarefa pessoal pendente</span></template>
                    <template x-if="filtro === 'setor'"><span>Nenhuma demanda do setor pendente</span></template>
                    <template x-if="filtro !== 'para_mim' && filtro !== 'setor'"><span>Nenhuma tarefa pendente</span></template>
                </h3>
                <p class="text-gray-500">Tudo em dia! Bom trabalho.</p>
            </div>
        </template>

        {{-- Lista de Tarefas com separadores de grupo --}}
        <template x-if="!loading && tarefas.length > 0">
            <div class="divide-y divide-gray-100">
                <template x-for="(t, index) in tarefas" :key="t.tipo + (t.id || t.processo_id) + index">
                    <div>
                        {{-- Separador de grupo (apenas no filtro "todos") --}}
                        <template x-if="filtro === 'todos' && showGroupHeader(t, index)">
                            <div class="px-6 py-2.5 flex items-center gap-2"
                                 :class="t.grupo === 'para_mim' ? 'bg-blue-50 border-l-4 border-blue-400' : 'bg-purple-50 border-l-4 border-purple-400'">
                                <template x-if="t.grupo === 'para_mim'">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </template>
                                <template x-if="t.grupo !== 'para_mim'">
                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </template>
                                <span class="text-xs font-semibold uppercase tracking-wider"
                                      :class="t.grupo === 'para_mim' ? 'text-blue-600' : 'text-purple-600'"
                                      x-text="t.grupo === 'para_mim' ? 'Para Mim — Tarefas pessoais' : 'Demandas do Setor — Qualquer técnico pode resolver'"></span>
                            </div>
                        </template>

                        {{-- Item da tarefa --}}
                        <a :href="t.url" 
                           class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition"
                           :class="t.atrasado ? 'bg-red-50/50' : ''">
                            
                            {{-- Indicador lateral de grupo --}}
                            <div class="w-1 h-10 rounded-full flex-shrink-0"
                                 :class="t.grupo === 'para_mim' ? 'bg-blue-400' : 'bg-purple-400'"></div>

                            {{-- Ícone --}}
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                 :class="getIconBgClass(t)">
                                <template x-if="t.tipo === 'assinatura'">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </template>
                                <template x-if="t.tipo === 'os'">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </template>
                                <template x-if="t.tipo === 'resposta'">
                                    <svg class="w-5 h-5" :class="t.atrasado ? 'text-red-600' : 'text-green-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                </template>
                                <template x-if="t.tipo === 'aprovacao'">
                                    <svg class="w-5 h-5" :class="t.atrasado ? 'text-red-600' : 'text-purple-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                            </div>

                            {{-- Conteúdo --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    {{-- Badge tipo --}}
                                    <span class="text-[10px] px-2 py-0.5 rounded font-semibold"
                                          :class="{
                                              'bg-blue-100 text-blue-700': t.tipo === 'os',
                                              'bg-amber-100 text-amber-700': t.tipo === 'assinatura',
                                              'bg-purple-100 text-purple-700': t.tipo === 'aprovacao',
                                              'bg-green-100 text-green-700': t.tipo === 'resposta'
                                          }"
                                          x-text="{'os': 'Ordem de Serviço', 'assinatura': 'Assinatura', 'aprovacao': 'Aprovação', 'resposta': 'Resposta'}[t.tipo]"></span>
                                    <template x-if="t.tipo_processo">
                                        <span class="text-[10px] px-2 py-0.5 rounded font-medium"
                                              :class="t.is_licenciamento ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'"
                                              x-text="t.tipo_processo"></span>
                                    </template>
                                    <template x-if="t.numero_processo">
                                        <span class="text-xs text-gray-500" x-text="t.numero_processo"></span>
                                    </template>
                                </div>
                                <p class="text-sm font-medium text-gray-900" x-text="t.titulo"></p>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="t.subtitulo"></p>
                                <template x-if="t.tipo_acao">
                                    <p class="text-xs text-blue-600 mt-0.5" x-text="t.tipo_acao"></p>
                                </template>
                            </div>

                            {{-- Info adicional --}}
                            <div class="text-right flex-shrink-0">
                                <span class="text-xs font-medium px-3 py-1.5 rounded-full" 
                                      :class="getBadgeClass(t)" 
                                      x-text="getBadgeText(t)"></span>
                                <p class="text-xs text-gray-400 mt-2" x-text="t.data"></p>
                            </div>
                        </a>
                    </div>
                </template>
            </div>
        </template>

        {{-- Paginação --}}
        <template x-if="!loading && lastPage > 1">
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50">
                <span class="text-sm text-gray-600">
                    Mostrando <span x-text="((currentPage - 1) * perPage) + 1"></span> a 
                    <span x-text="Math.min(currentPage * perPage, totalFiltrado)"></span> de 
                    <span x-text="totalFiltrado"></span> tarefas
                </span>
                <div class="flex items-center gap-2">
                    <button @click="prevPage()" 
                            :disabled="currentPage <= 1" 
                            class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed hover:bg-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <span class="text-sm text-gray-600">
                        Página <span x-text="currentPage"></span> de <span x-text="lastPage"></span>
                    </span>
                    <button @click="nextPage()" 
                            :disabled="currentPage >= lastPage" 
                            class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed hover:bg-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- Legenda --}}
    <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
            <span class="font-medium text-gray-700">Legenda:</span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-blue-400"></span>
                Para Mim — Só eu posso resolver
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-purple-400"></span>
                Meu Setor — Qualquer técnico do setor pode resolver
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-red-400"></span>
                Atrasado — Prazo excedido
            </span>
        </div>
    </div>
</div>

<script>
function todasTarefas() {
    return {
        tarefas: [],
        loading: true,
        currentPage: 1,
        lastPage: 1,
        totalFiltrado: 0,
        perPage: 20,
        filtro: 'todos',
        contadores: { total: 0, aprovacao: 0, resposta: 0, assinatura: 0, os: 0, para_mim: 0, setor: 0 },

        init() {
            this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    filtro: this.filtro
                });
                const r = await fetch(`{{ route('admin.dashboard.todas-tarefas-paginadas') }}?${params}`);
                const d = await r.json();
                this.tarefas = d.data;
                this.currentPage = d.current_page;
                this.lastPage = d.last_page;
                this.totalFiltrado = d.total;
                this.contadores = d.contadores;
            } catch(e) {
                console.error(e);
            }
            this.loading = false;
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.load();
            }
        },

        nextPage() {
            if (this.currentPage < this.lastPage) {
                this.currentPage++;
                this.load();
            }
        },

        // Mostrar cabeçalho de grupo quando muda de "para_mim" para "setor" na lista "todos"
        showGroupHeader(t, index) {
            if (!t.grupo) return false;
            if (index === 0) return true;
            const prev = this.tarefas[index - 1];
            return prev && prev.grupo !== t.grupo;
        },

        getIconBgClass(t) {
            if (t.tipo === 'assinatura') return 'bg-amber-100';
            if (t.tipo === 'os') return 'bg-blue-100';
            if (t.tipo === 'resposta') return t.atrasado ? 'bg-red-100' : 'bg-green-100';
            return t.atrasado ? 'bg-red-100' : 'bg-purple-100';
        },

        getBadgeClass(t) {
            if (t.tipo === 'assinatura') return 'bg-amber-100 text-amber-700';
            if (t.is_licenciamento === false) return 'bg-gray-100 text-gray-600';
            if (t.atrasado) return 'bg-red-100 text-red-700';
            if (t.dias_restantes === 0) return 'bg-orange-100 text-orange-700';
            if (t.dias_restantes !== null && t.dias_restantes <= 3) return 'bg-amber-100 text-amber-700';
            if (t.dias_restantes === null) return 'bg-gray-100 text-gray-600';
            return 'bg-green-100 text-green-700';
        },

        getBadgeText(t) {
            if (t.tipo === 'assinatura') return 'Assinar';
            if (t.is_licenciamento === false) return 'Verificar';
            if (t.tipo === 'os') {
                if (t.atrasado) return 'Atrasado';
                if (t.dias_restantes === 0) return 'Hoje';
                if (t.dias_restantes === null) return 'Sem prazo';
                return t.dias_restantes + 'd';
            }
            if (t.tipo === 'resposta') {
                if (t.atrasado) return (t.dias_pendente - 5) + 'd atraso';
                if (t.dias_restantes === 0) return 'Hoje';
                if (t.dias_restantes === null) return 'Verificar';
                return t.dias_restantes + 'd';
            }
            if (t.atrasado) return (t.dias_pendente - 5) + 'd atraso';
            if (t.dias_restantes === 0) return 'Hoje';
            if (t.dias_restantes === null) return '-';
            return t.dias_restantes + 'd';
        }
    }
}
</script>
@endsection
