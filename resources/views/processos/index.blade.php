@extends('layouts.admin')

@section('title', 'Processos')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Processos</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $processos->total() }} processo(s) encontrado(s)</p>
            </div>
            @if(isset($processosComPendencias) && $processosComPendencias->count() > 0)
            <a href="{{ route('admin.documentos-pendentes.index') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium flex items-center gap-2">
                <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                {{ $processosComPendencias->count() }} Pendentes
            </a>
            @endif
        </div>
        
        @if(!auth('interno')->user()->isAdmin())
            @if(auth('interno')->user()->isEstadual())
            <div class="mt-2 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Competência Estadual
            </div>
            @elseif(auth('interno')->user()->isMunicipal())
            <div class="mt-2 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
                {{ auth('interno')->user()->municipioRelacionado->nome ?? auth('interno')->user()->municipio }}
            </div>
            @endif
        @endif
    </div>

    {{-- Cards de Estatísticas --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        {{-- Card: Documentação Completa --}}
        <a href="{{ route('admin.processos.index-geral', array_merge(request()->query(), ['docs_obrigatorios' => 'completos'])) }}" 
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-green-300 transition-all cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1 group-hover:text-green-600 transition">Documentação Completa</p>
                    <p class="text-2xl font-bold text-green-600">{{ collect($statusDocsObrigatorios)->filter(fn($s) => $s['completo'] ?? false)->count() }}</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg group-hover:bg-green-100 transition">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- Card: Documentação Pendente (não enviada) --}}
        <a href="{{ route('admin.processos.index-geral', array_merge(request()->query(), ['docs_obrigatorios' => 'pendentes'])) }}" 
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-orange-300 transition-all cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1 group-hover:text-orange-600 transition">Documentação Incompleta</p>
                    <p class="text-2xl font-bold text-orange-600">
                        @php
                            $pendentes = $processos->getCollection()
                                ->filter(function($p) use ($statusDocsObrigatorios) {
                                    $status = $statusDocsObrigatorios[$p->id] ?? null;
                                    if (!$status) return false;
                                    return ($status['nao_enviado'] ?? 0) > 0 || (!($status['completo'] ?? false) && ($status['pendente'] ?? 0) > 0);
                                })
                                ->count();
                        @endphp
                        {{ $pendentes }}
                    </p>
                </div>
                <div class="p-3 bg-orange-50 rounded-lg group-hover:bg-orange-100 transition">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- Card: Documentação Pendente de Aprovação (Empresa enviou, aguarda aprovação) --}}
        <a href="{{ route('admin.documentos-pendentes.index') }}" 
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-purple-300 transition-all cursor-pointer group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1 group-hover:text-purple-600 transition">Aguardando Aprovação</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $processosComDocsPendentes->count() ?? 0 }}</p>
                </div>
                <div class="p-3 bg-purple-50 rounded-lg group-hover:bg-purple-100 transition">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
            </div>
        </a>
    </div>

    {{-- Layout em duas colunas --}}
    <div class="flex gap-6">
        {{-- Coluna Esquerda: Filtros Simplificados --}}
        <div class="w-72 flex-shrink-0">
            <form method="GET" action="{{ route('admin.processos.index-geral') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-4">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtros
                    </h3>
                </div>
                
                <div class="p-4 space-y-3">
                    {{-- Busca Rápida --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">🔍 Buscar</label>
                        <input type="text" name="busca" value="{{ request('busca') }}" 
                               placeholder="Nº ou estabelecimento"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">📊 Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                            <option value="">Todos</option>
                            @foreach($statusDisponiveis as $key => $nome)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Documentação --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">📄 Documentação</label>
                        <select name="docs_obrigatorios" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                            <option value="">Todos</option>
                            <option value="completos" {{ request('docs_obrigatorios') == 'completos' ? 'selected' : '' }}>✅ Completos</option>
                            <option value="pendentes" {{ request('docs_obrigatorios') == 'pendentes' ? 'selected' : '' }}>⏳ Pendentes</option>
                        </select>
                    </div>
                </div>

                {{-- Mais Filtros (Accordion) --}}
                <details class="border-t border-gray-100">
                    <summary class="px-4 py-3 cursor-pointer hover:bg-gray-50 flex items-center justify-between text-sm font-medium text-gray-700">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6m-6-6h-6"/>
                            </svg>
                            Mais filtros
                        </span>
                        <span class="text-gray-400">▼</span>
                    </summary>

                    <div class="px-4 py-3 space-y-3 border-t border-gray-100 bg-gray-50">
                        {{-- Tipo --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipo de Processo</label>
                            <select name="tipo" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                                <option value="">Todos</option>
                                @foreach($tiposProcesso as $tipo)
                                <option value="{{ $tipo->codigo }}" {{ request('tipo') == $tipo->codigo ? 'selected' : '' }}>{{ $tipo->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Ano --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ano</label>
                            <select name="ano" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                                <option value="">Todos</option>
                                @foreach($anos as $anoItem)
                                <option value="{{ $anoItem }}" {{ request('ano') == $anoItem ? 'selected' : '' }}>{{ $anoItem }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Responsável --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Responsável</label>
                            <select name="responsavel" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                                <option value="">Todos</option>
                                <option value="meus" {{ request('responsavel') == 'meus' ? 'selected' : '' }}>📌 Meus processos</option>
                                @if(auth('interno')->user()->setor)
                                <option value="meu_setor" {{ request('responsavel') == 'meu_setor' ? 'selected' : '' }}>🏢 Meu setor</option>
                                @endif
                                <option value="nao_atribuido" {{ request('responsavel') == 'nao_atribuido' ? 'selected' : '' }}>⚠️ Não atribuídos</option>
                            </select>
                        </div>

                        {{-- Ordenação --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ordenar por</label>
                            <select name="ordenacao" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                                <option value="recentes" {{ request('ordenacao', 'recentes') == 'recentes' ? 'selected' : '' }}>Mais recentes</option>
                                <option value="antigos" {{ request('ordenacao') == 'antigos' ? 'selected' : '' }}>Mais antigos</option>
                                <option value="numero" {{ request('ordenacao') == 'numero' ? 'selected' : '' }}>Número</option>
                                <option value="estabelecimento" {{ request('ordenacao') == 'estabelecimento' ? 'selected' : '' }}>Estabelecimento</option>
                            </select>
                        </div>
                    </div>
                </details>

                {{-- Botões --}}
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 rounded-b-xl flex gap-2">
                    <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filtrar
                    </button>
                    <a href="{{ route('admin.processos.index-geral') }}" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition" title="Limpar filtros">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>
            </form>
        </div>

        {{-- Coluna Direita: Lista de Processos --}}
        <div class="flex-1 min-w-0">
            @if($processos->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @foreach($processos as $processo)
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}" 
                       class="block hover:bg-gray-50 transition">
                        <div class="px-4 py-3">
                            <div class="flex items-start gap-4">
                                {{-- Ícone/Status --}}
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                        @if($processo->status == 'aberto') bg-blue-100
                                        @elseif($processo->status == 'em_analise') bg-yellow-100
                                        @elseif($processo->status == 'pendente') bg-orange-100
                                        @elseif($processo->status == 'concluido') bg-green-100
                                        @elseif($processo->status == 'arquivado') bg-gray-100
                                        @elseif($processo->status == 'parado') bg-red-100
                                        @else bg-gray-100
                                        @endif">
                                        <svg class="w-5 h-5 
                                            @if($processo->status == 'aberto') text-blue-600
                                            @elseif($processo->status == 'em_analise') text-yellow-600
                                            @elseif($processo->status == 'pendente') text-orange-600
                                            @elseif($processo->status == 'concluido') text-green-600
                                            @elseif($processo->status == 'arquivado') text-gray-600
                                            @elseif($processo->status == 'parado') text-red-600
                                            @else text-gray-600
                                            @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                </div>

                                {{-- Conteúdo Principal --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-semibold text-gray-900">{{ $processo->numero_processo }}</span>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                            @if($processo->status == 'aberto') bg-blue-100 text-blue-700
                                            @elseif($processo->status == 'em_analise') bg-yellow-100 text-yellow-700
                                            @elseif($processo->status == 'pendente') bg-orange-100 text-orange-700
                                            @elseif($processo->status == 'concluido') bg-green-100 text-green-700
                                            @elseif($processo->status == 'arquivado') bg-gray-100 text-gray-700
                                            @elseif($processo->status == 'parado') bg-red-100 text-red-700
                                            @else bg-gray-100 text-gray-700
                                            @endif">
                                            {{ $processo->status_nome }}
                                        </span>
                                        <span class="text-xs text-gray-400">{{ $processo->tipo_nome }}</span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-700 truncate">
                                        {{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $processo->estabelecimento->cnpj ?? $processo->estabelecimento->cpf }}
                                    </p>
                                </div>

                                {{-- Info Lateral --}}
                                <div class="flex-shrink-0 text-right">
                                    {{-- Responsável --}}
                                    @if($processo->responsavelAtual || $processo->setor_atual)
                                    <div class="mb-1">
                                        @if($processo->setor_atual)
                                        <p class="text-xs font-medium text-cyan-700">{{ $processo->setor_atual_nome }}</p>
                                        @endif
                                        @if($processo->responsavelAtual)
                                        <p class="text-xs text-gray-500">{{ $processo->responsavelAtual->nome }}</p>
                                        @endif
                                    </div>
                                    @else
                                    <p class="text-xs text-gray-400 italic mb-1">Não atribuído</p>
                                    @endif

                                    {{-- Docs Obrigatórios --}}
                                    @if(isset($statusDocsObrigatorios[$processo->id]))
                                        @php $docs = $statusDocsObrigatorios[$processo->id]; @endphp
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-medium rounded
                                            {{ $docs['completo'] ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                            @if($docs['completo'])
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            @endif
                                            {{ $docs['ok'] }}/{{ $docs['total'] }}
                                        </span>
                                    @endif

                                    {{-- Data --}}
                                    <p class="text-[10px] text-gray-400 mt-1">{{ $processo->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>

                {{-- Paginação --}}
                @if($processos->hasPages())
                <div class="px-4 py-4 border-t border-gray-200 bg-white">
                    {{ $processos->links('pagination.tailwind-clean') }}
                </div>
                @endif
            </div>
            @else
            {{-- Sem Resultados --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-4 text-sm font-medium text-gray-900">Nenhum processo encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">Tente ajustar os filtros</p>
                <a href="{{ route('admin.processos.index-geral') }}" class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-cyan-600 hover:text-cyan-700">
                    Limpar filtros
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
