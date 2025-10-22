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
    <section class="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 text-white py-16 md:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">
                    INFOVISA
                </h1>
                <p class="text-lg md:text-xl text-blue-100 mb-8 leading-relaxed">
                    Sistema de Vigilância Sanitária Municipal - Consulte processos e verifique documentos
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#consultar" class="bg-white text-blue-700 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition shadow-lg hover:shadow-xl inline-flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Consultar Processo</span>
                    </a>
                    <a href="#verificar" class="bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-400 transition shadow-lg hover:shadow-xl inline-flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Verificar Documento</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços Section -->
    <section id="servicos" class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Nossos Serviços</h2>
                <p class="text-lg text-gray-600">
                    Soluções digitais para simplificar os processos da vigilância sanitária
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Processos -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 hover:shadow-lg transition duration-300 border border-blue-200">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Processos Simplificados</h3>
                    <p class="text-sm text-gray-700 leading-relaxed">
                        Abertura e acompanhamento de processos sanitários de forma digital e transparente.
                    </p>
                </div>

                <!-- Card 2: Consulta -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 hover:shadow-lg transition duration-300 border border-green-200">
                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Consulta Rápida</h3>
                    <p class="text-sm text-gray-700 leading-relaxed">
                        Verifique o andamento do seu processo ou alvará sanitário em tempo real.
                    </p>
                </div>

                <!-- Card 3: Documentos -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 hover:shadow-lg transition duration-300 border border-purple-200">
                    <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Documentos Autênticos</h3>
                    <p class="text-sm text-gray-700 leading-relaxed">
                        Verifique a autenticidade dos documentos usando o código verificador único.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Consultar Processo Section -->
    <section id="consultar" class="py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                    <div class="text-center mb-6">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Consultar Andamento do Processo</h2>
                        <p class="text-sm text-gray-600">Digite o CNPJ da empresa para consultar</p>
                    </div>

                    <form action="{{ route('consultar.processo') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="cnpj" class="block text-sm font-semibold text-gray-700 mb-2">
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
                                class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            >
                            <p class="mt-1.5 text-xs text-gray-500">Formato: 00.000.000/0000-00</p>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    <section id="verificar" class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-xl shadow-lg p-6 md:p-8 border-2 border-purple-200">
                    <div class="text-center mb-6">
                        <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Verificar Autenticidade de Documento</h2>
                        <p class="text-sm text-gray-600">Digite o código verificador presente no documento para confirmar sua autenticidade.</p>
                    </div>

                    <form action="{{ route('verificar.documento') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="codigo_verificador" class="block text-sm font-semibold text-gray-700 mb-2">
                                Código Verificador
                            </label>
                            <input 
                                type="text" 
                                id="codigo_verificador"
                                name="codigo_verificador"
                                x-model="codigoVerificador"
                                placeholder="Digite o código presente no documento"
                                required
                                class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition uppercase"
                            >
                            <div class="mt-2.5 bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start space-x-2">
                                <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-xs text-blue-800">
                                    O código verificador está impresso no documento, geralmente no rodapé.
                                </p>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Verificar Autenticidade</span>
                        </button>
                    </form>

                    <!-- Como Funciona -->
                    <div class="mt-6 pt-6 border-t border-purple-200">
                        <h3 class="text-base font-bold text-gray-900 mb-2 flex items-center">
                            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Como funciona a verificação?
                        </h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Todos os documentos emitidos pela Vigilância Sanitária possuem um código único de verificação que garante sua autenticidade.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-700 text-white py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl md:text-3xl font-bold mb-3">Precisa de ajuda?</h2>
            <p class="text-base text-blue-100 mb-6">
                Nossa equipe está pronta para atender você. Entre em contato conosco.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="mailto:contato@infovisa.gov.br" class="bg-white text-blue-700 px-6 py-2.5 rounded-lg font-semibold hover:bg-blue-50 transition inline-flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Enviar E-mail</span>
                </a>
                <a href="tel:0000000000" class="bg-blue-500 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-blue-400 transition inline-flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span>Ligar Agora</span>
                </a>
            </div>
        </div>
    </section>

</div>
@endsection

