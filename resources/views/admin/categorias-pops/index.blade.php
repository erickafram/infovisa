@extends('layouts.admin')

@section('title', 'Categorias de POPs')
@section('page-title', 'Categorias de POPs')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Categorias de Documentos POPs</h2>
            <p class="mt-1 text-sm text-gray-600">Organize documentos por categorias temáticas</p>
        </div>
        <a href="{{ route('admin.configuracoes.categorias-pops.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Categoria
        </a>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabela --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($categorias->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma categoria cadastrada</h3>
                <p class="mt-1 text-sm text-gray-500">Comece criando uma nova categoria.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.configuracoes.categorias-pops.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nova Categoria
                    </a>
                </div>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Documentos</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ordem</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($categorias as $categoria)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $categoria->cor }}"></span>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $categoria->nome }}</div>
                                    @if($categoria->descricao)
                                        <div class="text-sm text-gray-500 mt-1">{{ Str::limit($categoria->descricao, 60) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $categoria->documentos_count }} {{ Str::plural('documento', $categoria->documentos_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm text-gray-600">{{ $categoria->ordem }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($categoria->ativo)
                                <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Ativo</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">Inativo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.configuracoes.categorias-pops.edit', $categoria) }}" 
                                   class="text-blue-600 hover:text-blue-900 transition-colors">
                                    Editar
                                </a>
                                @if($categoria->documentos_count == 0)
                                    <form action="{{ route('admin.configuracoes.categorias-pops.destroy', $categoria) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Tem certeza que deseja excluir esta categoria?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 transition-colors">
                                            Excluir
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 cursor-not-allowed" title="Não é possível excluir categoria com documentos">
                                        Excluir
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Paginação --}}
            @if($categorias->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $categorias->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
