@extends('layouts.admin')

@php($editing = $evento->exists)

@section('title', $editing ? 'Editar treinamento' : 'Novo treinamento')
@section('page-title', $editing ? 'Editar treinamento' : 'Novo treinamento')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $editing ? 'Atualizar evento' : 'Criar novo evento' }}</h2>
            <p class="mt-1 text-sm text-gray-600">Defina informações gerais, período e status das inscrições.</p>
        </div>
        <a href="{{ $editing ? route('admin.treinamentos.show', $evento) : route('admin.treinamentos.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            Voltar
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Corrija os campos abaixo:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.treinamentos.update', $evento) : route('admin.treinamentos.store') }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="grid gap-6 p-6 lg:grid-cols-2">
            <div class="lg:col-span-2">
                <label for="titulo" class="mb-2 block text-sm font-semibold text-gray-700">Título do evento</label>
                <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $evento->titulo) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="lg:col-span-2">
                <label for="descricao" class="mb-2 block text-sm font-semibold text-gray-700">Descrição</label>
                <textarea id="descricao" name="descricao" rows="5" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('descricao', $evento->descricao) }}</textarea>
            </div>

            <div>
                <label for="local" class="mb-2 block text-sm font-semibold text-gray-700">Local</label>
                <input type="text" id="local" name="local" value="{{ old('local', $evento->local) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="status" class="mb-2 block text-sm font-semibold text-gray-700">Status do evento</label>
                <select id="status" name="status" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @foreach(['planejado' => 'Planejado', 'aberto' => 'Aberto', 'encerrado' => 'Encerrado', 'cancelado' => 'Cancelado'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $evento->status ?: 'planejado') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="data_inicio" class="mb-2 block text-sm font-semibold text-gray-700">Data e hora de início</label>
                <input type="datetime-local" id="data_inicio" name="data_inicio" value="{{ old('data_inicio', $evento->data_inicio?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="data_fim" class="mb-2 block text-sm font-semibold text-gray-700">Data e hora de término</label>
                <input type="datetime-local" id="data_fim" name="data_fim" value="{{ old('data_fim', $evento->data_fim?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="lg:col-span-2 rounded-2xl border border-blue-100 bg-blue-50 p-4">
                <label class="flex items-start gap-3">
                    <input type="hidden" name="inscricoes_ativas" value="0">
                    <input type="checkbox" name="inscricoes_ativas" value="1" class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" @checked(old('inscricoes_ativas', $evento->inscricoes_ativas ?? true))>
                    <span>
                        <span class="block text-sm font-semibold text-blue-900">Permitir inscrições públicas</span>
                        <span class="block text-sm text-blue-700">Quando ativo, o link público de inscrição ficará disponível para participantes.</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
            <a href="{{ $editing ? route('admin.treinamentos.show', $evento) : route('admin.treinamentos.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                {{ $editing ? 'Salvar alterações' : 'Criar evento' }}
            </button>
        </div>
    </form>
</div>
@endsection