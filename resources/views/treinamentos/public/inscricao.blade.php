@extends('layouts.public')

@section('title', $evento->titulo . ' - Inscrição')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 px-4 py-10">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-3xl bg-white p-8 shadow-xl ring-1 ring-gray-100">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14zm0 0v6"/>
                    </svg>
                </div>
                <h1 class="mt-4 text-3xl font-bold text-gray-900">{{ $evento->titulo }}</h1>
                <p class="mt-2 text-sm text-gray-500">Preencha sua inscrição para participar do treinamento.</p>
            </div>

            <div class="mt-6 grid gap-4 rounded-2xl bg-gray-50 p-5 md:grid-cols-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Local</p>
                    <p class="mt-1 text-sm font-medium text-gray-900">{{ $evento->local ?: 'Não informado' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Início</p>
                    <p class="mt-1 text-sm font-medium text-gray-900">{{ $evento->data_inicio?->format('d/m/Y H:i') ?: 'A definir' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fim</p>
                    <p class="mt-1 text-sm font-medium text-gray-900">{{ $evento->data_fim?->format('d/m/Y H:i') ?: 'A definir' }}</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('treinamentos.public.inscricao.salvar', $evento->link_inscricao_token) }}" class="rounded-3xl bg-white p-8 shadow-xl ring-1 ring-gray-100">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Nome completo</label>
                    <input type="text" name="nome" value="{{ old('nome') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Telefone</label>
                    <input type="text" name="telefone" value="{{ old('telefone') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Instituição</label>
                    <input type="text" name="instituicao" value="{{ old('instituicao') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Cargo</label>
                    <input type="text" name="cargo" value="{{ old('cargo') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Cidade</label>
                    <input type="text" name="cidade" value="{{ old('cidade') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Observações</label>
                    <textarea name="observacoes" rows="4" class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('observacoes') }}</textarea>
                </div>
            </div>

            <button type="submit" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                Confirmar inscrição
            </button>
        </form>
    </div>
</div>
@endsection