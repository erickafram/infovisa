@extends('layouts.admin')

@section('title', 'Estabelecimentos')
@section('page-title', 'Estabelecimentos')

@section('content')
<div class="space-y-6">
    {{-- Header com botões --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        {{-- Título --}}
        <div>
            <h2 class="text-xl font-bold text-gray-900">Estabelecimentos</h2>
        </div>

        {{-- Botões de ação --}}
        <div class="flex gap-2">
            <a href="{{ route('admin.estabelecimentos.create.juridica') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium transition-colors">
                + Cad. Pessoa Jurídica
            </a>
            <a href="{{ route('admin.estabelecimentos.create.fisica') }}"
               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-medium transition-colors">
                + Cad. Pessoa Física
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('admin.estabelecimentos.index') }}" class="flex gap-3 items-end">
            {{-- Busca --}}
            <div class="flex-1">
                <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Buscar estabelecimento</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="CNPJ, CPF, Razão Social, Nome Fantasia ou Município"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18.75a7.5 7.5 0 006.15-3.1z" />
                    </svg>
                    Buscar
                </button>
                @if(request('search'))
                <a href="{{ route('admin.estabelecimentos.index') }}"
                   class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 text-sm font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Limpar
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Lista de Estabelecimentos --}}
    <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                @if($estabelecimentos->count() > 0)
                    {{-- Info de resultados --}}
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <p class="text-sm text-gray-700">
                            Exibindo {{ $estabelecimentos->firstItem() }} a {{ $estabelecimentos->lastItem() }} de {{ $estabelecimentos->total() }} resultado{{ $estabelecimentos->total() !== 1 ? 's' : '' }}.
                        </p>
                    </div>

                    {{-- Tabela --}}
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">
                                CNPJ/CPF
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-3/12">
                                Razão Social / Nome
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">
                                Nome Fantasia
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">
                                Município
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">
                                Grupo(s) de Risco
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                Situação
                            </th>
                            
                        </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($estabelecimentos as $estabelecimento)
                                <tr class="hover:bg-blue-50 hover:shadow-md cursor-pointer transition-all duration-200 border-l-4 border-transparent hover:border-blue-500" 
                                    onclick="window.location='{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}'">
                                    <td class="px-6 py-4 text-sm font-mono text-gray-900 text-center w-2/12">
                                        {{ $estabelecimento->documento_formatado }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 text-center w-3/12">
                                        {{ $estabelecimento->nome_razao_social }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 text-center w-2/12">
                                        {{ $estabelecimento->nome_fantasia }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 text-center w-2/12">
                                        {{ $estabelecimento->cidade }}
                                    </td>
                                    <td class="px-6 py-4 text-center w-2/12">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Não classificado
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center w-1/12">
                                        @if($estabelecimento->descricao_situacao_cadastral)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estabelecimento->descricao_situacao_cadastral === 'ATIVA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $estabelecimento->descricao_situacao_cadastral }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $estabelecimento->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $estabelecimento->ativo ? 'Ativa' : 'Inativa' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginação --}}
                    <div class="px-6 py-4 border-t border-gray-200 bg-white">
                        {{ $estabelecimentos->appends(request()->query())->links('vendor.pagination.clean') }}
                    </div>

                @else
                    {{-- Estado vazio --}}
                    <div class="text-center py-12 px-4">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum estabelecimento encontrado</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if(request()->filled('search'))
                                Tente ajustar a busca ou remova o filtro para ver todos os estabelecimentos.
                            @else
                                Nenhum estabelecimento foi cadastrado ainda.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
    </div>
</div>
@endsection
