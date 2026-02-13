@extends('layouts.admin')

@section('title', 'Processos')

@section('content')
@php
    // Pré-calcula contadores para os stats cards
    $totalCompletos = collect($statusDocsObrigatorios)->filter(fn($s) => $s['completo'] ?? false)->count();
    $totalNaoEnviados = collect($statusDocsObrigatorios)->filter(fn($s) => ($s['nao_enviado'] ?? 0) > 0)->count();
    $totalAguardandoAprovacao = ($processosComPendencias ?? collect())->count();
    $totalNaoAtribuidos = $processos->getCollection()->filter(fn($p) => !$p->responsavel_atual_id && !$p->setor_atual)->count();
@endphp

<div class="max-w-[1600px] mx-auto" x-data="{ mobileFilters: false }">
    {{-- Header --}}
    <div class="mb-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Processos</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $processos->total() }} encontrado(s)</p>
            </div>
        </div>
    </div>

    {{-- Layout principal --}}
    <div class="flex gap-5">
        {{-- Sidebar de Filtros --}}
        <div class="w-64 flex-shrink-0 hidden lg:block">
            <form method="GET" action="{{ route('admin.processos.index-geral') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-4">
                <div class="px-4 py-2.5 border-b border-gray-100 bg-gray-50 rounded-t-xl flex items-center justify-between">
                    <h3 class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtros
                    </h3>
                    @if(request()->hasAny(['busca', 'tipo', 'status', 'docs_obrigatorios', 'ano', 'responsavel', 'ordenacao']))
                        <a href="{{ route('admin.processos.index-geral') }}" class="text-[10px] text-red-500 hover:text-red-700 font-medium" title="Limpar todos os filtros">Limpar</a>
                    @endif
                </div>

                <div class="p-3 space-y-2.5">
                    {{-- Busca --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1 uppercase tracking-wider">Buscar</label>
                        <input type="text" name="busca" value="{{ request('busca') }}"
                               placeholder="Nº processo ou estabelecimento..."
                               class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- Tipo de Processo --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1 uppercase tracking-wider">Tipo</label>
                        <select name="tipo" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            @foreach($tiposProcesso as $tipo)
                            <option value="{{ $tipo->codigo }}" {{ request('tipo') == $tipo->codigo ? 'selected' : '' }}>{{ $tipo->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1 uppercase tracking-wider">Status</label>
                        <select name="status" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            @foreach($statusDisponiveis as $key => $nome)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Documentação --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1 uppercase tracking-wider">Documentação</label>
                        <select name="docs_obrigatorios" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="completos" {{ request('docs_obrigatorios') == 'completos' ? 'selected' : '' }}>Completos</option>
                            <option value="pendentes" {{ request('docs_obrigatorios') == 'pendentes' ? 'selected' : '' }}>Pendentes</option>
                        </select>
                    </div>

                    {{-- Responsável --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1 uppercase tracking-wider">Responsável</label>
                        <select name="responsavel" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="meus" {{ request('responsavel') == 'meus' ? 'selected' : '' }}>Meus processos</option>
                            @if(auth('interno')->user()->setor)
                            <option value="meu_setor" {{ request('responsavel') == 'meu_setor' ? 'selected' : '' }}>Meu setor</option>
                            @endif
                            <option value="nao_atribuido" {{ request('responsavel') == 'nao_atribuido' ? 'selected' : '' }}>Não atribuídos</option>
                        </select>
                    </div>

                    {{-- Ano e Ordenação --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-600 mb-1 uppercase tracking-wider">Ano</label>
                            <select name="ano" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos</option>
                                @foreach($anos as $anoItem)
                                <option value="{{ $anoItem }}" {{ request('ano') == $anoItem ? 'selected' : '' }}>{{ $anoItem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-600 mb-1 uppercase tracking-wider">Ordenar</label>
                            <select name="ordenacao" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="recentes" {{ request('ordenacao', 'recentes') == 'recentes' ? 'selected' : '' }}>Recentes</option>
                                <option value="antigos" {{ request('ordenacao') == 'antigos' ? 'selected' : '' }}>Antigos</option>
                                <option value="numero" {{ request('ordenacao') == 'numero' ? 'selected' : '' }}>Número</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Botão Filtrar --}}
                <div class="px-3 pb-3">
                    <button type="submit" class="w-full px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        {{-- Botão filtros mobile --}}
        <button @click="mobileFilters = !mobileFilters" class="lg:hidden fixed bottom-4 right-4 z-50 w-12 h-12 bg-blue-600 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-blue-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
        </button>

        {{-- Lista de Processos --}}
        <div class="flex-1 min-w-0">
            @if($processos->count() > 0)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-2.5 border-b border-gray-100 bg-gray-50 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 text-[11px] text-gray-600 flex-wrap">
                            @php
                                $quickQuery = request()->except(['quick', 'responsavel']);
                            @endphp
                            <span class="font-semibold text-gray-700">Filtro rapido:</span>
                            <a href="{{ route('admin.processos.index-geral', $quickQuery) }}"
                               class="px-2 py-0.5 rounded-full border {{ request('quick') ? 'border-gray-200 text-gray-500' : 'border-gray-300 text-gray-800 bg-white' }}">
                                Todos
                            </a>
                            <a href="{{ route('admin.processos.index-geral', array_merge($quickQuery, ['quick' => 'completo'])) }}"
                               class="px-2 py-0.5 rounded-full border {{ request('quick') === 'completo' ? 'border-green-300 text-green-700 bg-green-50' : 'border-gray-200 text-gray-600' }}">
                                Completos
                            </a>
                            <a href="{{ route('admin.processos.index-geral', array_merge($quickQuery, ['quick' => 'nao_enviado'])) }}"
                               class="px-2 py-0.5 rounded-full border {{ request('quick') === 'nao_enviado' ? 'border-red-300 text-red-700 bg-red-50' : 'border-gray-200 text-gray-600' }}">
                                Incompletos
                            </a>
                            <a href="{{ route('admin.processos.index-geral', array_merge($quickQuery, ['quick' => 'aguardando'])) }}"
                               class="px-2 py-0.5 rounded-full border {{ request('quick') === 'aguardando' ? 'border-amber-300 text-amber-700 bg-amber-50' : 'border-gray-200 text-gray-600' }}">
                                Aguardando aprovacao
                            </a>
                            <a href="{{ route('admin.processos.index-geral', array_merge($quickQuery, ['quick' => 'nao_atribuido'])) }}"
                               class="px-2 py-0.5 rounded-full border {{ request('quick') === 'nao_atribuido' ? 'border-gray-400 text-gray-700 bg-gray-100' : 'border-gray-200 text-gray-600' }}">
                                Nao atribuidos
                            </a>
                        </div>

                        @if(request('quick'))
                            <a href="{{ route('admin.processos.index-geral', $quickQuery) }}" class="text-[10px] text-gray-500 hover:text-gray-700">Limpar</a>
                        @endif
                    </div>
                    <div class="divide-y divide-gray-100">
                    @foreach($processos as $processo)
                        @php
                            $docs = $statusDocsObrigatorios[$processo->id] ?? null;
                            $prazo = $prazoFilaPublica[$processo->id] ?? null;
                            $temPendenciaAprovacao = ($processosComPendencias ?? collect())->contains($processo->id);
                            $temRespostasPendentes = ($processosComRespostasPendentes ?? collect())->contains($processo->id);
                            $naoAtribuido = !$processo->responsavel_atual_id && !$processo->setor_atual;

                            // Status de documentação
                            $docStatus = 'sem_info'; // cinza
                            if ($docs) {
                                if ($docs['completo']) {
                                    $docStatus = 'completo'; // verde
                                } elseif (($docs['nao_enviado'] ?? 0) > 0) {
                                    $docStatus = 'nao_enviado'; // vermelho
                                } elseif (($docs['pendente'] ?? 0) > 0) {
                                    $docStatus = 'aguardando'; // amarelo
                                } elseif (($docs['rejeitado'] ?? 0) > 0) {
                                    $docStatus = 'rejeitado'; // vermelho
                                }
                            }

                            // Cor do badge de status do processo
                            $statusColor = match($processo->status) {
                                'aberto' => 'text-blue-600',
                                'em_analise' => 'text-amber-600',
                                'pendente' => 'text-orange-600',
                                'concluido' => 'text-green-600',
                                'arquivado' => 'text-gray-500',
                                'parado' => 'text-red-600',
                                default => 'text-gray-600',
                            };
                            $statusDot = match($processo->status) {
                                'aberto' => 'bg-blue-400',
                                'em_analise' => 'bg-amber-400',
                                'pendente' => 'bg-orange-400',
                                'concluido' => 'bg-green-400',
                                'arquivado' => 'bg-gray-400',
                                'parado' => 'bg-red-400',
                                default => 'bg-gray-300',
                            };
                        @endphp

                        <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}"
                           class="block hover:bg-gray-50 transition relative">
                            <div class="px-4 py-3 flex items-center gap-4">
                                {{-- Barra de status da documentação --}}
                                <div class="w-1 self-stretch rounded-full
                                    @if($docStatus === 'completo') bg-green-300
                                    @elseif($docStatus === 'nao_enviado' || $docStatus === 'rejeitado') bg-red-300
                                    @elseif($docStatus === 'aguardando' || $temPendenciaAprovacao) bg-amber-300
                                    @else bg-gray-200
                                    @endif
                                "></div>

                                {{-- Informacoes principais --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full border border-gray-200 text-gray-600 flex-shrink-0 inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                            {{ $processo->status_nome }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 truncate">{{ $processo->tipo_nome }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-900">{{ $processo->numero_processo }}</span>
                                        <span class="text-xs text-gray-600 truncate" title="{{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}">
                                            {{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Indicadores visuais --}}
                                <div class="flex items-center gap-1">
                                    @if($docStatus === 'aguardando' || $temPendenciaAprovacao)
                                        <span class="w-6 h-6 rounded-full bg-gray-50 border border-gray-200 flex items-center justify-center" title="Documentos aguardando aprovação">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                    @elseif($docStatus === 'completo')
                                        <span class="w-6 h-6 rounded-full bg-gray-50 border border-gray-200 flex items-center justify-center" title="Documentação completa">
                                            <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                    @endif

                                    @if($temRespostasPendentes)
                                        <span class="w-6 h-6 rounded-full bg-gray-50 border border-gray-200 flex items-center justify-center" title="Respostas pendentes de avaliação">
                                            <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                        </span>
                                    @endif

                                    
                                </div>

                                {{-- Meta info --}}
                                <div class="hidden md:flex items-center gap-3 text-[10px] text-gray-500">
                                    @if($docs)
                                        <span class="font-semibold {{ $docs['completo'] ? 'text-green-500' : 'text-amber-500' }}">
                                            {{ $docs['ok'] }}/{{ $docs['total'] }}
                                        </span>
                                    @endif

                                    @if($prazo)
                                        <span class="font-medium {{ $prazo['atrasado'] ? 'text-red-500' : ($prazo['dias_restantes'] <= 5 ? 'text-amber-500' : 'text-cyan-500') }}">
                                            @if($prazo['atrasado'])
                                                {{ abs($prazo['dias_restantes']) }}d atraso
                                            @else
                                                {{ $prazo['dias_restantes'] }}d
                                            @endif
                                        </span>
                                    @endif

                                    @if($processo->setor_atual || $processo->responsavelAtual)
                                        <span class="inline-flex flex-col min-w-0">
                                            <span class="truncate text-cyan-700">{{ $processo->setor_atual_nome }}</span>
                                            @if($processo->responsavelAtual)
                                                <span class="truncate text-gray-600">{{ $processo->responsavelAtual->nome }}</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="italic text-gray-400">Nao atribuido</span>
                                    @endif

                                </div>
                            </div>
                        </a>
                    @endforeach
                    </div>
                </div>

                {{-- Paginação --}}
                @if($processos->hasPages())
                    <div class="mt-5">
                        {{ $processos->links('pagination.tailwind-clean') }}
                    </div>
                @endif
            @else
                {{-- Sem resultados --}}
                <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">Nenhum processo encontrado</h3>
                    <p class="mt-1 text-xs text-gray-500">Tente ajustar os filtros de busca</p>
                    <a href="{{ route('admin.processos.index-geral') }}" class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                        Limpar filtros
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
