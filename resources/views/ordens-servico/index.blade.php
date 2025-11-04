@extends('layouts.admin')

@section('title', 'Ordens de Serviço')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Cabeçalho --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ordens de Serviço</h1>
            <p class="text-sm text-gray-600 mt-1">Gerencie as ordens de serviço do sistema</p>
        </div>
        <a href="{{ route('admin.ordens-servico.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Ordem de Serviço
        </a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('admin.ordens-servico.index') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="px-6 py-5">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                {{-- Estabelecimento --}}
                <div>
                    <label for="estabelecimento" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Estabelecimento</label>
                    <div class="relative">
                        <input type="text"
                               id="estabelecimento"
                               name="estabelecimento"
                               value="{{ $filters['estabelecimento'] ?? '' }}"
                               placeholder="Buscar por CNPJ/CPF, fantasia ou razão social"
                               class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M5 11a6 6 0 1112 0 6 6 0 01-12 0z"/>
                            </svg>
                        </span>
                    </div>
                </div>

                {{-- Data Início --}}
                <div>
                    <label for="data_inicio" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Data de Início</label>
                    <input type="date"
                           id="data_inicio"
                           name="data_inicio"
                           value="{{ $filters['data_inicio'] ?? '' }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- Data Fim --}}
                <div>
                    <label for="data_fim" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Data de Término</label>
                    <input type="date"
                           id="data_fim"
                           name="data_fim"
                           value="{{ $filters['data_fim'] ?? '' }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Status</label>
                    <select id="status"
                            name="status"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Todos</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-xl border-t border-gray-200">
            <a href="{{ route('admin.ordens-servico.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 transition">
                Limpar
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                Aplicar filtros
            </button>
        </div>
    </form>

    {{-- Mensagens de sucesso --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    {{-- Tabela de Ordens de Serviço --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($ordensServico->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Número
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estabelecimento
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Técnicos
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data Início
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data Fim
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($ordensServico as $os)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('admin.ordens-servico.show', $os) }}'">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-blue-600 hover:text-blue-800">{{ $os->numero }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($os->estabelecimento)
                                <div class="text-sm text-gray-900">{{ $os->estabelecimento->nome_fantasia }}</div>
                                <div class="text-xs text-gray-500">{{ $os->estabelecimento->razao_social }}</div>
                            @else
                                <div class="flex items-center gap-1 text-amber-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span class="text-xs font-medium">Sem estabelecimento</span>
                                </div>
                                <div class="text-xs text-gray-500">Vincular ao editar/finalizar</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($os->tecnicos()->count() > 0)
                                <div class="text-sm text-gray-900">
                                    @foreach($os->tecnicos() as $tecnico)
                                        <div class="text-xs">{{ $tecnico->nome }}</div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $os->data_inicio ? $os->data_inicio->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $os->data_fim ? $os->data_fim->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {!! $os->status_badge !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.ordens-servico.show', $os) }}" 
                                   class="text-green-600 hover:text-green-900"
                                   title="Visualizar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.ordens-servico.edit', $os) }}" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.ordens-servico.destroy', $os) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir esta ordem de serviço?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900"
                                            title="Excluir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $ordensServico->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma ordem de serviço</h3>
            <p class="mt-1 text-sm text-gray-500">Comece criando uma nova ordem de serviço.</p>
            <div class="mt-6">
                <a href="{{ route('admin.ordens-servico.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nova Ordem de Serviço
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
