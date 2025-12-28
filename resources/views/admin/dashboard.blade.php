@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    // Calcular documentos atrasados e urgentes
    $docsAtrasados = 0;
    $docsUrgentes = 0;
    foreach($documentos_pendentes_aprovacao ?? [] as $doc) {
        $dias = (int) $doc->created_at->diffInDays(now());
        if ($dias > 5) $docsAtrasados++;
        elseif ($dias >= 4) $docsUrgentes++;
    }
    foreach($respostas_pendentes_aprovacao ?? [] as $resp) {
        $dias = (int) $resp->created_at->diffInDays(now());
        if ($dias > 5) $docsAtrasados++;
        elseif ($dias >= 4) $docsUrgentes++;
    }
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Olá, {{ Str::words(auth('interno')->user()->nome, 1, '') }}!</h1>
            <p class="text-sm text-gray-500">{{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM') }}</p>
        </div>
    </div>

    {{-- Alertas Urgentes --}}
    @if($docsAtrasados > 0)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="font-semibold text-red-800">{{ $docsAtrasados }} documento(s) com prazo vencido</h3>
            <p class="text-sm text-red-600">Documentos aguardando aprovação há mais de 5 dias úteis</p>
        </div>
        <a href="{{ route('admin.documentos-pendentes.index') }}" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
            Ver agora
        </a>
    </div>
    @endif

    {{-- Cards de Ação Rápida --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card Assinaturas --}}
        <a href="{{ route('admin.assinatura.pendentes') }}" class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-amber-300 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-200 transition">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                @if(($stats['documentos_pendentes_assinatura'] ?? 0) > 0)
                <span class="w-6 h-6 bg-amber-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                    {{ $stats['documentos_pendentes_assinatura'] }}
                </span>
                @endif
            </div>
            <h3 class="font-semibold text-gray-900">Assinaturas</h3>
            <p class="text-sm text-gray-500">{{ $stats['documentos_pendentes_assinatura'] ?? 0 }} pendente(s)</p>
        </a>

        {{-- Card Aprovações --}}
        <a href="{{ route('admin.documentos-pendentes.index') }}" class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-purple-300 hover:shadow-lg transition-all {{ $docsAtrasados > 0 ? 'border-red-300 bg-red-50/30' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 {{ $docsAtrasados > 0 ? 'bg-red-100' : 'bg-purple-100' }} rounded-lg flex items-center justify-center group-hover:{{ $docsAtrasados > 0 ? 'bg-red-200' : 'bg-purple-200' }} transition">
                    <svg class="w-5 h-5 {{ $docsAtrasados > 0 ? 'text-red-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @if(($stats['total_pendentes_aprovacao'] ?? 0) > 0)
                <span class="w-6 h-6 {{ $docsAtrasados > 0 ? 'bg-red-500' : 'bg-purple-500' }} text-white text-xs font-bold rounded-full flex items-center justify-center">
                    {{ $stats['total_pendentes_aprovacao'] }}
                </span>
                @endif
            </div>
            <h3 class="font-semibold text-gray-900">Aprovações</h3>
            <p class="text-sm {{ $docsAtrasados > 0 ? 'text-red-600' : 'text-gray-500' }}">
                @if($docsAtrasados > 0)
                    {{ $docsAtrasados }} atrasado(s)
                @else
                    {{ $stats['total_pendentes_aprovacao'] ?? 0 }} pendente(s)
                @endif
            </p>
        </a>

        {{-- Card Prazos --}}
        <a href="{{ route('admin.documentos.index', ['status' => 'com_prazos']) }}" class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-red-300 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center group-hover:bg-red-200 transition">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @if(($stats['documentos_vencendo'] ?? 0) > 0)
                <span class="w-6 h-6 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                    {{ $stats['documentos_vencendo'] }}
                </span>
                @endif
            </div>
            <h3 class="font-semibold text-gray-900">Prazos</h3>
            <p class="text-sm text-gray-500">{{ $stats['documentos_vencendo'] ?? 0 }} a vencer</p>
        </a>

        {{-- Card Estabelecimentos --}}
        <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-cyan-300 hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center group-hover:bg-cyan-200 transition">
                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                @if(($stats['estabelecimentos_pendentes'] ?? 0) > 0)
                <span class="w-6 h-6 bg-cyan-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                    {{ $stats['estabelecimentos_pendentes'] }}
                </span>
                @endif
            </div>
            <h3 class="font-semibold text-gray-900">Estabelecimentos</h3>
            <p class="text-sm text-gray-500">{{ $stats['estabelecimentos_pendentes'] ?? 0 }} novo(s)</p>
        </a>
    </div>

    {{-- Conteúdo Principal --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Coluna Principal --}}
        <div class="xl:col-span-2 space-y-6">
            
            {{-- Minhas Tarefas --}}
            @php
                // Agrupar documentos por processo (usar array PHP para evitar erro de modificação indireta)
                $tarefasArray = [];
                
                // Agrupar documentos pendentes de aprovação por processo
                foreach($documentos_pendentes_aprovacao ?? [] as $doc) {
                    $key = 'processo_' . $doc->processo_id;
                    if (!isset($tarefasArray[$key])) {
                        $diasPendente = (int) $doc->created_at->diffInDays(now());
                        $tarefasArray[$key] = [
                            'tipo' => 'aprovacao',
                            'processo_id' => $doc->processo_id,
                            'estabelecimento_id' => $doc->processo->estabelecimento_id,
                            'estabelecimento' => $doc->processo->estabelecimento->nome_fantasia ?? $doc->processo->estabelecimento->razao_social ?? 'Estabelecimento',
                            'numero_processo' => $doc->processo->numero_processo,
                            'primeiro_arquivo' => $doc->nome_original,
                            'documentos' => [$doc],
                            'respostas' => [],
                            'total' => 1,
                            'dias_pendente' => $diasPendente,
                            'atrasado' => $diasPendente > 5,
                            'created_at' => $doc->created_at,
                        ];
                    } else {
                        $tarefasArray[$key]['documentos'][] = $doc;
                        $tarefasArray[$key]['total']++;
                        // Usar o mais antigo para calcular prazo
                        if ($doc->created_at < $tarefasArray[$key]['created_at']) {
                            $tarefasArray[$key]['created_at'] = $doc->created_at;
                            $diasPendente = (int) $doc->created_at->diffInDays(now());
                            $tarefasArray[$key]['dias_pendente'] = $diasPendente;
                            $tarefasArray[$key]['atrasado'] = $diasPendente > 5;
                        }
                    }
                }
                
                // Agrupar respostas pendentes por processo
                foreach($respostas_pendentes_aprovacao ?? [] as $resposta) {
                    $key = 'processo_' . $resposta->documentoDigital->processo_id;
                    if (!isset($tarefasArray[$key])) {
                        $diasPendente = (int) $resposta->created_at->diffInDays(now());
                        $tarefasArray[$key] = [
                            'tipo' => 'resposta',
                            'processo_id' => $resposta->documentoDigital->processo_id,
                            'estabelecimento_id' => $resposta->documentoDigital->processo->estabelecimento_id,
                            'estabelecimento' => $resposta->documentoDigital->processo->estabelecimento->nome_fantasia ?? 'Estabelecimento',
                            'numero_processo' => $resposta->documentoDigital->processo->numero_processo,
                            'primeiro_arquivo' => $resposta->nome_original,
                            'documentos' => [],
                            'respostas' => [$resposta],
                            'total' => 1,
                            'dias_pendente' => $diasPendente,
                            'atrasado' => $diasPendente > 5,
                            'created_at' => $resposta->created_at,
                        ];
                    } else {
                        $tarefasArray[$key]['respostas'][] = $resposta;
                        $tarefasArray[$key]['total']++;
                        if ($resposta->created_at < $tarefasArray[$key]['created_at']) {
                            $tarefasArray[$key]['created_at'] = $resposta->created_at;
                            $diasPendente = (int) $resposta->created_at->diffInDays(now());
                            $tarefasArray[$key]['dias_pendente'] = $diasPendente;
                            $tarefasArray[$key]['atrasado'] = $diasPendente > 5;
                        }
                    }
                }
                
                // Converter para Collection e ordenar por mais atrasado primeiro
                $tarefasAgrupadas = collect($tarefasArray)->sortByDesc('dias_pendente')->take(5);
                $totalTarefas = count($documentos_pendentes_aprovacao ?? []) + count($respostas_pendentes_aprovacao ?? []);
            @endphp
            
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Minhas Tarefas</h2>
                    @if($totalTarefas > 0)
                    <span class="text-xs text-gray-500">{{ $totalTarefas }} arquivo(s) pendente(s)</span>
                    @endif
                </div>
                
                <div class="divide-y divide-gray-100">
                    {{-- Assinaturas Pendentes --}}
                    @forelse($documentos_pendentes_assinatura ?? [] as $assinatura)
                    <div class="px-5 py-3 flex items-center gap-4 hover:bg-gray-50 transition">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $assinatura->documentoDigital->tipoDocumento->nome ?? 'Documento' }}</p>
                            <p class="text-xs text-gray-500">Aguardando assinatura • {{ $assinatura->created_at->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('admin.assinatura.assinar', $assinatura->documentoDigital->id) }}" class="px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-lg hover:bg-amber-600 transition flex-shrink-0">
                            Assinar
                        </a>
                    </div>
                    @empty
                    @endforelse

                    {{-- Documentos Agrupados por Processo --}}
                    @foreach($tarefasAgrupadas as $tarefa)
                    @php
                        $diasRestantes = 5 - $tarefa['dias_pendente'];
                    @endphp
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$tarefa['estabelecimento_id'], $tarefa['processo_id']]) }}" 
                       class="block px-5 py-3 hover:bg-gray-50 transition {{ $tarefa['atrasado'] ? 'bg-red-50/50' : '' }}">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 {{ $tarefa['atrasado'] ? 'bg-red-100' : 'bg-purple-100' }} rounded-lg flex items-center justify-center flex-shrink-0 relative">
                                <svg class="w-5 h-5 {{ $tarefa['atrasado'] ? 'text-red-600' : 'text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                @if($tarefa['total'] > 1)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-{{ $tarefa['atrasado'] ? 'red' : 'purple' }}-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                                    {{ $tarefa['total'] }}
                                </span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $tarefa['primeiro_arquivo'] }}</p>
                                    @if($tarefa['total'] > 1)
                                    <span class="text-xs text-gray-400 flex-shrink-0">+ {{ $tarefa['total'] - 1 }} arquivo(s)</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $tarefa['estabelecimento'] }} • {{ $tarefa['numero_processo'] }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($tarefa['atrasado'])
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                        {{ $tarefa['dias_pendente'] - 5 }}d atrasado
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $diasRestantes <= 1 ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $diasRestantes }}d restante
                                    </span>
                                @endif
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                    @endforeach

                    @if(count($documentos_pendentes_assinatura ?? []) == 0 && $tarefasAgrupadas->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500">Nenhuma tarefa pendente</p>
                    </div>
                    @endif
                </div>

                @if($tarefasAgrupadas->count() >= 5 || $totalTarefas > 5)
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                    <a href="{{ route('admin.documentos-pendentes.index') }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                        Ver todas as tarefas →
                    </a>
                </div>
                @endif
            </div>

            {{-- Processos Atribuídos (Tramitados para mim/meu setor) --}}
            @if(count($processos_atribuidos ?? []) > 0)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Processos Atribuídos
                    </h2>
                    @if(($stats['processos_atribuidos'] ?? 0) > 0)
                    <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 text-xs font-medium rounded-full">
                        {{ $stats['processos_atribuidos'] }}
                    </span>
                    @endif
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($processos_atribuidos as $proc)
                    @php
                        $isMeuDireto = $proc->responsavel_atual_id === auth('interno')->id();
                    @endphp
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$proc->estabelecimento_id, $proc->id]) }}" 
                       class="block px-5 py-3 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 {{ $isMeuDireto ? 'bg-cyan-100' : 'bg-blue-100' }} rounded-lg flex items-center justify-center flex-shrink-0">
                                @if($isMeuDireto)
                                <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                @else
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $proc->numero_processo }}</p>
                                    <span class="px-1.5 py-0.5 text-[10px] font-medium rounded {{ $isMeuDireto ? 'bg-cyan-100 text-cyan-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $isMeuDireto ? 'Para mim' : 'Meu setor' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $proc->estabelecimento->nome_fantasia ?? $proc->estabelecimento->razao_social ?? 'Estabelecimento' }}
                                </p>
                                @if($proc->responsavel_desde)
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    Recebido {{ $proc->responsavel_desde->diffForHumans() }}
                                </p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($proc->status === 'aberto') bg-blue-100 text-blue-700
                                    @elseif($proc->status === 'em_analise') bg-yellow-100 text-yellow-700
                                    @elseif($proc->status === 'pendente') bg-orange-100 text-orange-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ $proc->status_nome }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @if(($stats['processos_atribuidos'] ?? 0) > 10)
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                    <a href="{{ route('admin.processos.index-geral', ['responsavel' => 'meus']) }}" class="text-sm text-cyan-600 hover:text-cyan-700 font-medium">
                        Ver todos os processos atribuídos →
                    </a>
                </div>
                @endif
            </div>
            @endif

            {{-- Novos Cadastros --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Novos Cadastros</h2>
                    @if(count($estabelecimentos_pendentes ?? []) > 0)
                    <span class="px-2 py-0.5 bg-cyan-100 text-cyan-700 text-xs font-medium rounded-full">
                        {{ count($estabelecimentos_pendentes) }}
                    </span>
                    @endif
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse(($estabelecimentos_pendentes ?? [])->take(5) as $est)
                    <a href="{{ route('admin.estabelecimentos.show', $est) }}" class="block px-5 py-3 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $est->nome_fantasia ?? $est->razao_social }}</p>
                                <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    {{ $est->cidade ?? '-' }} • {{ $est->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-400">
                        Nenhum cadastro pendente
                    </div>
                    @endforelse
                </div>
                @if(count($estabelecimentos_pendentes ?? []) > 5)
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                    <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="text-sm text-cyan-600 hover:text-cyan-700 font-medium">
                        Ver todos →
                    </a>
                </div>
                @endif
            </div>

            {{-- Ordens de Serviço --}}
            @if(count($ordens_servico_andamento ?? []) > 0)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Minhas Ordens de Serviço</h2>
                    <a href="{{ route('admin.ordens-servico.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Ver todas</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($ordens_servico_andamento->take(5) as $os)
                    @php
                        $diasRestantes = $os->data_fim ? now()->startOfDay()->diffInDays($os->data_fim->startOfDay(), false) : null;
                        $isVencido = $diasRestantes !== null && $diasRestantes < 0;
                        $isHoje = $diasRestantes === 0;
                        $isVencendo = $diasRestantes !== null && $diasRestantes > 0 && $diasRestantes <= 3;
                    @endphp
                    <a href="{{ route('admin.ordens-servico.show', $os) }}" 
                       class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors cursor-pointer">
                        <div class="flex items-center gap-4 min-w-0">
                            <span class="text-sm font-medium text-gray-900 whitespace-nowrap">#{{ $os->numero }}</span>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-900 truncate">{{ $os->estabelecimento->nome_fantasia ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                            @if($os->data_fim)
                                @if($isVencido)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-red-700 bg-red-50 rounded">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Vencido
                                    </span>
                                @elseif($isHoje)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-orange-700 bg-orange-50 rounded">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        Hoje
                                    </span>
                                @elseif($isVencendo)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-amber-700 bg-amber-50 rounded">
                                        {{ $diasRestantes }}d
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500">{{ $os->data_fim->format('d/m/Y') }}</span>
                                @endif
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Prazos a Vencer --}}
            @if(count($documentos_vencendo ?? []) > 0)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Prazos Próximos</h2>
                    <a href="{{ route('admin.documentos.index', ['status' => 'com_prazos']) }}" class="text-sm text-red-600 hover:text-red-700">Ver agenda</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($documentos_vencendo->take(5) as $doc)
                    @php
                        $diasFaltando = $doc->dias_faltando;
                        $isVencido = $diasFaltando < 0;
                        $isHoje = $diasFaltando == 0;
                    @endphp
                    <a href="{{ route('admin.documentos.show', $doc->id) }}" class="px-5 py-3 flex items-center gap-4 hover:bg-gray-50 transition {{ $isVencido ? 'bg-red-50/50' : '' }}">
                        <div class="w-8 h-8 {{ $isVencido ? 'bg-red-100' : ($isHoje ? 'bg-orange-100' : 'bg-gray-100') }} rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 {{ $isVencido ? 'text-red-600' : ($isHoje ? 'text-orange-600' : 'text-gray-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $doc->tipoDocumento->nome ?? 'Documento' }}</p>
                            <p class="text-xs text-gray-500">{{ $doc->numero_documento }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $isVencido ? 'bg-red-100 text-red-700' : ($isHoje ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ $isVencido ? 'Vencido' : ($isHoje ? 'Hoje' : $diasFaltando . 'd') }}
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Coluna Lateral --}}
        <div class="space-y-6">
            {{-- Processos Acompanhados --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Monitorando</h2>
                    <a href="{{ route('admin.processos.index-geral') }}" class="text-xs text-gray-500 hover:text-gray-700">Ver todos</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse(($processos_acompanhados ?? [])->take(5) as $proc)
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$proc->estabelecimento_id, $proc->id]) }}" class="block px-5 py-3 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $proc->numero_processo }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $proc->estabelecimento->nome_fantasia ?? 'Sem nome' }}</p>
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-400">
                        Nenhum processo monitorado
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Ações Rápidas --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg overflow-hidden" x-data="atalhosRapidos()">
                <div class="px-4 py-3 border-b border-blue-400/30 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-white">Ações Rápidas</h3>
                    <button @click="abrirModal()" class="text-blue-200 hover:text-white transition" title="Adicionar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </button>
                </div>
                <div class="divide-y divide-blue-400/20">
                    @forelse($atalhos_rapidos ?? [] as $atalho)
                    <div class="group relative">
                        <a href="{{ $atalho->url }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-blue-400/20 transition">
                            <span class="text-sm text-white">{{ $atalho->titulo }}</span>
                        </a>
                        <div class="absolute right-2 top-1/2 -translate-y-1/2 hidden group-hover:flex gap-1">
                            <button @click.prevent="editarAtalho({{ $atalho->id }}, '{{ addslashes($atalho->titulo) }}', '{{ addslashes($atalho->url) }}', '{{ $atalho->icone }}')" 
                                    class="p-1 text-blue-200 hover:text-white" title="Editar">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </button>
                            <button @click.prevent="excluirAtalho({{ $atalho->id }})" 
                                    class="p-1 text-blue-200 hover:text-red-300" title="Excluir">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @empty
                    <a href="{{ route('admin.documentos-pendentes.index') }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-blue-400/20 transition">
                        <span class="text-sm text-white">Documentos Pendentes</span>
                    </a>
                    <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-blue-400/20 transition">
                        <span class="text-sm text-white">Estabelecimentos Pendentes</span>
                    </a>
                    <a href="{{ route('admin.assinatura.pendentes') }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-blue-400/20 transition">
                        <span class="text-sm text-white">Assinaturas Pendentes</span>
                    </a>
                    @endforelse
                </div>

                {{-- Modal Adicionar/Editar Atalho --}}
                <div x-show="modalAberto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5)">
                    <div @click.away="fecharModal()" class="bg-white rounded-xl shadow-xl w-full max-w-md text-gray-900">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="font-semibold text-lg" x-text="editandoId ? 'Editar Atalho' : 'Novo Atalho'"></h3>
                            <button @click="fecharModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <form @submit.prevent="salvarAtalho()" class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Página</label>
                                <select x-model="form.url" @change="atualizarTituloPadrao()" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione uma página...</option>
                                    <optgroup label="Dashboard">
                                        <option value="{{ route('admin.dashboard') }}">Dashboard Principal</option>
                                    </optgroup>
                                    <optgroup label="Estabelecimentos">
                                        <option value="{{ route('admin.estabelecimentos.index') }}">Todos os Estabelecimentos</option>
                                        <option value="{{ route('admin.estabelecimentos.pendentes') }}">Estabelecimentos Pendentes</option>
                                        <option value="{{ route('admin.estabelecimentos.rejeitados') }}">Estabelecimentos Rejeitados</option>
                                        <option value="{{ route('admin.estabelecimentos.desativados') }}">Estabelecimentos Desativados</option>
                                    </optgroup>
                                    <optgroup label="Processos">
                                        <option value="{{ route('admin.processos.index-geral') }}">Todos os Processos</option>
                                        <option value="{{ route('admin.documentos-pendentes.index') }}">Documentos Pendentes de Aprovação</option>
                                    </optgroup>
                                    <optgroup label="Documentos">
                                        <option value="{{ route('admin.documentos.index') }}">Todos os Documentos</option>
                                        <option value="{{ route('admin.assinatura.pendentes') }}">Assinaturas Pendentes</option>
                                    </optgroup>
                                    <optgroup label="Ordens de Serviço">
                                        <option value="{{ route('admin.ordens-servico.index') }}">Todas as Ordens de Serviço</option>
                                        <option value="{{ route('admin.ordens-servico.create') }}">Nova Ordem de Serviço</option>
                                    </optgroup>
                                    <optgroup label="Usuários">
                                        <option value="{{ route('admin.usuarios-externos.index') }}">Usuários Externos</option>
                                        <option value="{{ route('admin.usuarios-internos.index') }}">Usuários Internos</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Título do Atalho</label>
                                <input type="text" x-model="form.titulo" required maxlength="100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ex: Meus Processos">
                                <p class="text-xs text-gray-500 mt-1">Personalize o nome que aparecerá no atalho</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ícone</label>
                                <select x-model="form.icone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="link">Link</option>
                                    <option value="folder">Pasta</option>
                                    <option value="document">Documento</option>
                                    <option value="building">Estabelecimento</option>
                                    <option value="clipboard">Processo</option>
                                    <option value="users">Usuários</option>
                                    <option value="chart">Relatório</option>
                                    <option value="search">Busca</option>
                                    <option value="calendar">Calendário</option>
                                    <option value="bell">Notificação</option>
                                    <option value="star">Favorito</option>
                                </select>
                            </div>
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="fecharModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                    Cancelar
                                </button>
                                <button type="submit" :disabled="salvando" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                                    <span x-show="!salvando" x-text="editandoId ? 'Salvar' : 'Adicionar'"></span>
                                    <span x-show="salvando">Salvando...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
            function atalhosRapidos() {
                return {
                    modalAberto: false,
                    editandoId: null,
                    salvando: false,
                    form: {
                        titulo: '',
                        url: '',
                        icone: 'link'
                    },
                    paginas: {
                        '{{ route('admin.dashboard') }}': { titulo: 'Dashboard Principal', icone: 'chart' },
                        '{{ route('admin.estabelecimentos.index') }}': { titulo: 'Todos os Estabelecimentos', icone: 'building' },
                        '{{ route('admin.estabelecimentos.pendentes') }}': { titulo: 'Estabelecimentos Pendentes', icone: 'building' },
                        '{{ route('admin.estabelecimentos.rejeitados') }}': { titulo: 'Estabelecimentos Rejeitados', icone: 'building' },
                        '{{ route('admin.estabelecimentos.desativados') }}': { titulo: 'Estabelecimentos Desativados', icone: 'building' },
                        '{{ route('admin.processos.index-geral') }}': { titulo: 'Todos os Processos', icone: 'clipboard' },
                        '{{ route('admin.documentos-pendentes.index') }}': { titulo: 'Documentos Pendentes', icone: 'document' },
                        '{{ route('admin.documentos.index') }}': { titulo: 'Todos os Documentos', icone: 'document' },
                        '{{ route('admin.assinatura.pendentes') }}': { titulo: 'Assinaturas Pendentes', icone: 'document' },
                        '{{ route('admin.ordens-servico.index') }}': { titulo: 'Ordens de Serviço', icone: 'clipboard' },
                        '{{ route('admin.ordens-servico.create') }}': { titulo: 'Nova Ordem de Serviço', icone: 'clipboard' },
                        '{{ route('admin.usuarios-externos.index') }}': { titulo: 'Usuários Externos', icone: 'users' },
                        '{{ route('admin.usuarios-internos.index') }}': { titulo: 'Usuários Internos', icone: 'users' },
                    },
                    abrirModal() {
                        this.editandoId = null;
                        this.form = { titulo: '', url: '', icone: 'link' };
                        this.modalAberto = true;
                    },
                    editarAtalho(id, titulo, url, icone) {
                        this.editandoId = id;
                        this.form = { titulo, url, icone };
                        this.modalAberto = true;
                    },
                    fecharModal() {
                        this.modalAberto = false;
                        this.editandoId = null;
                    },
                    atualizarTituloPadrao() {
                        if (!this.editandoId && this.form.url && this.paginas[this.form.url]) {
                            this.form.titulo = this.paginas[this.form.url].titulo;
                            this.form.icone = this.paginas[this.form.url].icone;
                        }
                    },
                    async salvarAtalho() {
                        this.salvando = true;
                        try {
                            const url = this.editandoId 
                                ? `/admin/atalhos-rapidos/${this.editandoId}`
                                : '/admin/atalhos-rapidos';
                            const method = this.editandoId ? 'PUT' : 'POST';
                            
                            const response = await fetch(url, {
                                method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify(this.form)
                            });
                            
                            const data = await response.json();
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert(data.error || 'Erro ao salvar atalho');
                            }
                        } catch (e) {
                            alert('Erro ao salvar atalho');
                        }
                        this.salvando = false;
                    },
                    async excluirAtalho(id) {
                        if (!confirm('Remover este atalho?')) return;
                        
                        try {
                            const response = await fetch(`/admin/atalhos-rapidos/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                }
                            });
                            
                            const data = await response.json();
                            if (data.success) {
                                window.location.reload();
                            }
                        } catch (e) {
                            alert('Erro ao excluir atalho');
                        }
                    }
                }
            }
            </script>
        </div>
    </div>
</div>
@endsection
