<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - InfoVISA Admin</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Estilos adicionais --}}
    @stack('styles')
</head>
<body class="bg-gray-50" x-data="{ userMenuOpen: false }">
    
    <div class="flex h-screen">
        {{-- Sidebar --}}
        <aside class="w-18 bg-gradient-to-b from-white to-gray-50 border-r border-gray-200 flex flex-col shadow-xl flex-shrink-0 overflow-y-auto"
               style="height: 100vh; position: sticky; top: 0;">
        
        
        {{-- Logo --}}
        <div class="flex items-center justify-center h-14 bg-gradient-to-br from-blue-600 to-blue-700 border-b border-blue-800 shadow-md">
            <svg class="w-6 h-6 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>

        {{-- Navegação --}}
        <nav class="flex-1 px-2.5 py-4 space-y-1.5">
            <!-- 1. Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               title="Dashboard"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>

            <!-- 2. Estabelecimentos -->
            <a href="{{ route('admin.estabelecimentos.index') }}"
               title="Estabelecimentos"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.estabelecimentos.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </a>

            <!-- 3. Processos -->
            <a href="{{ route('admin.processos.index-geral') }}" 
               title="Processos"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.processos.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </a>

            <!-- 4. Documentos -->
            <a href="{{ route('admin.documentos.index') }}" 
               title="Documentos"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.documentos.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </a>

            <!-- 5. Responsáveis -->
            <a href="{{ route('admin.responsaveis.index') }}"
               title="Responsáveis"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.responsaveis.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </a>

            <!-- 5.5 Receituários - Apenas Admin e Estadual -->
            @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual())
            <a href="{{ route('admin.receituarios.index') }}"
               title="Receituários"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.receituarios.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </a>
            @endif

            @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual() || auth('interno')->user()->isMunicipal())
            <!-- 6. Ordens de Serviço -->
            <a href="{{ route('admin.ordens-servico.index') }}"
               title="Ordens de Serviço"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.ordens-servico.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </a>

            <!-- 7. Relatórios -->
            <a href="{{ route('admin.relatorios.index') }}"
               title="Relatórios e Análises"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.relatorios.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </a>
            @endif

            @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isGestor())
            <!-- 6. Usuários Internos -->
            <a href="{{ route('admin.usuarios-internos.index') }}" 
               title="Usuários Internos"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.usuarios-internos.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </a>
            @endif

            @if(auth('interno')->user()->isAdmin())
            <!-- 7. Usuários Externos -->
            <a href="{{ route('admin.usuarios-externos.index') }}" 
               title="Usuários Externos"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.usuarios-externos.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </a>

            <!-- 8. Diário Oficial -->
            <a href="{{ route('admin.diario-oficial.index') }}" 
               title="Diário Oficial"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.diario-oficial.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }} relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                
                @php
                    $alertasNaoLidos = 0;
                    if(auth('interno')->check()) {
                        // Verificar se a tabela existe para evitar erro em ambiente sem migration
                        try {
                            $alertasNaoLidos = \App\Models\DiarioBuscaAlerta::where('usuario_interno_id', auth('interno')->id())
                                ->where('lido', false)
                                ->count();
                        } catch (\Exception $e) {
                            // Ignora erro se tabela não existir
                        }
                    }
                @endphp
                
                @if($alertasNaoLidos > 0)
                    <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white justify-center items-center font-bold border border-white">
                            {{ $alertasNaoLidos > 9 ? '9+' : $alertasNaoLidos }}
                        </span>
                    </span>
                @endif
            </a>
            @endif

            <!-- 9. Configurações (Apenas Administradores) -->
            @if(auth('interno')->user()->isAdmin())
            <a href="{{ route('admin.configuracoes.index') }}" 
               title="Configurações"
               class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.configuracoes.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
            @endif

            {{-- Logout --}}
            <div class="pt-3 mt-2 border-t border-gray-200">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            title="Sair"
                            class="group flex items-center justify-center w-full p-2.5 rounded-lg text-red-600 hover:bg-red-50 hover:scale-105 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    {{-- Conteúdo principal --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Header --}}
        <div class="sticky top-0 z-10 flex h-14 bg-white border-b border-gray-200 shadow-sm">
            <div class="flex-1 flex justify-between px-4 sm:px-6">
                <div class="flex items-center">
                    <h2 class="text-base font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Notificações --}}
                    @include('components.notificacoes')
                    
                    {{-- Menu do usuário --}}
                    <div class="relative" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 px-2 py-1 hover:bg-gray-100 transition-colors">
                            <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center mr-2">
                                <span class="text-white font-medium text-sm">
                                    {{ substr(auth('interno')->user()->nome, 0, 1) }}
                                </span>
                            </div>
                            <span class="text-gray-700 text-sm font-medium hidden md:block mr-1">
                                {{ Str::limit(auth('interno')->user()->nome, 20) }}
                            </span>
                            <svg class="h-4 w-4 text-gray-400 hidden md:block" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-show="userMenuOpen"
                             x-cloak
                             class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <div class="font-medium text-sm text-gray-900">{{ auth('interno')->user()->nome }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ auth('interno')->user()->nivel_acesso->label() }}</div>
                                <div class="text-xs text-gray-400 mt-1">{{ auth('interno')->user()->email }}</div>
                            </div>
                            <a href="{{ route('admin.perfil.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Meu Perfil
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Configurações
                            </a>
                            <a href="{{ route('admin.assinatura.configurar-senha') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                Assinatura Digital
                            </a>
                            <div class="border-t border-gray-200"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Sair
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Conteúdo da página --}}
        <main class="flex-1 overflow-y-auto p-6 sm:p-8 lg:p-10">
            @yield('content')
        </main>
    </div>
    </div>

    {{-- PDF.js Library (carregado globalmente para visualizador de PDFs) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Configurar worker do PDF.js
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
    </script>

    {{-- Função global para o visualizador de PDF com anotações --}}
    <script src="{{ asset('js/pdf-viewer-anotacoes.js') }}"></script>

    {{-- Scripts adicionais --}}
    @stack('scripts')

    {{-- Modals --}}
    @stack('modals')

    {{-- Assistente de IA Principal (canto inferior direito) --}}
    @include('components.assistente-ia-chat')

    {{-- Assistente de Documento (canto inferior esquerdo) --}}
    @include('components.assistente-documento-chat')

</body>
</html>
