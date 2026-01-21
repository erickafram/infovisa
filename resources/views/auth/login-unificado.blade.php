@extends('layouts.auth')

@section('title', 'Login - InfoVISA')

@section('content')
<div class="max-w-md mx-auto">
    {{-- Card de Login --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
        {{-- Logo e Título --}}
        <div class="text-center mb-6">
        
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Bem-vindo de volta</h1>
            <p class="text-gray-600 text-sm">Entre com suas credenciais para acessar o sistema</p>
        </div>

            {{-- Mensagens de Erro --}}
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 rounded-md p-3">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-red-800">Erro ao fazer login</h3>
                            <div class="mt-1 text-sm text-red-700">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Mensagem de Sucesso (se houver) --}}
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 rounded-md p-3">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- Formulário --}}
            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                {{-- CPF --}}
                <div class="mb-3">
                    <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">
                        CPF
                    </label>
                    <input 
                        type="text" 
                        id="cpf" 
                        name="cpf" 
                        value="{{ old('cpf') }}"
                        required
                        autofocus
                        maxlength="14"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cpf') border-red-300 @enderror"
                        placeholder="000.000.000-00"
                        oninput="formatCPF(this)"
                    >
                </div>

                {{-- Senha --}}
                <div class="mb-3">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Senha
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-300 @enderror"
                        placeholder="••••••••"
                    >
                </div>

                {{-- Lembrar-me e Esqueci a senha --}}
                <div class="flex items-center justify-between mb-4">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            id="remember"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <span class="ml-2 text-sm text-gray-600">Lembrar-me</span>
                    </label>

                    <a href="#" class="text-sm text-blue-600 hover:text-blue-500">
                        Esqueci minha senha
                    </a>
                </div>

                {{-- Botão de Entrar --}}
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2 transform hover:scale-[1.02]"
                >
                    <span>Entrar no Sistema</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </form>

            {{-- Link para cadastro --}}
             <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    {{-- Não tem uma conta? 
                    <a href="{{ route('registro') }}" class="text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                        Cadastre-se aqui
                    </a> --}}
                    Cadastro temporariamente desabilitado.
                </p>
            </div>
        </div>

        {{-- Link para home --}}
        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar para página inicial
            </a>
        </div>
    </div>
</div>

<script>
function formatCPF(input) {
    // Remove tudo que não é dígito
    let value = input.value.replace(/\D/g, '');
    
    // Aplica a máscara do CPF
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    }
    
    input.value = value;
}
</script>
@endsection
