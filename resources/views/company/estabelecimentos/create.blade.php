@extends('layouts.company')

@section('title', 'Novo Estabelecimento')
@section('page-title', 'Novo Estabelecimento')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div>
        <a href="{{ route('company.estabelecimentos.index') }}" class="text-sm text-blue-600 hover:text-blue-700 flex items-center mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para lista
        </a>
        <p class="text-sm text-gray-500">Escolha o tipo de pessoa para cadastrar</p>
    </div>

    {{-- Opções de Cadastro --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Pessoa Jurídica --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-900">Pessoa Jurídica</h2>
                    <p class="text-sm text-gray-500">Empresas com CNPJ</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Cadastre empresas, comércios, indústrias e outros estabelecimentos com CNPJ. 
                Os dados serão consultados automaticamente na Receita Federal.
            </p>
            <a href="{{ route('company.estabelecimentos.create.juridica') }}" 
               class="inline-flex items-center justify-center w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                Cadastrar Pessoa Jurídica
            </a>
        </div>

        {{-- Pessoa Física --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-900">Pessoa Física</h2>
                    <p class="text-sm text-gray-500">Profissionais autônomos com CPF</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Cadastre profissionais autônomos.
                Informe os dados manualmente.
            </p>
            <a href="{{ route('company.estabelecimentos.create.fisica') }}" 
               class="inline-flex items-center justify-center w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Cadastrar Pessoa Física
            </a>
        </div>
    </div>
</div>
@endsection
