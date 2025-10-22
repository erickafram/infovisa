@extends('layouts.admin')

@section('title', 'Cadastrar Pessoa Física')
@section('page-title', 'Cadastrar Pessoa Física')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Cadastrar Pessoa Física</h1>
                <p class="text-sm text-gray-600 mt-1">Preencha os dados do estabelecimento de pessoa física</p>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <form method="POST" action="{{ route('admin.estabelecimentos.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="tipo_pessoa" value="fisica">

        {{-- Dados Pessoais --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dados Pessoais</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Nome Completo --}}
                <div class="md:col-span-2">
                    <label for="nome_completo" class="block text-sm font-medium text-gray-700 mb-1">
                        Nome Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nome_completo" 
                           name="nome_completo"
                           value="{{ old('nome_completo') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('nome_completo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
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
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('cpf')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nome Fantasia --}}
                <div>
                    <label for="nome_fantasia" class="block text-sm font-medium text-gray-700 mb-1">
                        Nome Fantasia <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nome_fantasia" 
                           name="nome_fantasia"
                           value="{{ old('nome_fantasia') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('nome_fantasia')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Endereço --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Endereço</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- CEP --}}
                <div>
                    <label for="cep" class="block text-sm font-medium text-gray-700 mb-1">
                        CEP <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="cep" 
                           name="cep"
                           value="{{ old('cep') }}"
                           placeholder="00000-000"
                           maxlength="9"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('cep')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Logradouro --}}
                <div class="md:col-span-2">
                    <label for="endereco" class="block text-sm font-medium text-gray-700 mb-1">
                        Logradouro <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="endereco" 
                           name="endereco"
                           value="{{ old('endereco') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('endereco')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Número --}}
                <div>
                    <label for="numero" class="block text-sm font-medium text-gray-700 mb-1">
                        Número <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="numero" 
                           name="numero"
                           value="{{ old('numero') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('numero')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Complemento --}}
                <div>
                    <label for="complemento" class="block text-sm font-medium text-gray-700 mb-1">
                        Complemento
                    </label>
                    <input type="text" 
                           id="complemento" 
                           name="complemento"
                           value="{{ old('complemento') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('complemento')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bairro --}}
                <div>
                    <label for="bairro" class="block text-sm font-medium text-gray-700 mb-1">
                        Bairro <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="bairro" 
                           name="bairro"
                           value="{{ old('bairro') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('bairro')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cidade --}}
                <div>
                    <label for="cidade" class="block text-sm font-medium text-gray-700 mb-1">
                        Cidade <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="cidade" 
                           name="cidade"
                           value="{{ old('cidade') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('cidade')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Estado --}}
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="estado" 
                           name="estado"
                           value="{{ old('estado') }}"
                           maxlength="2"
                           placeholder="TO"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('estado')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Contato e Informações Adicionais --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Contato e Informações Adicionais</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('telefone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        E-mail
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email"
                           value="{{ old('email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipo de Estabelecimento --}}
                <div>
                    <label for="tipo_estabelecimento" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Estabelecimento <span class="text-red-500">*</span>
                    </label>
                    <select id="tipo_estabelecimento" 
                            name="tipo_estabelecimento"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione...</option>
                        <option value="restaurante" {{ old('tipo_estabelecimento') === 'restaurante' ? 'selected' : '' }}>Restaurante</option>
                        <option value="bar" {{ old('tipo_estabelecimento') === 'bar' ? 'selected' : '' }}>Bar</option>
                        <option value="lanchonete" {{ old('tipo_estabelecimento') === 'lanchonete' ? 'selected' : '' }}>Lanchonete</option>
                        <option value="supermercado" {{ old('tipo_estabelecimento') === 'supermercado' ? 'selected' : '' }}>Supermercado</option>
                        <option value="mercearia" {{ old('tipo_estabelecimento') === 'mercearia' ? 'selected' : '' }}>Mercearia</option>
                        <option value="padaria" {{ old('tipo_estabelecimento') === 'padaria' ? 'selected' : '' }}>Padaria</option>
                        <option value="acougue" {{ old('tipo_estabelecimento') === 'acougue' ? 'selected' : '' }}>Açougue</option>
                        <option value="farmacia" {{ old('tipo_estabelecimento') === 'farmacia' ? 'selected' : '' }}>Farmácia</option>
                        <option value="hospital" {{ old('tipo_estabelecimento') === 'hospital' ? 'selected' : '' }}>Hospital</option>
                        <option value="clinica" {{ old('tipo_estabelecimento') === 'clinica' ? 'selected' : '' }}>Clínica</option>
                        <option value="laboratorio" {{ old('tipo_estabelecimento') === 'laboratorio' ? 'selected' : '' }}>Laboratório</option>
                        <option value="pet_shop" {{ old('tipo_estabelecimento') === 'pet_shop' ? 'selected' : '' }}>Pet Shop</option>
                        <option value="outros" {{ old('tipo_estabelecimento') === 'outros' ? 'selected' : '' }}>Outros</option>
                    </select>
                    @error('tipo_estabelecimento')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Inscrição Estadual --}}
                <div>
                    <label for="inscricao_estadual" class="block text-sm font-medium text-gray-700 mb-1">
                        Inscrição Estadual
                    </label>
                    <input type="text" 
                           id="inscricao_estadual" 
                           name="inscricao_estadual"
                           value="{{ old('inscricao_estadual') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('inscricao_estadual')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Atividade Principal --}}
            <div class="mt-4">
                <label for="atividade_principal" class="block text-sm font-medium text-gray-700 mb-1">
                    Atividade Principal
                </label>
                <textarea id="atividade_principal" 
                          name="atividade_principal"
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('atividade_principal') }}</textarea>
                @error('atividade_principal')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex justify-between pt-6">
            <a href="{{ route('admin.estabelecimentos.index') }}" 
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            
            <button type="submit" 
                    class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                Cadastrar Estabelecimento
            </button>
        </div>
    </form>
</div>
@endsection
