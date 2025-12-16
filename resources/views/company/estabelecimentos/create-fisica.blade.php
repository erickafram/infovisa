@extends('layouts.company')

@section('title', 'Cadastrar Pessoa Física')
@section('page-title', 'Cadastrar Pessoa Física')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div>
        <a href="{{ route('company.estabelecimentos.create') }}" class="text-sm text-blue-600 hover:text-blue-700 flex items-center mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <p class="text-sm text-gray-500">Preencha os dados do profissional autônomo</p>
    </div>

    {{-- Formulário --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-center py-12">
            <div class="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Funcionalidade em Desenvolvimento</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                O cadastro de estabelecimentos por pessoa física está sendo implementado. 
                Em breve você poderá cadastrar seu estabelecimento diretamente por aqui.
            </p>
            <p class="text-sm text-gray-600 mb-4">
                Por enquanto, entre em contato com a Vigilância Sanitária para realizar o cadastro.
            </p>
            <a href="{{ route('company.estabelecimentos.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Voltar para Meus Estabelecimentos
            </a>
        </div>
    </div>
</div>
@endsection
