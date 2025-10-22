@extends('layouts.admin')

@section('title', 'Novo Tipo de Processo')
@section('page-title', 'Novo Tipo de Processo')

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.configuracoes.tipos-processo.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Novo Tipo de Processo</h1>
                <p class="text-sm text-gray-600 mt-1">Adicione um novo tipo de processo ao sistema</p>
            </div>
        </div>
    </div>

    {{-- Erros de Validação --}}
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800 mb-2">Erro ao criar tipo de processo:</h3>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('admin.configuracoes.tipos-processo.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Card Principal --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Informações Básicas</h3>

            <div class="space-y-6">
                {{-- Nome --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Tipo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nome" 
                           value="{{ old('nome') }}"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror"
                           placeholder="Ex: Licenciamento Sanitário">
                    @error('nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Código --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Código <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="codigo" 
                           value="{{ old('codigo') }}"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('codigo') border-red-500 @enderror"
                           placeholder="Ex: licenciamento">
                    <p class="mt-1 text-xs text-gray-500">Código único para identificação interna (sem espaços, apenas letras minúsculas e underscores)</p>
                    @error('codigo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição
                    </label>
                    <textarea name="descricao" 
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none @error('descricao') border-red-500 @enderror"
                              placeholder="Descreva brevemente este tipo de processo...">{{ old('descricao') }}</textarea>
                    @error('descricao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ordem --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Ordem de Exibição
                    </label>
                    <input type="number" 
                           name="ordem" 
                           value="{{ old('ordem', 0) }}"
                           min="0"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('ordem') border-red-500 @enderror"
                           placeholder="0">
                    <p class="mt-1 text-xs text-gray-500">Ordem em que aparecerá nas listagens (menor número aparece primeiro)</p>
                    @error('ordem')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Card Configurações --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Configurações</h3>

            <div class="space-y-4">
                {{-- Processo Anual --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               name="anual" 
                               id="anual"
                               {{ old('anual') ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </div>
                    <div class="ml-3">
                        <label for="anual" class="font-medium text-gray-700">Processo Anual</label>
                        <p class="text-sm text-gray-500">Apenas um processo deste tipo pode ser aberto por estabelecimento por ano</p>
                    </div>
                </div>

                {{-- Usuário Externo Pode Abrir --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               name="usuario_externo_pode_abrir" 
                               id="usuario_externo_pode_abrir"
                               {{ old('usuario_externo_pode_abrir') ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </div>
                    <div class="ml-3">
                        <label for="usuario_externo_pode_abrir" class="font-medium text-gray-700">Usuário Externo Pode Abrir</label>
                        <p class="text-sm text-gray-500">Permite que usuários externos (empresas) possam abrir este tipo de processo</p>
                    </div>
                </div>

                {{-- Ativo --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               name="ativo" 
                               id="ativo"
                               {{ old('ativo', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </div>
                    <div class="ml-3">
                        <label for="ativo" class="font-medium text-gray-700">Ativo</label>
                        <p class="text-sm text-gray-500">Tipo de processo disponível para uso no sistema</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.configuracoes.tipos-processo.index') }}"
               class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                Criar Tipo de Processo
            </button>
        </div>
    </form>
</div>
@endsection
