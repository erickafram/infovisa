@extends('layouts.admin')

@php($editing = $slide->exists)

@section('title', $editing ? 'Editar slide' : 'Novo slide')
@section('page-title', $editing ? 'Editar slide' : 'Novo slide')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <p class="text-sm font-medium text-blue-600">{{ $apresentacao->evento->titulo }}</p>
        <h2 class="text-2xl font-bold text-gray-900">{{ $editing ? 'Atualizar slide' : 'Criar slide' }}</h2>
        <p class="mt-1 text-sm text-gray-600">Apresentação: {{ $apresentacao->titulo }}</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin.treinamentos.slides.update', $slide) : route('admin.treinamentos.slides.store', $apresentacao) }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="grid gap-6 p-6">
            <div>
                <label for="titulo" class="mb-2 block text-sm font-semibold text-gray-700">Título do slide</label>
                <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $slide->titulo) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label for="ordem" class="mb-2 block text-sm font-semibold text-gray-700">Ordem</label>
                <input type="number" min="1" id="ordem" name="ordem" value="{{ old('ordem', $slide->ordem ?: 1) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label for="conteudo" class="mb-2 block text-sm font-semibold text-gray-700">Conteúdo</label>
                <textarea id="conteudo" name="conteudo" rows="10" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm leading-6 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('conteudo', $slide->conteudo) }}</textarea>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.treinamentos.apresentacoes.show', $apresentacao) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">{{ $editing ? 'Salvar slide' : 'Criar slide' }}</button>
        </div>
    </form>
</div>
@endsection