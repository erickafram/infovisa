@extends('layouts.admin')

@section('title', 'Novo Usuário Interno')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.usuarios-internos.index') }}" 
               class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Novo Usuário Interno</h1>
                <p class="text-sm text-gray-600 mt-1">Cadastre um novo usuário interno do sistema</p>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.usuarios-internos.store') }}" class="space-y-6">
            @csrf

            {{-- Informações Pessoais --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Informações Pessoais
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nome --}}
                    <div class="md:col-span-2">
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">
                            Nome Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nome" 
                               name="nome" 
                               value="{{ old('nome') }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror">
                        @error('nome')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- CPF --}}
                    <div>
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">
                            CPF <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="cpf" 
                               name="cpf" 
                               value="{{ old('cpf') }}"
                               placeholder="000.000.000-00"
                               maxlength="14"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cpf') border-red-500 @enderror">
                        @error('cpf')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               placeholder="email@exemplo.com"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Telefone --}}
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">
                            Telefone
                        </label>
                        <input type="text" 
                               id="telefone" 
                               name="telefone" 
                               value="{{ old('telefone') }}"
                               placeholder="(00) 00000-0000"
                               maxlength="20"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('telefone') border-red-500 @enderror">
                        @error('telefone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Informações Profissionais --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Informações Profissionais
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Matrícula --}}
                    <div>
                        <label for="matricula" class="block text-sm font-medium text-gray-700 mb-1">
                            Matrícula
                        </label>
                        <input type="text" 
                               id="matricula" 
                               name="matricula" 
                               value="{{ old('matricula') }}"
                               maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('matricula') border-red-500 @enderror">
                        @error('matricula')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Cargo --}}
                    <div>
                        <label for="cargo" class="block text-sm font-medium text-gray-700 mb-1">
                            Cargo
                        </label>
                        <input type="text" 
                               id="cargo" 
                               name="cargo" 
                               value="{{ old('cargo') }}"
                               maxlength="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cargo') border-red-500 @enderror">
                        @error('cargo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Município --}}
                    <div>
                        <label for="municipio" class="block text-sm font-medium text-gray-700 mb-1">
                            Município
                        </label>
                        <input type="text" 
                               id="municipio" 
                               name="municipio" 
                               value="{{ old('municipio') }}"
                               maxlength="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('municipio') border-red-500 @enderror">
                        @error('municipio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nível de Acesso --}}
                    <div>
                        <label for="nivel_acesso" class="block text-sm font-medium text-gray-700 mb-1">
                            Nível de Acesso <span class="text-red-500">*</span>
                        </label>
                        <select id="nivel_acesso" 
                                name="nivel_acesso" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nivel_acesso') border-red-500 @enderror">
                            <option value="">Selecione um nível</option>
                            @foreach(\App\Enums\NivelAcesso::cases() as $nivel)
                                <option value="{{ $nivel->value }}" {{ old('nivel_acesso') == $nivel->value ? 'selected' : '' }}>
                                    {{ $nivel->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('nivel_acesso')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500" id="nivel-descricao"></p>
                    </div>
                </div>
            </div>

            {{-- Segurança --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Segurança
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Senha --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Senha <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Mínimo de 8 caracteres</p>
                    </div>

                    {{-- Confirmar Senha --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmar Senha <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required
                               minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Status
                </h2>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="ativo" 
                           name="ativo" 
                           value="1"
                           {{ old('ativo', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="ativo" class="ml-2 text-sm font-medium text-gray-700">
                        Usuário ativo
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500">Usuários inativos não poderão acessar o sistema</p>
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.usuarios-internos.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Criar Usuário
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Máscara de CPF
    document.getElementById('cpf').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        }
    });

    // Máscara de Telefone
    document.getElementById('telefone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        }
    });

    // Descrição do nível de acesso
    const nivelSelect = document.getElementById('nivel_acesso');
    const nivelDescricao = document.getElementById('nivel-descricao');
    
    const descricoes = {
        'administrador': 'Acesso completo ao sistema, incluindo gestão de usuários',
        'gestor_estadual': 'Gestão de processos e estabelecimentos em nível estadual',
        'gestor_municipal': 'Gestão de processos e estabelecimentos em nível municipal',
        'tecnico_estadual': 'Análise técnica de processos em nível estadual',
        'tecnico_municipal': 'Análise técnica de processos em nível municipal'
    };

    nivelSelect.addEventListener('change', function() {
        nivelDescricao.textContent = descricoes[this.value] || '';
    });

    // Mostrar descrição inicial se houver valor selecionado
    if (nivelSelect.value) {
        nivelDescricao.textContent = descricoes[nivelSelect.value] || '';
    }
</script>
@endpush
@endsection
