@extends('layouts.admin')

@section('title', 'Pesquisas de Satisfação')
@section('page-title', 'Pesquisas de Satisfação')

@section('content')
<div class="max-w-8xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.configuracoes.index') }}"
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Pesquisas de Satisfação</h1>
                <p class="text-sm text-gray-600 mt-0.5">Crie questionários para avaliação das inspeções sanitárias</p>
            </div>
        </div>
        <a href="{{ route('admin.configuracoes.pesquisas-satisfacao.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Pesquisa
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($pesquisas->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhuma pesquisa cadastrada</h3>
        <p class="text-sm text-gray-500 mb-6">Crie a primeira pesquisa de satisfação para avaliar as inspeções.</p>
        <a href="{{ route('admin.configuracoes.pesquisas-satisfacao.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Criar Pesquisa
        </a>
    </div>
    @else
    <div class="space-y-4">
        @foreach($pesquisas as $pesquisa)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start justify-between gap-4">
                {{-- Info --}}
                <div class="flex items-start gap-4 min-w-0 flex-1">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0
                        {{ $pesquisa->tipo_publico === 'interno' ? 'bg-blue-100' : 'bg-amber-100' }}">
                        @if($pesquisa->tipo_publico === 'interno')
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-semibold text-gray-900">{{ $pesquisa->titulo }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                {{ $pesquisa->tipo_publico === 'interno' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $pesquisa->tipo_publico_label }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                {{ $pesquisa->ativo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $pesquisa->ativo ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>
                        @if($pesquisa->descricao)
                        <p class="text-sm text-gray-500 mt-0.5">{{ Str::limit($pesquisa->descricao, 120) }}</p>
                        @endif
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $pesquisa->perguntas_count }} {{ $pesquisa->perguntas_count === 1 ? 'pergunta' : 'perguntas' }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $pesquisa->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                        {{-- Link de resposta --}}
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs text-gray-400">Link:</span>
                            <code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono">{{ $pesquisa->link_resposta }}</code>
                            <button type="button"
                                    onclick="navigator.clipboard.writeText('{{ $pesquisa->link_resposta }}').then(() => alert('Link copiado!'))"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">Copiar</button>
                        </div>
                    </div>
                </div>
                {{-- Ações --}}
                <div class="flex items-center gap-1 flex-shrink-0">
                    <form action="{{ route('admin.configuracoes.pesquisas-satisfacao.toggle', $pesquisa) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="p-2 rounded-lg text-sm transition-colors {{ $pesquisa->ativo ? 'text-green-600 hover:bg-green-50' : 'text-gray-400 hover:bg-gray-100' }}"
                                title="{{ $pesquisa->ativo ? 'Desativar' : 'Ativar' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $pesquisa->ativo ? 'M5 13l4 4L19 7' : 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' }}"/>
                            </svg>
                        </button>
                    </form>
                    <a href="{{ route('admin.configuracoes.pesquisas-satisfacao.edit', $pesquisa) }}"
                       class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                       title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <form action="{{ route('admin.configuracoes.pesquisas-satisfacao.destroy', $pesquisa) }}" method="POST"
                          onsubmit="return confirm('Excluir esta pesquisa permanentemente?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Excluir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
