@extends('layouts.auth')

@section('title', 'Cadastro de Usuário Externo')

@section('content')
<div x-data="{
    cpf: '',
    telefone: '',
    formatCpf() {
        let value = this.cpf.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.cpf = value;
        }
    },
    formatTelefone() {
        let value = this.telefone.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length === 11) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            } else {
                value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
            }
            this.telefone = value;
        }
    }
}" style="max-width: 700px; margin: 0 auto;">
    <!-- Card de Cadastro -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Criar Conta</h1>
            <p class="text-gray-600">Preencha seus dados para se cadastrar</p>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulário -->
        <form action="{{ route('registro.submit') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Nome Completo (linha inteira) -->
            <div>
                <label for="nome" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Nome Completo <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    value="{{ old('nome') }}"
                    required
                    class="w-full px-4 py-2.5 border-2 @error('nome') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Digite seu nome completo"
                >
                @error('nome')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- CPF e Email (duas colunas) -->
            <div class="grid grid-cols-2 gap-4">
                <!-- CPF -->
                <div>
                    <label for="cpf" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        CPF <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="cpf" 
                        name="cpf" 
                        x-model="cpf"
                        @input="formatCpf()"
                        value="{{ old('cpf') }}"
                        maxlength="14"
                        required
                        class="w-full px-4 py-2.5 border-2 @error('cpf') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="000.000.000-00"
                    >
                    @error('cpf')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        E-mail <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-2.5 border-2 @error('email') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="seu@email.com"
                    >
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Telefone e Vínculo (duas colunas) -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Telefone -->
                <div>
                    <label for="telefone" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Telefone <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="telefone" 
                        name="telefone" 
                        x-model="telefone"
                        @input="formatTelefone()"
                        value="{{ old('telefone') }}"
                        maxlength="15"
                        required
                        class="w-full px-4 py-2.5 border-2 @error('telefone') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="(00) 00000-0000"
                    >
                    @error('telefone')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Vínculo com Estabelecimento -->
                <div>
                    <label for="vinculo_estabelecimento" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Vínculo <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="vinculo_estabelecimento" 
                        name="vinculo_estabelecimento" 
                        required
                        class="w-full px-4 py-2.5 border-2 @error('vinculo_estabelecimento') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                        <option value="">Selecione...</option>
                        @foreach($vinculos as $value => $label)
                            <option value="{{ $value }}" {{ old('vinculo_estabelecimento') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('vinculo_estabelecimento')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Senha e Confirmar Senha (duas colunas) -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Senha -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Senha <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-2.5 border-2 @error('password') border-red-300 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Mínimo 8 caracteres"
                    >
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Confirmar Senha <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                        class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Digite novamente"
                    >
                </div>
            </div>

            <!-- Info sobre senha -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-xs text-blue-800">
                    <strong>Senha:</strong> Mínimo 8 caracteres incluindo letras.
                </p>
            </div>

            <!-- Aceite de Termos -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input 
                        id="aceite_termos" 
                        name="aceite_termos" 
                        type="checkbox" 
                        required
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 @error('aceite_termos') border-red-300 @enderror"
                    >
                </div>
                <div class="ml-3">
                    <label for="aceite_termos" class="text-sm text-gray-700">
                        Li e aceito os <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold">Termos e Condições de Uso</a> <span class="text-red-500">*</span>
                    </label>
                    @error('aceite_termos')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botão de Submit -->
            <button 
                type="submit" 
                class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
            >
                <span>Criar Conta</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>
        </form>

        <!-- Link para Login -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Já tem uma conta? 
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                    Faça login
                </a>
            </p>
        </div>
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start space-x-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-sm text-blue-900 font-semibold mb-1">Cadastro de Usuário Externo</p>
                <p class="text-xs text-blue-800">
                    Este cadastro é destinado a proprietários, responsáveis técnicos e legais de estabelecimentos. 
                    Após o cadastro, você poderá vincular seus estabelecimentos e acompanhar processos.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
