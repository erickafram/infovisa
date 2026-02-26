@extends('layouts.public')

@section('title', 'Obrigado – ' . $pesquisa->titulo)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-emerald-50 flex items-center justify-center px-4 py-16">
    <div class="max-w-lg mx-auto text-center">

        {{-- Ícone de sucesso --}}
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-100 shadow-lg mb-6">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-3">Obrigado pela avaliação!</h1>
        <p class="text-gray-500 text-base mb-2">
            Sua resposta à pesquisa <span class="font-semibold text-gray-700">«{{ $pesquisa->titulo }}»</span> foi registrada com sucesso.
        </p>
        <p class="text-sm text-gray-400 mb-8">
            Sua opinião é muito importante para melhorar continuamente os serviços da Vigilância Sanitária.
        </p>

        <a href="{{ route('home') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-xl shadow transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Voltar ao início
        </a>
    </div>
</div>
@endsection
