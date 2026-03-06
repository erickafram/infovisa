@extends('layouts.public')

@section('title', 'Pergunta interativa')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 px-4 py-10">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-3xl bg-white p-8 shadow-xl ring-1 ring-gray-100">
            <p class="text-sm font-medium text-blue-600">{{ $evento->titulo }}</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900">Participe da pergunta ao vivo</h1>
            <p class="mt-3 text-base leading-7 text-gray-600">{{ $pergunta->enunciado }}</p>
        </div>

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('treinamentos.public.pergunta.responder', $pergunta->token) }}" class="rounded-3xl bg-white p-8 shadow-xl ring-1 ring-gray-100">
            @csrf

            @if(empty($participante['nome']) || empty($participante['email']))
                <div class="mb-6 grid gap-5 rounded-2xl bg-gray-50 p-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Nome</label>
                        <input type="text" name="nome" value="{{ old('nome', $participante['nome'] ?? '') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $participante['email'] ?? '') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-gray-700">Telefone</label>
                        <input type="text" name="telefone" value="{{ old('telefone', $participante['telefone'] ?? '') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            @else
                <div class="mb-6 rounded-2xl bg-blue-50 p-4 text-sm text-blue-800">
                    Respondendo como <span class="font-semibold">{{ $participante['nome'] }}</span> ({{ $participante['email'] }}).
                </div>
            @endif

            <div class="space-y-3">
                @foreach($pergunta->opcoes as $opcao)
                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-gray-200 px-4 py-4 transition hover:border-blue-400 hover:bg-blue-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="opcao_id" value="{{ $opcao->id }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500" @checked((string) old('opcao_id') === (string) $opcao->id) required>
                        <span class="text-sm font-medium text-gray-800">{{ $opcao->texto }}</span>
                    </label>
                @endforeach
            </div>

            <button type="submit" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                Enviar resposta
            </button>
        </form>
    </div>
</div>
@endsection