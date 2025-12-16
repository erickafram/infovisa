<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - InfoVISA Empresa</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>

            {{-- Navegação --}}
            <nav class="flex-1 px-2.5 py-4 space-y-1.5">
                {{-- Dashboard --}}
                <a href="{{ route('company.dashboard') }}" 
                   title="Dashboard"
                   class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('company.dashboard') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>

                {{-- Estabelecimentos --}}
                <a href="{{ route('company.estabelecimentos.index') }}"
                   title="Meus Estabelecimentos"
                   class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('company.estabelecimentos.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </a>

                {{-- Processos --}}
                <a href="{{ route('company.processos.index') }}" 
                   title="Meus Processos"
                   class="group flex items-center justify-center p-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('company.processos.*') ? 'bg-blue-600 text-white shadow-md scale-105' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:scale-105' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </a>

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
                        {{-- Menu do usuário --}}
                        <div class="relative" @click.away="userMenuOpen = false">
                            <button @click="userMenuOpen = !userMenuOpen"
                                    class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 px-2 py-1 hover:bg-gray-100 transition-colors">
                                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center mr-2">
                                    <span class="text-white font-medium text-sm">
                                        {{ substr(auth('externo')->user()->nome, 0, 1) }}
                                    </span>
                                </div>
                                <span class="text-gray-700 text-sm font-medium hidden md:block mr-1">
                                    {{ Str::limit(auth('externo')->user()->nome, 20) }}
                                </span>
                                <svg class="h-4 w-4 text-gray-400 hidden md:block" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>

                            <div x-show="userMenuOpen"
                                 x-cloak
                                 class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <div class="font-medium text-sm text-gray-900">{{ auth('externo')->user()->nome }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ auth('externo')->user()->email }}</div>
                                </div>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Meu Perfil
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
                {{-- Alertas --}}
                @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
