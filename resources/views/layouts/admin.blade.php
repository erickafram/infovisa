<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - InfoVISA Admin</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-expanded { width: 240px; }
        .sidebar-collapsed { width: 72px; }
        @media (max-width: 1023px) {
            .sidebar-expanded, .sidebar-collapsed { width: 240px; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50" x-data="{ 
    sidebarOpen: false, 
    sidebarExpanded: localStorage.getItem('sidebarExpanded') === 'true',
    userMenuOpen: false,
    isMobile: window.innerWidth < 1024,
    toggleSidebar() {
        this.sidebarExpanded = !this.sidebarExpanded;
        localStorage.setItem('sidebarExpanded', this.sidebarExpanded);
    },
    showLabels() {
        return this.isMobile || this.sidebarExpanded;
    }
}" x-init="window.addEventListener('resize', () => { isMobile = window.innerWidth < 1024 })">
    
    {{-- Overlay Mobile --}}
    <div x-show="sidebarOpen" 
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside class="fixed lg:relative inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-gray-200 shadow-lg transition-all duration-300 ease-in-out"
               :class="{
                   'translate-x-0': sidebarOpen,
                   '-translate-x-full lg:translate-x-0': !sidebarOpen,
                   'sidebar-expanded': sidebarExpanded,
                   'sidebar-collapsed': !sidebarExpanded
               }">
        
            {{-- Logo Header --}}
            <div class="flex items-center h-14 bg-gradient-to-r from-cyan-600 to-blue-600 border-b border-cyan-700 px-4"
                 :class="showLabels() ? 'justify-between' : 'lg:justify-center'">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span x-show="showLabels()" class="text-white font-bold text-lg">InfoVISA</span>
                </div>
                {{-- Toggle Desktop --}}
                <button @click="toggleSidebar()" 
                        x-show="sidebarExpanded"
                        class="hidden lg:flex items-center justify-center w-6 h-6 rounded text-white/70 hover:text-white hover:bg-white/10 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
                {{-- Close Mobile --}}
                <button @click="sidebarOpen = false" 
                        class="lg:hidden flex items-center justify-center w-6 h-6 rounded text-white/70 hover:text-white hover:bg-white/10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Expand Button (quando collapsed) --}}
            <button @click="toggleSidebar()" 
                    x-show="!sidebarExpanded"
                    class="hidden lg:flex items-center justify-center h-10 text-gray-400 hover:text-cyan-600 hover:bg-gray-50 transition border-b border-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3" :class="!showLabels() ? 'lg:px-2' : ''">
                <div class="space-y-1">
                    @php
                        $menuItems = [
                            ['route' => 'admin.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard', 'check' => 'admin.dashboard'],
                            ['route' => 'admin.estabelecimentos.index', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'label' => 'Estabelecimentos', 'check' => 'admin.estabelecimentos.*'],
                            ['route' => 'admin.processos.index-geral', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Processos', 'check' => 'admin.processos.*'],
                            ['route' => 'admin.documentos.index', 'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'label' => 'Documentos', 'check' => 'admin.documentos.*'],
                            ['route' => 'admin.responsaveis.index', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'label' => 'Responsáveis', 'check' => 'admin.responsaveis.*'],
                        ];
                    @endphp

                    @foreach($menuItems as $item)
                    <a href="{{ route($item['route']) }}" 
                       title="{{ $item['label'] }}"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs($item['check']) ? 'bg-cyan-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium truncate">{{ $item['label'] }}</span>
                    </a>
                    @endforeach

                    {{-- Receituários - Admin e Estadual --}}
                    @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual())
                    <a href="{{ route('admin.receituarios.index') }}" 
                       title="Receituários"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.receituarios.*') ? 'bg-cyan-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium">Receituários</span>
                    </a>
                    @endif

                    {{-- Ordens de Serviço --}}
                    @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual() || auth('interno')->user()->isMunicipal())
                    <a href="{{ route('admin.ordens-servico.index') }}" 
                       title="Ordens de Serviço"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.ordens-servico.*') ? 'bg-cyan-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium">Ordens de Serviço</span>
                    </a>

                    {{-- Relatórios --}}
                    <a href="{{ route('admin.relatorios.index') }}" 
                       title="Relatórios"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.relatorios.*') ? 'bg-cyan-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium">Relatórios</span>
                    </a>
                    @endif

                    {{-- Separador Administração --}}
                    @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isGestor())
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <p x-show="showLabels()" class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administração</p>
                    </div>

                    {{-- Usuários Internos --}}
                    <a href="{{ route('admin.usuarios-internos.index') }}" 
                       title="Usuários Internos"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.usuarios-internos.*') ? 'bg-cyan-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium">Usuários Internos</span>
                    </a>
                    @endif

                    @if(auth('interno')->user()->isAdmin())
                    {{-- Usuários Externos --}}
                    <a href="{{ route('admin.usuarios-externos.index') }}" 
                       title="Usuários Externos"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.usuarios-externos.*') ? 'bg-cyan-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium">Usuários Externos</span>
                    </a>

                    {{-- Diário Oficial --}}
                    <a href="{{ route('admin.diario-oficial.index') }}" 
                       title="Diário Oficial"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 relative {{ request()->routeIs('admin.diario-oficial.*') ? 'bg-cyan-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium">Diário Oficial</span>
                        @php
                            $alertasNaoLidos = 0;
                            try {
                                $alertasNaoLidos = \App\Models\DiarioBuscaAlerta::where('usuario_interno_id', auth('interno')->id())->where('lido', false)->count();
                            } catch (\Exception $e) {}
                        @endphp
                        @if($alertasNaoLidos > 0)
                        <span class="absolute top-1 right-1 flex h-5 w-5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-5 w-5 bg-red-500 text-[10px] text-white justify-center items-center font-bold">{{ $alertasNaoLidos > 9 ? '9+' : $alertasNaoLidos }}</span>
                        </span>
                        @endif
                    </a>
                    @endif

                    {{-- Configurações --}}
                    @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->nivel_acesso->value === 'gestor_estadual')
                    <a href="{{ route('admin.configuracoes.index') }}" 
                       title="Configurações"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.configuracoes.*') ? 'bg-cyan-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium">Configurações</span>
                    </a>
                    @endif
                </div>
            </nav>

            {{-- User Info & Logout --}}
            <div class="border-t border-gray-200 p-3">
                <div x-show="showLabels()" class="mb-3 px-2">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ auth('interno')->user()->nome }}</p>
                    <p class="text-xs text-gray-500">{{ auth('interno')->user()->nivel_acesso->label() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            title="Sair do Sistema"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition-all duration-200"
                            :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium">Sair</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            {{-- Top Header --}}
            <header class="sticky top-0 z-30 flex items-center h-14 bg-white border-b border-gray-200 shadow-sm px-4 lg:px-6">
                {{-- Mobile Menu Button --}}
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="lg:hidden p-2 -ml-2 mr-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Page Title --}}
                <h1 class="text-lg font-semibold text-gray-900 truncate">@yield('page-title', 'Dashboard')</h1>

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Right Actions --}}
                <div class="flex items-center gap-2">
                    {{-- Notificações --}}
                    @include('components.notificacoes')
                    
                    {{-- User Menu --}}
                    <div class="relative" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-sm">
                                <span class="text-white font-semibold text-sm">{{ substr(auth('interno')->user()->nome, 0, 1) }}</span>
                            </div>
                            <span class="hidden md:block text-sm font-medium text-gray-700 max-w-[120px] truncate">
                                {{ auth('interno')->user()->nome }}
                            </span>
                            <svg class="hidden md:block w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="userMenuOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">
                            
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ auth('interno')->user()->nome }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ auth('interno')->user()->nivel_acesso->label() }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ auth('interno')->user()->email }}</p>
                            </div>
                            
                            <a href="{{ route('admin.perfil.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Meu Perfil
                            </a>
                            <a href="{{ route('admin.assinatura.configurar-senha') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                Assinatura Digital
                            </a>
                            
                            <div class="border-t border-gray-100 my-1"></div>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Sair do Sistema
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- PDF.js Library --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
    </script>

    {{-- PDF Viewer --}}
    <script src="{{ asset('js/pdf-viewer-anotacoes.js') }}"></script>

    @stack('scripts')
    @stack('modals')

    {{-- Assistentes IA --}}
    @include('components.assistente-ia-chat')
    @include('components.assistente-documento-chat')

</body>
</html>
