@extends('layouts.company')

@section('title', 'Meus Processos')
@section('page-title', 'Meus Processos')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div>
        <p class="text-sm text-gray-500">Acompanhe os processos dos seus estabelecimentos</p>
    </div>

    {{-- Estatísticas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
            <p class="text-[10px] font-medium text-gray-500 uppercase mb-1">Total</p>
            <p class="text-xl font-bold text-gray-800">{{ $estatisticas['total'] }}</p>
        </div>
        <a href="{{ route('company.processos.index', ['status' => 'em_andamento']) }}" 
           class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-[10px] font-medium text-gray-500 uppercase mb-1">Em Andamento</p>
            <p class="text-xl font-bold text-blue-600">{{ $estatisticas['em_andamento'] }}</p>
        </a>
        <a href="{{ route('company.processos.index', ['status' => 'concluido']) }}"
           class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-[10px] font-medium text-gray-500 uppercase mb-1">Concluídos</p>
            <p class="text-xl font-bold text-green-600">{{ $estatisticas['concluidos'] }}</p>
        </a>
        <a href="{{ route('company.processos.index', ['status' => 'arquivado']) }}"
           class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <p class="text-[10px] font-medium text-gray-500 uppercase mb-1">Arquivados</p>
            <p class="text-xl font-bold text-gray-600">{{ $estatisticas['arquivados'] }}</p>
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('company.processos.index') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por número do processo..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <select name="estabelecimento_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos os estabelecimentos</option>
                    @foreach($estabelecimentos as $est)
                    <option value="{{ $est->id }}" {{ request('estabelecimento_id') == $est->id ? 'selected' : '' }}>
                        {{ $est->nome_fantasia ?: $est->razao_social ?: $est->nome_completo }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'status', 'estabelecimento_id']))
                <a href="{{ route('company.processos.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                    Limpar
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Lista de Processos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($processos->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estabelecimento</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($processos as $processo)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $processo->numero_processo }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $processo->tipoProcesso->nome ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $processo->estabelecimento->nome_fantasia ?: $processo->estabelecimento->razao_social }}</div>
                            <div class="text-xs text-gray-500">{{ $processo->estabelecimento->documento_formatado }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($processo->status === 'concluido') bg-green-100 text-green-800
                                @elseif($processo->status === 'em_andamento') bg-blue-100 text-blue-800
                                @elseif($processo->status === 'arquivado') bg-gray-100 text-gray-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ str_replace('_', ' ', ucfirst($processo->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $processo->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <a href="{{ route('company.processos.show', $processo->id) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ver detalhes
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Paginação --}}
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $processos->links() }}
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum processo encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">Os processos serão exibidos aqui quando forem criados para seus estabelecimentos.</p>
        </div>
        @endif
    </div>
</div>
@endsection
