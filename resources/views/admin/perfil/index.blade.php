@extends('layouts.admin')

@section('title', 'Meu Perfil')
@section('page-title', 'Meu Perfil')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
            <span class="text-white font-bold text-2xl">
                {{ strtoupper(substr($usuario->nome, 0, 2)) }}
            </span>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $usuario->nome }}</h2>
            <p class="text-sm text-gray-600">{{ $usuario->nivel_acesso->label() }}</p>
            @if($usuario->municipio)
                <p class="text-xs text-gray-500">{{ $usuario->municipio }}</p>
            @endif
        </div>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- Dados Pessoais --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Dados Pessoais
            </h3>
        </div>

        <form action="{{ route('admin.perfil.update-dados') }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nome Completo --}}
                <div class="md:col-span-2">
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
                    <input type="text" 
                           id="nome" 
                           name="nome" 
                           value="{{ old('nome', $usuario->nome) }}"
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror">
                    @error('nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- E-mail --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail *</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $usuario->email) }}"
                           required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Telefone --}}
                <div>
                    <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                    <input type="text" 
                           id="telefone" 
                           name="telefone" 
                           value="{{ old('telefone', $usuario->telefone_formatado) }}"
                           x-data
                           x-mask="(99) 99999-9999"
                           placeholder="(00) 00000-0000"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('telefone') border-red-500 @enderror">
                    @error('telefone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CPF (somente leitura) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CPF</label>
                    <input type="text" 
                           value="{{ $usuario->cpf_formatado }}"
                           disabled
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500">O CPF não pode ser alterado</p>
                </div>

                {{-- Matrícula (somente leitura) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Matrícula</label>
                    <input type="text" 
                           value="{{ $usuario->matricula ?? '-' }}"
                           disabled
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                </div>

                {{-- Cargo (somente leitura) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cargo</label>
                    <input type="text" 
                           value="{{ $usuario->cargo ?? '-' }}"
                           disabled
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                </div>

                {{-- Setor (somente leitura) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Setor</label>
                    <input type="text" 
                           value="{{ $usuario->setor ?? '-' }}"
                           disabled
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    {{-- Alterar Senha --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Alterar Senha de Acesso
            </h3>
        </div>

        <form action="{{ route('admin.perfil.update-senha') }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Senha Atual --}}
                <div>
                    <label for="senha_atual" class="block text-sm font-medium text-gray-700 mb-2">Senha Atual *</label>
                    <input type="password" 
                           id="senha_atual" 
                           name="senha_atual" 
                           required
                           autocomplete="current-password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('senha_atual') border-red-500 @enderror">
                    @error('senha_atual')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nova Senha --}}
                <div>
                    <label for="nova_senha" class="block text-sm font-medium text-gray-700 mb-2">Nova Senha *</label>
                    <input type="password" 
                           id="nova_senha" 
                           name="nova_senha" 
                           required
                           autocomplete="new-password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nova_senha') border-red-500 @enderror">
                    @error('nova_senha')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Mínimo de 8 caracteres</p>
                </div>

                {{-- Confirmar Nova Senha --}}
                <div>
                    <label for="nova_senha_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nova Senha *</label>
                    <input type="password" 
                           id="nova_senha_confirmation" 
                           name="nova_senha_confirmation" 
                           required
                           autocomplete="new-password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 focus:ring-4 focus:ring-amber-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Alterar Senha
                </button>
            </div>
        </form>
    </div>

    {{-- Informações da Conta --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Informações da Conta
            </h3>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Nível de Acesso</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $usuario->nivel_acesso->label() }}</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Status</p>
                    <p class="mt-1">
                        @if($usuario->ativo)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Ativo
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Inativo
                            </span>
                        @endif
                    </p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Assinatura Digital</p>
                    <p class="mt-1">
                        @if($usuario->temSenhaAssinatura())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Configurada
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Não configurada
                            </span>
                        @endif
                    </p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Cadastrado em</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ $usuario->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
