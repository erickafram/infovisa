@extends('layouts.admin')

@section('title', 'Estabelecimentos Rejeitados')
@section('page-title', 'Estabelecimentos Rejeitados')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Estabelecimentos Rejeitados</h2>
            <p class="text-sm text-gray-600 mt-1">Estabelecimentos que foram rejeitados e podem ser reiniciados</p>
        </div>

        <a href="{{ route('admin.estabelecimentos.index') }}"
           class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
    </div>

    {{-- Tabs de Navegação --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('admin.estabelecimentos.pendentes') }}"
               class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-3 px-1 text-sm font-medium flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Pendentes
                @if(isset($totalPendentes) && $totalPendentes > 0)
                <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $totalPendentes }}</span>
                @endif
            </a>
            <a href="{{ route('admin.estabelecimentos.rejeitados') }}"
               class="border-b-2 border-red-500 text-red-600 py-3 px-1 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Rejeitados
                @if(isset($totalRejeitados) && $totalRejeitados > 0)
                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $totalRejeitados }}</span>
                @endif
            </a>
            <a href="{{ route('admin.estabelecimentos.desativados') }}"
               class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-3 px-1 text-sm font-medium flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Desativados
                @if(isset($totalDesativados) && $totalDesativados > 0)
                <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $totalDesativados }}</span>
                @endif
            </a>
        </nav>
    </div>

    {{-- Filtro de Busca --}}
    @if($estabelecimentos->total() > 5)
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <form method="GET" action="{{ route('admin.estabelecimentos.rejeitados') }}" class="flex gap-3 items-end">
            <div class="flex-1">
                <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="CNPJ, CPF, Razão Social, Nome..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
                @if(request('search'))
                <a href="{{ route('admin.estabelecimentos.rejeitados') }}"
                   class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 text-sm font-semibold transition-colors">
                    Limpar
                </a>
                @endif
            </div>
        </form>
    </div>
    @endif

    {{-- Lista de Rejeitados --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($estabelecimentos->count() > 0)
            {{-- Info de resultados --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-red-800">
                        {{ $estabelecimentos->total() }} estabelecimento{{ $estabelecimentos->total() !== 1 ? 's' : '' }} rejeitado{{ $estabelecimentos->total() !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>

            {{-- Cards de Estabelecimentos --}}
            <div class="divide-y divide-gray-200">
                @foreach($estabelecimentos as $estabelecimento)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        {{-- Informações --}}
                        <div class="flex-1 space-y-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $estabelecimento->nome_razao_social }}
                                    </h3>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}
                                    </span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        Rejeitado
                                    </span>
                                </div>
                                @if($estabelecimento->nome_fantasia && $estabelecimento->tipo_pessoa === 'juridica')
                                <p class="text-sm text-gray-600">{{ $estabelecimento->nome_fantasia }}</p>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Documento:</span>
                                    <p class="font-medium text-gray-900">{{ $estabelecimento->documento_formatado }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Município:</span>
                                    <p class="font-medium text-gray-900">{{ $estabelecimento->cidade }} - {{ $estabelecimento->estado }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Rejeitado em:</span>
                                    <p class="font-medium text-gray-900">{{ $estabelecimento->aprovado_em->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>

                            @if($estabelecimento->motivo_rejeicao)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <p class="text-xs font-semibold text-red-800 mb-1">Motivo da Rejeição:</p>
                                <p class="text-sm text-red-700">{{ $estabelecimento->motivo_rejeicao }}</p>
                            </div>
                            @endif

                            @if($estabelecimento->aprovadoPor)
                            <div class="text-xs text-gray-500">
                                Rejeitado por <strong>{{ $estabelecimento->aprovadoPor->nome }}</strong>
                            </div>
                            @endif
                        </div>

                        {{-- Ações --}}
                        <div class="flex flex-col gap-2 min-w-[200px]">
                            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
                               class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Ver Detalhes
                            </a>

                            <button onclick="showReiniciarModal{{ $estabelecimento->id }}()"
                                    class="inline-flex items-center justify-center gap-2 bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reiniciar (Pendente)
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Modal de Reiniciar --}}
                <div id="modal-reiniciar-{{ $estabelecimento->id }}" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Reiniciar Estabelecimento</h3>
                                <button onclick="hideReiniciarModal{{ $estabelecimento->id }}()" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">O status do estabelecimento voltará para "Pendente" e poderá ser reanalisado.</p>
                            <form action="{{ route('admin.estabelecimentos.reiniciar', $estabelecimento->id) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                                    <textarea name="observacao" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                              placeholder="Motivo do reinício..."></textarea>
                                </div>
                                <div class="flex gap-3">
                                    <button type="button" onclick="hideReiniciarModal{{ $estabelecimento->id }}()"
                                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                            class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                                        Reiniciar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    function showReiniciarModal{{ $estabelecimento->id }}() {
                        document.getElementById('modal-reiniciar-{{ $estabelecimento->id }}').classList.remove('hidden');
                    }
                    function hideReiniciarModal{{ $estabelecimento->id }}() {
                        document.getElementById('modal-reiniciar-{{ $estabelecimento->id }}').classList.add('hidden');
                    }
                </script>
                @endforeach
            </div>

            {{-- Paginação --}}
            @if($estabelecimentos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $estabelecimentos->links() }}
            </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum estabelecimento rejeitado</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Não há estabelecimentos rejeitados no momento.
                </p>
                <div class="mt-6">
                    <a href="{{ route('admin.estabelecimentos.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Ver Todos os Estabelecimentos
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
