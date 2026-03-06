@extends('layouts.admin')

@php($editing = $pergunta->exists)
@php($opcoesValues = old('opcoes', $opcoes instanceof \Illuminate\Support\Collection ? $opcoes->all() : (array) $opcoes))

@section('title', $editing ? 'Editar pergunta' : 'Nova pergunta')
@section('page-title', $editing ? 'Editar pergunta' : 'Nova pergunta')

@section('content')
<div class="mx-auto max-w-4xl space-y-6" x-data="{
    opcoes: @js(array_values($opcoesValues)),
    addOption() {
        this.opcoes.push('');
    },
    removeOption(index) {
        if (this.opcoes.length > 2) {
            this.opcoes.splice(index, 1);
        }
    }
}">
    <div>
        <p class="text-sm font-medium text-blue-600">{{ $slide->apresentacao->evento->titulo }}</p>
        <h2 class="text-2xl font-bold text-gray-900">{{ $editing ? 'Atualizar pergunta' : 'Criar pergunta interativa' }}</h2>
        <p class="mt-1 text-sm text-gray-600">Slide: {{ $slide->titulo }}</p>
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

    <form method="POST" action="{{ $editing ? route('admin.treinamentos.perguntas.update', $pergunta) : route('admin.treinamentos.perguntas.store', $slide) }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="space-y-6 p-6">
            <div>
                <label for="enunciado" class="mb-2 block text-sm font-semibold text-gray-700">Enunciado</label>
                <textarea id="enunciado" name="enunciado" rows="4" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm leading-6 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>{{ old('enunciado', $pergunta->enunciado) }}</textarea>
            </div>

            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                <label class="flex items-start gap-3">
                    <input type="hidden" name="ativa" value="0">
                    <input type="checkbox" name="ativa" value="1" class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" @checked(old('ativa', $pergunta->ativa ?? true))>
                    <span>
                        <span class="block text-sm font-semibold text-blue-900">Pergunta ativa</span>
                        <span class="block text-sm text-blue-700">Perguntas ativas podem receber respostas pelo link público e pelo QR Code.</span>
                    </span>
                </label>
            </div>

            <div>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Alternativas</h3>
                        <p class="text-xs text-gray-500">Informe pelo menos duas opções de resposta.</p>
                    </div>
                    <button type="button" @click="addOption()" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Adicionar alternativa</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(opcao, index) in opcoes" :key="index">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-100 text-sm font-bold text-gray-700" x-text="index + 1"></div>
                            <input type="text" :name="`opcoes[${index}]`" x-model="opcoes[index]" class="flex-1 rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <button type="button" @click="removeOption(index)" class="inline-flex items-center rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50" :disabled="opcoes.length <= 2">Remover</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.treinamentos.apresentacoes.show', $slide->apresentacao) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">{{ $editing ? 'Salvar pergunta' : 'Criar pergunta' }}</button>
        </div>
    </form>
</div>
@endsection