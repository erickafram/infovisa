@extends('layouts.admin')

@section('title', 'Receituários')
@section('page-title', 'Receituários')

@section('content')
<div class="max-w-8xl mx-auto">
    
    {{-- Botão de Ação --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Novo Receituário
                <svg class="w-4 h-4 ml-1" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute left-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                 style="display: none;">
                
                <div class="py-2">
                    <a href="{{ route('admin.receituarios.create', ['tipo' => 'medico']) }}" 
                       class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition-colors group">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-900">Médico, Dentista ou Veterinário</div>
                            <div class="text-xs text-gray-500">Cadastro de profissionais de saúde</div>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.receituarios.create', ['tipo' => 'instituicao']) }}" 
                       class="flex items-center gap-3 px-4 py-3 hover:bg-green-50 transition-colors group">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-900">Hospital, Clínica e Similares</div>
                            <div class="text-xs text-gray-500">Cadastro de instituições de saúde</div>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.receituarios.create', ['tipo' => 'secretaria']) }}" 
                       class="flex items-center gap-3 px-4 py-3 hover:bg-purple-50 transition-colors group">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-900">Secretaria de Saúde e VISA</div>
                            <div class="text-xs text-gray-500">Cadastro de órgãos públicos</div>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.receituarios.create', ['tipo' => 'talidomida']) }}" 
                       class="flex items-center gap-3 px-4 py-3 hover:bg-red-50 transition-colors group">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center group-hover:bg-red-200 transition-colors">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-900">Prescritor de Talidomida</div>
                            <div class="text-xs text-gray-500">Cadastro especial para talidomida</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.receituarios.index') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="busca" value="{{ request('busca') }}" 
                       placeholder="Nome, CPF, CNPJ ou Razão Social..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="tipo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="medico" {{ request('tipo') == 'medico' ? 'selected' : '' }}>Médico/Dentista/Vet</option>
                    <option value="instituicao" {{ request('tipo') == 'instituicao' ? 'selected' : '' }}>Instituição</option>
                    <option value="secretaria" {{ request('tipo') == 'secretaria' ? 'selected' : '' }}>Secretaria</option>
                    <option value="talidomida" {{ request('tipo') == 'talidomida' ? 'selected' : '' }}>Talidomida</option>
                </select>
            </div>
            
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>
            
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Filtrar
            </button>
            
            @if(request()->hasAny(['busca', 'tipo', 'status']))
                <a href="{{ route('admin.receituarios.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Limpar
                </a>
            @endif
        </form>
    </div>

    {{-- Lista --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($receituarios->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome/Razão Social</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF/CNPJ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Município</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastro</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($receituarios as $receituario)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $receituario->tipo == 'medico' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $receituario->tipo == 'instituicao' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $receituario->tipo == 'secretaria' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $receituario->tipo == 'talidomida' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $receituario->tipo_nome }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $receituario->identificador }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $receituario->cpf_formatado ?? $receituario->cnpj_formatado }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $receituario->municipio->nome ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $receituario->status == 'ativo' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $receituario->status == 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $receituario->status == 'inativo' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst($receituario->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $receituario->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.receituarios.show', $receituario->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">Ver</a>
                                <a href="{{ route('admin.receituarios.edit', $receituario->id) }}" 
                                   class="text-green-600 hover:text-green-900">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $receituarios->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum receituário encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">Comece criando um novo receituário.</p>
            </div>
        @endif
    </div>
</div>
@endsection
