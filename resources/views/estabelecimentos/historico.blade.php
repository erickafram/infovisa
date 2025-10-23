@extends('layouts.admin')

@section('title', 'Histórico do Estabelecimento')
@section('page-title', 'Histórico de Alterações')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Histórico de Alterações</h2>
            <p class="text-sm text-gray-600 mt-1">{{ $estabelecimento->nome_razao_social }}</p>
        </div>

        <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
           class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
    </div>

    {{-- Timeline de Histórico --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($historicos->count() > 0)
            <div class="p-6">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($historicos as $index => $historico)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    {{-- Ícone --}}
                                    <div>
                                        @php
                                            $iconConfig = [
                                                'criado' => ['bg' => 'bg-blue-500', 'icon' => 'M12 4v16m8-8H4'],
                                                'atualizado' => ['bg' => 'bg-gray-500', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                                                'aprovado' => ['bg' => 'bg-green-500', 'icon' => 'M5 13l4 4L19 7'],
                                                'rejeitado' => ['bg' => 'bg-red-500', 'icon' => 'M6 18L18 6M6 6l12 12'],
                                                'arquivado' => ['bg' => 'bg-gray-500', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                                                'reiniciado' => ['bg' => 'bg-yellow-500', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                                            ];
                                            $config = $iconConfig[$historico->acao] ?? $iconConfig['atualizado'];
                                        @endphp
                                        <span class="h-8 w-8 rounded-full {{ $config['bg'] }} flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                                            </svg>
                                        </span>
                                    </div>

                                    {{-- Conteúdo --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    @switch($historico->acao)
                                                        @case('criado')
                                                            Estabelecimento Criado
                                                            @break
                                                        @case('atualizado')
                                                            Dados Atualizados
                                                            @break
                                                        @case('aprovado')
                                                            Estabelecimento Aprovado
                                                            @break
                                                        @case('rejeitado')
                                                            Estabelecimento Rejeitado
                                                            @break
                                                        @case('arquivado')
                                                            Estabelecimento Arquivado
                                                            @break
                                                        @case('reiniciado')
                                                            Status Reiniciado
                                                            @break
                                                        @default
                                                            {{ ucfirst($historico->acao) }}
                                                    @endswitch
                                                </p>
                                                @if($historico->usuario)
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    por <span class="font-medium">{{ $historico->usuario->nome }}</span>
                                                </p>
                                                @endif
                                            </div>
                                            <div class="text-right text-sm text-gray-500">
                                                <p>{{ $historico->created_at->format('d/m/Y') }}</p>
                                                <p class="text-xs">{{ $historico->created_at->format('H:i') }}</p>
                                            </div>
                                        </div>

                                        {{-- Status Change --}}
                                        @if($historico->status_anterior || $historico->status_novo)
                                        <div class="mt-2 flex items-center gap-2 text-sm">
                                            @if($historico->status_anterior)
                                            <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">
                                                {{ ucfirst($historico->status_anterior) }}
                                            </span>
                                            @endif
                                            @if($historico->status_anterior && $historico->status_novo)
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                            @endif
                                            @if($historico->status_novo)
                                            @php
                                                $statusColors = [
                                                    'pendente' => 'bg-yellow-100 text-yellow-800',
                                                    'aprovado' => 'bg-green-100 text-green-800',
                                                    'rejeitado' => 'bg-red-100 text-red-800',
                                                    'arquivado' => 'bg-gray-100 text-gray-800',
                                                ];
                                                $color = $statusColors[$historico->status_novo] ?? 'bg-gray-100 text-gray-700';
                                            @endphp
                                            <span class="px-2 py-1 rounded-full {{ $color }} text-xs font-medium">
                                                {{ ucfirst($historico->status_novo) }}
                                            </span>
                                            @endif
                                        </div>
                                        @endif

                                        {{-- Observação --}}
                                        @if($historico->observacao)
                                        <div class="mt-2 text-sm text-gray-700 bg-gray-50 rounded-lg p-3 border border-gray-200">
                                            <p class="font-medium text-gray-900 mb-1">Observação:</p>
                                            <p>{{ $historico->observacao }}</p>
                                        </div>
                                        @endif

                                        {{-- Dados Alterados --}}
                                        @if($historico->dados_alterados && count($historico->dados_alterados) > 0)
                                        <div class="mt-2">
                                            <details class="group">
                                                <summary class="cursor-pointer text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                    Ver campos alterados ({{ count($historico->dados_alterados) }})
                                                </summary>
                                                <div class="mt-2 text-xs bg-gray-50 rounded-lg p-3 border border-gray-200">
                                                    <pre class="whitespace-pre-wrap">{{ json_encode($historico->dados_alterados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            </details>
                                        </div>
                                        @endif

                                        {{-- Informações Técnicas --}}
                                        @if($historico->ip_address)
                                        <div class="mt-2 text-xs text-gray-500">
                                            IP: {{ $historico->ip_address }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Paginação --}}
            @if($historicos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $historicos->links() }}
            </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum histórico encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Não há registros de alterações para este estabelecimento.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
