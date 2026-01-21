@extends('layouts.company')

@section('title', 'Editar Estabelecimento')
@section('page-title', 'Editar Estabelecimento')

@section('content')
<div class="max-w-8xl mx-auto" x-data="estabelecimentoEdit()">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" 
               class="text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Editar Estabelecimento</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $estabelecimento->nome_fantasia }}</p>
            </div>
        </div>

        @if($estabelecimento->tipo_pessoa === 'juridica')
        <button @click="atualizarPelaApi()" 
                :disabled="loading"
                type="button"
                class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-900 bg-gradient-to-r from-green-100 to-green-200 rounded-lg hover:from-green-200 hover:to-green-300 shadow-sm hover:shadow transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span x-text="loading ? 'Atualizando...' : 'Atualizar pela API'"></span>
        </button>
        @endif
    </div>

    {{-- Mensagem de Feedback --}}
    <div x-show="mensagem" 
         x-transition
         :class="{
             'bg-green-50 border-green-200 text-green-800': tipoMensagem === 'success',
             'bg-red-50 border-red-200 text-red-800': tipoMensagem === 'error',
             'bg-blue-50 border-blue-200 text-blue-800': tipoMensagem === 'info'
         }"
         class="mb-6 p-4 rounded-lg border"
         x-cloak>
        <p class="text-sm font-medium" x-text="mensagem"></p>
    </div>

    <form method="POST" action="{{ route('company.estabelecimentos.update', $estabelecimento->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Dados Gerais --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Dados Gerais
                </h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($estabelecimento->tipo_pessoa === 'juridica')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CNPJ</label>
                        <input type="text" readonly value="{{ $estabelecimento->documento_formatado }}"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Razão Social</label>
                        <input type="text" name="razao_social" x-model="formData.razao_social"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CPF</label>
                        <input type="text" readonly value="{{ $estabelecimento->documento_formatado }}"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
                        <input type="text" name="nome_completo" x-model="formData.nome_completo"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @endif

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome Fantasia *</label>
                        <input type="text" name="nome_fantasia" x-model="formData.nome_fantasia" required
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        {{-- Endereço --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    Endereço
                </h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                        <input type="text" name="cep" x-model="formData.cep" maxlength="9"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logradouro</label>
                        <input type="text" name="endereco" x-model="formData.endereco"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Número</label>
                        <input type="text" name="numero" x-model="formData.numero"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Complemento</label>
                        <input type="text" name="complemento" x-model="formData.complemento"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
                        <input type="text" name="bairro" x-model="formData.bairro"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Município</label>
                        <input type="text" readonly x-model="formData.cidade"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <input type="text" readonly x-model="formData.estado"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                    </div>
                </div>
            </div>
        </div>

        {{-- Contatos --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Contatos
                </h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telefone *</label>
                        <input type="text" name="telefone" x-model="formData.telefone" required
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">E-mail *</label>
                        <input type="email" name="email" x-model="formData.email" required
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-3 bg-gray-50 rounded-lg p-4 border border-gray-200">
            <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" 
               class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition-all">
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
function estabelecimentoEdit() {
    return {
        loading: false,
        mensagem: '',
        tipoMensagem: 'info',
        formData: {
            razao_social: '{{ old('razao_social', $estabelecimento->razao_social) }}',
            nome_completo: '{{ old('nome_completo', $estabelecimento->nome_completo) }}',
            nome_fantasia: '{{ old('nome_fantasia', $estabelecimento->nome_fantasia) }}',
            cep: '{{ old('cep', $estabelecimento->cep) }}',
            endereco: '{{ old('endereco', $estabelecimento->endereco) }}',
            numero: '{{ old('numero', $estabelecimento->numero) }}',
            complemento: '{{ old('complemento', $estabelecimento->complemento) }}',
            bairro: '{{ old('bairro', $estabelecimento->bairro) }}',
            cidade: '{{ old('cidade', $estabelecimento->cidade) }}',
            estado: '{{ old('estado', $estabelecimento->estado) }}',
            telefone: '{{ old('telefone', $estabelecimento->telefone) }}',
            email: '{{ old('email', $estabelecimento->email) }}'
        },

        async atualizarPelaApi() {
            if (!confirm('Deseja atualizar os dados consultando a API da Receita Federal?')) {
                return;
            }

            this.loading = true;
            this.mensagem = '';

            try {
                const cnpj = '{{ $estabelecimento->cnpj }}';
                
                const response = await fetch('{{ url("/api/consultar-cnpj") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ cnpj })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    this.formData.razao_social = result.data.razao_social || this.formData.razao_social;
                    this.formData.nome_fantasia = result.data.nome_fantasia || this.formData.nome_fantasia;
                    this.formData.cep = result.data.cep || this.formData.cep;
                    this.formData.endereco = result.data.endereco || this.formData.endereco;
                    this.formData.numero = result.data.numero || this.formData.numero;
                    this.formData.complemento = result.data.complemento || this.formData.complemento;
                    this.formData.bairro = result.data.bairro || this.formData.bairro;
                    this.formData.telefone = result.data.telefone || this.formData.telefone;
                    this.formData.email = result.data.email || this.formData.email;

                    this.mostrarMensagem('Dados atualizados! Revise e clique em "Salvar Alterações".', 'success');
                } else {
                    this.mostrarMensagem(result.message || 'Erro ao consultar CNPJ', 'error');
                }
            } catch (error) {
                this.mostrarMensagem('Erro ao comunicar com a API.', 'error');
            } finally {
                this.loading = false;
            }
        },

        mostrarMensagem(texto, tipo) {
            this.mensagem = texto;
            this.tipoMensagem = tipo;
            setTimeout(() => { this.mensagem = ''; }, 8000);
        }
    }
}
</script>
@endsection
