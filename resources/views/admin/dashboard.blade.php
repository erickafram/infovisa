@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-4">
    {{-- Mensagem de boas-vindas --}}
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-xl font-bold text-gray-900">
            Olá, {{ auth('interno')->user()->nome }}! 👋
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Bem-vindo ao painel administrativo do InfoVISA. 
            Nível de acesso: <span class="font-semibold text-blue-600">{{ auth('interno')->user()->nivel_acesso->label() }}</span>
        </p>
    </div>

    {{-- Cards de Estatísticas --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Usuários Externos --}}
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 truncate">Usuários Externos</dt>
                            <dd class="flex items-baseline mt-1">
                                <div class="text-xl font-bold text-gray-900">{{ $stats['usuarios_externos'] }}</div>
                                <div class="ml-2 flex items-baseline">
                                    <span class="text-xs text-gray-500">({{ $stats['usuarios_externos_ativos'] }} ativos)</span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-2">
                <div class="text-xs">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-700">Ver todos →</a>
                </div>
            </div>
        </div>

        {{-- Usuários Internos --}}
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 truncate">Usuários Internos</dt>
                            <dd class="flex items-baseline mt-1">
                                <div class="text-xl font-bold text-gray-900">{{ $stats['usuarios_internos'] }}</div>
                                <div class="ml-2 flex items-baseline">
                                    <span class="text-xs text-gray-500">({{ $stats['usuarios_internos_ativos'] }} ativos)</span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-2">
                <div class="text-xs">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-700">Ver todos →</a>
                </div>
            </div>
        </div>

        {{-- Pendências --}}
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-xs font-medium text-gray-500 truncate">E-mails Pendentes</dt>
                            <dd class="flex items-baseline mt-1">
                                <div class="text-xl font-bold text-gray-900">{{ $stats['usuarios_externos_pendentes'] }}</div>
                                <div class="ml-2 flex items-baseline">
                                    <span class="text-xs text-gray-500">verificações</span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-2">
                <div class="text-xs">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-700">Ver pendências →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabelas de Dados Recentes --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Usuários Externos Recentes --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4">
                <h3 class="text-base leading-6 font-semibold text-gray-900 mb-3">
                    Usuários Externos Recentes
                </h3>
                <div class="flow-root">
                    <ul class="-my-3 divide-y divide-gray-200">
                        @forelse($usuarios_externos_recentes as $usuario)
                        <li class="py-3">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-medium text-xs">
                                            {{ substr($usuario->nome, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $usuario->nome }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $usuario->email }}
                                    </p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $usuario->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="py-3 text-center text-gray-500 text-xs">
                            Nenhum usuário externo cadastrado ainda.
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Usuários Internos Recentes --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-4">
                <h3 class="text-base leading-6 font-semibold text-gray-900 mb-3">
                    Usuários Internos Recentes
                </h3>
                <div class="flow-root">
                    <ul class="-my-3 divide-y divide-gray-200">
                        @forelse($usuarios_internos_recentes as $usuario)
                        <li class="py-3">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-purple-600 font-medium text-xs">
                                            {{ substr($usuario->nome, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $usuario->nome }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $usuario->nivel_acesso->label() }}
                                    </p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $usuario->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="py-3 text-center text-gray-500 text-xs">
                            Nenhum usuário interno cadastrado ainda.
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

