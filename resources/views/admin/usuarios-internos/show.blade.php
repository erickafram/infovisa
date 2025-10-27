@extends('layouts.admin')

@section('title', 'Detalhes do Usuário Interno')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detalhes do Usuário Interno</h1>
            <p class="text-sm text-gray-600 mt-1">Informações completas do usuário</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.usuarios-internos.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar
            </a>
            <a href="{{ route('admin.usuarios-internos.edit', $usuarioInterno) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Informações Principais --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Dados Pessoais --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Dados Pessoais
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nome Completo</label>
                        <p class="text-gray-900 font-medium">{{ $usuarioInterno->nome }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">CPF</label>
                        <p class="text-gray-900 font-medium">{{ $usuarioInterno->cpf_formatado }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <p class="text-gray-900 font-medium">{{ $usuarioInterno->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Telefone</label>
                        <p class="text-gray-900 font-medium">{{ $usuarioInterno->telefone_formatado ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Dados Profissionais --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Dados Profissionais
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Matrícula</label>
                        <p class="text-gray-900 font-medium">{{ $usuarioInterno->matricula ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Cargo</label>
                        <p class="text-gray-900 font-medium">{{ $usuarioInterno->cargo ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Município</label>
                        <p class="text-gray-900 font-medium">
                            @if($usuarioInterno->municipioRelacionado)
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $usuarioInterno->municipioRelacionado->nome }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nível de Acesso</label>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $usuarioInterno->nivel_acesso->color() }}">
                            {{ $usuarioInterno->nivel_acesso->label() }}
                        </span>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Descrição do Nível</label>
                        <p class="text-sm text-gray-700">{{ $usuarioInterno->nivel_acesso->descricao() }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        @if($usuarioInterno->ativo)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Ativo</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Inativo</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Informações do Sistema --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações do Sistema</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Cadastrado em</label>
                        <p class="text-sm text-gray-900">{{ $usuarioInterno->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Última atualização</label>
                        <p class="text-sm text-gray-900">{{ $usuarioInterno->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email verificado</label>
                        @if($usuarioInterno->email_verified_at)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                Verificado em {{ $usuarioInterno->email_verified_at->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                Não verificado
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ações</h2>
                <div class="space-y-2">
                    <a href="{{ route('admin.usuarios-internos.edit', $usuarioInterno) }}" 
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Usuário
                    </a>
                    <form action="{{ route('admin.usuarios-internos.destroy', $usuarioInterno) }}" method="POST" 
                          onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Excluir Usuário
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
