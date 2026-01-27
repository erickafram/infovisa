@extends('layouts.company')

@section('title', 'Meu Perfil')
@section('page-title', 'Meu Perfil')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    
    {{-- Informações do Usuário --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center">
                    <span class="text-2xl font-bold text-white">{{ substr($usuario->nome, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $usuario->nome }}</h2>
                    <p class="text-blue-100 text-sm">CPF: {{ $usuario->cpf_formatado }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulário de Dados --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Dados de Contato</h3>
            <p class="text-sm text-gray-500">Atualize seu e-mail e telefone</p>
        </div>
        
        <form action="{{ route('company.perfil.update-dados') }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                <input type="text" id="nome" value="{{ $usuario->nome }}" disabled
                       class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-gray-500 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400">O nome não pode ser alterado</p>
            </div>

            <div>
                <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                <input type="text" id="cpf" value="{{ $usuario->cpf_formatado }}" disabled
                       class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-gray-500 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400">O CPF não pode ser alterado</p>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input type="text" name="telefone" id="telefone" value="{{ old('telefone', $usuario->telefone_formatado) }}" required
                       x-data x-mask="(99) 99999-9999"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('telefone') border-red-500 @enderror">
                @error('telefone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="pt-2">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    {{-- Formulário de Senha --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Alterar Senha</h3>
            <p class="text-sm text-gray-500">Mantenha sua conta segura</p>
        </div>
        
        <form action="{{ route('company.perfil.update-senha') }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label for="senha_atual" class="block text-sm font-medium text-gray-700 mb-1">Senha Atual</label>
                <input type="password" name="senha_atual" id="senha_atual" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('senha_atual') border-red-500 @enderror">
                @error('senha_atual')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="nova_senha" class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                <input type="password" name="nova_senha" id="nova_senha" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nova_senha') border-red-500 @enderror">
                @error('nova_senha')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-400">Mínimo de 8 caracteres</p>
            </div>
            
            <div>
                <label for="nova_senha_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
                <input type="password" name="nova_senha_confirmation" id="nova_senha_confirmation" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="pt-2">
                <button type="submit" class="px-6 py-2.5 bg-amber-600 text-white font-medium rounded-lg hover:bg-amber-700 transition">
                    Alterar Senha
                </button>
            </div>
        </form>
    </div>

    {{-- Informações Adicionais --}}
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-6">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Informações da Conta</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Cadastrado em:</span>
                <span class="text-gray-900 ml-1">{{ $usuario->created_at->format('d/m/Y') }}</span>
            </div>
            @if($usuario->aceite_termos_em)
            <div>
                <span class="text-gray-500">Termos aceitos em:</span>
                <span class="text-gray-900 ml-1">{{ $usuario->aceite_termos_em->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
