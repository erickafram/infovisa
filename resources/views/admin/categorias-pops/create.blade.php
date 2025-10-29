@extends('layouts.admin')

@section('title', 'Nova Categoria')
@section('page-title', 'Nova Categoria')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">Criar Nova Categoria</h2>
            <p class="mt-1 text-sm text-gray-600">Categorias ajudam a organizar documentos POPs por temas</p>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.configuracoes.categorias-pops.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Nome --}}
            <div>
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">
                    Nome <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="nome" 
                       id="nome" 
                       value="{{ old('nome') }}"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Ex: GASES MEDICINAIS, BIOSSEGURANÇA">
            </div>

            {{-- Descrição --}}
            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">
                    Descrição
                </label>
                <textarea name="descricao" 
                          id="descricao" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Descreva brevemente o tema desta categoria...">{{ old('descricao') }}</textarea>
            </div>

            {{-- Cor --}}
            <div>
                <label for="cor" class="block text-sm font-medium text-gray-700 mb-1">
                    Cor <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-3">
                    <input type="color" 
                           name="cor" 
                           id="cor" 
                           value="{{ old('cor', '#6366f1') }}"
                           required
                           class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                    <span class="text-sm text-gray-600">Escolha uma cor para identificar visualmente esta categoria</span>
                </div>
            </div>

            {{-- Ordem --}}
            <div>
                <label for="ordem" class="block text-sm font-medium text-gray-700 mb-1">
                    Ordem de Exibição
                </label>
                <input type="number" 
                       name="ordem" 
                       id="ordem" 
                       value="{{ old('ordem', 0) }}"
                       min="0"
                       class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">Categorias são ordenadas por este número (menor primeiro)</p>
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.configuracoes.categorias-pops.index') }}" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Criar Categoria
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
