@extends('layouts.admin')

@section('title', 'Usuários Internos')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Usuários Internos</h1>
            <p class="text-sm text-gray-600 mt-1">Gerencie os usuários internos do sistema</p>
        </div>
        <a href="{{ route('admin.usuarios-internos.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Usuário
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
        <form method="GET" action="{{ route('admin.usuarios-internos.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Nome --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input type="text" name="nome" value="{{ request('nome') }}" 
                           placeholder="Buscar por nome"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- CPF --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                    <input type="text" name="cpf" value="{{ request('cpf') }}" 
                           placeholder="000.000.000-00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ request('email') }}" 
                           placeholder="email@exemplo.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- Município --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Município</label>
                    <input type="text" name="municipio" value="{{ request('municipio') }}" 
                           placeholder="Nome do município"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- Nível de Acesso --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nível de Acesso</label>
                    <select name="nivel_acesso" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Todos</option>
                        @foreach(\App\Enums\NivelAcesso::cases() as $nivel)
                            <option value="{{ $nivel->value }}" {{ request('nivel_acesso') == $nivel->value ? 'selected' : '' }}>
                                {{ $nivel->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="ativo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Todos</option>
                        <option value="1" {{ request('ativo') === '1' ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ request('ativo') === '0' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filtrar
                </button>
                <a href="{{ route('admin.usuarios-internos.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium">
                    Limpar Filtros
                </a>
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <a href="{{ route('admin.usuarios-internos.index', array_merge(request()->all(), ['sort' => 'nome', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-blue-600">
                                Nome
                                @if(request('sort') === 'nome')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">CPF</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Município</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nível de Acesso</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($usuarios as $usuario)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 font-semibold text-sm">{{ strtoupper(substr($usuario->nome, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $usuario->nome }}</div>
                                        @if($usuario->cargo)
                                            <div class="text-xs text-gray-500">{{ $usuario->cargo }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $usuario->cpf_formatado }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $usuario->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($usuario->municipioRelacionado)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $usuario->municipioRelacionado->nome }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $usuario->nivel_acesso->color() }}">
                                    {{ $usuario->nivel_acesso->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($usuario->ativo)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Ativo</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Inativo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.usuarios-internos.show', $usuario) }}" 
                                       class="p-1 text-blue-600 hover:bg-blue-50 rounded transition" title="Visualizar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.usuarios-internos.edit', $usuario) }}" 
                                       class="p-1 text-green-600 hover:bg-green-50 rounded transition" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.usuarios-internos.destroy', $usuario) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-red-600 hover:bg-red-50 rounded transition" title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                Nenhum usuário encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($usuarios->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    {{-- Resumo --}}
    <div class="mt-4 text-sm text-gray-600">
        Mostrando {{ $usuarios->firstItem() ?? 0 }} a {{ $usuarios->lastItem() ?? 0 }} de {{ $usuarios->total() }} usuários
    </div>
</div>
@endsection
