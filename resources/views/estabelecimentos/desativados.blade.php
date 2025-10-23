@extends('layouts.admin')

@section('title', 'Estabelecimentos Desativados')
@section('page-title', 'Estabelecimentos Desativados')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Estabelecimentos Desativados</h2>
            <p class="text-sm text-gray-600 mt-1">Estabelecimentos que foram desativados por administradores</p>
        </div>

        <a href="{{ route('admin.estabelecimentos.index') }}"
           class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>
    </div>

    {{-- Filtro de Busca --}}
    @if($estabelecimentos->total() > 5)
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <form method="GET" action="{{ route('admin.estabelecimentos.desativados') }}" class="flex gap-3 items-end">
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
                <a href="{{ route('admin.estabelecimentos.desativados') }}"
                   class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 text-sm font-semibold transition-colors">
                    Limpar
                </a>
                @endif
            </div>
        </form>
    </div>
    @endif

    {{-- Lista de Desativados --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($estabelecimentos->count() > 0)
            {{-- Info de resultados --}}
            <div class="px-4 py-3 border-b border-gray-200 bg-orange-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        <p class="text-xs font-medium text-orange-800">
                            {{ $estabelecimentos->total() }} estabelecimento{{ $estabelecimentos->total() !== 1 ? 's' : '' }} desativado{{ $estabelecimentos->total() !== 1 ? 's' : '' }}
                        </p>
                    </div>
                    <p class="text-xs text-gray-600">
                        Exibindo {{ $estabelecimentos->firstItem() }} a {{ $estabelecimentos->lastItem() }} de {{ $estabelecimentos->total() }}
                    </p>
                </div>
            </div>

            {{-- Tabela Compacta --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razão Social / Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome Fantasia</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Município</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desativado em</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($estabelecimentos as $estabelecimento)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900">
                                {{ $estabelecimento->documento_formatado }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-gray-900">{{ $estabelecimento->nome_razao_social }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $estabelecimento->nome_fantasia ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $estabelecimento->cidade }} - {{ $estabelecimento->estado }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($estabelecimento->motivo_desativacao)
                                    <div class="text-orange-700 max-w-xs">
                                        {{ Str::limit($estabelecimento->motivo_desativacao, 60) }}
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $estabelecimento->updated_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
                                       class="text-blue-600 hover:text-blue-800"
                                       title="Ver Detalhes">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if(auth('interno')->user()->nivel_acesso->isAdmin())
                                    <form action="{{ route('admin.estabelecimentos.ativar', $estabelecimento->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('Tem certeza que deseja reativar este estabelecimento?')"
                                                class="text-green-600 hover:text-green-800"
                                                title="Reativar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum estabelecimento desativado</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Não há estabelecimentos desativados no momento.
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
