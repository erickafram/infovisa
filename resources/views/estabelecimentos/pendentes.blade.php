@extends('layouts.admin')

@section('title', 'Estabelecimentos Pendentes')
@section('page-title', 'Estabelecimentos Pendentes de Aprovação')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Estabelecimentos Pendentes</h2>
            <p class="text-sm text-gray-600 mt-1">Analise e aprove ou rejeite os estabelecimentos cadastrados</p>
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
               class="border-b-2 border-yellow-500 text-yellow-600 py-3 px-1 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Pendentes
                @if(isset($totalPendentes) && $totalPendentes > 0)
                <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $totalPendentes }}</span>
                @endif
            </a>
            <a href="{{ route('admin.estabelecimentos.rejeitados') }}"
               class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-3 px-1 text-sm font-medium flex items-center gap-2 transition-colors">
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
        <form method="GET" action="{{ route('admin.estabelecimentos.pendentes') }}" class="flex gap-3 items-end">
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
                <a href="{{ route('admin.estabelecimentos.pendentes') }}"
                   class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 text-sm font-semibold transition-colors">
                    Limpar
                </a>
                @endif
            </div>
        </form>
    </div>
    @endif

    {{-- Lista de Pendentes --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($estabelecimentos->count() > 0)
            {{-- Info de resultados --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-yellow-50">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-yellow-800">
                        {{ $estabelecimentos->total() }} estabelecimento{{ $estabelecimentos->total() !== 1 ? 's' : '' }} aguardando aprovação
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
                                    <span class="text-gray-500">Cadastrado em:</span>
                                    <p class="font-medium text-gray-900">{{ $estabelecimento->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>

                            @if($estabelecimento->usuarioExterno)
                            <div class="text-sm">
                                <span class="text-gray-500">Cadastrado por:</span>
                                <span class="font-medium text-gray-900">{{ $estabelecimento->usuarioExterno->nome }}</span>
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
                                Analisar
                            </a>

                            <form action="{{ route('admin.estabelecimentos.aprovar', $estabelecimento->id) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Tem certeza que deseja aprovar este estabelecimento?')"
                                        class="w-full inline-flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm font-medium transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Aprovar Rápido
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
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
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum estabelecimento pendente</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Todos os estabelecimentos foram analisados!
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
