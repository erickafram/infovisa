@extends('layouts.admin')

@section('title', 'Estabelecimentos Desativados')
@section('page-title', 'Estabelecimentos Desativados')

@section('content')
<div class="max-w-8xl mx-auto space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Desativados</h2>
            <p class="text-xs text-gray-400 mt-0.5">Estabelecimentos desativados por administradores</p>
        </div>
        <a href="{{ route('admin.estabelecimentos.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-gray-100 rounded-lg p-1 text-sm">
        <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="flex-1 text-center px-3 py-2 rounded-md font-medium transition text-gray-500 hover:text-gray-700">
            Pendentes
            @if(isset($totalPendentes) && $totalPendentes > 0)
                <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold">{{ $totalPendentes }}</span>
            @endif
        </a>
        <a href="{{ route('admin.estabelecimentos.rejeitados') }}" class="flex-1 text-center px-3 py-2 rounded-md font-medium transition text-gray-500 hover:text-gray-700">
            Rejeitados
            @if(isset($totalRejeitados) && $totalRejeitados > 0)
                <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full bg-red-100 text-red-700 font-bold">{{ $totalRejeitados }}</span>
            @endif
        </a>
        <a href="{{ route('admin.estabelecimentos.desativados') }}" class="flex-1 text-center px-3 py-2 rounded-md font-medium transition bg-white text-gray-700 shadow-sm">
            Desativados
            @if(isset($totalDesativados) && $totalDesativados > 0)
                <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full bg-gray-200 text-gray-700 font-bold">{{ $totalDesativados }}</span>
            @endif
        </a>
    </div>

    {{-- Busca --}}
    @if($estabelecimentos->total() > 5 || request('search'))
    <form method="GET" action="{{ route('admin.estabelecimentos.desativados') }}" class="flex gap-2">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por CNPJ, CPF, nome..."
                   class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">Buscar</button>
        @if(request('search'))
            <a href="{{ route('admin.estabelecimentos.desativados') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Limpar</a>
        @endif
    </form>
    @endif

    {{-- Lista --}}
    @if($estabelecimentos->count() > 0)
        <div class="space-y-3">
            @foreach($estabelecimentos as $estabelecimento)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow"
                     x-data="{ showReativar: false }">
                    <div class="p-4">
                        {{-- Linha 1: Nome + Badges --}}
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 leading-tight">{{ $estabelecimento->nome_razao_social }}</h3>
                                @if($estabelecimento->nome_fantasia && $estabelecimento->tipo_pessoa === 'juridica' && $estabelecimento->nome_fantasia !== $estabelecimento->razao_social)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $estabelecimento->nome_fantasia }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[11px] px-2 py-0.5 rounded-full font-medium {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600' }}">
                                    {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}
                                </span>
                                <span class="text-[11px] px-2 py-0.5 rounded-full font-medium bg-gray-100 text-gray-600">Desativado</span>
                            </div>
                        </div>

                        {{-- Linha 2: Dados --}}
                        <div class="flex items-center gap-4 text-xs text-gray-500 mb-2">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                {{ $estabelecimento->documento_formatado }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $estabelecimento->cidade }}/{{ $estabelecimento->estado }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $estabelecimento->updated_at->format('d/m/Y') }}
                            </span>
                        </div>

                        {{-- Motivo --}}
                        @if($estabelecimento->motivo_desativacao)
                        <div class="bg-gray-50 border border-gray-100 rounded-lg px-3 py-2 mb-3">
                            <p class="text-xs text-gray-600"><span class="font-semibold">Motivo:</span> {{ $estabelecimento->motivo_desativacao }}</p>
                        </div>
                        @endif

                        {{-- Ações --}}
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Ver Detalhes
                            </a>
                            @if(auth('interno')->user()->isAdmin())
                            <button @click="showReativar = true"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Reativar
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Painel Reativar (inline) --}}
                    @if(auth('interno')->user()->isAdmin())
                    <div x-show="showReativar" x-collapse x-cloak class="border-t border-gray-100 bg-green-50 px-4 py-3">
                        <form action="{{ route('admin.estabelecimentos.ativar', $estabelecimento->id) }}" method="POST" class="space-y-2">
                            @csrf
                            <p class="text-xs text-green-700">O estabelecimento será reativado e voltará a aparecer na listagem principal.</p>
                            <div class="flex gap-2">
                                <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition">Confirmar Reativação</button>
                                <button type="button" @click="showReativar = false" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition">Cancelar</button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if($estabelecimentos->hasPages())
            <div class="mt-4">{{ $estabelecimentos->links() }}</div>
        @endif
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Nenhum estabelecimento desativado</h3>
            <p class="text-sm text-gray-400 mt-1">Não há estabelecimentos desativados no momento</p>
            <a href="{{ route('admin.estabelecimentos.index') }}" class="inline-flex items-center gap-1 mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium">
                Ver todos os estabelecimentos
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    @endif
</div>
@endsection
