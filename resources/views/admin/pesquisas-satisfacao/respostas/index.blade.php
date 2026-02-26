@extends('layouts.admin')

@section('title', 'Respostas das Pesquisas de Satisfação')
@section('page-title', 'Pesquisas de Satisfação')

@section('content')
<div class="max-w-8xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Pesquisas de Satisfação</h1>
        <p class="text-sm text-gray-600 mt-0.5">Visualize todas as respostas das pesquisas de satisfação</p>
    </div>

    {{-- Cards de Estatísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalRespostas }}</p>
                    <p class="text-xs text-gray-500">Total de Respostas</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $respostasInterno }}</p>
                    <p class="text-xs text-gray-500">Respostas Internas</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $respostasExterno }}</p>
                    <p class="text-xs text-gray-500">Respostas Externas</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('admin.pesquisas-satisfacao.respostas.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Pesquisa</label>
                <select name="pesquisa_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas</option>
                    @foreach($pesquisas as $p)
                        <option value="{{ $p->id }}" {{ request('pesquisa_id') == $p->id ? 'selected' : '' }}>
                            {{ Str::limit($p->titulo, 30) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                <select name="tipo_respondente" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="interno" {{ request('tipo_respondente') === 'interno' ? 'selected' : '' }}>Interno</option>
                    <option value="externo" {{ request('tipo_respondente') === 'externo' ? 'selected' : '' }}>Externo</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nº da OS</label>
                <input type="text" name="ordem_servico_id" value="{{ request('ordem_servico_id') }}" 
                       placeholder="ID da OS"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" 
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="data_fim" value="{{ request('data_fim') }}" 
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" 
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filtrar
                </button>
                <a href="{{ route('admin.pesquisas-satisfacao.respostas.index') }}" 
                   class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                   title="Limpar filtros">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Tabela de Respostas --}}
    @if($respostas->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhuma resposta encontrada</h3>
        <p class="text-sm text-gray-500">Nenhuma resposta foi registrada com os filtros aplicados.</p>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Respondente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pesquisa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">OS</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estabelecimento</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($respostas as $resposta)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <div>
                                <p class="font-medium text-gray-900">{{ $resposta->respondente_nome ?? 'Anônimo' }}</p>
                                @if($resposta->respondente_email)
                                    <p class="text-xs text-gray-500">{{ $resposta->respondente_email }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-gray-700">{{ Str::limit($resposta->pesquisa->titulo ?? '-', 30) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($resposta->tipo_respondente === 'interno')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Interno</span>
                            @elseif($resposta->tipo_respondente === 'externo')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Externo</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($resposta->ordemServico)
                                <a href="{{ route('admin.ordens-servico.show', $resposta->ordemServico) }}" 
                                   class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                    OS #{{ $resposta->ordemServico->numero }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($resposta->estabelecimento)
                                <span class="text-gray-700 text-xs">{{ Str::limit($resposta->estabelecimento->nome_fantasia ?? $resposta->estabelecimento->razao_social ?? '-', 25) }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            {{ $resposta->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.pesquisas-satisfacao.respostas.show', $resposta) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors"
                                   title="Ver detalhes">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.pesquisas-satisfacao.respostas.destroy', $resposta) }}"
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta resposta?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 transition-colors"
                                            title="Excluir resposta">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($respostas->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $respostas->links() }}
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
