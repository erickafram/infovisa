@extends('layouts.admin')

@section('title', 'Adicionar Processo')
@section('page-title', 'Adicionar Processo')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.processos.index', $estabelecimento->id) }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Adicionar Processo</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $estabelecimento->nome_fantasia }}</p>
            </div>
        </div>
    </div>

    {{-- Mensagens de Erro --}}
    @if($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-800 mb-2">Há erros no formulário:</p>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Formulário --}}
    <form method="POST" action="{{ route('admin.estabelecimentos.processos.store', $estabelecimento->id) }}" class="space-y-6">
        @csrf

        {{-- Seleção de Tipo de Processo --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Selecione o Tipo de Processo</h2>
            <p class="text-sm text-gray-600 mb-6">Escolha o tipo de processo que deseja abrir para este estabelecimento.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($tipos as $key => $nome)
                    <label class="relative flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" 
                               name="tipo" 
                               value="{{ $key }}" 
                               class="sr-only peer"
                               required
                               {{ old('tipo') === $key ? 'checked' : '' }}>
                        <div class="flex items-center gap-3 w-full">
                            <div class="flex-shrink-0">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-blue-600 peer-checked:bg-blue-600 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 12 12">
                                        <path d="M10.28 2.28L3.989 8.575 1.695 6.28A1 1 0 00.28 7.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 2.28z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    @if($key === 'licenciamento')
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    @elseif($key === 'analise_rotulagem')
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    @elseif($key === 'projeto_arquitetonico')
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    @elseif($key === 'administrativo')
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    @endif
                                    <span class="font-medium text-gray-900">{{ $nome }}</span>
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('tipo')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Observações --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Observações (Opcional)</h2>
            <textarea name="observacoes" 
                      rows="4" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                      placeholder="Adicione observações sobre este processo...">{{ old('observacoes') }}</textarea>
            <p class="mt-2 text-sm text-gray-500">Máximo de 1000 caracteres</p>
            @error('observacoes')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Informações Adicionais --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-900">Informações Importantes</p>
                    <ul class="mt-2 text-sm text-blue-700 space-y-1 list-disc list-inside">
                        <li>O número do processo será gerado automaticamente no formato: {{ date('Y') }}/XXXXX</li>
                        <li>O processo será criado com status "Aberto"</li>
                        <li>Você poderá alterar o status posteriormente</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Botões de ação --}}
        <div class="flex items-center justify-between gap-4 pt-4">
            <a href="{{ route('admin.estabelecimentos.processos.index', $estabelecimento->id) }}"
               class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Criar Processo
            </button>
        </div>
    </form>
</div>
@endsection
