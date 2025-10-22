@extends('layouts.public')

@section('title', 'InfoVISA - Sistema de Vigilância Sanitária Municipal')

@section('content')
<div x-data="{ 
    cnpj: '', 
    codigoVerificador: '',
    formatCnpj() {
        let value = this.cnpj.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            this.cnpj = value;
        }
    }
}">

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white py-20 md:py-32 overflow-hidden">
        <!-- Decorative Background -->
        <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:20px_20px]"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <!-- Icon -->
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/10 backdrop-blur-sm rounded-2xl mb-6 shadow-2xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                
                <!-- Title -->
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-3 leading-tight">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-white to-blue-100">
                        InfoVISA
                    </span>
                </h1>
                
                <!-- Subtitle -->
                <p class="text-base md:text-lg text-blue-50 mb-2 max-w-3xl mx-auto leading-relaxed">
                    Sistema de Vigilância Sanitária Municipal
                </p>
                <p class="text-sm text-blue-100 mb-8 max-w-2xl mx-auto">
                    Consulte processos e verifique documentos com segurança e transparência
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="#consultar" class="group bg-white text-blue-700 px-6 py-3 rounded-xl text-sm font-semibold hover:bg-blue-50 transition-all duration-300 shadow-xl hover:shadow-2xl hover:-translate-y-1 inline-flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Consultar Processo</span>
                    </a>
                    <a href="#verificar" class="group bg-white/10 backdrop-blur-sm text-white border-2 border-white/30 px-6 py-3 rounded-xl text-sm font-semibold hover:bg-white/20 transition-all duration-300 shadow-xl hover:shadow-2xl hover:-translate-y-1 inline-flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Verificar Documento</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços Section -->
    <section id="servicos" class="py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-10">
                <span class="inline-block px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold mb-2">
                    Nossos Serviços
                </span>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Soluções Digitais</h2>
                <p class="text-base text-gray-600 max-w-2xl mx-auto">
                    Simplificando os processos da vigilância sanitária com tecnologia e transparência
                </p>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Processos -->
                <div class="group bg-white rounded-2xl p-6 hover:shadow-2xl transition-all duration-300 border border-gray-100 hover:border-blue-200 hover:-translate-y-2">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Processos Simplificados</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Abertura e acompanhamento de processos sanitários de forma digital e transparente.
                    </p>
                </div>

                <!-- Card 2: Consulta -->
                <div class="group bg-white rounded-2xl p-6 hover:shadow-2xl transition-all duration-300 border border-gray-100 hover:border-green-200 hover:-translate-y-2">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Consulta Rápida</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Verifique o andamento do seu processo ou alvará sanitário em tempo real.
                    </p>
                </div>

                <!-- Card 3: Documentos -->
                <div class="group bg-white rounded-2xl p-6 hover:shadow-2xl transition-all duration-300 border border-gray-100 hover:border-purple-200 hover:-translate-y-2">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Documentos Autênticos</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Verifique a autenticidade dos documentos usando o código verificador único.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Consultar Processo Section -->
    <section id="consultar" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="bg-gradient-to-br from-white to-blue-50 rounded-3xl shadow-2xl p-6 md:p-8 border border-blue-100">
                    <!-- Header -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">Consultar Processo</h2>
                        <p class="text-sm text-gray-600">Digite o CNPJ da empresa para consultar o andamento</p>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('consultar.processo') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="cnpj" class="block text-sm font-semibold text-gray-900 mb-2">
                                CNPJ da Empresa
                            </label>
                            <input 
                                type="text" 
                                id="cnpj"
                                name="cnpj"
                                x-model="cnpj"
                                @input="formatCnpj()"
                                placeholder="00.000.000/0000-00"
                                maxlength="18"
                                required
                                class="w-full px-4 py-3 text-base border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all shadow-sm"
                            >
                            <p class="mt-2 text-sm text-gray-500 flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Formato: 00.000.000/0000-00
                            </p>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-2.5 rounded-xl text-sm font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-xl hover:shadow-2xl hover:-translate-y-1 flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>Consultar Processo</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Verificar Documento Section -->
    <section id="verificar" class="py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="bg-gradient-to-br from-white to-purple-50 rounded-3xl shadow-2xl p-6 md:p-8 border border-purple-100">
                    <!-- Header -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">Verificar Documento</h2>
                        <p class="text-sm text-gray-600">Confirme a autenticidade usando o código verificador</p>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('verificar.documento') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="codigo_verificador" class="block text-sm font-semibold text-gray-900 mb-2">
                                Código Verificador
                            </label>
                            <input 
                                type="text" 
                                id="codigo_verificador"
                                name="codigo_verificador"
                                x-model="codigoVerificador"
                                placeholder="Digite o código presente no documento"
                                required
                                class="w-full px-4 py-3 text-base border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all uppercase shadow-sm"
                            >
                            <div class="mt-3 bg-purple-50 border border-purple-200 rounded-xl p-4 flex items-start space-x-3">
                                <svg class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-purple-900 leading-relaxed">
                                    O código verificador está impresso no documento, geralmente no rodapé.
                                </p>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-2.5 rounded-xl text-sm font-semibold hover:from-purple-700 hover:to-purple-800 transition-all duration-300 shadow-xl hover:shadow-2xl hover:-translate-y-1 flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Verificar Autenticidade</span>
                        </button>
                    </form>

                    <!-- Info Box -->
                    <div class="mt-6 pt-6 border-t border-purple-200">
                        <h3 class="text-base font-bold text-gray-900 mb-2 flex items-center">
                            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Como funciona a verificação?
                        </h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Todos os documentos emitidos pela Vigilância Sanitária possuem um código único de verificação que garante sua autenticidade e validade jurídica.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="relative bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-white py-12 overflow-hidden">
        <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:20px_20px]"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-white/10 backdrop-blur-sm rounded-2xl mb-4 shadow-xl">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h2 class="text-xl md:text-2xl font-bold mb-2">Precisa de ajuda?</h2>
            <p class="text-base text-blue-100 mb-5 max-w-2xl mx-auto">
                Nossa equipe está pronta para atender você. Entre em contato conosco.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="mailto:contato@infovisa.gov.br" class="group bg-white text-blue-700 px-6 py-3 rounded-xl text-sm font-semibold hover:bg-blue-50 transition-all duration-300 shadow-xl hover:shadow-2xl hover:-translate-y-1 inline-flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Enviar E-mail</span>
                </a>
                <a href="tel:0000000000" class="group bg-white/10 backdrop-blur-sm text-white border-2 border-white/30 px-6 py-3 rounded-xl text-sm font-semibold hover:bg-white/20 transition-all duration-300 shadow-xl hover:shadow-2xl hover:-translate-y-1 inline-flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span>Ligar Agora</span>
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
