@extends('layouts.admin')

@section('title', 'Tipos de Documento Obrigatório')
@section('page-title', 'Tipos de Documento Obrigatório')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-gray-600">Cadastre os tipos de documentos que os estabelecimentos precisam apresentar (CNPJ, Contrato Social, etc.)</p>
        </div>
        <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Tipo
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="busca" value="{{ request('busca') }}" 
                       placeholder="Buscar por nome ou descrição..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-40">
                <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos os status</option>
                    <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                    <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativos</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Filtrar
            </button>
            @if(request()->hasAny(['busca', 'status']))
            <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                Limpar
            </a>
            @endif
        </form>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <p class="text-sm text-red-800">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Tabela --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($tipos->isEmpty())
        <div class="p-8 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500">Nenhum tipo de documento encontrado</p>
        </div>
        @else
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nome</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Descrição</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Ordem</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($tipos as $tipo)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="text-sm font-medium text-gray-900">{{ $tipo->nome }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm text-gray-600">{{ Str::limit($tipo->descricao, 60) ?: '-' }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm text-gray-600">{{ $tipo->ordem }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($tipo->ativo)
                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Ativo</span>
                        @else
                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Inativo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.edit', $tipo) }}" 
                               class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('admin.configuracoes.tipos-documento-obrigatorio.destroy', $tipo) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Tem certeza que deseja excluir este tipo de documento?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Excluir">
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
        
        @if($tipos->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $tipos->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
