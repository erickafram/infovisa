@extends('layouts.public')

@section('title', $evento->titulo . ' - Inscrição')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 px-4 py-10">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="rounded-3xl bg-white p-5 shadow-xl ring-1 ring-gray-100 md:p-6">
            <div class="text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14zm0 0v6"/>
                    </svg>
                </div>
                <h1 class="mt-3 text-2xl font-bold text-gray-900 md:text-[1.7rem]">{{ $evento->titulo }}</h1>
                <p class="mt-1 text-sm text-gray-500">Preencha sua inscrição para participar do treinamento.</p>
            </div>

            <div class="mt-4 grid gap-3 rounded-2xl bg-gray-50 p-4 md:grid-cols-3">
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

        <form method="POST" action="{{ route('treinamentos.public.inscricao.salvar', $evento->link_inscricao_token) }}" class="rounded-3xl bg-white p-5 shadow-xl ring-1 ring-gray-100 md:p-6">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Nome completo</label>
                    <input type="text" name="nome" value="{{ old('nome') }}" class="js-uppercase w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Telefone Celular</label>
                    <input type="text" name="telefone" value="{{ old('telefone') }}" maxlength="15" placeholder="(63) 99999-9999" class="js-telefone w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Instituição</label>
                    <input type="text" name="instituicao" value="{{ old('instituicao') }}" class="js-uppercase w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Cargo</label>
                    <input type="text" name="cargo" value="{{ old('cargo') }}" class="js-uppercase w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Cidade</label>
                    <select name="cidade" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm uppercase focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">SELECIONE A CIDADE</option>
                        @foreach($municipios as $municipio)
                            <option value="{{ $municipio }}" @selected(old('cidade') === $municipio)>{{ mb_strtoupper($municipio) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                Confirmar inscrição
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-uppercase').forEach(function (input) {
            input.addEventListener('input', function () {
                this.value = this.value.toUpperCase();
            });
        });

        document.querySelectorAll('.js-telefone').forEach(function (input) {
            function applyMask() {
                var digits = input.value.replace(/\D/g, '').slice(0, 11);

                if (digits.length <= 2) {
                    input.value = digits ? '(' + digits : '';
                    return;
                }

                if (digits.length <= 6) {
                    input.value = '(' + digits.slice(0, 2) + ') ' + digits.slice(2);
                    return;
                }

                if (digits.length <= 10) {
                    input.value = '(' + digits.slice(0, 2) + ') ' + digits.slice(2, 6) + '-' + digits.slice(6);
                    return;
                }

                input.value = '(' + digits.slice(0, 2) + ') ' + digits.slice(2, 7) + '-' + digits.slice(7);
            }

            input.addEventListener('input', applyMask);
            applyMask();
        });
    });
</script>
@endsection