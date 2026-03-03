@extends('layouts.admin')

@section('title', 'Relatório de Documentos por Servidor')

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb + Título --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.relatorios.index') }}" class="hover:text-gray-700">Relatórios</a>
                <span>/</span>
                <span class="text-gray-900">Documentos por Servidor</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Documentos por Servidor</h1>
            <p class="text-gray-500 mt-1">Acompanhamento de documentos e prazos agrupados por servidor</p>
        </div>
    </div>

    {{-- Cards de Totais --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total de Documentos</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totais['total'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Com Prazo</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($totais['com_prazo'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 shadow-sm hover:shadow-md transition-all p-4 {{ $totais['atrasados'] > 0 ? 'bg-red-50' : '' }}">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Atrasados</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($totais['atrasados'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 shadow-sm hover:shadow-md transition-all p-4 {{ $totais['vencendo'] > 0 ? 'bg-amber-50' : '' }}">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Vencendo em 5 dias</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($totais['vencendo'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-800">Filtros</h2>
            @if(request()->hasAny(['servidor_id', 'tipo_documento_id', 'status_prazo', 'status', 'data_inicio', 'data_fim']))
                <a href="{{ route('admin.relatorios.documentos-por-servidor') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Limpar filtros</a>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.relatorios.documentos-por-servidor') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Servidor</label>
                <select name="servidor_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($servidores as $servidor)
                        <option value="{{ $servidor->id }}" @selected((string) request('servidor_id') === (string) $servidor->id)>
                            {{ $servidor->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de documento</label>
                <select name="tipo_documento_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($tiposDocumento as $tipo)
                        <option value="{{ $tipo->id }}" @selected((string) request('tipo_documento_id') === (string) $tipo->id)>
                            {{ $tipo->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status do Prazo</label>
                <select name="status_prazo" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="atrasado" @selected(request('status_prazo') === 'atrasado')>Atrasados</option>
                    <option value="vencendo" @selected(request('status_prazo') === 'vencendo')>Vencendo em 5 dias</option>
                    <option value="em_dia" @selected(request('status_prazo') === 'em_dia')>Em dia</option>
                    <option value="sem_prazo" @selected(request('status_prazo') === 'sem_prazo')>Sem prazo</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status Documento</label>
                <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="rascunho" @selected(request('status') === 'rascunho')>Rascunho</option>
                    <option value="aguardando_assinatura" @selected(request('status') === 'aguardando_assinatura')>Aguardando Assinatura</option>
                    <option value="assinado" @selected(request('status') === 'assinado')>Assinado</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data inicial</label>
                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data final</label>
                <div class="flex gap-2">
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                        Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Ranking por Servidor --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h2 class="text-base font-semibold text-gray-900">Documentos por Servidor</h2>
                <span class="text-xs text-gray-500 ml-auto">{{ count($dadosPorServidor) }} servidor(es)</span>
            </div>
        </div>

        @if(count($dadosPorServidor) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">Servidor</th>
                        <th class="px-3 py-3 text-center font-semibold">Setor</th>
                        <th class="px-3 py-3 text-center font-semibold">Total</th>
                        <th class="px-3 py-3 text-center font-semibold">
                            <span class="text-red-600">Atrasados</span>
                        </th>
                        <th class="px-3 py-3 text-center font-semibold">
                            <span class="text-amber-600">Vencendo</span>
                        </th>
                        <th class="px-3 py-3 text-center font-semibold">
                            <span class="text-green-600">Em dia</span>
                        </th>
                        <th class="px-3 py-3 text-center font-semibold">Finalizados</th>
                        <th class="px-3 py-3 text-center font-semibold">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($dadosPorServidor as $dado)
                    <tr class="hover:bg-gray-50 transition-colors {{ $dado['atrasados'] > 0 ? 'bg-red-50/50' : '' }}">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700">
                                    {{ strtoupper(substr($dado['servidor']->nome, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $dado['servidor']->nome }}</p>
                                    <p class="text-xs text-gray-500">{{ $dado['servidor']->nivel_acesso?->label() ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center text-gray-600 text-xs">{{ $dado['servidor']->setor ?? '-' }}</td>
                        <td class="px-3 py-3 text-center font-semibold text-gray-900">{{ $dado['total'] }}</td>
                        <td class="px-3 py-3 text-center">
                            @if($dado['atrasados'] > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    {{ $dado['atrasados'] }}
                                </span>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($dado['vencendo'] > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    {{ $dado['vencendo'] }}
                                </span>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($dado['em_dia'] > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                    {{ $dado['em_dia'] }}
                                </span>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-gray-600">{{ $dado['finalizados'] }}</td>
                        <td class="px-3 py-3 text-center">
                            <a href="{{ route('admin.relatorios.documentos-por-servidor', array_merge(request()->query(), ['servidor_id' => $dado['servidor']->id])) }}" 
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                Ver docs
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="font-medium">Nenhum dado encontrado</p>
            <p class="text-sm mt-1">Tente ajustar os filtros</p>
        </div>
        @endif
    </div>

    {{-- Listagem Detalhada de Documentos --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h2 class="text-base font-semibold text-gray-900">Documentos — Detalhamento</h2>
                <span class="text-xs text-gray-500 ml-auto">{{ $documentos->total() }} resultado(s)</span>
            </div>
        </div>

        @if($documentos->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Documento</th>
                        <th class="px-3 py-3 text-left font-semibold">Tipo</th>
                        <th class="px-3 py-3 text-left font-semibold">Servidor</th>
                        <th class="px-3 py-3 text-left font-semibold">Estabelecimento</th>
                        <th class="px-3 py-3 text-center font-semibold">Status</th>
                        <th class="px-3 py-3 text-center font-semibold">Prazo</th>
                        <th class="px-3 py-3 text-center font-semibold">Vencimento</th>
                        <th class="px-3 py-3 text-left font-semibold">OS</th>
                        <th class="px-3 py-3 text-center font-semibold">Criado em</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($documentos as $doc)
                    @php
                        $vencido = $doc->data_vencimento && !$doc->prazo_finalizado_em && $doc->data_vencimento->lt(now()->startOfDay());
                        $vencendo = $doc->data_vencimento && !$doc->prazo_finalizado_em && !$vencido && $doc->data_vencimento->lte(now()->addDays(5)->startOfDay());
                        $prazoFinalizado = $doc->prazo_finalizado_em !== null;

                        if ($vencido) {
                            $diasAtraso = $doc->data_vencimento->diffInDays(now());
                        }
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors {{ $vencido ? 'bg-red-50/40' : ($vencendo ? 'bg-amber-50/40' : '') }}">
                        <td class="px-4 py-3">
                            @if($doc->processo_id && $doc->processo?->estabelecimento_id)
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$doc->processo->estabelecimento_id, $doc->processo_id]) }}" class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                {{ $doc->numero_documento }}
                            </a>
                            @else
                            <span class="font-medium text-gray-900">{{ $doc->numero_documento }}</span>
                            @endif
                            @if($doc->nome)
                            <p class="text-xs text-gray-500 mt-0.5 truncate max-w-[200px]">{{ $doc->nome }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-gray-700 text-xs">{{ $doc->tipoDocumento->nome ?? '-' }}</td>
                        <td class="px-3 py-3">
                            <span class="text-gray-900 text-xs font-medium">{{ $doc->usuarioCriador->nome ?? '-' }}</span>
                        </td>
                        <td class="px-3 py-3 text-xs text-gray-700">
                            @if($doc->processo?->estabelecimento)
                                <span class="truncate max-w-[180px] block">{{ $doc->processo->estabelecimento->nome_fantasia ?? $doc->processo->estabelecimento->razao_social }}</span>
                                @if($doc->processo->estabelecimento->municipio)
                                <span class="text-gray-400">{{ $doc->processo->estabelecimento->municipio->nome }}</span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($doc->status === 'assinado')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Assinado</span>
                            @elseif($doc->status === 'aguardando_assinatura')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Ag. Assinatura</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Rascunho</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($prazoFinalizado)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Finalizado</span>
                            @elseif($vencido)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    {{ $diasAtraso }} dia{{ $diasAtraso > 1 ? 's' : '' }} atrasado
                                </span>
                            @elseif($vencendo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Vencendo</span>
                            @elseif($doc->data_vencimento)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Em dia</span>
                            @else
                                <span class="text-gray-400 text-xs">Sem prazo</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-xs text-gray-700">
                            {{ $doc->data_vencimento?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-3 py-3 text-xs">
                            @if($doc->ordemServico)
                                <a href="{{ route('admin.ordens-servico.show', $doc->ordemServico->id) }}" class="text-blue-600 hover:underline">
                                    {{ $doc->ordemServico->numero }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-xs text-gray-600">
                            {{ $doc->created_at->format('d/m/Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        <div class="px-5 py-4 border-t border-gray-200">
            {{ $documentos->links() }}
        </div>
        @else
        <div class="p-8 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="font-medium">Nenhum documento encontrado</p>
            <p class="text-sm mt-1">Tente ajustar os filtros</p>
        </div>
        @endif
    </div>
</div>
@endsection
