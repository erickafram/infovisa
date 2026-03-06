@extends('layouts.public')

@section('title', 'Resposta registrada')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-blue-50 px-4 py-16">
    <div class="mx-auto max-w-2xl rounded-3xl bg-white p-10 text-center shadow-xl ring-1 ring-gray-100">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="mt-6 text-3xl font-bold text-gray-900">Resposta registrada com sucesso</h1>
        <p class="mt-3 text-base leading-7 text-gray-600">Sua participação na pergunta <span class="font-semibold text-gray-900">{{ $pergunta->enunciado }}</span> foi recebida.</p>
        <p class="mt-2 text-sm text-gray-500">Evento: {{ $evento->titulo }}</p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Voltar ao início</a>
            @if($evento->inscricoes_ativas)
                <a href="{{ route('treinamentos.public.inscricao', $evento->link_inscricao_token) }}" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">Abrir inscrição do evento</a>
            @endif
        </div>
    </div>
</div>
@endsection