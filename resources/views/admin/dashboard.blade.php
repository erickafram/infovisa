@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Visão Geral')

@section('content')
<div class="space-y-6">
    {{-- Header Compacto --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight flex items-center gap-2">
                Olá, {{ auth('interno')->user()->nome }}! 
                <span class="text-xs font-normal text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Admin</span>
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Resumo das atividades de hoje.
            </p>
        </div>
        <div class="flex items-center gap-2">
             <span class="text-xs font-medium text-gray-500 bg-white px-3 py-1.5 rounded-md border border-gray-200 shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ now()->format('d/m/Y') }}
             </span>
        </div>
    </div>

    {{-- Stats Grid Compacto --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $statCards = [
                [
                    'title' => 'Ordens de Serviço',
                    'value' => $stats['ordens_servico_andamento'] ?? 0,
                    'color' => 'blue',
                    'route' => 'admin.ordens-servico.index',
                    'label' => 'Em andamento'
                ],
                [
                    'title' => 'Assinaturas',
                    'value' => $stats['documentos_pendentes_assinatura'] ?? 0,
                    'color' => 'amber',
                    'route' => 'admin.assinatura.pendentes',
                    'label' => 'Pendentes'
                ],
                [
                    'title' => 'Prazos',
                    'value' => $stats['documentos_vencendo'] ?? 0,
                    'color' => 'red',
                    'route' => 'admin.documentos.index',
                    'params' => ['status' => 'com_prazos'],
                    'label' => 'A vencer'
                ],
                [
                    'title' => 'Estabelecimentos',
                    'value' => $stats['estabelecimentos_pendentes'] ?? 0,
                    'color' => 'cyan',
                    'route' => 'admin.estabelecimentos.pendentes',
                    'label' => 'Novos'
                ]
            ];
        @endphp

        @foreach($statCards as $card)
            <a href="{{ route($card['route'], $card['params'] ?? []) }}" 
               class="group relative bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md hover:border-{{ $card['color'] }}-200 transition-all duration-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $card['title'] }}</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <h3 class="text-2xl font-bold text-gray-800 group-hover:text-{{ $card['color'] }}-600 transition-colors">
                                {{ $card['value'] }}
                            </h3>
                            <span class="text-[10px] font-medium px-1.5 py-0.5 bg-{{ $card['color'] }}-50 text-{{ $card['color'] }}-600 rounded-full">
                                {{ $card['label'] }}
                            </span>
                        </div>
                    </div>
                    <div class="p-2 bg-{{ $card['color'] }}-50 rounded-lg text-{{ $card['color'] }}-600 group-hover:bg-{{ $card['color'] }}-100 transition-colors">
                        @if($card['color'] == 'blue') 
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        @elseif($card['color'] == 'amber')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        @elseif($card['color'] == 'red')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @elseif($card['color'] == 'cyan')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        {{-- SECTION 1: MY TASKS --}}
        <div class="xl:col-span-2 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Assinaturas Pendentes Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col h-full">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                             <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
                             <h3 class="text-sm font-semibold text-gray-800">Assinaturas</h3>
                        </div>
                        <a href="{{ route('admin.assinatura.pendentes') }}" class="text-[10px] font-bold text-amber-600 hover:text-amber-700 bg-amber-50 hover:bg-amber-100 px-2 py-0.5 rounded transition-colors uppercase tracking-wide">
                            Ver todas
                        </a>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <div class="divide-y divide-gray-50">
                            @forelse($documentos_pendentes_assinatura ?? [] as $assinatura)
                            <div class="group flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 transition-colors">
                                <div class="flex-1 min-w-0 pr-4">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $assinatura->documentoDigital->tipoDocumento->nome ?? 'Documento' }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 flex items-center gap-1">
                                        {{ $assinatura->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <a href="{{ route('admin.assinatura.assinar', $assinatura->documentoDigital->id) }}" class="opacity-0 group-hover:opacity-100 transition-opacity px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold rounded shadow-sm uppercase">
                                    Assinar
                                </a>
                            </div>
                            @empty
                            <div class="py-6 text-center">
                                <span class="text-xs text-gray-400">Nenhuma pendência.</span>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Prazos Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col h-full">
                     <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                             <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div>
                             <h3 class="text-sm font-semibold text-gray-800">Prazos</h3>
                        </div>
                        <a href="{{ route('admin.documentos.index', ['status' => 'com_prazos']) }}" class="text-[10px] font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-2 py-0.5 rounded transition-colors uppercase tracking-wide">
                            Agenda
                        </a>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <div class="divide-y divide-gray-50">
                            @forelse($documentos_vencendo ?? [] as $doc)
                                @php
                                    $diasFaltando = $doc->dias_faltando;
                                    $isVencido = $diasFaltando < 0;
                                    $isHoje = $diasFaltando == 0;
                                    $badgeClass = $isVencido ? 'text-red-600 bg-red-50' : ($isHoje ? 'text-orange-600 bg-orange-50' : 'text-gray-500 bg-gray-50');
                                    $texto = $isVencido ? 'Vencido' : ($isHoje ? 'Hoje' : $diasFaltando . ' d');
                                @endphp
                                <a href="{{ route('admin.documentos.show', $doc->id) }}" class="block px-4 py-2.5 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 pr-2">
                                            <p class="text-xs font-medium text-gray-900 truncate">{{ Str::limit($doc->tipoDocumento->nome, 30) }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $doc->numero_documento }}</p>
                                        </div>
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold {{ $badgeClass }}">
                                            {{ $texto }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="py-6 text-center">
                                    <span class="text-xs text-gray-400">Nenhum prazo próximo.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ordens de Serviço (Wide) --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                 <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                         <h3 class="text-sm font-semibold text-gray-800">Ordens de Serviço Ativas</h3>
                         <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">{{ count($ordens_servico_andamento) }}</span>
                    </div>
                    <a href="{{ route('admin.ordens-servico.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Ver todas</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="text-left">
                                <th class="px-4 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Número</th>
                                <th class="px-4 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estabelecimento</th>
                                <th class="px-4 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cidade</th>
                                <th class="px-4 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Prazo</th>
                                <th class="px-4 py-2 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($ordens_servico_andamento ?? [] as $os)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span class="text-xs font-bold text-gray-700">#{{ $os->numero }}</span>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-xs font-medium text-gray-900">{{ Str::limit($os->estabelecimento->nome_fantasia ?? '-', 25) }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $os->tipo_acao_id ? 'Fiscalização' : 'Vistoria' }}</div>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span class="text-[10px] text-gray-500">{{ $os->municipio->nome ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        @if($os->data_fim)
                                            <span class="text-xs {{ $os->data_fim->isPast() ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                                {{ $os->data_fim->format('d/m') }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-right">
                                        <a href="{{ route('admin.ordens-servico.show', $os) }}" class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-xs text-gray-400">
                                        Nenhuma ordem de serviço.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- SECTION 2: MONITORING --}}
        <div class="space-y-6">
            
            {{-- Estabelecimentos Recentes --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-cyan-50/20">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide">Novos Cadastros</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($estabelecimentos_pendentes ?? [] as $est)
                        <div class="p-3 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-xs font-bold text-gray-900 truncate max-w-[70%]">
                                    <a href="{{ route('admin.estabelecimentos.show', $est) }}" class="hover:underline">
                                        {{ $est->nome_fantasia ?? $est->razao_social }}
                                    </a>
                                </h4>
                                <span class="text-[10px] text-gray-400">{{ $est->created_at->format('d/m') }}</span>
                            </div>
                            <p class="text-[10px] text-gray-500 flex items-center gap-1 mb-2">
                                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ $est->cidade ?? '-' }}
                            </p>
                            <a href="{{ route('admin.estabelecimentos.show', $est) }}" class="block w-full rounded border border-cyan-200 bg-cyan-50 py-1 text-center text-[10px] font-bold text-cyan-700 hover:bg-cyan-100 transition-colors">
                                Analisar
                            </a>
                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-gray-400">Nenhum cadastro recente.</div>
                    @endforelse
                </div>
            </div>

            {{-- Processos Acompanhados --}}
             <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wide">Monitoramento</h3>
                    <a href="{{ route('admin.processos.index-geral') }}" class="text-[10px] text-gray-500 hover:text-gray-900 font-medium">Ver tudo</a>
                </div>
                <div>
                    @forelse($processos_acompanhados ?? [] as $proc)
                        <a href="{{ route('admin.estabelecimentos.processos.show', [$proc->estabelecimento_id, $proc->id]) }}" class="flex items-center gap-3 p-3 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors">
                            <div class="flex-shrink-0 w-6 h-6 rounded bg-purple-50 flex items-center justify-center text-purple-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-gray-800 truncate">{{ $proc->numero_processo }}</p>
                                <p class="text-[10px] text-gray-500 truncate">{{ $proc->estabelecimento->nome_fantasia ?? 'Sem nome' }}</p>
                            </div>
                        </a>
                    @empty
                         <div class="p-4 text-center text-xs text-gray-400">Nenhum processo em monitoramento.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
