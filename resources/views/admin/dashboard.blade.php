@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Header simples --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Olá, {{ auth('interno')->user()->nome }}!</h2>
            <p class="text-sm text-gray-500">Painel administrativo InfoVISA</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.ordens-servico.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-100">
                <span class="w-5 h-5 flex items-center justify-center bg-blue-600 text-white text-xs font-bold rounded">{{ $stats['ordens_servico_andamento'] ?? 0 }}</span>
                OS
            </a>
            <a href="{{ route('admin.assinatura.pendentes') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-700 text-sm font-medium rounded-lg hover:bg-amber-100">
                <span class="w-5 h-5 flex items-center justify-center bg-amber-500 text-white text-xs font-bold rounded">{{ $stats['documentos_pendentes_assinatura'] ?? 0 }}</span>
                Assinaturas
            </a>
            <a href="{{ route('admin.documentos.index', ['status' => 'com_prazos']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100">
                <span class="w-5 h-5 flex items-center justify-center bg-red-500 text-white text-xs font-bold rounded">{{ $stats['documentos_vencendo'] ?? 0 }}</span>
                Prazos
            </a>
            <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-cyan-50 text-cyan-700 text-sm font-medium rounded-lg hover:bg-cyan-100">
                <span class="w-5 h-5 flex items-center justify-center bg-cyan-500 text-white text-xs font-bold rounded">{{ $stats['estabelecimentos_pendentes'] ?? 0 }}</span>
                Pendentes
            </a>
        </div>
    </div>

    {{-- Grid 3 colunas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        {{-- Minhas OS --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Minhas OS</h3>
                <a href="{{ route('admin.ordens-servico.index') }}" class="text-xs text-blue-600 hover:underline">Ver todas</a>
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @forelse($ordens_servico_andamento ?? [] as $os)
                @php
                    $prazoVencido = $os->data_fim && $os->data_fim->isPast();
                    $prazoUrgente = $os->data_fim && !$prazoVencido && $os->data_fim->diffInDays(now()) <= 3;
                @endphp
                <a href="{{ route('admin.ordens-servico.show', $os) }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50">
                    <div>
                        <span class="text-sm font-medium text-gray-800">#{{ $os->numero }}</span>
                        @if($prazoVencido)
                        <span class="ml-1.5 px-1.5 py-0.5 bg-red-100 text-red-600 text-[10px] font-medium rounded">Vencido</span>
                        @elseif($prazoUrgente)
                        <span class="ml-1.5 px-1.5 py-0.5 bg-orange-100 text-orange-600 text-[10px] font-medium rounded">Urgente</span>
                        @endif
                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($os->estabelecimento->nome_fantasia ?? '-', 30) }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="px-4 py-6 text-center text-sm text-gray-400">Nenhuma OS</div>
                @endforelse
            </div>
        </div>

        {{-- Assinaturas Pendentes --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Assinaturas Pendentes</h3>
                <a href="{{ route('admin.assinatura.pendentes') }}" class="text-xs text-amber-600 hover:underline">Ver todas</a>
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @forelse($documentos_pendentes_assinatura ?? [] as $assinatura)
                <div class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50">
                    <div>
                        <p class="text-sm text-gray-800">{{ Str::limit($assinatura->documentoDigital->tipoDocumento->nome ?? 'Documento', 25) }}</p>
                        <p class="text-xs text-gray-400">{{ $assinatura->created_at->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('admin.assinatura.assinar', $assinatura->documentoDigital->id) }}" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded">
                        Assinar
                    </a>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-sm text-gray-400">Nenhuma assinatura</div>
                @endforelse
            </div>
        </div>

        {{-- Prazos a Vencer --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Prazos a Vencer</h3>
                <a href="{{ route('admin.documentos.index', ['status' => 'com_prazos']) }}" class="text-xs text-red-600 hover:underline">Ver todos</a>
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @forelse($documentos_vencendo ?? [] as $doc)
                @php
                    $diasFaltando = $doc->dias_faltando;
                    $corBadge = $diasFaltando <= 0 ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700';
                    $textoPrazo = $diasFaltando < 0 ? 'Vencido' : ($diasFaltando == 0 ? 'Hoje' : $diasFaltando . 'd');
                @endphp
                <a href="{{ route('admin.documentos.show', $doc->id) }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50">
                    <div>
                        <p class="text-sm text-gray-800">{{ Str::limit($doc->tipoDocumento->nome, 25) }}</p>
                        <p class="text-xs text-gray-400">{{ $doc->numero_documento }}</p>
                    </div>
                    <span class="px-2 py-0.5 {{ $corBadge }} text-xs font-semibold rounded">{{ $textoPrazo }}</span>
                </a>
                @empty
                <div class="px-4 py-6 text-center text-sm text-gray-400">Nenhum prazo</div>
                @endforelse
            </div>
        </div>

        {{-- Estabelecimentos Pendentes --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Estabelecimentos Pendentes</h3>
                <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="text-xs text-cyan-600 hover:underline">Ver todos</a>
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @forelse($estabelecimentos_pendentes ?? [] as $estabelecimento)
                <a href="{{ route('admin.estabelecimentos.show', $estabelecimento) }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50">
                    <div>
                        <p class="text-sm text-gray-800">{{ Str::limit($estabelecimento->nome_fantasia ?? $estabelecimento->razao_social, 28) }}</p>
                        <p class="text-xs text-gray-400">{{ $estabelecimento->cidade }} • {{ $estabelecimento->created_at->diffForHumans() }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="px-4 py-6 text-center text-sm text-gray-400">Nenhum pendente</div>
                @endforelse
            </div>
        </div>

        {{-- Minhas Designações --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Minhas Designações</h3>
                <a href="{{ route('admin.processos.index-geral') }}" class="text-xs text-emerald-600 hover:underline">Ver todas</a>
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @forelse($processos_designados ?? [] as $designacao)
                <a href="{{ route('admin.estabelecimentos.processos.show', [$designacao->processo->estabelecimento_id, $designacao->processo->id]) }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50">
                    <div>
                        <span class="text-sm font-medium text-gray-800">{{ $designacao->processo->numero_processo }}</span>
                        @if($designacao->data_limite && $designacao->isAtrasada())
                        <span class="ml-1.5 px-1.5 py-0.5 bg-red-100 text-red-600 text-[10px] font-medium rounded">Atrasado</span>
                        @endif
                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($designacao->descricao_tarefa, 35) }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="px-4 py-6 text-center text-sm text-gray-400">Nenhuma designação</div>
                @endforelse
            </div>
        </div>

        {{-- Processos Acompanhados --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Processos Acompanhados</h3>
                <a href="{{ route('admin.processos.index-geral') }}" class="text-xs text-purple-600 hover:underline">Ver todos</a>
            </div>
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @forelse($processos_acompanhados ?? [] as $processo)
                <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50">
                    <div>
                        <span class="text-sm font-medium text-gray-800">{{ $processo->numero_processo }}</span>
                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social, 30) }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @empty
                <div class="px-4 py-6 text-center text-sm text-gray-400">Nenhum processo</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
