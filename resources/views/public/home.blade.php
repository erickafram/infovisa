@extends('layouts.public')

@section('title', 'InfoVISA - Sistema de Vigilância Sanitária')

@section('content')
<div x-data="{ codigoVerificador: '' }">
    <!-- Hero Section -->
    <section class="relative bg-white overflow-hidden">
        <!-- Background Decor -->
        <div class="absolute inset-0 z-0">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-100 rounded-full blur-3xl opacity-60 mix-blend-multiply animate-blob"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-100 rounded-full blur-3xl opacity-60 mix-blend-multiply animate-blob" style="animation-delay: 2s"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-pink-100 rounded-full blur-3xl opacity-60 mix-blend-multiply animate-blob" style="animation-delay: 4s"></div>
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 md:pt-32 md:pb-32 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/80 backdrop-blur border border-blue-100/50 text-blue-700 rounded-full text-sm font-medium mb-8 shadow-sm hover:shadow-md transition-shadow">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                Sistema de Vigilância Sanitária Digital
            </div>

            <!-- Main Heading -->
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-6 tracking-tight leading-tight">
                Vigilância Sanitária <br class="hidden md:block" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600">Digital e Integrada</span>
            </h1>

            <!-- Subheading -->
            <p class="text-base md:text-lg text-gray-600 mb-8 max-w-3xl mx-auto leading-relaxed">
                Sistema oficial de Vigilância Sanitária do Estado do Tocantins. Protocole processos de licenciamento, 
                análise de projetos, rotulagem e outros serviços sanitários. Acompanhe cada etapa com total transparência.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="{{ route('fila.processos') }}" class="group relative inline-flex items-center justify-center px-6 py-3 bg-gray-900 text-white text-sm font-bold rounded-2xl hover:bg-gray-800 transition-all shadow-xl hover:shadow-2xl overflow-hidden">
                    <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-shimmer"></div>
                    <svg class="w-4 h-4 mr-2 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Consultar Processo
                </a>
                <a href="#verificar" class="group inline-flex items-center justify-center px-6 py-3 bg-white text-gray-700 text-sm font-bold rounded-2xl border border-gray-200 hover:border-blue-200 hover:bg-blue-50/50 transition-all shadow-sm hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2 text-blue-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Verificar Autenticidade
                </a>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="py-24 bg-gray-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="h-14 w-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Múltiplos Processos</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Licenciamento sanitário, análise de projetos arquitetônicos, rotulagem, receituários e outros serviços de vigilância sanitária.</p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="h-14 w-14 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Documentos Digitais</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Alvarás, licenças e documentos assinados digitalmente com código de verificação e QR-CODE para autenticação.</p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="h-14 w-14 bg-green-50 rounded-2xl flex items-center justify-center text-green-600 mb-6">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Rastreabilidade Total</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Transparência completa em todas as etapas, com histórico de inspeções, pareceres técnicos e comunicações oficiais.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Verification Section -->
    <section id="verificar" class="py-24 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="bg-gradient-to-br from-blue-900 to-indigo-900 rounded-[2.5rem] p-8 md:p-16 text-white shadow-2xl relative overflow-hidden">
                <!-- Decorative Circles -->
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl"></div>

                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold mb-4">Verificação de Autenticidade</h2>
                        <p class="text-blue-100 text-base mb-6 leading-relaxed">
                            Verifique a autenticidade de documentos emitidos pela Vigilância Sanitária do Tocantins. A verificação é pública, gratuita e instantânea através do código ou QR-CODE.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-blue-50 text-sm">
                                <span class="bg-blue-500/20 p-1.5 rounded-full">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                Validação em tempo real junto à base de dados oficial
                            </li>
                            <li class="flex items-center gap-3 text-blue-50 text-sm">
                                <span class="bg-blue-500/20 p-1.5 rounded-full">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                Prevenção contra fraudes e adulterações de documentos
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white/10 backdrop-blur-lg p-6 rounded-3xl border border-white/20">
                        <form action="{{ route('verificar.documento') }}" method="POST" class="space-y-5">
                            @csrf
                            <div>
                                <label for="codigo_verificador" class="block text-xs font-semibold text-blue-50 mb-2">
                                    Código Verificador
                                </label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="codigo_verificador"
                                        name="codigo_verificador"
                                        x-model="codigoVerificador"
                                        placeholder="EX: ABC-123-XYZ"
                                        required
                                        class="w-full px-4 py-3 text-sm font-mono bg-white/10 border-2 border-white/20 rounded-xl focus:ring-4 focus:ring-blue-500/30 focus:border-white text-white placeholder-blue-200/50 uppercase transition-all"
                                    >
                                    <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-blue-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-blue-200 flex items-center gap-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    O código encontra-se no rodapé do documento oficial
                                </p>
                            </div>

                            <button type="submit" class="w-full bg-white text-blue-900 py-3 px-6 rounded-xl text-sm font-bold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2 group">
                                Verificar Agora
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
