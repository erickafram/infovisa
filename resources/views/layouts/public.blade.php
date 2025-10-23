<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Vigilância Sanitária Municipal - Consulte processos e verifique documentos">
    <title>@yield('title', 'InfoVISA - Sistema de Vigilância Sanitária Municipal')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between w-full">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center space-x-2 flex-shrink-0">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">InfoVISA</span>
                </a>

                <!-- Desktop Menu Centralizado -->
                <div class="hidden md:flex items-center space-x-6 flex-1 justify-center">
                    <a href="#servicos" class="text-gray-700 hover:text-blue-600 transition font-medium">Serviços</a>
                    <a href="#consultar" class="text-gray-700 hover:text-blue-600 transition font-medium">Consultar</a>
                    <a href="#verificar" class="text-gray-700 hover:text-blue-600 transition font-medium">Verificar</a>
                </div>

                <!-- Botão Área Restrita e Mobile Menu -->
                <div class="flex items-center space-x-4 flex-shrink-0">
                    <!-- Botão Área Restrita -->
                    @auth('interno')
                        <a href="{{ route('admin.dashboard') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                            Dashboard
                        </a>
                    @elseauth('externo')
                        <a href="{{ route('empresa.dashboard') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                            Área Restrita
                        </a>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" 
                 x-cloak
                 @click.away="mobileMenuOpen = false"
                 class="md:hidden mt-4 pb-4 space-y-2">
                <a href="#servicos" class="block text-gray-700 hover:text-blue-600 transition font-medium py-2">Serviços</a>
                <a href="#consultar" class="block text-gray-700 hover:text-blue-600 transition font-medium py-2">Consultar</a>
                <a href="#verificar" class="block text-gray-700 hover:text-blue-600 transition font-medium py-2">Verificar</a>
                <a href="{{ route('login') }}" class="block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium text-center">
                    Área Restrita
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12 mt-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo e Descrição -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">InfoVISA</span>
                    </div>
                    <p class="text-gray-400 max-w-md">
                        Sistema de Vigilância Sanitária Municipal
                    </p>
                </div>

                <!-- Links Rápidos -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Links Rápidos</h3>
                    <ul class="space-y-2">
                        <li><a href="#servicos" class="hover:text-white transition">Serviços</a></li>
                        <li><a href="#consultar" class="hover:text-white transition">Consulta</a></li>
                        <li><a href="#verificar" class="hover:text-white transition">Verificação</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Área Restrita</a></li>
                    </ul>
                </div>

                <!-- Contato -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Contato</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>contato@infovisa.gov.br</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>(00) 0000-0000</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} InfoVISA. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Alpine.js Data -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('app', () => ({
                mobileMenuOpen: false
            }))
        })
    </script>
</body>
</html>

