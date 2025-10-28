@extends('layouts.admin')

@section('title', 'Configurar Senha de Assinatura Digital')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">🔐 Senha de Assinatura Digital</h1>
            <p class="mt-2 text-sm text-gray-600">
                Configure uma senha específica para assinar documentos digitalmente. Esta senha é diferente da sua senha de login e garante a segurança das suas assinaturas.
            </p>
        </div>

        @if($usuario->temSenhaAssinatura())
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-medium text-green-800">Senha de assinatura já configurada</span>
                </div>
                <p class="mt-1 text-xs text-green-700 ml-7">Você pode alterá-la preenchendo o formulário abaixo.</p>
            </div>
        @else
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-medium text-yellow-800">Senha de assinatura não configurada</span>
                </div>
                <p class="mt-1 text-xs text-yellow-700 ml-7">Configure sua senha para poder assinar documentos digitalmente.</p>
            </div>
        @endif

        <form action="{{ route('admin.assinatura.salvar-senha') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Senha Atual (Login) --}}
            <div>
                <label for="senha_atual" class="block text-sm font-medium text-gray-700 mb-1">
                    Senha de Login Atual *
                </label>
                <input type="password" 
                       name="senha_atual" 
                       id="senha_atual"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('senha_atual') border-red-500 @enderror"
                       placeholder="Digite sua senha de login para confirmar"
                       required>
                <p class="mt-1 text-xs text-gray-500">
                    Digite sua senha de login do sistema para confirmar a operação
                </p>
                @error('senha_atual')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nova Senha de Assinatura --}}
            <div>
                <label for="senha_assinatura" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $usuario->temSenhaAssinatura() ? 'Nova Senha de Assinatura Digital *' : 'Senha de Assinatura Digital *' }}
                </label>
                <input type="password" 
                       name="senha_assinatura" 
                       id="senha_assinatura"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('senha_assinatura') border-red-500 @enderror"
                       placeholder="Digite a senha de assinatura (mínimo 6 caracteres)"
                       minlength="6"
                       required>
                <p class="mt-1 text-xs text-gray-500">
                    Mínimo de 6 caracteres. Esta senha será usada apenas para assinar documentos.
                </p>
                @error('senha_assinatura')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirmar Senha de Assinatura --}}
            <div>
                <label for="senha_assinatura_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmar Senha de Assinatura *
                </label>
                <input type="password" 
                       name="senha_assinatura_confirmation" 
                       id="senha_assinatura_confirmation"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Digite novamente a senha de assinatura"
                       minlength="6"
                       required>
                <p class="mt-1 text-xs text-gray-500">
                    Digite a mesma senha para confirmar
                </p>
            </div>

            {{-- Informações de Segurança --}}
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">ℹ️ Informações Importantes</h3>
                <ul class="text-xs text-blue-800 space-y-1 ml-4 list-disc">
                    <li>A senha de assinatura é diferente da sua senha de login</li>
                    <li>Você precisará desta senha toda vez que for assinar um documento</li>
                    <li>Guarde esta senha em local seguro</li>
                    <li>Não compartilhe sua senha de assinatura com ninguém</li>
                    <li>A senha é criptografada e não pode ser recuperada, apenas redefinida</li>
                </ul>
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <a href="{{ route('admin.dashboard') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300">
                    {{ $usuario->temSenhaAssinatura() ? 'Alterar Senha' : 'Configurar Senha' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
