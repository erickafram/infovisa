@extends('layouts.admin')

@section('title', 'Alertas de Processos')
@section('page-title', 'Alertas de Processos')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-sm text-gray-500">Visualize todos os alertas vinculados aos processos, com foco em prazos e pendências.</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
            <p class="text-[10px] font-medium text-gray-500 uppercase mb-1">Total</p>
            <p class="text-xl font-bold text-gray-800">{{ $estatisticas['total'] }}</p>
        </div>
        <a href="{{ route('admin.alertas-processos.index', array_merge(request()->except('status', 'page'), ['status' => 'pendente'])) }}"
           class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm hover:shadow-md transition-shadow {{ request('status') === 'pendente' ? 'ring-2 ring-orange-500' : '' }}">
            <p class="text-[10px] font-medium text-gray-500 uppercase mb-1">Pendentes</p>
            <p class="text-xl font-bold text-orange-600">{{ $estatisticas['pendentes'] }}</p>
        </a>
        <div class="bg-white rounded-lg p-3 border border-red-200 shadow-sm">
            <p class="text-[10px] font-medium text-gray-500 uppercase mb-1">Vencidos</p>
            <p class="text-xl font-bold text-red-600">{{ $estatisticas['vencidos'] }}</p>
        </div>
        <a href="{{ route('admin.alertas-processos.index', array_merge(request()->except('status', 'page'), ['status' => 'concluido'])) }}"
           class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm hover:shadow-md transition-shadow {{ request('status') === 'concluido' ? 'ring-2 ring-green-500' : '' }}">
            <p class="text-[10px] font-medium text-gray-500 uppercase mb-1">Concluídos</p>
            <p class="text-xl font-bold text-green-600">{{ $estatisticas['concluidos'] }}</p>
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.alertas-processos.index') }}" class="grid grid-cols-1 lg:grid-cols-4 gap-3">
            <div class="lg:col-span-2">
                <input type="text"
                       name="busca"
                       value="{{ request('busca') }}"
                       placeholder="Buscar por descrição, processo, estabelecimento ou CNPJ"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>
            <div>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Todos os status</option>
                    <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendentes</option>
                    <option value="concluido" {{ request('status') === 'concluido' ? 'selected' : '' }}>Concluídos</option>
                </select>
            </div>
            <div>
                <select name="estabelecimento_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Todos os estabelecimentos</option>
                    @foreach($estabelecimentos as $est)
                    <option value="{{ $est->id }}" {{ request('estabelecimento_id') == $est->id ? 'selected' : '' }}>
                        {{ $est->nome_fantasia ?: $est->razao_social ?: $est->nome_completo }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-4 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium">
                    Filtrar
                </button>
                @if(request()->hasAny(['status', 'estabelecimento_id', 'busca']))
                <a href="{{ route('admin.alertas-processos.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                    Limpar
                </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($alertas->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($alertas as $alerta)
            <div class="p-4 sm:p-6 hover:bg-gray-50 transition-colors {{ $alerta->status !== 'concluido' ? ($alerta->isVencido() ? 'bg-red-50' : ($alerta->isProximo() ? 'bg-yellow-50' : '')) : 'bg-gray-50' }}">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 mt-0.5">
                                @if($alerta->status === 'concluido')
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                @elseif($alerta->isVencido())
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                @elseif($alerta->isProximo())
                                <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                @else
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $alerta->descricao }}</p>

                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="font-medium {{ $alerta->isVencido() && $alerta->status !== 'concluido' ? 'text-red-600' : '' }}">
                                            {{ $alerta->data_alerta?->format('d/m/Y') ?? '-' }}
                                        </span>
                                    </span>

                                    @if($alerta->processo)
                                    <a href="{{ route('admin.estabelecimentos.processos.show', [$alerta->processo->estabelecimento_id, $alerta->processo_id]) }}" class="flex items-center gap-1 text-blue-600 hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ $alerta->processo->numero ?? $alerta->processo->numero_processo }}
                                    </a>
                                    @endif

                                    @if($alerta->processo?->estabelecimento)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        {{ $alerta->processo->estabelecimento->nome_fantasia ?: $alerta->processo->estabelecimento->razao_social ?: $alerta->processo->estabelecimento->nome_completo }}
                                    </span>
                                    @endif

                                    @php
                                        $estabelecimentoAlerta = $alerta->processo?->estabelecimento;
                                        $municipioNomeAlerta = data_get($estabelecimentoAlerta, 'municipioRelacionado.nome')
                                            ?? (is_object(data_get($estabelecimentoAlerta, 'municipio')) ? data_get($estabelecimentoAlerta, 'municipio.nome') : null)
                                            ?? $estabelecimentoAlerta?->cidade;
                                    @endphp
                                    @if($municipioNomeAlerta)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $municipioNomeAlerta }}
                                    </span>
                                    @endif

                                    @if($alerta->usuarioCriador)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $alerta->usuarioCriador->nome }}
                                    </span>
                                    @endif
                                </div>

                                @if($alerta->status === 'concluido' && $alerta->concluido_em)
                                <p class="mt-2 text-xs text-green-600 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Resolvido em {{ $alerta->concluido_em->format('d/m/Y \à\s H:i') }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 lg:ml-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full
                            @if($alerta->status === 'concluido') bg-green-100 text-green-700
                            @elseif($alerta->isVencido()) bg-red-100 text-red-700
                            @elseif($alerta->isProximo()) bg-yellow-100 text-yellow-700
                            @else bg-blue-100 text-blue-700
                            @endif">
                            @if($alerta->status === 'concluido') Concluído
                            @elseif($alerta->isVencido()) Vencido
                            @elseif($alerta->isProximo()) Próximo
                            @else Pendente
                            @endif
                        </span>

                        @if($alerta->status !== 'concluido' && $alerta->processo)
                        <form action="{{ route('admin.estabelecimentos.processos.alertas.concluir', [$alerta->processo->estabelecimento_id, $alerta->processo_id, $alerta->id]) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors"
                                    onclick="return confirm('Confirma que este alerta foi resolvido?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Marcar Resolvido
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $alertas->links('pagination.tailwind-clean') }}
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum alerta encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">Os alertas vinculados aos processos aparecerão aqui.</p>
        </div>
        @endif
    </div>
</div>
@endsection
