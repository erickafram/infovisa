@extends('layouts.admin')

@section('title', 'Responsáveis')
@section('page-title', 'Responsáveis')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header com busca e filtros --}}
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('admin.responsaveis.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Busca --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Buscar por Nome ou CPF
                    </label>
                    <input type="text" 
                           name="busca" 
                           value="{{ request('busca') }}"
                           placeholder="Digite o nome ou CPF..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- Filtro por Tipo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tipo
                    </label>
                    <select name="tipo" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="legal" {{ request('tipo') === 'legal' ? 'selected' : '' }}>Legal</option>
                        <option value="tecnico" {{ request('tipo') === 'tecnico' ? 'selected' : '' }}>Técnico</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
                <a href="{{ route('admin.responsaveis.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    {{-- Estatísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 space-y-0 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-full">
            <div class="flex items-center justify-between h-full">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Total de Responsáveis</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $responsaveis->total() }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-full">
            <div class="flex items-center justify-between h-full">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Responsáveis Legais</p>
                    <p class="text-2xl font-bold text-blue-900 mt-1">{{ \App\Models\Responsavel::where('tipo', 'legal')->count() }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-full">
            <div class="flex items-center justify-between h-full">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Responsáveis Técnicos</p>
                    <p class="text-2xl font-bold text-green-900 mt-1">{{ \App\Models\Responsavel::where('tipo', 'tecnico')->count() }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela de Responsáveis --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estabelecimentos</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($responsaveis as $responsavel)
                    <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location='{{ route('admin.responsaveis.show', $responsavel->id) }}'">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $responsavel->nome }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600">{{ $responsavel->cpf_formatado }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600">{{ $responsavel->telefone_formatado }}</div>
                            <div class="text-xs text-gray-500">{{ $responsavel->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $responsavel->total_estabelecimentos }} estabelecimento(s)</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.responsaveis.show', $responsavel->id) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                Ver detalhes
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum responsável encontrado</h3>
                            <p class="mt-1 text-sm text-gray-500">Tente ajustar os filtros de busca.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($responsaveis->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $responsaveis->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
