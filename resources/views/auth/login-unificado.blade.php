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
                    Não tem uma conta? 
                    <a href="{{ route('registro') }}" class="text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                        Cadastre-se aqui
                    </a>
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

{{-- Modal de Verificação de CPF --}}
<div id="modalVerificacaoCPF" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
        <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Criar Nova Conta</h3>
            <p class="text-sm text-gray-600">Digite seu CPF para iniciar o cadastro como usuário externo</p>
        </div>

        <div class="mb-6">
            <label for="cpf_verificacao" class="block text-sm font-medium text-gray-700 mb-2">
                CPF
            </label>
            <input 
                type="text" 
                id="cpf_verificacao" 
                maxlength="14"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="000.000.000-00"
                oninput="formatCPF(this)"
            >
            <p id="mensagemVerificacao" class="mt-2 text-sm hidden"></p>
        </div>

        <div class="flex gap-3">
            <button 
                type="button"
                onclick="fecharModal()"
                class="flex-1 px-4 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors"
            >
                Cancelar
            </button>
            <button 
                type="button"
                onclick="verificarCPF()"
                class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
            >
                Verificar
            </button>
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

function verificarCPFCadastro() {
    document.getElementById('modalVerificacaoCPF').classList.remove('hidden');
    document.getElementById('cpf_verificacao').focus();
}

function fecharModal() {
    document.getElementById('modalVerificacaoCPF').classList.add('hidden');
    document.getElementById('cpf_verificacao').value = '';
    document.getElementById('mensagemVerificacao').classList.add('hidden');
}

function verificarCPF() {
    const cpfInput = document.getElementById('cpf_verificacao');
    const cpf = cpfInput.value.replace(/\D/g, '');
    const mensagem = document.getElementById('mensagemVerificacao');
    
    if (!cpf || cpf.length !== 11) {
        mensagem.textContent = 'Por favor, digite um CPF válido.';
        mensagem.className = 'mt-2 text-sm text-red-600';
        mensagem.classList.remove('hidden');
        return;
    }
    
    // Validação básica de CPF
    if (!validarCPF(cpf)) {
        mensagem.textContent = 'CPF inválido. Por favor, verifique o número digitado.';
        mensagem.className = 'mt-2 text-sm text-red-600';
        mensagem.classList.remove('hidden');
        return;
    }
    
    // CPF válido - redirecionar para cadastro
    mensagem.textContent = '✓ CPF válido! Redirecionando para o cadastro...';
    mensagem.className = 'mt-2 text-sm text-green-600 font-semibold';
    mensagem.classList.remove('hidden');
    
    setTimeout(() => {
        window.location.href = '{{ route("registro") }}?cpf=' + cpfInput.value;
    }, 1000);
}

function validarCPF(cpf) {
    // Remove caracteres não numéricos
    cpf = cpf.replace(/\D/g, '');
    
    // Verifica se tem 11 dígitos
    if (cpf.length !== 11) return false;
    
    // Verifica se todos os dígitos são iguais
    if (/^(\d)\1+$/.test(cpf)) return false;
    
    // Validação do primeiro dígito verificador
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(9))) return false;
    
    // Validação do segundo dígito verificador
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.charAt(10))) return false;
    
    return true;
}

// Permitir verificar com Enter
document.addEventListener('DOMContentLoaded', function() {
    const cpfInput = document.getElementById('cpf_verificacao');
    if (cpfInput) {
        cpfInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                verificarCPF();
            }
        });
    }
});
</script>
@endsection
