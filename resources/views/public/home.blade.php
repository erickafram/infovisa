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
    <section class="bg-gray-50 py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <!-- Title -->
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    INFO<span class="text-blue-600">VISA</span>
                </h1>
                
                <!-- Subtitle -->
                <p class="text-base text-gray-600 mb-8 max-w-2xl mx-auto">
                    Sistema de Vigilância Sanitária Municipal - Consulte processos e verifique documentos
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="#consultar" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Consultar Processo
                    </a>
                    <a href="#verificar" class="inline-flex items-center justify-center px-6 py-3 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Verificar Documento
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços Section -->
    <section id="servicos" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Nossos Serviços</h2>
                <p class="text-sm text-gray-600">
                    Soluções digitais para simplificar os processos de vigilância sanitária
                </p>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1: Processos -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Processos Simplificados</h3>
                    <p class="text-sm text-gray-600">
                        Abertura e acompanhamento de processos sanitários de forma digital e transparente.
                    </p>
                </div>

                <!-- Card 2: Consulta -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Consulta Rápida</h3>
                    <p class="text-sm text-gray-600">
                        Verifique o andamento do seu processo ou alvará sanitário em tempo real.
                    </p>
                </div>

                <!-- Card 3: Documentos -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Documentos Autênticos</h3>
                    <p class="text-sm text-gray-600">
                        Verifique a autenticidade dos documentos usando o código verificador único.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Consultar Processo Section -->
    <section id="consultar" class="py-16 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-2 flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Consultar Andamento do Processo
                    </h2>
                </div>

                <!-- Form -->
                <form action="{{ route('consultar.processo') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="cnpj" class="block text-sm font-medium text-gray-700 mb-2">
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
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <p class="mt-1.5 text-xs text-gray-500">Formato: 00.000.000/0000-00</p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Consultar Processo
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Verificar Documento Section -->
    <section id="verificar" class="py-16 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-2 flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Verificar Autenticidade de Documento
                    </h2>
                    <p class="text-sm text-gray-600">Digite o código verificador presente no documento para confirmar sua autenticidade.</p>
                </div>

                <!-- Form -->
                <form action="{{ route('verificar.documento') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="codigo_verificador" class="block text-sm font-medium text-gray-700 mb-2">
                            Código Verificador
                        </label>
                        <input 
                            type="text" 
                            id="codigo_verificador"
                            name="codigo_verificador"
                            x-model="codigoVerificador"
                            placeholder="Digite o código presente no documento"
                            required
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                        >
                        <p class="mt-1.5 text-xs text-gray-500">O código verificador está impresso no documento, geralmente no rodapé.</p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Verificar Autenticidade
                    </button>
                </form>

                <!-- Info Box -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Como funciona a verificação?</h3>
                        <p class="text-xs text-gray-600">
                            Todos os documentos emitidos pela Vigilância Sanitária possuem um código único de verificação que garante sua autenticidade.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
