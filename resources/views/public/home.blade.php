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
    <section class="relative bg-gradient-to-br from-blue-50 via-white to-purple-50 py-20 md:py-28 overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-100 rounded-full opacity-20 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-100 rounded-full opacity-20 blur-3xl"></div>
        </div>
        
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-medium mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Sistema de Vigilância Sanitária
                </div>
                
                <!-- Title -->
                <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-6 tracking-tight">
                    INFO<span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">VISA</span>
                </h1>
                
                <!-- Description -->
                <p class="text-lg md:text-xl text-gray-600 mb-4 max-w-3xl mx-auto leading-relaxed">
                    Plataforma digital que <strong class="text-gray-900">moderniza e simplifica</strong> a gestão sanitária municipal e estadual
                </p>
                
                <p class="text-base text-gray-500 mb-10 max-w-2xl mx-auto">
                    Acompanhe processos em tempo real, consulte documentos oficiais e verifique autenticidade de alvarás e licenças — tudo de forma transparente e acessível
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('fila.processos') }}" class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-semibold rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Consultar Processos
                    </a>
                    <a href="#verificar" class="group inline-flex items-center justify-center px-6 py-3 bg-white text-gray-700 text-sm font-semibold rounded-xl border-2 border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5 mr-2 text-blue-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Verificar Documento
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- Verificar Documento Section -->
    <section id="verificar" class="py-16 bg-white">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl mb-4 shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Verificar Documento</h2>
                <p class="text-sm text-gray-600">Confirme a autenticidade de alvarás e licenças</p>
            </div>
            
            <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-2xl shadow-xl border border-gray-200 p-8">
                <form action="{{ route('verificar.documento') }}" method="POST" class="space-y-5">
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
                            placeholder="Ex: ABC123XYZ"
                            required
                            class="w-full px-4 py-3 text-base font-mono border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white uppercase transition-all"
                        >
                        <p class="mt-2 text-xs text-gray-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Código impresso no rodapé do documento
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3.5 px-4 rounded-xl text-base font-semibold hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Verificar Agora
                    </button>
                </form>
            </div>
        </div>
    </section>

</div>
@endsection
