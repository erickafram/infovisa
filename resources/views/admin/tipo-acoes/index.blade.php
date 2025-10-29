@extends('layouts.admin')

@section('title', 'Tipos de Ações')
@section('page-title', 'Tipos de Ações')

@section('content')
<div class="space-y-6">
    {{-- Header com Botão Adicionar --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Tipos de Ações</h2>
            <p class="mt-1 text-sm text-gray-600">Gerencie as ações realizadas pela vigilância sanitária</p>
        </div>
        <a href="{{ route('admin.configuracoes.tipo-acoes.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Ação
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.configuracoes.tipo-acoes.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Busca --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" 
                       name="busca" 
                       value="{{ request('busca') }}"
                       placeholder="Descrição ou código..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Competência --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Competência</label>
                <select name="competencia" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todas</option>
                    <option value="estadual" {{ request('competencia') === 'estadual' ? 'selected' : '' }}>Estadual</option>
                    <option value="municipal" {{ request('competencia') === 'municipal' ? 'selected' : '' }}>Municipal</option>
                    <option value="ambos" {{ request('competencia') === 'ambos' ? 'selected' : '' }}>Ambos</option>
                </select>
            </div>

            {{-- Atividade SIA --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Atividade SIA</label>
                <select name="atividade_sia" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todas</option>
                    <option value="1" {{ request('atividade_sia') === '1' ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ request('atividade_sia') === '0' ? 'selected' : '' }}>Não</option>
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="ativo" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="1" {{ request('ativo') === '1' ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ request('ativo') === '0' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>

            {{-- Botões --}}
            <div class="md:col-span-4 flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('admin.configuracoes.tipo-acoes.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($tipoAcoes->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma ação cadastrada</h3>
                <p class="mt-1 text-sm text-gray-500">Comece criando uma nova ação.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.configuracoes.tipo-acoes.create') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nova Ação
                    </a>
                </div>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Competência</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">SIA</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tipoAcoes as $acao)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $acao->descricao }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 font-mono">{{ $acao->codigo_procedimento }}</div>
                        </td>
                        <td class="px-6 py-4">
                            {!! $acao->competencia_badge !!}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($acao->atividade_sia)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Sim
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-600">
                                    Não
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($acao->ativo)
                                <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Ativo</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">Inativo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.configuracoes.tipo-acoes.edit', $acao) }}" 
                                   class="text-blue-600 hover:text-blue-900 transition-colors">
                                    Editar
                                </a>
                                <form action="{{ route('admin.configuracoes.tipo-acoes.destroy', $acao) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta ação?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Paginação --}}
            @if($tipoAcoes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $tipoAcoes->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
