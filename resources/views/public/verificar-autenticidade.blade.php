@extends('layouts.public')

@section('title', 'Verificar Autenticidade de Documento')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Verificar Autenticidade de Documento</h1>
            <p class="text-gray-600">
                Digite o código de autenticidade do documento para verificar sua validade
            </p>
        </div>

        @if(isset($erro))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-red-800">Erro na Verificação</h3>
                        <p class="mt-1 text-sm text-red-700">{{ $erro }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('verificar.autenticidade.verificar') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                    Código de Autenticidade
                </label>
                <input type="text" 
                       name="codigo" 
                       id="codigo"
                       value="{{ old('codigo', $codigo ?? '') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm @error('codigo') border-red-500 @enderror"
                       placeholder="Ex: 7b3a4c6534cdf3494aadbdee3b00bb9c"
                       required>
                @error('codigo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    O código de autenticidade está localizado no rodapé do documento PDF
                </p>
            </div>

            <button type="submit" 
                    class="w-full px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-colors">
                🔍 Verificar Autenticidade
            </button>
        </form>

        <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h3 class="text-sm font-semibold text-blue-900 mb-2">ℹ️ Como Verificar</h3>
            <ul class="text-xs text-blue-800 space-y-1 ml-4 list-disc">
                <li>Localize o código de autenticidade no rodapé do documento PDF</li>
                <li>Cole ou digite o código completo no campo acima</li>
                <li>Clique em "Verificar Autenticidade"</li>
                <li>Você também pode escanear o QR Code do documento com seu celular</li>
            </ul>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Voltar para a página inicial
            </a>
        </div>
    </div>
</div>
@endsection
