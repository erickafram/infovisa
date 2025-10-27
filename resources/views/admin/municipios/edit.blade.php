@extends('layouts.admin')

@section('title', 'Editar Município')
@section('page-title', 'Editar Município')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.configuracoes.municipios.update', $municipio->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nome do Município <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nome" 
                           value="{{ old('nome', $municipio->nome) }}" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase @error('nome') border-red-500 @enderror">
                    @error('nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Código IBGE <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="codigo_ibge" 
                           value="{{ old('codigo_ibge', $municipio->codigo_ibge) }}" 
                           required
                           maxlength="7"
                           placeholder="1721000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('codigo_ibge') border-red-500 @enderror">
                    @error('codigo_ibge')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">7 dígitos do código IBGE</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        UF <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="uf" 
                           value="{{ old('uf', $municipio->uf) }}" 
                           required
                           maxlength="2"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase @error('uf') border-red-500 @enderror">
                    @error('uf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="ativo" 
                               {{ old('ativo', $municipio->ativo) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Município ativo</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.configuracoes.municipios.index') }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Atualizar Município
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
