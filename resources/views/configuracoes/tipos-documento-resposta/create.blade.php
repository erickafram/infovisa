@extends('layouts.admin')
@section('title', 'Novo Tipo de Documento Resposta')
@section('page-title', 'Novo Tipo de Documento Resposta')

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.configuracoes.tipos-documento-resposta.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div>
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                <input type="text" name="nome" id="nome" value="{{ old('nome') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                       placeholder="Ex: ROI dos equipamentos">
                @error('nome') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                <textarea name="descricao" id="descricao" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                          placeholder="Descrição opcional">{{ old('descricao') }}</textarea>
            </div>
            <div>
                <label for="ordem" class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                <input type="number" name="ordem" id="ordem" value="{{ old('ordem', 0) }}" min="0"
                       class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Setor</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tipo_setor" value="todos" {{ old('tipo_setor', 'todos') === 'todos' ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Todos</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tipo_setor" value="publico" {{ old('tipo_setor') === 'publico' ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Público</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="tipo_setor" value="privado" {{ old('tipo_setor') === 'privado' ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Privado</span>
                    </label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Define se este documento se aplica a estabelecimentos públicos, privados ou ambos.</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="ativo" id="ativo" value="1" {{ old('ativo', true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="ativo" class="text-sm text-gray-700">Ativo</label>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-4">
            <a href="{{ route('admin.configuracoes.tipos-documento-resposta.index') }}" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">Salvar</button>
        </div>
    </form>
</div>
@endsection
