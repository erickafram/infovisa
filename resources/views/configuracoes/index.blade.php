@extends('layouts.admin')

@section('title', 'Configurações')
@section('page-title', 'Configurações do Sistema')

@php
    $user = auth('interno')->user();
    $isAdmin = $user->isAdmin();
    $isGestorEstadual = $user->nivel_acesso->value === 'gestor_estadual';
@endphp

@section('content')
<div class="max-w-8xl mx-auto">
    <div class="mb-6">
        <p class="text-gray-600">Gerencie as configurações e parâmetros do sistema</p>
    </div>

    {{-- Grid de Cards de Configurações --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        @if($isAdmin)
        {{-- Tipos de Processo - Apenas Admin --}}
        <a href="{{ route('admin.configuracoes.tipos-processo.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Tipos de Processo</h3>
                    <p class="text-xs text-gray-600">Configure os tipos de processos disponíveis no sistema</p>
                </div>
            </div>
        </a>
        @endif

        @if($isAdmin || $isGestorEstadual)
        {{-- Tipos de Documento - Admin e Gestor Estadual --}}
        <a href="{{ route('admin.configuracoes.tipos-documento.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Tipos de Documento</h3>
                    <p class="text-xs text-gray-600">Configure os tipos de documentos disponíveis</p>
                </div>
            </div>
        </a>

        {{-- Modelos de Documentos - Admin e Gestor Estadual --}}
        <a href="{{ route('admin.configuracoes.modelos-documento.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Modelos de Documentos</h3>
                    <p class="text-xs text-gray-600">Crie e gerencie modelos de documentos digitais</p>
                </div>
            </div>
        </a>

        {{-- Avisos do Sistema - Admin e Gestor Estadual --}}
        <a href="{{ route('admin.configuracoes.avisos.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Avisos do Sistema</h3>
                    <p class="text-xs text-gray-600">Crie avisos para usuários internos por nível de acesso</p>
                </div>
            </div>
        </a>
        @endif

        @if($isAdmin || $isGestorEstadual)
        {{-- Tipos de Ações - Admin e Gestor Estadual --}}
        <a href="{{ route('admin.configuracoes.tipo-acoes.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Tipos de Ações</h3>
                    <p class="text-xs text-gray-600">Configure ações realizadas pela vigilância sanitária</p>
                </div>
            </div>
        </a>
        @endif

        @if($isAdmin)
        {{-- Tipos de Setor - Apenas Admin --}}
        <a href="{{ route('admin.configuracoes.tipo-setores.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Tipos de Setor</h3>
                    <p class="text-xs text-gray-600">Configure setores e vincule a níveis de acesso</p>
                </div>
            </div>
        </a>
        @endif

        @if($isAdmin || $isGestorEstadual)
        {{-- Pactuação - Admin e Gestor Estadual --}}
        <a href="{{ route('admin.configuracoes.pactuacao.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Pactuação</h3>
                    <p class="text-xs text-gray-600">Configure competências municipais e estaduais por atividade (CNAE)</p>
                </div>
            </div>
        </a>

        {{-- Equipamentos de Radiação Ionizante - Admin e Gestor Estadual --}}
        <a href="{{ route('admin.configuracoes.equipamentos-radiacao.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Equipamentos de Imagem</h3>
                    <p class="text-xs text-gray-600">Configure atividades que exigem cadastro de equipamentos de imagem</p>
                </div>
            </div>
        </a>
        @endif

        @if($isAdmin)
        {{-- Municípios - Apenas Admin --}}
        <a href="{{ route('admin.configuracoes.municipios.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Municípios</h3>
                    <p class="text-xs text-gray-600">Gerencie o cadastro de municípios do Tocantins</p>
                </div>
            </div>
        </a>

        {{-- Documentos POPS/IA - Apenas Admin --}}
        <a href="{{ route('admin.configuracoes.documentos-pops.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Documentos IA</h3>
                    <p class="text-xs text-gray-600">Gerencie documentos POPs, categorias e integração com Assistente IA</p>
                </div>
            </div>
        </a>

        {{-- Configurações do Sistema - Apenas Admin --}}
        <a href="{{ route('admin.configuracoes.sistema.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Configurações do Sistema</h3>
                    <p class="text-xs text-gray-600">Configure logomarca estadual e parâmetros globais do sistema</p>
                </div>
            </div>
        </a>

        {{-- Documentos de Ajuda - Apenas Admin --}}
        <a href="{{ route('admin.configuracoes.documentos-ajuda.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Documentos de Ajuda</h3>
                    <p class="text-xs text-gray-600">Gerencie documentos de ajuda exibidos nos processos (manuais, guias, etc.)</p>
                </div>
            </div>
        </a>

        {{-- Chat Broadcast (Suporte InfoVISA) - Apenas Admin --}}
        <a href="{{ route('admin.configuracoes.chat-broadcast.index') }}" 
           class="block bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Mensagens do Suporte</h3>
                    <p class="text-xs text-gray-600">Envie mensagens de broadcast para usuários por nível de acesso</p>
                </div>
            </div>
        </a>
        @endif

    </div>

    @if($isAdmin || $isGestorEstadual)
    {{-- Seção: Lista de Documentos por Atividade - Admin e Gestor Estadual --}}
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Documentação por Atividade
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Lista de Documentos por Atividade --}}
            <a href="{{ route('admin.configuracoes.listas-documento.index') }}" 
               class="block bg-gradient-to-br from-blue-50 to-white rounded-xl shadow-sm border-2 border-blue-300 p-4 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900 mb-1">Lista de Documentos por Atividade</h3>
                        <p class="text-xs text-gray-600">Configure documentos exigidos por tipo de processo, atividade e escopo (Estado/Município)</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
